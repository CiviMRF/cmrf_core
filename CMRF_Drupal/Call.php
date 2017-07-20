<?php

/**
 * A simple, serialisable implementation of CMRF\Core\Call
 *
 * @author Björn Endres, SYSTOPIA (endres@systopia.de)
 */

namespace CMRF\Drupal;

use CMRF\Core\AbstractCall as AbstractCall;
use CMRF\Core\Call         as CallInterface;

class Call extends AbstractCall {

  protected $request  = NULL;
  protected $reply    = NULL;
  protected $status   = CallInterface::STATUS_INIT;
  protected $metadata = '{}';
  protected $cached_until = NULL;

  public static function createNew($connector_id, $core, $entity, $action, $parameters, $options, $callback, $factory) {
    $call = new Call($core, $connector_id, $factory);

    // compile request
    $call->request = $call->compileRequest($parameters, $options);
    $call->request['entity'] = $entity;
    $call->request['action'] = $action;
    $call->status = CallInterface::STATUS_INIT;
    $call->metadata = '{}';

    // set the caching flag
    if (!empty($options['cache'])) {
      $call->cached_until = new \DateTime();
      $call->cached_until->modify('+'.$options['cache']);
    }

    return $call;
  }

  public static function createWithRecord($connector_id, $core, $record, $factory) {
    $call = new Call($core, $connector_id, $factory, $record->cid);
    $call->status = $record->status;
    $call->metadata = $record->metadata;
    if (!empty($record->cached_until)) {
      $call->cached_until = $record->cached_until;
    }
    $call->request = json_decode($record->request, TRUE);
    $call->reply   = json_decode($record->reply, TRUE);
    return $call;
  }

  public function setReply($data, $newstatus = CallInterface::STATUS_DONE) {
    // update the cached data
    $this->reply = $data;
    $this->reply_date = new \DateTime();
    $this->status = $newstatus;

    $this->factory->update($this);
  }

  public function setID($id) {
    parent::setID($id);
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

  public function getStats() {
    return $this->metadata;
  }

  /**
   * Returns the date and time when the call should be processed.
   *
   * @return \DateTime|null
   */
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
    $error = array(
      'is_error'      => '1',
      'error_message' => $error_message,
      'error_code'    => $error_code);

    $this->status = $status;
    $this->reply = $error;
    $this->reply_date = new \DateTime();
    $this->factory->update($this);
  }
}

