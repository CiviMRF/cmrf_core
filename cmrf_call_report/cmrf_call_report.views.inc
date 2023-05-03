<?php

/**
 * @file
 * Provide views data for dblog.module.
 */

/**
 * Implements hook_views_data().
 */
function cmrf_call_report_views_data() {
  $data = [];

  $data['civicrm_api_call']['table']['group'] = t('CMRF Calls');
  $data['civicrm_api_call']['table']['wizard_id'] = 'civicrm_api_call';

  $data['civicrm_api_call']['table']['base'] = [
    'field' => 'cid',
    'title' => t('CiviMRF Calls'),
    'help' => t('All the logged CiviMRF API Calls'),
  ];
  $data['civicrm_api_call']['cid'] = [
    'title' => t('CID'),
    'help' => t('Call ID.'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['civicrm_api_call']['status'] = [
    'title' => t('Status'),
    'help' => t('Status van de request'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'cmrf_call_report_status',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['civicrm_api_call']['connector_id'] = [
    'title' => t('Connector ID'),
    'help' => t('The connection the request was made to'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'cmrf_call_report_connectors',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['civicrm_api_call']['entity'] = [
    'title' => t('Entity'),
    'help' => t('the CiviCRM API entity.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['civicrm_api_call']['action'] = [
    'title' => t('Action'),
    'help' => t('The CiviCRM API action.'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['civicrm_api_call']['request'] = [
    'title' => t('Request'),
    'help' => t('Request'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['civicrm_api_call']['reply'] = [
    'title' => t('Reply'),
    'help' => t('Reply'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['civicrm_api_call']['metadata'] = [
    'title' => t('Meta Data'),
    'help' => t('More info about the call'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['civicrm_api_call']['request_hash'] = [
    'title' => t('Hash'),
    'help' => t('Hash (makes request tamper free)'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['civicrm_api_call']['create_date'] = [
    'title' => t('Created'),
    'help' => t('Time the call was created'),
    'field' => [
      'id' => 'cmrf_views_date',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['civicrm_api_call']['scheduled_date'] = [
    'title' => t('Scheduled'),
    'help' => t('Time the call is scheduled to be executed'),
    'field' => [
      'id' => 'cmrf_views_date',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['civicrm_api_call']['reply_date'] = [
    'title' => t('Reply Date'),
    'help' => t('The moment the call returned'),
    'field' => [
      'id' => 'cmrf_views_date',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['civicrm_api_call']['cached_until'] = [
    'title' => t('Cached Until'),
    'help' => t('Stored in cache until'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['civicrm_api_call']['retry_count'] = [
    'title' => t('Retry Count'),
    'help' => t('How many times its retried'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  return $data;
}
