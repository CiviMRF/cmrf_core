<?php


namespace Drupal\cmrf_call_report\Plugin\views\filter;

use CMRF\Core\Call;
use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Exposes call report status to the views module
 *
 * @ViewsFilter("cmrf_call_report_connectors")
 */
class CMRFConnectors extends InOperator {
  public function getValueOptions() {
    if (!isset($this->valueOptions)) {
      $core = \Drupal::service('cmrf_core.core');
      $this->valueOptions = $core->getConnectors();
    }
    return $this->valueOptions;
  }
}
