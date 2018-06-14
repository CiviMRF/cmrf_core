<?php

function cmrf_views_admin_list_datasets() {
  $page = array();

  $page['info']['#markup'] = '<p>'. t('After you have added or modified the datasets, do not forget to update the views cache to fetch the fields from CiviCRM').'</p>';
  $page['fetch']['#markup'] = '<p>'.l('Update views cache', 'admin/config/civimrf/views/update_entity_list').'</p>';

  $datasets = db_select('cmrf_views_dataset', NULL, array('fetch' => PDO::FETCH_ASSOC))
    ->fields('cmrf_views_dataset')
    ->orderBy('name')
    ->execute()
    ->fetchAllAssoc('id', PDO::FETCH_ASSOC);

  $page['cmrf_views_datasets'] = array(
    '#markup' => theme('cmrf_views_list_datasets', array('datasets' => $datasets)),
  );

  return $page;
}

function cmrf_views_dataset_form($form, &$form_state, $dataset=null) {
  $profiles = cmrf_core_list_profiles();
  foreach($profiles as $profile_name => $profile) {
    $profiles_options[$profile_name] = $profile['label'];
  }

  $form_state['dataset'] = $dataset;
  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Title'),
    '#default_value' => isset($dataset['title']) ? $dataset['title'] : '',
    '#required' => TRUE,
  );
  $form['name'] = array(
    '#type' => 'machine_name',
    '#description' => t('The name is used in URLs for this dataset. Use only lowercase alphanumeric characters, underscores (_), and hyphens (-).'),
    '#size' => '64',
    '#required' => TRUE,
    '#default_value' => isset($dataset['name']) ? $dataset['name'] : '',
    '#machine_name' => array(
      'exists' => 'cmrf_views_dataset_name_exists',
      'source' => array('label'),
      'replace_pattern' => '[^0-9a-z_\-]',
      'error' => t('Please only use lowercase alphanumeric characters, underscores (_), and hyphens (-) for style names.'),
    ),
  );

  $form['profile'] = array(
    '#type' => 'select',
    '#title' => t('CiviMRF Connection profile'),
    '#options' => $profiles_options,
    '#default_value' => isset($dataset['profile']) ? $dataset['profile'] : '',
    '#required' => true,
  );
  $form['entity'] = array(
    '#type' => 'textfield',
    '#title' => t('Entity'),
    '#default_value' => isset($dataset['entity']) ? $dataset['entity'] : '',
    '#required' => true,
  );
  $form['action'] = array(
    '#type' => 'textfield',
    '#title' => t('Action'),
    '#default_value' => isset($dataset['action']) ? $dataset['action'] : 'Get',
    '#required' => true,
  );
  $form['getcount'] = array(
    '#type' => 'textfield',
    '#title' => t('Getcount api action'),
    '#default_value' => isset($dataset['getcount']) ? $dataset['getcount'] : 'Getcount',
    '#required' => true,
  );
  $form['params'] = array(
    '#type' => 'textarea',
    '#title' => t('API Parameters'),
    '#description' => t('Enter the api parameters in JSON format. E.g. {"contact_sub_type": "Student", "is_deleted": "0", "is_deceased": "0"}'),
    '#default_value' => isset($dataset['params']) ? $dataset['params'] : '',
    '#required' => false,
  );

  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save dataset'));

  $form['#submit'][] = 'cmrf_views_dataset_form_submit';
  // By default, render the form using theme_system_settings_form().
  $form['#theme'] = 'system_settings_form';

  return $form;
}

function cmrf_views_dataset_name_exists($value) {
  $exists = db_query_range('SELECT 1 FROM {cmrf_views_dataset} WHERE name = :name', 0, 1, array(':name' => $value))->fetchField();
  return $exists;
}

function cmrf_views_dataset_form_submit($form, &$form_state) {
  form_state_values_clean($form_state);
  $dataset = $form_state['values'];
  if (isset($form_state['dataset']['id']) && !empty($form_state['dataset']['id'])) {
    $dataset['id'] = $form_state['dataset']['id'];
  }
  $dataset = cmrf_views_dataset_save($dataset);
  if ($dataset['is_new']) {
    drupal_set_message(t('Dataset %name created.', ['%name' => $dataset['title']]));
  } else {
    drupal_set_message(t('Dataset %name updated.', ['%name' => $dataset['title']]));
  }

  $form_state['redirect'] = 'admin/config/civimrf/views';
}

/**
 * Form builder; Form for deleting a CiviMRF views dataset
 *
 * @param $dataset
 *   The dataset
 * @return array
 */
function cmrf_views_dataset_delete_form($form, &$form_state, $dataset) {
  $form_state['dataset'] = $dataset;
  return confirm_form($form, t('Are you sure you want to delete dataset: %dataset', array('%dataset' => $dataset['title'])),'admin/config/civimrf/views');
}

/**
 * Submit handler to delete a CiviMRF connection profile.
 */
function cmrf_views_dataset_delete_form_submit($form, &$form_state) {
  $dataset = $form_state['dataset'];
  cmrf_views_dataset_delete($dataset);
  drupal_set_message(t('CiviMRF dataset for views %name was deleted.', array('%name' => $dataset['title'])));
  $form_state['redirect'] = 'admin/config/civimrf/views';
}

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
  views_invalidate_cache();
  $form_state['redirect'] = 'admin/config/civimrf/views';
}

function crmf_views_update_entity_list_form_cancel($form, &$form_state) {
  $form_state['redirect'] = 'admin/config/civimrf/views';
}

function theme_cmrf_views_list_datasets($variables) {
  $datasets = $variables['datasets'];

  $header = array(t('Title'), t('Machine name'), t('Profile'), t('Entity'), t('Action'), array('data' => t('Operations'), 'colspan' => 3));
  $rows = array();
  foreach ($datasets as $id => $dataset) {
    $row = array();
    $row[] = $dataset['title'];
    $row[] = $dataset['name'];
    $row[] = $dataset['profile'];
    $row[] = $dataset['entity'];
    $row[] = $dataset['action'];
    $row[] = l(t('edit'), 'admin/config/civimrf/views/edit-dataset/' . $dataset['name']);
    $row[] = l(t('delete'), 'admin/config/civimrf/views/delete-dataset/' .$dataset['name']);

    $rows[] = $row;
  }

  if (empty($rows)) {
    $rows[] = array(array(
      'colspan' => 6,
      'data' => t('There are currently no datasets. <a href="!url">Add a new one</a>.', array('!url' => url('admin/config/civimrf/views/add-dataset'))),
    ));
  }

  return theme('table', array('header' => $header, 'rows' => $rows));
}
