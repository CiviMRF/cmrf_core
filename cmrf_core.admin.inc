<?php

function cmrf_core_admin_list_profiles() {
  $page = array();

  $profiles = cmrf_core_list_profiles();
  $page['cmrf_profile_list'] = array(
    '#markup' => theme('cmrf_profile_list', array('profiles' => $profiles)),
  );

  return $page;
}

function cmrf_core_admin_list_calls() {
  $page = array();

  $calls = db_select('cmrf_core_call', NULL, array('fetch' => PDO::FETCH_ASSOC))
    ->fields('cmrf_core_call')
    ->orderBy('create_date')
    ->execute()
    ->fetchAllAssoc('cid', PDO::FETCH_ASSOC);

  $page['cmrf_call_list'] = array(
    '#markup' => theme('cmrf_call_list', array('calls' => $calls)),
  );

  return $page;
}

/**
 * Form builder; Form for adding a new image style.
 *
 * @ingroup forms
 * @see image_style_add_form_submit()
 */
function cmrf_core_profile_form($form, &$form_state, $profile=null) {
  $form_state['profile'] = $profile;
  $form['label'] = array(
    '#type' => 'textfield',
    '#title' => t('Profile name'),
    '#default_value' => isset($profile['label']) ? $profile['label'] : '',
    '#required' => TRUE,
  );
  $form['name'] = array(
    '#type' => 'machine_name',
    '#description' => t('The name is used in URLs for profiles. Use only lowercase alphanumeric characters, underscores (_), and hyphens (-).'),
    '#size' => '64',
    '#required' => TRUE,
    '#default_value' => isset($profile['name']) ? $profile['name'] : '',
    '#machine_name' => array(
      'exists' => 'cmrf_core_profile_add_name_exists',
      'source' => array('label'),
      'replace_pattern' => '[^0-9a-z_\-]',
      'error' => t('Please only use lowercase alphanumeric characters, underscores (_), and hyphens (-) for style names.'),
    ),
  );

  $connectors = cmrf_core_list_connectors();
  $connectors_options = array();
  foreach($connectors as $connector_name => $connector) {
    $connectors_options[$connector_name] = $connector['label'];
  }
  $form['connector']['#type'] = 'select';
  $form['connector']['#title'] = t('Select connector');
  $form['connector']['#options'] = $connectors_options;
  $form['connector']['#required'] = true;
  $form['connector']['#default_value'] = isset($profile['connector']) ? $profile['connector'] : '';

  $form['url'] = array(
    '#type' => 'textfield',
    '#title' => t('URL'),
    '#default_value' => isset($profile['url']) ? $profile['url'] : '',
    '#description' => t("The URL to your CiviCRM installation e.g. https://civi.my.site/sites/all/modules/civicrm/extern/rest.php."),
    '#states' => array(
      'visible' => array(
        ':input[name="connector"]' => array('value' => 'curl'),
      ),
      'required' => array(
        ':input[name="connector"]' => array('value' => 'curl'),
      ),
    ),
  );

  $form['site_key'] = array(
    '#type' => 'password',
    '#title' => t('Site key'),
    '#default_value' => isset($profile['site_key']) ? $profile['site_key'] : null,
    '#description' => t("The site key of your civicrm installation."),
    '#states' => array(
      'visible' => array(
        ':input[name="connector"]' => array('value' => 'curl'),
      ),
      'required' => array(
        ':input[name="connector"]' => array('value' => 'curl'),
      ),
    ),
  );

  $form['api_key'] = array(
    '#type' => 'password',
    '#title' => t('Api key'),
    '#default_value' => isset($profile['api_key']) ? $profile['api_key'] : null,
    '#description' => t("The api key of your civicrm installation."),
    '#states' => array(
      'visible' => array(
        ':input[name="connector"]' => array('value' => 'curl'),
      ),
      'required' => array(
        ':input[name="connector"]' => array('value' => 'curl'),
      ),
    ),
  );

  $form['cache_expire_days'] = array(
    '#type' => 'textfield',
    '#title' => t('Expire cache after days'),
    '#default_value' => isset($profile['cache_expire_days']) ? $profile['cache_expire_days'] : 0,
    '#description' => t("Clear the cache after n days (Set to 0 to disable). Successful calls will be removed as soon as they have been executed. This is about the failed calls."),
    '#required' => true,
  );

  $form['default']['#type'] = 'checkbox';
  $form['default']['#title'] = t('Default profile');
  $form['default']['#default_value'] = isset($profile['default']) && $profile['default'] ? 1 : 0;

  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save configuration'));

  if (!empty($_POST) && form_get_errors()) {
    drupal_set_message(t('The settings have not been saved because of the errors.'), 'error');
  }

  $form['#validate'][] = 'cmrf_core_profile_form_validate';
  $form['#submit'][] = 'cmrf_core_profile_form_submit';
  // By default, render the form using theme_system_settings_form().
  $form['#theme'] = 'system_settings_form';

  return $form;
}

function cmrf_core_profile_form_validate($form, &$form_state) {
  if ($form_state['values']['connector'] == 'curl') {
    if (empty($form_state['values']['url'])) {
      form_set_error('url', t('Url is required'));
    }

    if (empty($form_state['values']['site_key'])) {
      $form_state['values']['site_key'] = !empty($form_state['profile']['site_key']) ? $form_state['profile']['site_key'] : '';
      if (empty($form_state['values']['site_key'])) {
        form_set_error('site_key', t('Site key is required'));
      }
    }
    if (empty($form_state['values']['api_key'])) {
      $form_state['values']['api_key'] = !empty($form_state['profile']['site_key']) ? $form_state['profile']['api_key'] : '';
      if (empty($form_state['values']['api_key'])) {
        form_set_error('api_key', t('Api key is required'));
      }
    }
  }
}

/**
 * Submit handler for adding a new image style.
 */
function cmrf_core_profile_form_submit($form, &$form_state) {
  form_state_values_clean($form_state);
  $profile = $form_state['values'];
  if (isset($form_state['profile']['pid']) && !empty($form_state['profile']['pid'])) {
    $profile['pid'] = $form_state['profile']['pid'];
  }
  $profile = cmrf_core_profile_save($profile);
  if ($profile['is_new']) {
    drupal_set_message(t('Profile %name created.', ['%name' => $profile['label']]));
  } else {
    drupal_set_message(t('Profile %name updated.', ['%name' => $profile['label']]));
  }

  if (isset($form_state['profile']['default']) && $form_state['profile']['default'] && !$form_state['values']['default']) {
    variable_del('cmrf_core_default_profile');
  } elseif (isset($form_state['values']['default']) && $form_state['values']['default']) {
    variable_set('cmrf_core_default_profile', $profile['name']);
  }


  $form_state['redirect'] = 'admin/config/civimrf/profiles';
}

function cmrf_core_profile_add_name_exists($value) {
  $exists = db_query_range('SELECT 1 FROM {cmrf_core_profiles} WHERE name = :name', 0, 1, array(':name' => $value))->fetchField();
  return $exists;
}

/**
 * Form builder; Form for deleting a CiviMRF connection profile
 *
 * @param $profile
 *   The profile
 * @return array
 */
function cmrf_core_profile_delete_form($form, &$form_state, $profile) {
  $form_state['profile'] = $profile;
  return confirm_form($form, t('Are you sure you want to delete profile: %profile', array('%profile' => $profile['label'])),'admin/config/civimrf/profiles');
}

/**
 * Submit handler to delete a CiviMRF connection profile.
 */
function cmrf_core_profile_delete_form_submit($form, &$form_state) {
  $profile = $form_state['profile'];
  cmrf_core_profile_delete($profile);
  drupal_set_message(t('CiviMRF connection profile %name was deleted.', array('%name' => $profile['label'])));
  $form_state['redirect'] = 'admin/config/civimrf/profiles';
}

/**
 * Confirmation form to revert a database profile to its default.
 */
function cmrf_core_profile_revert_form($form, &$form_state, $profile) {
  $form_state['profile'] = $profile;

  return confirm_form(
    $form,
    t('Revert the %name profile?', array('%name' => $profile['label'])),
    'admin/config/civimrf/profiles',
    t('Reverting this profiles will delete the customized settings and restore the defaults provided by the @module module.', array('@module' => $profile['module'])),
    t('Revert'),  t('Cancel')
  );
}

/**
 * Submit handler to convert an overridden profile to its default.
 */
function cmrf_core_profile_revert_form_submit($form, &$form_state) {
  drupal_set_message(t('The %profile profile has been reverted to its defaults.', array('%profile' => $form_state['profile']['label'])));
  cmrf_core_profile_delete($form_state['profile']);
  $form_state['redirect'] = 'admin/config/civimrf/profiles';
}

function theme_cmrf_profile_list($variables) {
  $profiles = $variables['profiles'];

  $header = array(t('Profile'), t('Machine name'), t('Default'), t('State'), array('data' => t('Operations'), 'colspan' => 3));
  $rows = array();
  foreach ($profiles as $profile_name => $profile) {
    $row = array();
    if ($profile['readonly']) {
      $row[] = $profile['label'];
    } else {
      $row[] = l($profile['label'], 'admin/config/civimrf/profiles/edit/' . $profile_name);
    }
    $row[] = $profile_name;
    $row[] = $profile['default'] ? t('Default') : '';
    if ($profile['storage'] == CRMF_CORE_PROFILE_STORAGE_CODE) {
      $row[] = t('In code');
      if ($profile['readonly']) {
        $row[] = '';
        $row[] = '';
      } else {
        $row[] = l(t('edit'), 'admin/config/civimrf/profiles/edit/' . $profile_name);
        $row[] = '';
      }
    }
    elseif ($profile['storage'] == CRMF_CORE_PROFILE_STORAGE_OVERRIDE) {
      $row[] = t('Overridden');
      if ($profile['readonly']) {
        $row[] = '';
        $row[] = '';
      } else {
        $row[] = l(t('edit'), 'admin/config/civimrf/profiles/edit/' . $profile_name);
        $row[] = l(t('revert'), 'admin/config/civimrf/profiles/revert/' .$profile_name);
      }
    }
    else {
      $row[] = t('User defined');
      if ($profile['readonly']) {
        $row[] = '';
        $row[] = '';
      } else {
        $row[] = l(t('edit'), 'admin/config/civimrf/profiles/edit/' . $profile_name);
        $row[] = l(t('delete'), 'admin/config/civimrf/profiles/delete/' .$profile_name);
      }
    }
    $rows[] = $row;
  }

  if (empty($rows)) {
    $rows[] = array(array(
      'colspan' => 4,
      'data' => t('There are currently no profiles. <a href="!url">Add a new one</a>.', array('!url' => url('admin/config/civimrf/profiles/add'))),
    ));
  }

  return theme('table', array('header' => $header, 'rows' => $rows));
}

function theme_cmrf_call_list($variables) {
  $core = cmrf_core_get_core();
  $calls = $variables['calls'];

  $header = array(t('Date'), t('Status'), t('Profile'), t('Request'), t('Reply'), t('Scheduled date'), t('Cached until'), t('Retry count'));
  $rows = array();
  foreach ($calls as $cid => $call) {
    $path = 'admin/config/civimrf/calls/'.$call['cid'];
    $date = new \DateTime($call['create_date']);
    $status = $call['status'];
    $profile = $core->getConnectionProfile($call['connector_id']);
    $request =truncate_utf8($call['request'], 42, true, true);
    $reply = truncate_utf8($call['reply'], 42, true, true);
    $scheduled_date = '';
    if (!empty($call['scheduled_date'])) {
      $scheduled_date = new \DateTime($call['scheduled_date']);
      $scheduled_date = format_date($scheduled_date->getTimestamp());
    }
    $caching_until = '';
    if (!empty($call['cached_until'])) {
      $caching_until = new \DateTime($call['cached_until']);
      $caching_until = format_date($caching_until->getTimestamp());
    }
    $retry_count = $call['retry_count'];

    $row = array();
    $row[] = l(format_date($date->getTimestamp()), $path);
    $row[] = l($status, $path);
    $row[] = $profile['label'];
    $row[] = $request;
    $row[] = $reply;
    $row[] = $scheduled_date;
    $row[] = $caching_until;
    $row[] = $retry_count;
    $rows[] = $row;
  }

  if (empty($rows)) {
    $rows[] = array(array(
      'colspan' => 8,
      'data' => t('There are currently no calls persisted.'),
    ));
  }

  return theme('table', array('header' => $header, 'rows' => $rows));
}

function cmrf_core_admin_view_call($cid) {
  $core = cmrf_core_get_core();
  $call = db_query('SELECT * FROM {cmrf_core_call} WHERE cid = :id', array(':id' => $cid))->fetchObject();
  if ($call) {
    $date = new \DateTime($call->create_date);
    $date = format_date($date->getTimestamp());
    $status = $call->status;
    $profile = $core->getConnectionProfile($call->connector_id);
    $request = nl2br(json_encode(json_decode($call->request, true), JSON_PRETTY_PRINT));
    $reply = nl2br(json_encode(json_decode($call->reply,true), JSON_PRETTY_PRINT));
    $metadata = nl2br(json_encode(json_decode($call->metadata,true), JSON_PRETTY_PRINT));
    $scheduled_date = '';
    if (!empty($call->scheduled_date)) {
      $scheduled_date = new \DateTime($call->scheduled_date);
      $scheduled_date = format_date($scheduled_date->getTimestamp());
    }
    $caching_until = '';
    if (!empty($call->cached_until)) {
      $caching_until = new \DateTime($call->cached_until);
      $caching_until = format_date($caching_until->getTimestamp());
    }
    $retry_count = $call->retry_count;

    $rows = array(
      array(
        array('data' => t('Call ID'), 'header' => TRUE),
        array('data' => $call->cid)
      ),
      array(
        array('data' => t('Date'), 'header' => TRUE),
        array('data' => $date)
      ),
      array(
        array('data' => t('Status'), 'header' => TRUE),
        array('data' => $status)
      ),
      array(
        array('data' => t('Profile'), 'header' => TRUE),
        array('data' => $profile['label'])
      ),
      array(
        array('data' => t('Request'), 'header' => TRUE),
        array('data' => $request)
      ),
      array(
        array('data' => t('Reply'), 'header' => TRUE),
        array('data' => $reply)
      ),
      array(
        array('data' => t('Scheduled date'), 'header' => TRUE),
        array('data' => $scheduled_date)
      ),
      array(
        array('data' => t('Cached until'), 'header' => TRUE),
        array('data' => $caching_until)
      ),
      array(
        array('data' => t('Retry count'), 'header' => TRUE),
        array('data' => $retry_count)
      ),
      array(
        array('data' => t('Metedata'), 'header' => TRUE),
        array('data' => $metadata)
      ),
    );
    $build['#attached']['css'] = array(
      drupal_get_path('module', 'cmrf_core') . '/css/cmrf_core.css',
    );
    $build['call_log'] = array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#attributes' => array('class' => 'cmrf_call_log_view_table'),
    );
    return $build;
  }
  return '';
}
