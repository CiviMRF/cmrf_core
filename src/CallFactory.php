<?php


namespace Drupal\cmrf_core;

use CMRF\PersistenceLayer\SQLPersistingCallFactory;

class CallFactory extends SQLPersistingCallFactory {

  /**
   * @var \Drupal\cmrf_core\Core;
   */
  private $core;

  protected $table_name;

  public function __construct($sql_connection, $table_name, $constructor, $loader) {
    parent::__construct($sql_connection, $table_name, $constructor, $loader);
  }

  public function purgeCachedCalls() {
    parent::purgeCachedCalls();
    foreach ($this->core->getConnectors() as $connector_id => $connector) {
      $profile = $this->core->getConnectionProfile($connector_id);
      if ($profile['cache_expire_days'] > 0) {
        $today = new \DateTime();
        $today->modify('-' . $profile['cache_expire_days'] . ' days');
        $sql = "DELETE from {$this->table_name}"
          . " WHERE DATE(`create_date`) < '" . $today->format('Y-m-d') . "'"
          . " AND `connector_id` = '" . $connector_id . "'";
        \Drupal::database()->query($sql);
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
