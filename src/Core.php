<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 18.07.17
 * Time: 17:19
 */

namespace Drupal\cmrf_core;

use CMRF\Core\Core as AbstractCore;
use CMRF\PersistenceLayer\CallFactory;
use CMRF\PersistenceLayer\SQLPersistingCallFactory;


class Core extends AbstractCore {

  public function __construct() {
    $info=\Drupal::database()->getConnectionOptions();
    if(!isset($info['port'])) {
      $info['port']=NULL;
    }
    $conn=new \mysqli($info['host'],$info['username'],$info['password'],$info['database'],$info['port']);
    $table_name=\Drupal::database()->prefixTables("{civicrm_api_call}");
    $factory = new SQLPersistingCallFactory($conn, $table_name, array('\Drupal\cmrf_core\Call','createNew'), array('\Drupal\cmrf_core\Call','createWithRecord'));
    parent::__construct($factory);
  }

  protected function getConnection($connector_id) {
    // TODO: Implement getConnection() method.
  }

  public function getCall($call_id) {
    // TODO: Implement getCall() method.
  }

  public function findCall($options) {
    // TODO: Implement findCall() method.
  }

  public function getConnectionProfiles() {
    // TODO: Implement getConnectionProfiles() method.
  }

  public function getDefaultProfile() {
    // TODO: Implement getDefaultProfile() method.
  }

  protected function getRegisteredConnectors() {
    // TODO: Implement getRegisteredConnectors() method.
  }

  protected function storeRegisteredConnectors($connectors) {
    // TODO: Implement storeRegisteredConnectors() method.
  }

  protected function getSettings() {
    // TODO: Implement getSettings() method.
  }

  protected function storeSettings($settings) {
    // TODO: Implement storeSettings() method.
  }

}
