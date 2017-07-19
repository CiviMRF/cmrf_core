<?php
/**
 * Created by PhpStorm.
 * User: enno
 * Date: 19.07.17
 * Time: 13:36
 */

namespace Drupal\cmrf_example;


use Drupal\cmrf_core\Core;

class CiviClient {

  /** @var Core */
  public $core;

  public function __construct(Core $core) {
    $this->core=$core;
  }

  private function connector() {
    return \Drupal::config('cmrf_example.settings')->get('connector');
  }

  public function getContactIds() {
    $call=$this->core->createCall($this->connector(),'Contact','get',array('return'=>'id'),array());
    $this->core->executeCall($call);
    return $call->getReply();
  }
}
