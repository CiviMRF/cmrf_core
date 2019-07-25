<?php

namespace Drupal\cmrf_webform;

use RuntimeException;

class CMRFSubmissionsManager extends CMRFManager {

  protected function queueApiQuery(SubmissionInterface $entity) {
    
  }

  protected function queryApi(SubmissionInterface $entity) {
    $connector = $this->getModuleConnector();
    $parameters = $entity->toArray();
    $options = [];

    $reply = $this->sendApiRequest(
      $connector,
      $entity->getEntity(),
      $entity->getAction(),
      $parameters,
      $options
    );
  }

  public function execute($submission, $handlers) {
    $handlers = (array) $handlers;

    $delete = false;
    foreach ($handlers as $ind => $handler) {
      if (!($handler instanceof SubmissionInterface)) {
        throw new RuntimeException("handler #$ind passed to " . __CLASS__ . " does not implement SubmissionInterface");
      }
      if ($handler->submitInBackground()) {
        $this->queueApiQuery($handler);
      }
      else {
        $this->queryApi($handler);
      }
      $delete = $delete || $handler->getDeleteSubmission();
    }

    if ($delete) {
      /* $submission->delete(); */
    }
  }

}
