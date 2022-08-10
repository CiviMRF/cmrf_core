<?php namespace Drupal\cmrf_core;

use CMRF\Connection\Curl;
use CMRF\Core\Call;

class Connection extends Curl {

  public function queueCall(Call $call) {
    // We don't have to do anything here.
    // Except for saving the call.
    $this->core->getFactory()->update($call);
  }

}
