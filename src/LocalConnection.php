<?php namespace Drupal\cmrf_core;

use CMRF\Connection\Local;
use CMRF\Core\Call;

class LocalConnection extends Local {

  public function queueCall(Call $call) {
    // We don't have to do anything here.
    // Except for saving the call.
    $this->core->getFactory()->update($call);
  }

}
