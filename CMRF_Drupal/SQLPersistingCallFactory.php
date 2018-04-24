<?php

/**
 * Drupal specific instance of SQLPersistingCallFactory. The reason for this is that upon a cache clear 
 * we wanted to clear all cached calls no matter whether it is not yet expired.
 * 
 * @author Jaap Jansma (jaap.jansma@civicoop.org)
 */

namespace CMRF\Drupal;

use mysqli;

class SQLPersistingCallFactory extends \CMRF\PersistenceLayer\SQLPersistingCallFactory {
  
  /** @var mysqli */
  private $connection;

  public function __construct(mysqli $sql_connection, $table_name, callable $constructor, callable $loader) {
    parent::__construct($sql_connection, $table_name, $constructor, $loader);
    $this->connection=$sql_connection;
  }
  
  public function clearCachedCalls() {
    $stmt = $this->connection->query("delete from {$this->table_name} where status = 'DONE'");
  }
  
}
