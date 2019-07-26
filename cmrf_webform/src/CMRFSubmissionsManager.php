<?php

namespace Drupal\cmrf_webform;

use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformInterface;
use Drupal\cmrf_webform\Entity\Submission;
use RuntimeException;

class CMRFSubmissionsManager extends CMRFManager {

  protected $queueFactory;
  protected $queueError;
  protected $submissionError;

  protected function resetErrors() {
    $this->queueError = false;
    $this->submissionError = false;
  }

  public function __construct($core, $translation, $queue) {
    parent::__construct($core, $translation);

    $this->queueFactory = $queue;
    $this->resetErrors();
  }

  public function getQueueError() {
    return $this->queueError;
  }

  public function getSubmissionError() {
    return $this->submissionError;
  }

  protected function getQueue() {
    return $this->queueFactory->get('cmrf_submissions', true);
  }

  protected function queueApiQuery(WebformSubmissionInterface $submission, SubmissionInterface $entity) {
    $queue = $this->getQueue();
    if (!$queue->createItem(['submission' => $submission, 'handler' => $entity])) {
      $this->queueError = true;
    }
  }

  protected function getQueuedQuery() {
    $queue = $this->getQueue();
    return $queue->claimItem(60);
  }

  protected function removeProcessedQuery($item) {
    $queue = $this->getQueue();
    if (!$queue->deleteItem($item)) {
      $this->queueError = true;
    }
  }

  protected function queryApi(WebformSubmissionInterface $submission, SubmissionInterface $entity, $cron) {
    $connector = $entity->getConnector();
    $parameters = $submission->toArray();
    $options = [];

    try {
      $reply = $this->sendApiRequest(
        $connector,
        $entity->getEntity(),
        $entity->getAction(),
        $parameters,
        $options
      );
      return true;
    }
    catch (RuntimeException $e) {
      // fallback in case there was an error sending
      if (!$cron) {
        $this->queueApiQuery($submission, $entity);
        $this->submissionError = $e;
      } else {
        throw $e;
      }
      return false;
    }
  }

  protected function executeSingleHandler(WebformSubmissionInterface $submission, SubmissionInterface $handler, $cron = false) {
    $delete = true;
    if (!$handler->getSubmitInBackground() xor $cron) {
      $delete = $this->queryApi($submission, $handler, $cron) && $delete;
      // Delete only if all of the cmrf submission handlers are set to deleting.
      $delete = $delete && $handler->getDeleteSubmission();
    }
    elseif (!$cron) {
      $this->queueApiQuery($submission, $handler);
      // Don't delete outside of cron if there is at least one cron task.
      $delete = false;
    }

    return $delete;
  }

  public function executeSubmissionHandlers(WebformSubmissionInterface $submission) {
    $this->resetErrors();
    $webform = $submission->getWebform();
    $cmrf_handlers = Submission::getForWebform($webform);

    $delete = count($cmrf_handlers) > 0;
    foreach ($cmrf_handlers as $ind => $handler) {
      $delete = $delete && $this->executeSingleHandler($submission, $handler);
    }

    if ($delete) {
      $submission->delete();
    }
    return count($cmrf_handlers);
  }

  public function executeQueuedSubmissionHandlers() {
    $this->resetErrors();
    $error = false;
    while (!$error && $item = $this->getQueuedQuery()) {
      $data = $item->data;
      try {
        if ($this->executeSingleHandler($data['submission'], $data['handler'], true)) {
          $data['submission']->delete();
        }
        $this->removeProcessedQuery($item);
      }
      catch (RuntimeException $e) {
        $error = true;
        $this->submissionError = $e;
      }
    }
    return $error;
  }

  public function deleteWebformHandlers(WebformSubmissionInterface $webform) {
    $cmrf_handlers = Submission::getForWebform($webform);
    foreach ($cmrf_handlers as $handler) {
      $handler->delete();
    }
  }

}
