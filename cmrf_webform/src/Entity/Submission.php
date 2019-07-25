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
 *     "webform" = "webform",
 *     "delete_submission" = "delete_submission",
 *     "submit_in_background" = "submit_in_background",
 *     "entity" = "entity",
 *     "action" = "action",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "webform",
 *     "delete_submission",
 *     "submit_in_background",
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
   * The submission ID.
   *
   * @var string
   */
  public $id;

  /**
   * The submission label.
   *
   * @var string
   */
  public $label;

  /**
   * The target webform entity.
   *
   * @var mixed
   */
  public $webform;

  /**
   * Whether to delete submission after sending
   *
   * @var mixed
   */
  public $delete_submission;

  /**
   * Whether to submit to API in cron run
   *
   * @var mixed
   */
  public $submit_in_background;

  /**
   * The submission entity name.
   *
   * @var string
   */
  public $entity;

  /**
   * The submission action name.
   *
   * @var string
   */
  public $action;

  public function setWebform($value) {
    $this->webform = $value;
  }

  public function getWebform() {
    return $this->webform;
  }

  public function setDeleteSubmission($value) {
    $this->delete_submission = $value;
  }

  public function getDeleteSubmission() {
    return $this->delete_submission;
  }

  public function setSubmitInBackground($value) {
    $this->submit_in_background = $value;
  }

  public function getSubmitInBackground() {
    return $this->submit_in_background;
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
