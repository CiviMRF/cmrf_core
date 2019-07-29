<?php

namespace Drupal\cmrf_webform;

use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformInterface;
use Drupal\cmrf_webform\Entity\Submission;
use RuntimeException;

// todo: Implement a better error mechanism, likely different types of exceptions to throw.
class CMRFSubmissionsManager extends CMRFManagerBase {

  const QUEUE_NAME = 'cmrf_submissions';

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
    $queue->deleteItem($item);
  }

  protected function queryApi(WebformSubmissionInterface $submission, SubmissionInterface $entity, $cron) {
    $connector = $entity->getConnector();
    $parameters = $submission->getData();
    $options = [];

    try {
      $reply = $this->sendApiRequest(
        $connector,
        $entity->getEntity(),
        $entity->getAction(),
        $parameters,
        $options,
        NULL
      );
      return true;
    }
    catch (RuntimeException $e) {
      if (!$cron) {
        // fail silently - not to disturb user
        $this->submissionError = $e;
      } else {
        throw $e;
      }
      return false;
    }
  }

  protected function executeSingleHandler(WebformSubmissionInterface $submission, SubmissionInterface $handler, $cron = false) {
    $delete = $handler->getDeleteSubmission();
    if (!$handler->getSubmitInBackground() xor $cron) {
      $delete = $this->queryApi($submission, $handler, $cron) && $delete;
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
    $handler = Submission::getForWebform($webform);

    if ($handler) {
      $delete = $this->executeSingleHandler($submission, $handler);

      if ($delete) {
        $submission->delete();
      }
    }

    return $handler == true;
  }

  public function executeQueuedSubmissionHandlers() {
    $this->resetErrors();
    $success = true;
    while ($success && $item = $this->getQueuedQuery()) {
      $data = $item->data;
      try {
        if ($this->executeSingleHandler($data['submission'], $data['handler'], true)) {
          $data['submission']->delete();
        }
        $this->removeProcessedQuery($item);
      }
      catch (RuntimeException $e) {
        $success = false;
        $this->submissionError = $e;
      }
    }
    return $success;
  }

  public function deleteWebformHandler(WebformInterface $webform) {
    $handler = Submission::getForWebform($webform);
    if ($handler) {
      $handler->delete();
    }
  }

}
