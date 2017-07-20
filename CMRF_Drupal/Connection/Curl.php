<?php

namespace CMRF\Drupal\Connection;

use \CMRF\Connection\Curl as AbstractCurl;
use \CMRF\Core\Call;

class Curl extends AbstractCurl {

  public function queueCall(Call $call) {

    // TODO: override if async calls are possible
    //$this->executeCall();
    //$call->triggerCallback();
  }

}