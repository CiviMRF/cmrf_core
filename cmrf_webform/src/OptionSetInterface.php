<?php

namespace Drupal\cmrf_webform;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\cmrf_core\ApiCallEntityInterface;

/**
 * Provides an interface defining an OptionSet entity.
 */
interface OptionSetInterface extends ConfigEntityInterface, ApiCallEntityInterface {

  public function getWebformId();

  public function getParameters();
  public function getDecodedParameters($as_array);
  public function setParameters($value);

  public function getKeyProperty();
  public function setKeyProperty($value);

  public function getValueProperty();
  public function setValueProperty($value);

  public function getCache();
  public function setCache($value);

  public function needsRecaching();
  public function setRecached();

}
