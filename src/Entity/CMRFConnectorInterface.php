<?php namespace Drupal\cmrf_core\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining CiviMRF Connector entities.
 */
interface CMRFConnectorInterface extends ConfigEntityInterface {

  public function getType();

}
