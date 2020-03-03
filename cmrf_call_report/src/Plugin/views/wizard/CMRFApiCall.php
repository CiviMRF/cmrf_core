<?php

namespace Drupal\cmrf_call_report\Plugin\views\wizard;

use Drupal\views\Plugin\views\wizard\WizardPluginBase;

/**
 * Defines a wizard for the watchdog table.
 *
 * @ViewsWizard(
 *   id = "civicrm_api_call",
 *   module = "cmrf_core",
 *   base_table = "civicrm_api_call",
 *   title = @Translation("CMRF Calls")
 * )
 */
class CMRFApiCall extends WizardPluginBase {

  /**
   * Set the created column.
   *
   * @var string
   */
  protected $createdColumn = 'create_date';

  /**
   * {@inheritdoc}
   */
  protected function defaultDisplayOptions() {
    $display_options = parent::defaultDisplayOptions();

    // Add permission-based access control.
    $display_options['access']['type'] = 'perm';
    $display_options['access']['options']['perm'] = 'access site reports';

    return $display_options;
  }

}
