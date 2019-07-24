<?php

namespace Drupal\cmrf_webform;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an OptionSet entity.
 */
interface SubmissionInterface extends ConfigEntityInterface {

  public function getWebformId();

  public function getEntity();
  public function setEntity($value);

  public function getAction();
  public function setAction($value);

}
