<?php namespace Drupal\cmrf_core;

use CMRF\Connection\Curl as CurlConnection;
use CMRF\Core\Core as AbstractCore;
use CMRF\PersistenceLayer\SQLPersistingCallFactory;
use Drupal\cmrf_core\Entity\CMRFConnector;
use Drupal\cmrf_core\Entity\CMRFProfile;

class Core extends AbstractCore {

  public function __construct() {
    $info = \Drupal::database()->getConnectionOptions();
    if (!isset($info['port'])) {
      $info['port'] = NULL;
    }
    $conn       = new \mysqli($info['host'], $info['username'], $info['password'], $info['database'], $info['port']);
    $table_name = \Drupal::database()->prefixTables("{civicrm_api_call}");
    $factory    = new SQLPersistingCallFactory($conn, $table_name, ['\Drupal\cmrf_core\Call', 'createNew'], ['\Drupal\cmrf_core\Call', 'createWithRecord']);
    parent::__construct($factory);
  }

  protected function getConnection($connector_id) {
    //TODO: store open connections for reuse.
    return new CurlConnection($this, $connector_id);
  }

  public function getConnectionProfile($connector_id) {
    $entity = CMRFConnector::load($connector_id);
    if ($entity == NULL) {
      throw new \Exception("Unregistered connector '$connector_id'.", 1);
    }
    return $this->getConnectionProfiles()[$entity->profile];
  }


  public function getConnectionProfiles() {

    $return  = [];
    $query   = \Drupal::entityQuery('cmrf_profile');
    $results = $query->execute();
    $ids     = array_keys($results);
    /** @var CMRFProfile[] $loaded */
    $loaded = CMRFProfile::loadMultiple($ids);
    foreach ($loaded as $entity) {
      $return[$entity->id()] = [
        'label'    => $entity->label(),
        'url'      => $entity->url,
        'api_key'  => $entity->api_key,
        'site_key' => $entity->site_key,
      ];
    }
    return $return;
  }

  public function getDefaultProfile() {
    $entity = CMRFProfile::load('default');
    return [
      'url'      => $entity->url,
      'api_key'  => $entity->api_key,
      'site_key' => $entity->site_key,
    ];
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
    $connectors   = [];
    $connector_id = $this->generateURN("connector:$connector_name", $connectors);
    $connector    = [
      'type'    => $connector_name,
      'profile' => $profile,
      'id'      => $connector_id,
    ];

    $id    = $connector_name;
    $count = 1;
    while (CMRFConnector::load($id) !== NULL) {
      $count = $count + 1;
      $id    = $connector_name . '_' . $count;
    }

    $entity = CMRFConnector::create();
    $entity->set('id', $id);
    $entity->set('label', $connector_id);

    $entity->type    = $connector_name;
    $entity->profile = $profile;
    $entity->save();
    return $entity->id();
  }

  public function unregisterConnector($connector_identifier) {
    $entity = CMRFConnector::load($connector_identifier);
    $entity->delete();
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
    return [];
  }

  protected function storeSettings($settings) {
    //no settings yet in d8.
  }

}
