<?php

namespace Drupal\cmrf_core;

interface ApiCallEntityInterface {

  public function getConnector();
  public function getConnectorEntity();
  public function setConnector($value);

  public function getEntity();
  public function setEntity($value);

  public function getAction();
  public function setAction($value);

}
