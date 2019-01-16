<?php

/**
 * Drupal specific instance of SQLPersistingCallFactory. The reason for this is that upon a cache clear 
 * we wanted to clear all cached calls no matter whether it is not yet expired.
 * 
 * @author Jaap Jansma (jaap.jansma@civicoop.org)
 */

namespace CMRF\Drupal;

class SQLPersistingCallFactory extends \CMRF\PersistenceLayer\SQLPersistingCallFactory {
  
  public function clearCachedCalls() {
    if (isset($this->connection)) {
      $this->connection->query("delete from {$this->table_name} where status = 'DONE'");
    }
  }
  
}
