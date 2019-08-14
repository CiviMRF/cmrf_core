<?php namespace Drupal\cmrf_core;

use CMRF\Connection\Curl as AbstractCurl;
use CMRF\Core\Call;

class Connection extends AbstractCurl {

  public function queueCall(Call $call) {
    // We don't have to do anything here.
    // Except for saving the call.
    $this->core->getFactory()->update($call);
  }

}
