<?php namespace Drupal\cmrf_core\cmrf_views\Form;

use Drupal\Core\Entity\EntityForm;

class CMRFViewsDataset extends EntityForm {

  // Get connection profiles from the 'core'.
  //$conn_profiles=
  /*
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
  'replace_pattern' => '[^0-9a-zA-Z_\-]',
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

  return $form;*/

}