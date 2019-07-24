<?php

namespace Drupal\cmrf_webform\Entity;

use Drupal;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\cmrf_webform\SubmissionInterface;
use RuntimeException;

/**
 * Defines the Submission entity.
 *
 * @ConfigEntityType(
 *   id = "cmrf_webform_submission",
 *   label = @Translation("CiviCRM Webform integration submission handler"),
 *   handlers = {
 *     "list_builder" = "Drupal\cmrf_webform\Controller\SubmissionListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cmrf_webform\Form\SubmissionForm",
 *       "edit" = "Drupal\cmrf_webform\Form\SubmissionForm",
 *       "delete" = "Drupal\cmrf_webform\Form\SubmissionDeleteForm",
 *     }
 *   },
 *   config_prefix = "cmrf_webform_submission",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "entity" = "entity",
 *     "action" = "action",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "entity",
 *     "action",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/cmrf_webform_submission/{cmrf_webform_submission}",
 *     "delete-form" = "/admin/config/system/cmrf_webform_submission/{cmrf_webform_submission}/delete",
 *   }
 * )
 */
class Submission extends ConfigEntityBase implements SubmissionInterface {

  /**
   * The option set ID.
   *
   * @var string
   */
  public $id;

  /**
   * The option set label.
   *
   * @var string
   */
  public $label;

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

  public function getWebformId() {
    return 'cmrf_' . $this->id;
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

}
