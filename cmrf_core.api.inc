<?php

/**
 * Example implementation of the provided API by this module
 **/

/**
 * Modify any CiviMRF connectors provided by other modules.
 *
 * This hook allows modules to modify, add, or remove connectors.
 *
 * If your module needs to provide a new connector (rather than modify an existing
 * one) use hook_cmrf_core_connectors() instead.
 * Note: do not forget to implement the connector class.
 *
 * @see hook_cmrf_core_default_profile()
 */
function hook_cmrf_core_connectors_alter(&$connectors) {
  // Change the default curl implementation.
  if (isset($connectors['curl'])) {
    $connectors['curl']['callback'] = 'overide_curl_callback';
    // The callback function overide_curl_callback will look like:
    //   function overide_curl_callback(\CMRF\Core\Core $core, $connector_id) {
    //     return new \MyCMRF\Connectors\OverridenCurlConnector($core, $connector_id);
    //   }
  }
}

/**
 * Provide module-based CiviMRF connectors for reuse throughout Drupal.
 *
 * This hook allows your module to provide CiviMRF Connectors. This may be useful if
 * you require a different connector e.g. a connector to a rabbit mq implementation
 *
 * Note: do not forget to implement the connector class.
 *
 * @return array
 *   An array of connectors, keyed by the connector name.
 */
function hook_cmrf_core_connectors() {
  $connectors['my_module_connector'] = array(
    'label' => 'Custom Connector',
    'callback' => 'my_module_connector_callback',
    // The callback function my_module_connector_callback will look like:
    //
    //   function my_module_connector_callback(\CMRF\Core\Core $core, $connector_id) {
    //     return new \MyCMRF\Connectors\MyConnector($core, $connector_id);
    //   }
  );

  return $connectors;
}


/**
 * Modify any CiviMRF connection profiles provided by other modules or the user.
 *
 * This hook allows modules to modify, add, or remove profiles. This may
 * be useful to modify default profiles provided by other modules or enforce
 * that the api key and site key are not stored in the database but on the file system.
 *
 * Note that modifications to these profiles may negatively affect the user experience, such as if an
 * api key is changed through this hook, the user may attempt to change
 * the key but it will be immediately be reset.
 *
 * The best use of this hook is usually to modify default profiles, which are not
 * editable by the user until they are overridden, so such interface
 * contradictions will not occur. This hook can target default (or user) connection profiles
 * by checking the $profile['storage'] property.
 *
 * If your module needs to provide a new profile (rather than modify an existing
 * one) use hook_cmrf_core_default_profile() instead.
 *
 * @see hook_cmrf_core_default_profile()
 */
function hook_cmrf_core_profiles_alter(&$profiles) {
  // Check that we only affect a default style.
  if ($profiles['default']['storage'] == IMAGE_STORAGE_DEFAULT) {
    // Add an additional effect to the thumbnail style.
    $profiles['default']['url'] = 'https://the.secure.civicrm.org/sites/all/modules/';
    $profiles['default']['site_key'] = '1237655hghhgh';
    $profiles['default']['api_key'] = 'abcdef67899';
    $profiles['default']['readonly'] = true;
  }
}

/**
 * Provide module-based CiviMRF profiles for reuse throughout Drupal.
 *
 * This hook allows your module to provide your own CiviMRF profiles. This may be useful if
 * you require the credentials of the connection profile to be stored somewhere else.
 * E.g. in an encrypted file.
 *
 * @return array
 *   An array of CiviMRF profiles, keyed by the profile name.
 */
function hook_cmrf_core_profiles() {
  $profiles['mymodule_profile'] = array(
    'label' => 'My module CiviMRF profile', // Required
    'connector' => 'curl', // Required
    'url' => 'https://your.civi.org/sites/all/modules/civicrm/extern/rest.php', // The rest end point
    'site_key' => 'abcdefgh1234',
    'api_key' => 'poiuyt09877',
    'readonly' => true, // Whether the user can override and change the connector parameters.
  );

  return $profiles;
}