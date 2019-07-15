<?php

namespace Drupal\cmrf_webform\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\cmrf_webform\OptionSetInterface;

/**
 * Defines the OptionSet entity.
 *
 * @ConfigEntityType(
 *   id = "cmrf_webform_option_set",
 *   label = @Translation("CiviCRM Webform integration option set"),
 *   handlers = {
 *     "list_builder" = "Drupal\cmrf_webform\Controller\CmrfWebformListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cmrf_webform\Form\OptionSetForm",
 *       "edit" = "Drupal\cmrf_webform\Form\OptionSetForm",
 *       "delete" = "Drupal\cmrf_webform\Form\OptionSetDeleteForm",
 *     }
 *   },
 *   config_prefix = "cmrf_webform_option_set",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "title" = "title",
 *     "entity" = "entity",
 *     "action" = "action",
 *     "parameters" = "parameters",
 *     "key_property" = "key_property",
 *     "value_property" = "value_property",
 *     "cache" = "cache",
 *   },
 *   config_export = {
 *     "id",
 *     "title",
 *     "entity",
 *     "action",
 *     "parameters",
 *     "key_property",
 *     "value_property",
 *     "cache",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/cmrf_webform_option_set/{cmrf_webform_option_set}",
 *     "delete-form" = "/admin/config/system/cmrf_webform_option_set/{cmrf_webform_option_set}/delete",
 *   }
 * )
 */
class OptionSet extends ConfigEntityBase implements OptionSetInterface {

  /**
   * The option set ID.
   *
   * @var string
   */
  public $id;

  /**
   * The option set title.
   *
   * @var string
   */
  public $title;

  /**
   * The option set entity name.
   *
   * @var string
   */
  public $entity;

  /**
   * The option set action name.
   *
   * @var string
   */
  public $action;

  /**
   * The option set parameters string.
   *
   * @var string
   */
  public $parameters;

  /**
   * The option set key property name.
   *
   * @var string
   */
  public $key_property;

  /**
   * The option set value property name.
   *
   * @var string
   */
  public $value_property;

  /**
   * The option set cache settings.
   *
   * @var string
   */
  public $cache;


  public function getTitle() {
    return $this->title;
  }

  public function setTitle($value) {
    $this->title = $value;
  }

  public function getEntity() {
    return $this->entity;
  }

  public function setEntity($value) {
    $this->entity = $value;
  }

  public function getAction() {
    return $this->action;
  }

  public function setAction($value) {
    $this->action = $value;
  }

  public function getParameters() {
    return $this->parameters;
  }

  public function setParameters($value) {
    $this->parameters = $value;
  }

  public function getKeyProperty() {
    return $this->key_property;
  }

  public function setKeyProperty($value) {
    $this->key_property = $value;
  }

  public function getValueProperty() {
    return $this->value_property;
  }

  public function setValueProperty($value) {
    $this->value_property = $value;
  }

  public function getCache() {
    return $this->cache;
  }

  public function setCache($value) {
    $this->cache = $value;
  }
}
