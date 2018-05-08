<?php

function crmf_views_update_entity_list_form($form, &$form_state) {
  $form['confirm_text']['#markup'] = '<p>'.t('Are you sure to update the list of CiviMRF Entities available in Drupal views?').'</p>';
  
  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Update entities available in views'));
  $form['actions']['cancel'] = array('#type' => 'submit', '#value' => t('Cancel'), '#submit' => 'crmf_views_update_entity_list_form_cancel');
  
  $form['#submit'][] = 'crmf_views_update_entity_list_form_submit';
  // By default, render the form using theme_system_settings_form().
  $form['#theme'] = 'system_settings_form';
  
  return $form;
}

function crmf_views_update_entity_list_form_submit($form, &$form_state) {
  cmrf_views_entity_list(TRUE);
  $form_state['redirect'] = 'admin/config';
}

function crmf_views_update_entity_list_form_cancel($form, &$form_state) {
  $form_state['redirect'] = 'admin/config';
}
