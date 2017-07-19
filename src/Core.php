<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 18.07.17
 * Time: 17:19
 */

namespace Drupal\cmrf_core;

use CMRF\Connection\Curl;
use CMRF\Core\Core as AbstractCore;
use CMRF\PersistenceLayer\CallFactory;
use CMRF\PersistenceLayer\SQLPersistingCallFactory;
use Drupal\cmrf_core\Entity\CMRFProfile;


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
    //TODO: store open connections for reuse.
    return new Curl($this,$connector_id);
  }

  public function getConnectionProfiles() {

    $return=array();
    $query = \Drupal::entityQuery('cmrf_profile');
    $results=$query->execute();
    $ids=array_keys($results);
    /** @var CMRFProfile[] $loaded */
    $loaded=CMRFProfile::loadMultiple($ids);
    foreach($loaded as $entity) {
      $return[$entity->id()]=array(
        'url'=>$entity->url,
        'api_key'=>$entity->api_key,
        'site_key'=>$entity->site_key
      );
    }
    return $return;
  }

  public function getDefaultProfile() {
    $entity=CMRFProfile::load('default');
    return array(
      'url'=>$entity->url,
      'api_key'=>$entity->api_key,
      'site_key'=>$entity->site_key
    );
  }

  public function registerConnector($connector_name, $profile = NULL) {
    // first, make sure the profile is o.k.
    if ($profile === NULL) {
      $profile = $this->getDefaultProfile();
    }

    $profiles = $this->getConnectionProfiles();

    if (!isset($profiles[$profile])) {
      throw new \Exception("Invalid profile '$profile'.", 1);
    }

    // find a new ID for the connector
    $connector_id = $this->generateURN("connector:$connector_name", $connectors);
    $connector = array(
      'type'    => $connector_name,
      'profile' => $profile,
      'id'      => $connector_id
    );
    //TODO: implement config entity to store connector. and check if it's already present.
    return $connector_id;
  }

  public function unregisterConnector($connector_identifier) {
    //TODO: check if entity is present and delete it.
  }


  protected function getRegisteredConnectors() {
    // we're overriding registerConnector and unregisterConnector as the heavy lifting is handed over to drupal.
    // therefore: nothing to find here.
  }

  protected function storeRegisteredConnectors($connectors) {
    // we're overriding registerConnector and unregisterConnector as the heavy lifting is handed over to drupal.
    // therefore: nothing to find here.
  }

  protected function getSettings() {
    return array();
  }

  protected function storeSettings($settings) {
    //no settings yet in d8.
  }

}
