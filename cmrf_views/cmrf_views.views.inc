<?php

/**
 * Implements hook_views_data().
 */
function cmrf_views_views_data() {
  return cmrf_views_entity_list(FALSE);
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