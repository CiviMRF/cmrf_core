<?php

namespace Drupal\cmrf_webform\Entity;

use Drupal;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\cmrf_webform\DefaultValueInterface;
use Drupal\webform\WebformInterface;
use Drupal\cmrf_core\Entity\CMRFConnector;
use RuntimeException;

/**
 * Defines the CiviMRF Webform DefaultValue entity.
 *
 * @ConfigEntityType(
 *   id = "cmrf_webform_default_value",
 *   label = @Translation("CiviMRF Webform Default Values"),
 *   handlers = {
 *     "list_builder" = "Drupal\cmrf_webform\Controller\CMRFWebformListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cmrf_webform\Form\DefaultValueForm",
 *       "edit" = "Drupal\cmrf_webform\Form\DefaultValueForm",
 *       "delete" = "Drupal\cmrf_webform\Form\DefaultValueDeleteForm",
 *     }
 *   },
 *   config_prefix = "cmrf_webform_default_value",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "connector" = "connector",
 *     "webform" = "webform",
 *     "entity" = "entity",
 *     "action" = "action",
 *     "field_key" = "field_key",
 *     "parameters" = "parameters",
 *     "options" = "options",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "connector",
 *     "webform",
 *     "entity",
 *     "action",
 *     "field_key",
 *     "parameters",
 *     "options",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/cmrf/cmrf_webform/default_values/manage/{cmrf_webform_default_value}",
 *     "delete-form" = "/admin/config/cmrf/cmrf_webform/default_values/manage/{cmrf_webform_default_value}/delete",
 *   }
 * )
 */
class DefaultValue extends ConfigEntityBase implements DefaultValueInterface {

  /**
   * The default value ID.
   *
   * @var string
   */
  public $id;

  /**
   * The default value label.
   *
   * @var string
   */
  public $label;

  /**
   * The connector entity's id.
   *
   * @var string
   */
  public $connector;

  /**
   * The webform entity's id.
   *
   * @var string
   */
  public $webform;

  /**
   * The default value entity name.
   *
   * @var string
   */
  public $entity;

  /**
   * The default value action name.
   *
   * @var string
   */
  public $action;

  /**
   * The default value for element machine name.
   *
   * @var string
   */
  public $field_key;

  /**
   * The default value parameters string.
   *
   * @var string
   */
  public $parameters;

  /**
   * The default value options string.
   *
   * @var string
   */
  public $options;

  public function getConnector() {
    return $this->connector;
  }

  public function getConnectorEntity() {
    return CMRFConnector::load($this->connector);
  }

  public function setConnector($value) {
    $this->connector = $value;
  }

  public static function getForWebform(WebformInterface $entity) {
    $handler_ids = Drupal::entityQuery('cmrf_webform_default_value')
      ->condition('webform', $entity->id())
      ->execute();

    if (count($handler_ids) > 0) {
      $handler = array();
      foreach ($handler_ids as $id) {
        $handler[] = static::load($id);
      }
      return $handler;
    }
    return NULL;
  }

  public function setWebform($value) {
    $this->webform = $value;
  }

  public function getWebform() {
    return $this->webform;
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

  public function getFieldKey() {
    return $this->field_key;
  }

  public function setFieldKey($value) {
    $this->field_key = $value;
  }

  public function getParameters() {
    return $this->parameters;
  }

  public function getDecodedParameters($as_array = true) {
    return json_decode($this->parameters, $as_array);
  }

  public function setParameters($value) {
    $this->parameters = $value;
  }

  public function getOptions() {
    return $this->options;
  }

  public function getDecodedOptions($as_array = true) {
    return json_decode($this->options, $as_array);
  }

  public function setOptions($value) {
    $this->options = $value;
  }

}
