<?php

/**
 * Implements hook_views_data().
 */
function cmrf_views_views_data() {
  $data = [];

  // Base data.
  $data['cmrf_views']['table']['group'] = t('CiviCRM');
  $data['cmrf_views']['table']['base']  = [
    'title'    => t('CiviCRM Data'),
    'help'     => t('Queries CiviCRM'),
    'query_id' => 'cmrf_views',
  ];

  // Fields.
  $data['cmrf_views']['display_name']        = [
    'title' => t('Display name'),
    'help'  => t('Fitbit users\' display name.'),
    'field' => [
      'id' => 'standard',
    ],
  ];
  $data['cmrf_views']['average_daily_steps'] = [
    'title' => t('Average daily steps'),
    'help'  => t('The average daily steps over all the users logged Fitbit data.'),
    'field' => [
      'id' => 'numeric',
    ],
  ];
  $data['cmrf_views']['avatar']              = [
    'title' => t('Avatar'),
    'help'  => t('Fitbit users\' account picture.'),
    'field' => [
      'id' => 'standard',
    ],
  ];
  $data['cmrf_views']['height']              = [
    'title' => t('Height'),
    'help'  => t('Fibit users\'s height.'),
    'field' => [
      'id'    => 'numeric',
      'float' => TRUE,
    ],
  ];

  return $data;
}
