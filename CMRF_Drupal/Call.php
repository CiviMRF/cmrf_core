<?php

/**
 * A simple, serialisable implementation of CMRF\Core\Call
 *
 * @author Björn Endres, SYSTOPIA (endres@systopia.de)
 */

namespace CMRF\Drupal;

use CMRF\Core\AbstractCall as AbstractCall;
use CMRF\Core\Call         as CallInterface;

include_once('CMRF/Core/AbstractCall.php');

class Call extends AbstractCall {

  protected $record   = NULL;
  protected $request  = NULL;
  protected $reply    = NULL;


  public function __construct($connector_id, $core, $entity, $action, $parameters, $options, $callback) {
    AbstractCall::__construct($core, $connector_id);

    // compile request
    $this->request = $this->compileRequest($parameters, $options);
    $this->request['entity'] = $entity;
    $this->request['action'] = $action;

    // create DB entry
    $this->record = array(
      'status'       => CallInterface::STATUS_INIT,
      'connector_id' => $this->getConnectorID(),
      'request'      => json_encode($this->request), 
      'metadata'     => '{}',
      'request_hash' => $this->getHash(),
      'create_date'  => date('YmdHis'),
      );

    // set the caching flag
    if (!empty($options['cache'])) {
      $this->record['cached_until'] = date('YmdHis', strtotime("now +" . $options['cache']));
    }

    drupal_write_record('cmrf_core_call', $this->record);
    $this->id = $this->record['cid'];
  }

  public function setReply($data, $newstatus = CallInterface::STATUS_DONE) {
    // update the DB
    $update = array(
      'cid'        => $this->id,
      'status'     => $newstatus,
      'reply_date' => date('YmdHis'),
      'reply'      => json_encode($data));
    error_log(print_r($update,1));
    drupal_write_record('cmrf_core_call', $update, array('cid'));

    // update the cached data
    $this->reply = $data;
    $this->record['status'] = $newstatus;
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
    return $this->record['status_id'];
  }

  public function getStats() {
    return $this->record['metadata'];
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
    $this->data['status']        = $status;
    $this->data['error_message'] = $error_message;
    $this->data['error_code']    = $error_code;
  }
}

