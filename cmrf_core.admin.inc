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
    '#type' => 'textfield',
    '#title' => t('Site key'),
    '#default_value' => variable_get('cmrf_core_default_site_key', ''),
    '#description' => t("The site key of your civicrm installation."),
    '#required' => TRUE,
  );

  $form['cmrf_core_default_api_key'] = array(
    '#type' => 'textfield',
    '#title' => t('Api key'),
    '#default_value' => variable_get('cmrf_core_default_api_key', ''),
    '#description' => t("The api key of your civicrm installation."),
    '#required' => TRUE,
  );

  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save configuration'));

  if (!empty($_POST) && form_get_errors()) {
    drupal_set_message(t('The settings have not been saved because of the errors.'), 'error');
  }
  $form['#submit'][] = 'cmrf_core_admin_form_submit';
  // By default, render the form using theme_system_settings_form().
  $form['#theme'] = 'system_settings_form';

  return $form;
}

/**
 * Form submission handler for system_settings_form().
 *
 * If you want node type configure style handling of your checkboxes,
 * add an array_filter value to your form.
 */
function cmrf_core_admin_form_submit($form, &$form_state) {
  // Exclude unnecessary elements.
  form_state_values_clean($form_state);

  foreach ($form_state['values'] as $key => $value) {
    if (is_array($value) && isset($form_state['values']['array_filter'])) {
      $value = array_keys(array_filter($value));
    }
    variable_set($key, $value);
  }

  // Test the connection by trying to retrieve one contact of the system.
  $result = cmrf_example_sendCall('Contact', 'getcount', array(), array());
  $reply = $result->getReply();
  if (empty($result) || $result->getStatus() != \CMRF\Core\Call::STATUS_DONE || !empty($reply['is_error'])) {
    drupal_set_message(t('Could not connect to your CiviCRM installation.'), 'error');
  } else {
    drupal_set_message(t('The configuration options have been saved.'));
  }
}