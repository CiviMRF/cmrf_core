<?php

namespace Drupal\cmrf_webform\Manager;

use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformInterface;
use Drupal\cmrf_webform\SubmissionInterface;
use Drupal\cmrf_webform\Entity\Submission;
use Drupal\cmrf_webform\Exception\QueueException;
use Drupal\cmrf_webform\Exception\SubmissionException;
use RuntimeException;

class CMRFSubmissionsManager extends CMRFManagerBase {

  const QUEUE_NAME = 'cmrf_submissions';

  protected $queueFactory;

  public function __construct($core, $translation, $queue) {
    parent::__construct($core, $translation);

    $this->queueFactory = $queue;
  }

  protected function getQueue() {
    return $this->queueFactory->get('cmrf_submissions', true);
  }

  protected function queueApiQuery(WebformSubmissionInterface $submission, SubmissionInterface $entity) {
    $queue = $this->getQueue();
    if (!$queue->createItem(['submission' => $submission, 'handler' => $entity])) {
      throw new QueueException("Couldn't add CiviMRF Webform Submission task to a queue");
    }
  }

  protected function getQueuedQuery() {
    $queue = $this->getQueue();
    return $queue->claimItem(60);
  }

  protected function releaseQuery($item) {
    $queue = $this->getQueue();
    return $queue->releaseItem($item);
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
      throw new SubmissionException($e->getMessage());
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
    $total = 0;

    while ($item = $this->getQueuedQuery()) {
      try {
        $data = $item->data;
        if ($this->executeSingleHandler($data['submission'], $data['handler'], true)) {
          $data['submission']->delete();
        }
        $this->removeProcessedQuery($item);
        $total += 1;
      }
      catch (RuntimeException $e) {
        $this->releaseQuery($item);
        throw $e;
      }
    }

    return $total;
  }

  public function deleteWebformHandler(WebformInterface $webform) {
    $handler = Submission::getForWebform($webform);
    if ($handler) {
      $handler->delete();
    }
  }

}
