<?php
namespace Drupal\cmrf_call_report\Plugin\views\filter;

use CMRF\Core\Call;
use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Exposes call report status to the views module
 *
 * @ViewsFilter("cmrf_call_report_status")
 */
class CMRFApiCallStatusTypes extends InOperator {

  public function getValueOptions() {
    if (!isset($this->valueOptions)) {
      $this->valueOptions = [
        Call::STATUS_INIT => Call::STATUS_INIT,
        Call::STATUS_WAITING =>  Call::STATUS_WAITING,
        Call::STATUS_SENDING => Call::STATUS_SENDING,
        Call::STATUS_DONE => Call::STATUS_DONE,
        Call::STATUS_RETRY => Call::STATUS_RETRY,
        Call::STATUS_FAILED => Call::STATUS_FAILED ,
      ];
    }
    return $this->valueOptions;
  }

}
