<?php

/**
 * Example implementation of the provided API by this module
 **/

/**
 * Modify the CiviCRM API entities available in Drupal views.
 * 
 * For each entity specify which API entity it is, which action is used for get and which action is 
 * used for the getcount. 
 * Keep in mind that the fields are retrieved with the getfields method.
 *
 * Each entity exist of the following data:
 *   'label' => The label of the entity,
 *   'entity' => The api entity,
 *   'action' => The action used for retrieval of the data,
 *   'count' => the action used for the getcount api call,
 *   'params' => array of api params
 *   'profile' => name of the cmrf profile to use
 * 
 *   
 */
function hook_cmrf_views_entity_list_alter(&$entity_list, $profile) {
  $label = $profile['label'].': '.t('Portal Team Member');
  $entity_list['PortalTeamMember'] = array(
    'label' => $label,
    'entity' => 'PortalTeamMember',
    'action' => 'get',
    'count' => 'getcount',
  );
}