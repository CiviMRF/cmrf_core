<?php

namespace Drupal\cmrf_webform;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\cmrf_core\ApiCallEntityInterface;

/**
 * Provides an interface defining an OptionSet entity.
 */
interface SubmissionInterface extends ConfigEntityInterface, ApiCallEntityInterface {

  public function setWebform($value);
  public function getWebform();

  public function setDeleteSubmission($value);
  public function getDeleteSubmission();

  public function setSubmitInBackground($value);
  public function getSubmitInBackground();

}
