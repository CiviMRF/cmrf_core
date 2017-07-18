<?php

function cmrf_core_admin_form() {

  $form = array();

  $form['cmrf_core_default_url'] = array(
    '#type' => 'textfield',
    '#title' => t('URL'),
    '#default_value' => variable_get('cmrf_core_default_url', ''),
    '#description' => t("The URL to your CiviCRM installation e.g. https://civi.my.site/sites/all/modules/civicrm/extern/rest.php."),
    '#required' => TRUE,
  );

  $form['cmrf_core_default_site_key'] = array(
    '#type' => 'password',
    '#title' => t('Site key'),
    '#default_value' => variable_get('cmrf_core_default_site_key', ''),
    '#description' => t("The site key of your civicrm installation."),
  );

  $form['cmrf_core_default_api_key'] = array(
    '#type' => 'password',
    '#title' => t('Api key'),
    '#default_value' => variable_get('cmrf_core_default_api_key', ''),
    '#description' => t("The api key of your civicrm installation."),
  );

  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save configuration'));

  if (!empty($_POST) && form_get_errors()) {
    drupal_set_message(t('The settings have not been saved because of the errors.'), 'error');
  }

  $form['#validate'][] = 'cmrf_core_admin_form_validate';
  $form['#submit'][] = 'cmrf_core_admin_form_submit';
  // By default, render the form using theme_system_settings_form().
  $form['#theme'] = 'system_settings_form';

  return $form;
}

/**
 * Validation for the admin_form. Check whether the site key or api key is empty. If so do not change the key.
 * 
 * @param $form
 * @param $form_state
 */
function cmrf_core_admin_form_validate($form, &$form_state) {
  if (empty($form_state['values']['cmrf_core_default_site_key'])) {
    $form_state['values']['cmrf_core_default_site_key'] = variable_get('cmrf_core_default_site_key', '');
    if (empty($form_state['values']['cmrf_core_default_site_key'])) {
      form_set_error('cmrf_core_default_site_key', t('Site key is required'));
    }
  }
  if (empty($form_state['values']['cmrf_core_default_api_key'])) {
    $form_state['values']['cmrf_core_default_api_key'] = variable_get('cmrf_core_default_api_key', '');
    if (empty($form_state['values']['cmrf_core_default_api_key'])) {
      form_set_error('cmrf_core_default_site_key', t('Api key is required'));
    }
  }
}

function cmrf_core_admin_form_submit($form, &$form_state) {
  variable_set('cmrf_core_default_url', $form_state['values']['cmrf_core_default_url']);
  variable_set('cmrf_core_default_site_key', $form_state['values']['cmrf_core_default_site_key']);
  variable_set('cmrf_core_default_api_key', $form_state['values']['cmrf_core_default_api_key']);

  // Test the connection by trying to retrieve one contact of the system.
  $result = cmrf_example_sendCall('Contact', 'getcount', array(), array());
  $reply = $result->getReply();
  if (empty($result) || $result->getStatus() != \CMRF\Core\Call::STATUS_DONE || !empty($reply['is_error'])) {
    drupal_set_message(t('Could not connect to your CiviCRM installation.'), 'error');
  } else {
    drupal_set_message(t('The configuration options have been saved.'));
  }
}