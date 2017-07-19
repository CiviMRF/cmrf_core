<?php

/**
 * Drupal-based implementation of a CMRF Core
 *
 * @author Björn Endres, SYSTOPIA (endres@systopia.de)
 */

namespace CMRF\Drupal;

include_once('Call.php');

use CMRF\Core\Core         as AbstractCore;
use CMRF\Core\AbstractCall as AbstractCall;
use CMRF\Core\Connection;
use CMRF\Drupal\Call       as DrupalCall;


class Core extends AbstractCore {

  protected $connections = array();

  public function __construct() {
  }

  public function getDefaultProfile() {
    return 'default';
  }

  /**
   * Retrieve the connection from the connection profile
   * Get instance of the connector through a drupal callback function.
   *
   * @param $connector_id
   * @return Connection
   */
  protected function getConnection($connector_id) {
    if (!isset($this->connections[$connector_id])) {
      $connectors = cmrf_core_list_connectors();
      $profile = $this->getConnectionProfile($connector_id);
      if (!isset($connectors[$profile['connector']])) {
        watchdog('cmrf_core', t('No connector available for %connector_name', array('connector_name' => $profile['connector'])), array(), WATCHDOG_ERROR);
      }
      if (!isset($connectors[$profile['connector']]['callback']) || !function_exists($connectors[$profile['connector']]['callback'] )) {
        watchdog('cmrf_core', t('No connector available for %connector_name', array('connector_name' => $profile['connector'])), array(), WATCHDOG_ERROR);
      }
      $this->connections[$connector_id] = call_user_func($connectors[$profile['connector']]['callback'], $this, $connector_id);
    }

    return $this->connections[$connector_id];
  }


  /************************************
   *    Call related infrastructure   *
   ************************************/



  public function createCall($connector_id, $entity, $action, $parameters = array(), $options = array(), $callback = NULL) {
    if (!empty($options['cache'])) {
      $hash = AbstractCall::getHashFromParams($entity, $action, $parameters, $options);
      $result = db_query(
        "SELECT *
         FROM {cmrf_core_call}
         WHERE request_hash = :hash
           AND connector_id = :connectorid
           AND cached_until > NOW()
         LIMIT 1;",
         array(":hash" => $hash, ":connectorid" => $connector_id));
      
      foreach ($result as $cached_entry) {
        return DrupalCall::createWithRecord($connector_id, $this, $cached_entry);
      }
    }
    
    // not cached/no caching:
    return DrupalCall::createNew($connector_id, $this, $entity, $action, $parameters, $options, $callback);
  }

  public function getCall($call_id) {
    // TODO: implmenet
    return NULL;
  }

  public function findCall($options) {
    // TODO: implmenet
    return NULL;
  }


  /*********************************************************
   *  Use Drupal variables to store config for the moment  *
   *********************************************************/

  public function getConnectionProfiles() {
    return cmrf_core_list_profiles();
  }

  public function getRegisteredConnectors() {
    return variable_get('cmrf_core_connectors');
  }

  protected function storeRegisteredConnectors($connectors) {
    return variable_set('cmrf_core_connectors', $connectors);
  }

  public function getSettings() {
    return variable_get('cmrf_core_settings');
  }

  protected function storeSettings($settings) {
    return variable_set('cmrf_core_settings', $settings);
  }

}
