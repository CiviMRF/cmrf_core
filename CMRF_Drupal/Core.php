<?php

/**
 * Drupal-based implementation of a CMRF Core
 *
 * @author Björn Endres, SYSTOPIA (endres@systopia.de)
 */

namespace CMRF\Drupal;

include_once('CMRF/Core/Core.php');
include_once('CMRF/Connection/Curl.php');
include_once('CMRF/Local/Call.php');

use CMRF\Core\Core       as AbstractCore;
use CMRF\Connection\Curl as CurlConnection;
use CMRF\Local\Call      as Call;


class Core extends AbstractCore {

  public function getDefaultProfile() {
    return 'default';
  }


  public function _createConnection($connection_id, $connector_id) {
    return new CurlConnection($connection_id, $this, $connector_id);
  }

  public function isReady() {
    // TODO:
    return TRUE;
  }


  /************************************
   *    Call related infrastructure   *
   ************************************/


  public function createCall($entity, $action, $parameters, $options = NULL, $callback = NULL) {
    // TODO: implement drupal table based call store
    $id = $this->generateURN("call:curl");
    return new Call($id, $this, $entity, $action, $parameters, $options, $callback);
  }

  public function getCallStatus($call_id) {
    // TODO: implmenet
    return NULL;
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
    error_log("B.getConnectionProfiles");
    return variable_get('cmrf_core_connection_profiles');
  }

  protected function storeConnectionProfiles($profiles) {
    return variable_set('cmrf_core_connection_profiles', $profiles);
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

  public function getConnections() {
    // FIXME: needs to go into DB structure
    return variable_get('cmrf_core_connections');
  }

  protected function storeConnections($connections) {
    // FIXME: needs to go into DB structure
    return variable_set('cmrf_core_connections', $connections);
  }

}
