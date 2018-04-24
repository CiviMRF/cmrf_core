<?php

/**
 * Drupal-based implementation of a CMRF Core
 *
 * @author Björn Endres, SYSTOPIA (endres@systopia.de)
 */

namespace CMRF\Drupal;

require_once(dirname(__FILE__) .'/Call.php');
require_once(dirname(__FILE__) .'/Connection/Curl.php');
require_once(dirname(__FILE__) .'/SQLPersistingCallFactory.php');

use CMRF\Core\Core         as AbstractCore;
use CMRF\Core\Connection;
use CMRF\Drupal\SQLPersistingCallFactory;


class Core extends AbstractCore {

  protected $connections = array();

  public function __construct() {
    $info = \Database::getConnectionInfo('default');
    $info = $info['default'];
    if(!isset($info['port']) || empty($info['port'])) {
      $info['port'] = NULL;
    }

    $table_name = \Database::getConnection()->prefixTables("{cmrf_core_call}");
    $connection = new \mysqli($info['host'],$info['username'],$info['password'],$info['database'],$info['port']);
    $factory = new SQLPersistingCallFactory($connection, $table_name, array('\CMRF\Drupal\Call','createNew'), array('\CMRF\Drupal\Call','createWithRecord'));
    parent::__construct($factory);
  }

  public function getDefaultProfile() {
    $profile = cmrf_core_default_profile();
    return $profile['name'];
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
