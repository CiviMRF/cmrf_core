<?php

namespace Drupal\cmrf_webform;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\cmrf_core\ApiCallEntityInterface;

/**
 * Provides an interface defining an DefaultValue entity.
 */
interface DefaultValueInterface extends ConfigEntityInterface, ApiCallEntityInterface {

  public function setWebform($value);
  public function getWebform();

  public function getParameters();
  public function getDecodedParameters($as_array);
  public function setParameters($value);

  public function getOptions();
  public function getDecodedOptions($as_array);
  public function setOptions($value);
}
