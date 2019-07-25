<?php

namespace Drupal\cmrf_webform;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an OptionSet entity.
 */
interface SubmissionInterface extends ConfigEntityInterface {

  public function setWebform($value);
  public function getWebform();

  public function setDeleteSubmission($value);
  public function getDeleteSubmission();

  public function setSubmitInBackground($value);
  public function getSubmitInBackground();

  public function getEntity();
  public function setEntity($value);

  public function getAction();
  public function setAction($value);

}
