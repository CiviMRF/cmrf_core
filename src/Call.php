<?php

namespace Drupal\cmrf_core;

use CMRF\Core\AbstractCall;
use CMRF\Core\Call as CallInterface;

class Call extends AbstractCall {

  protected array $request;

  protected ?array $reply = NULL;

  protected string $status = CallInterface::STATUS_INIT;

  protected array $metadata = [];

  protected ?\DateTime $cached_until = NULL;

  /**
   * @param string $connector_id
   * @param \Drupal\cmrf_core\Core $core
   * @param string $entity
   * @param string $action
   * @param array $parameters
   * @param array|null $options
   * @param callable[]|callable|null $callbacks
   * @param CallFactory $factory
   * @param string $api_version
   *
   * @return static
   */
  public static function createNew($connector_id, $core, $entity, $action, $parameters, $options, $callbacks,
    $factory, string $api_version) {

    if (!is_array($callbacks)) {
      if (NULL === $callbacks) {
        $callbacks = [];
      }
      else {
        $callbacks = [$callbacks];
      }
    }

    return static::create($connector_id, $core, $api_version, $entity, $action, $parameters, $options ?? [], $callbacks,
      $factory
    );
  }

  /**
   * @param string $connector_id
   * @param \Drupal\cmrf_core\Core $core
   * @param string $api_version
   * @param string $entity
   * @param string $action
   * @param array $parameters
   * @param array $options
   * @param callable[] $callbacks
   * @param CallFactory $factory
   *
   * @return static
   */
  protected static function create(
    string $connector_id,
    Core $core,
    string $api_version,
    string $entity,
    string $action,
    array $parameters,
    array $options,
    array $callbacks,
    CallFactory $factory
  ): self {
    $call = new Call($core, $connector_id, $factory);

    // compile request
    if ('3' === $api_version) {
      $call->request = $call->compileRequest($parameters, $options);
    }
    else {
      $call->request = $parameters;
    }
    $call->request['entity']     = $entity;
    $call->request['action']     = $action;
    $call->request['version']    = $api_version;
    $call->status                = CallInterface::STATUS_INIT;
    $call->metadata['callbacks'] = $callbacks;
    $call->callbacks = $callbacks;

    $call->initOptions($options);

    return $call;
  }

  public static function createWithRecord($connector_id, $core, $record, $factory) {
    $call              = new Call($core, $connector_id, $factory, $record->cid);
    $call->status      = $record->status;
    $call->metadata    = json_decode($record->metadata, TRUE);
    $call->retry_count = $record->retry_count;
    if (!empty($record->cached_until)) {
      $call->cached_until = new \DateTime($record->cached_until);
    }
    $call->request = json_decode($record->request, TRUE);
    if (!isset($call->request['version'])) {
      // For backward compatibility.
      $call->request['version'] = '3';
    }
    $call->reply   = json_decode($record->reply, TRUE);
    $call->date    = new \DateTime($record->create_date);
    if (!empty($record->reply_date)) {
      $call->reply_date = new \DateTime($record->reply_date);
    }
    if (!empty($record->scheduled_date)) {
      $call->scheduled_date = new \DateTime($record->scheduled_date);
    }
    if (isset($call->metadata['callbacks']) && is_array($call->metadata['callbacks'])) {
      $call->callbacks = $call->metadata['callbacks'];
    }
    return $call;
  }

  public function setReply($data, $newstatus = CallInterface::STATUS_DONE) {
    // update the cached data
    $this->reply      = $data;
    $this->reply_date = new \DateTime();
    $this->status     = $newstatus;
    $this->checkForRetry();
    $this->factory->update($this);
    $this->checkAndTriggerFailure();
    $this->checkAndTriggerDone();
  }

  public function getApiVersion(): string {
    return $this->request['version'];
  }

  public function getEntity() {
    return $this->request['entity'];
  }

  public function getAction() {
    return $this->request['action'];
  }

  public function getParameters() {
    return $this->extractParameters($this->request);
  }

  public function getOptions() {
    return $this->extractOptions($this->request);
  }

  public function getStatus() {
    return $this->status;
  }

  public function getMetadata() {
    return $this->metadata;
  }

  public function getCachedUntil() {
    return $this->cached_until;
  }

  public function getRequest() {
    return $this->request;
  }

  public function getReply() {
    return $this->reply;
  }

  public function triggerCallback() {
    // TODO:
  }

  public function setStatus($status, $error_message, $error_code = NULL) {
    $error = [
      'is_error'      => '1',
      'error_message' => $error_message,
      'error_code'    => $error_code,
    ];

    $this->status     = $status;
    $this->reply      = $error;
    $this->reply_date = new \DateTime();
    $this->checkForRetry();

    $this->factory->update($this);
    $this->checkAndTriggerFailure();
    $this->checkAndTriggerDone();
  }

  protected function initOptions($options): void {
    // Set the retry options
    if (isset($options['retry_count'])) {
      $this->retry_count = $options['retry_count'];
    }
    if (isset($options['retry_interval'])) {
      $this->metadata['retry_interval'] = $options['retry_interval'];
    }
    foreach ($options as $key => $val) {
      $this->metadata[$key] = $val;
    }

    // set the caching flag
    if (!empty($options['cache'])) {
      $this->cached_until = new \DateTime();
      $this->cached_until->modify('+' . $options['cache']);
    }
  }

  protected function checkForRetry() {
    if ($this->status == \CMRF\Core\Call::STATUS_FAILED && $this->retry_count > 0) {
      $this->retry_count    = $this->retry_count - 1;
      $this->scheduled_date = $this->getRetryScheduledDate();
      $this->status         = \CMRF\Core\Call::STATUS_RETRY;
    }
  }

  protected function getRetryScheduledDate() {
    $default_retry_interval = '10 minutes';
    $now                    = new \DateTime();
    if (isset($this->metadata['retry_interval'])) {
      $now->modify('+ ' . $this->metadata['retry_interval']);
      return $now;
    }

    $now->modify('+ ' . $default_retry_interval);
    return $now;
  }

  protected function checkAndTriggerFailure() {
    if ($this->status == \CMRF\Core\Call::STATUS_FAILED) {
      \Drupal::moduleHandler()->invokeAll('cmrf_core_call_failed', ['call' => $this]);
    }
  }

  protected function checkAndTriggerDone() {
    if ($this->status == \CMRF\Core\Call::STATUS_DONE) {
      \Drupal::moduleHandler()->invokeAll('cmrf_core_call_done', ['call' => $this]);
    }
  }

}
