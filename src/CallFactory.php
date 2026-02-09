<?php


namespace Drupal\cmrf_core;

use CMRF\PersistenceLayer\CallFactory as AbstractCallFactory;

class CallFactory extends AbstractCallFactory {

  /**
   * @var \Drupal\cmrf_core\Core;
   */
  private $core;

  protected $table_name;

  public function __construct(callable $constructor, callable $loader)
  {
    $this->table_name = trim(\Drupal::database()->prefixTables("{civicrm_api_call}"), '"');
    parent::__construct($constructor, $loader);

  }


  public function createOrFetch($connector_id, $core, $entity, $action, $parameters, $options, $callback, string $api_version = '3') {
    /** @var \CMRF\Core\Call $call */
    $call = parent::createOrFetch($connector_id, $core, $entity, $action, $parameters, $options, $callback, $api_version);

    /** @var \Drupal\Core\Database\Connection $connection */
    $connection = \Drupal::service('database');
    if (!empty($options['cache'])) {
      $today = new \DateTime();
      $today = $today->format('Y-m-d H:i:s');
      $hash = $call->getHash();

      $stmnt = $connection->query("SELECT * FROM `" . $this->table_name . "` WHERE `request_hash` = ? AND `connector_id` = ? AND `cached_until` >= ? ORDER BY `cid` ASC LIMIT 1", array($hash, $connector_id, $today));
      $dataset = $stmnt->fetchObject();
      if ($dataset != NULL) {
        return $this->call_load($connector_id, $core, $dataset);
      }
    }

    $query = "INSERT INTO `{$this->table_name}` (`status`,`connector_id`,`entity`,`action`,`request`,`metadata`,`request_hash`,`create_date`,`scheduled_date`, `duration`) VALUES (?,?,?,?,?,?,?,?,?,?)";
    $status = $call->getStatus();
    $connectorID=$call->getConnectorID();
    $entity=$call->getEntity();
    $action=$call->getAction();
    $request=json_encode($call->getRequest());
    $metadata=json_encode($call->getMetadata());
    $hash=$call->getHash();
    $date=date('Y-m-d H:i:s');
    $duration = $call->getDuration();
    $scheduled_date = NULL;
    if($call->getScheduledDate() != NULL) {
      $scheduled_date=$call->getScheduledDate()->format('Y-m-d H:i:s');
    }

    $connection->query($query, [$status,$connectorID,$entity,$action,$request,$metadata,$hash,$date, $scheduled_date, $duration]);
    $call->setID($connection->lastInsertId());

    return $call;
  }

  public function update(\CMRF\Core\Call $call) {
    $id=$call->getID();
    if(empty($id)) {
      throw new \Exception("Unpersisted call given out to update. This won't work.");
    }
    else {
      /** @var \Drupal\Core\Database\Connection $connection */
      $connection = \Drupal::service('database');
      $cache_date=null;
      if ($call->getCachedUntil()) {
        $cache_date=$call->getCachedUntil()->format('Y-m-d H:i:s');
      }
      $status=$call->getStatus();
      $reply=json_encode($call->getReply());
      $reply_date = NULL;
      if($call->getReplyDate() != NULL) {
        $reply_date=$call->getReplyDate()->format('Y-m-d H:i:s');
      }
      $scheduled_date = NULL;
      if($call->getScheduledDate() != NULL) {
        $scheduled_date=$call->getScheduledDate()->format('Y-m-d H:i:s');
      }
      $retrycount=$call->getRetryCount();
      $duration = $call->getDuration();
      $connection->query("UPDATE `{$this->table_name}` set `status`=?,`reply`=?,`reply_date`=?,`scheduled_date`=?,`cached_until`=?,`retry_count`=?, `duration`=? where `cid`=?", [$status,$reply,$reply_date,$scheduled_date,$cache_date,$retrycount,$duration,$id]);
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
    $result = \Drupal::database()->query("
      select cid from {$this->table_name}
      where (status = 'INIT' OR status = 'RETRY')
      and (DATE(scheduled_date) < NOW() or scheduled_date is NULL)
      ORDER BY scheduled_date ASC");
    if ($result) {
      while ($dataset = $result->fetch_object()) {
        $call_ids[] = $dataset->cid;
      }
    }
    return $call_ids;
  }

  public function loadCall($call_id,$core) {
    $stmt = \Drupal::database()->query("SELECT * FROM `".$this->table_name."` WHERE `cid` = ? LIMIT 1", [$call_id]);
    $dataset=$stmt->fetchObject();
    if($dataset != NULL) {
      return $this->call_load($dataset->connector_id,$core,$dataset);
    }
  }

  public function purgeCachedCalls() {
    parent::purgeCachedCalls();
    \Drupal::database()->query("DELETE FROM `". $this->table_name . "` WHERE `status` = 'DONE' and (`cached_until` < NOW() OR `cached_until` is NULL)");
    foreach ($this->core->getConnectors() as $connector_id => $connector) {
      $profile = $this->core->getConnectionProfile($connector_id)??['cache_expire_days'=>0, 'cache_clear_failed_api_calls'=>''];
      if ($profile['cache_expire_days'] > 0) {
        $today = new \DateTime();
        $today->modify('-' . $profile['cache_expire_days'] . ' days');
        $sql = "DELETE from {$this->table_name}"
          . " WHERE DATE(`create_date`) < '" . $today->format('Y-m-d') . "'"
          . " AND `connector_id` = '" . $connector_id . "'";
        \Drupal::database()->query($sql);
      }
      $clearFailedApiCalls = explode("\n", $profile['cache_clear_failed_api_calls'] ?? '');
      foreach ($clearFailedApiCalls as $clearFailedApiCall) {
        if (!empty($clearFailedApiCall)) {
          list($apiEntity, $apiAction) = explode(".", $clearFailedApiCall, 2);
          if (!empty($apiEntity) && !empty($apiAction)) {
            $sql = "DELETE from {$this->table_name}"
              . " WHERE `status` = 'FAIL' "
              . " AND `request` LIKE '%\"entity\":\"".$apiEntity."\"%'"
              . " AND `request` LIKE '%\"action\":\"".$apiAction."\"%'"
              . " AND `connector_id` = '" . $connector_id . "'";
            \Drupal::database()->query($sql);
          }
        }
      }
    }
  }

  /**
   * @return \Drupal\cmrf_core\Core
   */
  public function getCore(): Core {
    return $this->core;
  }

  /**
   * @param \Drupal\cmrf_core\Core $core
   */
  public function setCore(Core $core): void {
    $this->core = $core;
  }

}
