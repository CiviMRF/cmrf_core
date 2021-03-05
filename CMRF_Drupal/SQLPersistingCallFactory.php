<?php

/**
 * Drupal specific instance of SQLPersistingCallFactory. The reason for this is that upon a cache clear
 * we wanted to clear all cached calls no matter whether it is not yet expired.
 *
 * @author Jaap Jansma (jaap.jansma@civicoop.org)
 */

namespace CMRF\Drupal;

use CMRF\Core\AbstractCall;
use CMRF\PersistenceLayer\CallFactory;

class SQLPersistingCallFactory extends CallFactory {

  /** @var string */
  protected $table_name;

  static function schema() {
    return array(
      'description' => 'CMRF CiviCRM integration API calls',
      'fields' => array(
        'cid' => array(
          'type' => 'serial',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'description' => 'Call ID',
        ),
        'status' => array(
          'description' => 'Status',
          'type' => 'varchar',
          'length' => 8,
          'not null' => TRUE,
          'default' => 'INIT',
        ),
        'connector_id' => array(
          'description' => 'Connector ID',
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
        ),
        'request' => array(
          'description' => 'The request data sent',
          'type' => 'text',
          'size' => 'big',
          'serialize' => FALSE,
          'not null' => FALSE,
        ),
        'reply' => array(
          'description' => 'The reply data received',
          'type' => 'text',
          'size' => 'big',
          'serialize' => FALSE,
          'not null' => FALSE,
        ),
        'metadata' => array(
          'description' => 'Custom metadata on the request',
          'type' => 'text',
          'serialize' => FALSE,
          'not null' => FALSE,
        ),
        'request_hash' => array(
          'type' => 'varchar',
          'length' => 255,
          'not null' => TRUE,
          'default' => '',
          'description' => 'SHA1 hash of the request, enables quick lookups for caches',
        ),
        'create_date' => array(
          'type' => NULL,
          'mysql_type' => 'timestamp',
          'not null' => TRUE,
          'description' => 'Creation timestamp of this call',
        ),
        'scheduled_date' => array(
          'type' => NULL,
          'mysql_type' => 'timestamp',
          'not null' => FALSE,
          'description' => 'Scheduted timestamp of this call',
        ),
        'reply_date' => array(
          'type' => NULL,
          'mysql_type' => 'timestamp',
          'not null' => FALSE,
          'description' => 'Reply timestamp of this call',
        ),
        'cached_until' => array(
          'type' => NULL,
          'mysql_type' => 'timestamp',
          'not null' => FALSE,
          'description' => 'Cache timeout of this call',
        ),
        'retry_count' => array(
          'description' => 'Retry counter for multiple submissions',
          'type' => 'int',
          'size' => 'tiny',
          'not null' => TRUE,
          'default' => 0,
        ),
      ),
      'indexes' => array(
        'cmrf_by_connector'  => array('connector_id', 'status'),
        'cmrf_cache_index'   => array('connector_id', 'request_hash', 'cached_until'),
      ),
      'primary key' => array('cid'),
    );
  }

  public function __construct($table_name, callable $constructor, callable $loader) {
    parent::__construct($constructor, $loader);
    $this->table_name=$table_name;
  }

  /** @return \CMRF\Core\Call */
  public function createOrFetch($connector_id, $core, $entity, $action, $parameters, $options, $callback) {
    if(!empty($options['cache'])) {
      $today = new \DateTime();
      $hash = AbstractCall::getHashFromParams($entity,$action,$parameters,$options);

      $result = db_query("SELECT * FROM {$this->table_name} WHERE request_hash = :hash and connector_id = :cid and cached_until > :cache_timeout limit 1", array(
        ':hash' => $hash,
        ':cid' => $connector_id,
        ':cache_timeout' => $today->format('Y-m-d H:i:s'),
      ));

      $dataset=$result->fetchAssoc();
      if($dataset != NULL) {
        return $this->call_load($connector_id,$core,$this->toObject($dataset));
      }
    }

    /** @var \CMRF\Core\Call $call */
    $call=$this->call_construct($connector_id,$core,$entity,$action,$parameters,$options,$callback);
    db_insert($this->table_name)->fields(array(
      'status' => $call->getStatus(),
      'connector_id' => $call->getConnectorID(),
      'request' => json_encode($call->getRequest()),
      'metadata' => json_encode($call->getMetadata()),
      'request_hash' => $call->getHash(),
      'create_date' => date('Y-m-d H:i:s'),
      'scheduled_date' => $call->getScheduledDate() ? $call->getScheduledDate()->format('Y-m-d H:i:s') : null,
    ))->execute();
    $call->setID(\Database::getConnection()->lastInsertId());

    return $call;
  }

  public function update(\CMRF\Core\Call $call) {
    $id=$call->getID();
    if(empty($id)) {
      throw new \Exception("Unpersisted call given out to update. This won't work.");
    }
    else {
      db_update($this->table_name)->fields(array(
        'status' => $call->getStatus(),
        'reply' => json_encode($call->getReply()),
        'reply_date' => $call->getReplyDate() ? $call->getReplyDate()->format('Y-m-d H:i:s') : null,
        'scheduled_date' => $call->getScheduledDate() ? $call->getScheduledDate()->format('Y-m-d H:i:s') : null,
        'cached_until' => $call->getCachedUntil() ? $call->getCachedUntil()->format('Y-m-d H:i:s') : null,
        'retry_count' => $call->getRetryCount(),
      ))->condition('cid', $id, '=')->execute();
    }

  }

  public function purgeCachedCalls() {
    db_query("delete from {$this->table_name} where status = 'DONE' and (cached_until < NOW() or cached_until is NULL)");

    $core = cmrf_core_get_core();
    $connectors = $core->getRegisteredConnectors();
    foreach($connectors as $connector) {
      $profile = $core->getConnectionProfile($connector['id']);
      if ($profile['cache_expire_days'] > 0) {
        $today = new \DateTime();
        $today->modify('-'.$profile['cache_expire_days'].' days');
        db_query("delete from {$this->table_name} where DATE(`create_date`) < '".$today->format('Y-m-d')."' AND `connector_id` = '".$connector['id']."'");
      }
    }

  }

  /**
   * Returns the queued calls which are ready for processing.
   *
   * @return array
   *   The array consists of the call ids
   */
  public function getQueuedCallIds() {
    $call_ids = array();
    $result = db_query("
      select cid from {$this->table_name}
      where (status = 'INIT' OR status = 'RETRY')
      and (DATE(scheduled_date) < NOW() or scheduled_date is NULL)
      ORDER BY scheduled_date ASC");
    if ($result) {
      while ($dataset = $result->fetchAssoc()) {
        $call_ids[] = $dataset['cid'];
      }
    }
    return $call_ids;
  }

  public function loadCall($call_id,$core) {
    $result = db_query("select * from {$this->table_name} where cid = :cid limit 1", array(
      ':cid' => $call_id,
    ));
    $dataset=$result->fetchAssoc($result);
    if($dataset != NULL) {
      return $this->call_load($dataset->connector_id,$core,$this->toObject($dataset));
    }
  }

  public function findCall($options,$core) {
    //TODO: not yet implemented, as options is not yet known.
    return parent::findCall($options); // TODO: Change the autogenerated stub
  }

  public function clearCachedCalls() {
    if (isset($this->connection)) {
      db_query("delete from {$this->table_name} where status = 'DONE'");
    }
  }

  /**
   * @param $record
   *
   * @return \stdClass
   */
  private function toObject($record) {
    // Create new stdClass object
    $object = new \stdClass();

    // Use loop to convert array into
    // stdClass object
    foreach ($record as $key => $value) {
      if (is_array($value)) {
        $value = $this->toObject($value);
      }
      $object->$key = $value;
    }
    return $object;
  }

}
