<?php

/**
 * Implements hook_views_data().
 */
function cmrf_views_views_data() {
  $data = array();
	
	$reloadData = false;
	$strData = variable_get('cmrf_views_data');
	if (!empty($strData)) {
		$data = json_decode($strData, true);
		$dataTimestamp = variable_get('cmrf_views_data_timestamp');
		if (empty($dataTimestamp)) {
			$reloadData = true;
		} else {
			$dataDate = new DateTime($dataTimestamp);
			$dataDate->modify('+6 hours');
			$now = new DateTime();
			if ($now >= $dataDate) {
				$reloadData = true;
			}
		}
	} else {
		$reloadData = true;
	}
	
	if ($reloadData) {
		_cmrf_views_civicrm_entities($data);
		variable_set('cmrf_views_data', json_encode($data));
		variable_set('cmrf_views_data_timestamp', date('Y-m-d H:i'));
	}
	return $data;
}

function _cmrf_views_civicrm_entities(&$data) {
	$parameters = array();
  $entities = array(
  	'Activity',
		'ActivityContact',
		'ActivityType',
		'Address',
		'Campaign',
		'Case',
		'CaseContact',
		'CaseType',
		'Contact',
		'Contribution',
		'Email',
		'Event',
		'Grant',
		'Group',
		'GroupContact',
		'Im',
		'Membership',
		'MembershipPayment',
		'MembershipStatus',
		'MembershipType',
		'Note',
		'OpenID',
		'Participant',
		'ParticipantPayment',
		'ParticipantStatusType',
		'Phone',
		'Pledge',
		'PledgePayment',
		'Relationship',
		'RelationshipType',
		'Website',		
	);
	foreach($entities as $entity) {
		$fields = _cmrf_views_civicrm_fields($entity);
		if (count($fields)) {
			$data['cmrf_'.$entity] = $fields;
			$data['cmrf_'.$entity]['table']['group'] = t('CiviMRF '.$entity); 
			$data['cmrf_'.$entity]['table']['base'] = array(
  			'field' => 'id',
    		'title' => t('CiviMRF '.$entity),
    		'query class' => 'cmrf_views',
  		);
		}
	}
}

function _cmrf_views_civicrm_fields($entity) {
	$return = array();
	$parameters = array(
		'api_action' => 'get',
	);
  $options['limit'] = 0;
	$call = cmrf_views_sendCall($entity, 'getfields', $parameters, $options);
	if ($call->getStatus() != \CMRF\Core\Call::STATUS_DONE) {
		return;
	}
  $fields = $call->getReply();
	if (isset($fields['values']) && is_array($fields['values'])) {
		foreach($fields['values'] as $field_name => $field) {
			$fieldOtions = false;	
			
			// Check whether this field is a select field (such as event_type_id)
			if (isset($field["pseudoconstant"])) {
				$optionsParameter = array('field' => $field_name);
				$optionsOptions['limit'] = 0;
				$call = cmrf_views_sendCall($entity, 'getoptions', $optionsParameter, $options);
				if ($call->getStatus() == \CMRF\Core\Call::STATUS_DONE) {
	  			$options = $call->getReply();	
					if ($options && is_array($options['values'])) {
						$fieldOtions = $options['values'];
					}
				}	
			}
			
			if (!isset($field['type'])) {
				$field['type'] = 0; // Set to 0 so we assign a default handler
			}
			$return[$field_name]['title'] = $field['title'];
			if (isset($field['description'])) {
				$return[$field_name]['help'] = $field['description'];
			}
			$return[$field_name]['field']['click sortable'] = true;
			switch ($field['type']) {
				case 1: // Integer
					$return[$field_name]['field']['handler'] = 'views_handler_field_numeric';
					$return[$field_name]['sort']['handler'] = 'views_handler_sort';
					if (!empty($fieldOtions)) {
						$return[$field_name]['filter']['handler'] = 'cmrf_views_handler_filter_in_operator';
						$return[$field_name]['filter']['options'] = $fieldOtions;
					} else {
						$return[$field_name]['filter']['handler'] = 'views_handler_filter_numeric';
					}
					$return[$field_name]['argument']['handler'] = 'views_handler_numeric';
					break;
				case 12: // Date and time field
					$return[$field_name]['field']['handler'] = 'cmrf_views_handler_field_date';
					$return[$field_name]['sort']['handler'] = 'views_handler_sort';
					if (!empty($fieldOtions)) {
						$return[$field_name]['filter']['handler'] = 'cmrf_views_handler_filter_in_operator';
						$return[$field_name]['filter']['options'] = $fieldOtions;
					} else {
						$return[$field_name]['filter']['handler'] = 'views_handler_filter_date';
					}
					$return[$field_name]['argument']['handler'] = 'views_handler_argument_date';
					break;
				case 16: // Boolean
					$return[$field_name]['field']['handler'] = 'views_handler_field_boolean';
					$return[$field_name]['sort']['handler'] = 'views_handler_sort';
					$return[$field_name]['filter']['handler'] = 'views_handler_filter_boolean_operator';
					$return[$field_name]['filter']['use equal'] = TRUE;
					$return[$field_name]['argument']['handler'] = 'views_handler_argument';
					break;	
				case 32: // Text and Long Text
					$return[$field_name]['field']['handler'] = 'cmrf_views_handler_field_markup';
					$return[$field_name]['sort']['handler'] = 'views_handler_sort';
					if (!empty($fieldOtions)) {
						$return[$field_name]['filter']['handler'] = 'cmrf_views_handler_filter_in_operator';
						$return[$field_name]['filter']['options'] = $fieldOtions;
					} else {
						$return[$field_name]['filter']['handler'] = 'views_handler_filter';
					}
					$return[$field_name]['argument']['handler'] = 'views_handler_argument';
					break; 
				default:
					$return[$field_name]['field']['handler'] = 'views_handler_field';
					$return[$field_name]['sort']['handler'] = 'views_handler_sort';
					if (!empty($fieldOtions)) {
						$return[$field_name]['filter']['handler'] = 'cmrf_views_handler_filter_in_operator';
						$return[$field_name]['filter']['options'] = $fieldOtions;
					} else {
						$return[$field_name]['filter']['handler'] = 'views_handler_filter';
					}
					$return[$field_name]['argument']['handler'] = 'views_handler_argument';
					break;		
			}
		}
	}
	return $return;
}

/**
 * Implements hook_views_plugins().
 */
function cmrf_views_views_plugins() {
  return array(
    'query' => array(
      'cmrf_views' => array(
        'title' => t('CiviMRF'),
        'help' => t('Queries CiviCRM'),
        'handler' => 'cmrf_views_plugin_query_civimrf',
      ),
    ),
  );
}