<?php namespace Drupal\cmrf_views;

use Drupal\cmrf_core\Call;
use Drupal\cmrf_core\Core;
use Drupal\cmrf_views\Entity\CMRFDataset;

class CMRFViews {

  protected $core;

  public function __construct(Core $core) {
    $this->core      = $core;
    $this->connector = 'devz_no';
  }

  /**
   * Retrieve a list of entities available for Drupal Views.
   *
   * This function caches the result as the definition are build upon requesting the data
   * from the remote civicrm installation.
   *
   * When $reset is TRUE then the cache is ignored and new values are stored in the cache.
   *
   * Returns the data in the format for the hook_views_data.
   *
   * @param bool $reset
   *   Whether the cache should reset.
   *
   * @return array
   *   In format which could be used by the hook_views_data.
   */
  public function getViewsData($reset = FALSE) {
    $data = [];
    //$strData = variable_get('cmrf_views_entities');
    //if (!empty($strData)) {
    //  $data = json_decode($strData, TRUE);
    //  if (!is_array($data)) {
    //    $data  = [];
    //    $reset = TRUE;
    //  }
    //}
    //else {
    //  $reset = TRUE;
    //}

    if (TRUE || $reset) {
      $data     = [];
      $entities = $this->getDatasets();
      if (!empty($entities)) {
        foreach ($entities as $entity_name => $entity_definition) {
          $fields = $this->getEntityFields($entity_definition['entity'], $entity_definition['action']);
          if (count($fields)) {
            $entity_definition['params'] = isset($entity_definition['params']) ? $entity_definition['params'] : '';
            $data['cmrf:' . $entity_definition['profile'] . '_' . $entity_name] = [
              'table' => [
                'group' => $entity_definition['label'],
                'base'  => [
                  'field'       => 'id',
                  'title'       => $entity_definition['label'],
                  'query class' => 'cmrf_views',
                  'entity'      => $entity_definition['entity'],
                  'action'      => $entity_definition['action'],
                  'count'       => $entity_definition['count'],
                  'profile'     => $entity_definition['profile'],
                  'params'      => json_encode($entity_definition['params']),
                ],
              ],
              $fields,
            ];
          }
        }
      }

      //variable_set('cmrf_views_entities', json_encode($data));
    }
    return $data;
  }


  /**
   * Get views datasets from the config entity.
   *
   * @return array
   */
  public function getDatasets() {

    // Return init.
    $return = [];

    // Get entities ids.
    $query   = \Drupal::entityQuery('cmrf_dataset');
    $results = $query->execute();
    $ids     = array_keys($results);

    // Load entities by id.
    $loaded = CMRFDataset::loadMultiple($ids);
    if (!empty($loaded)) {
      foreach ($loaded as $entity) {
        $return[$entity->id()]           = [
          'label'    => $entity->label(),
          'profile'  => $entity->profile,
          'entity'   => $entity->entity,
          'action'   => $entity->action,
          'getcount' => $entity->getcount,
        ];
        $params                          = json_decode($entity->params, TRUE);
        $return[$entity->id()]['params'] = empty($params) ? [] : $params;
      }
    }

    return $return;
  }

  private function fetchOptions($api_entity, $api_action, $field_name) {
    // Get field options API call.
    $call = $this->core->createCall(
      $this->connector,
      $api_entity,
      'getoptions',
      ['field' => $field_name],
      [
        'limit' => 0,
        'cache' => '5 minutes',
      ]
    );

    // Execute call.
    $this->core->executeCall($call);
    if ($call->getStatus() == Call::STATUS_DONE) {
      $optionResult = $call->getReply();
      if (isset($optionResult['values']) && is_array($optionResult['values'])) {
        return $optionResult['values'];
      }
    }

    // Get fields API call.
    $call = $this->core->createCall(
      $this->connector,
      $api_entity,
      'getfields',
      ['api_action' => $api_action],
      ['limit' => 0]
    );

    // Execute call.
    $this->core->executeCall($call);
    if ($call->getStatus() == Call::STATUS_DONE) {
      $fields = $call->getReply();
      if (isset($fields['values']) && is_array($fields['values']) && isset($fields['values'][$field_name]) && isset($fields['values'][$field_name]['options']) && is_array($fields['values'][$field_name]['options'])) {
        return $fields['values'][$field_name]['options'];
      }
    }
    return [];
  }

  /**
   * Retrieve all the fields for an entity in the formal of Drupal views.
   */
  public function getEntityFields($api_entity, $api_action) {
    $return = [];

    // Create API call.
    $call = $this->core->createCall(
      $this->connector,
      $api_entity,
      'getfields',
      ['api_action' => $api_action],
      ['limit' => 0]
    );

    // Execute call.
    $this->core->executeCall($call);
    if ($call->getStatus() != Call::STATUS_DONE) {
      return;
    }
    $fields = $call->getReply();
    if (isset($fields['values']) && is_array($fields['values'])) {
      foreach ($fields['values'] as $field_name => $field) {
        $fieldOtions = FALSE;
        $filterField = TRUE;
        $returnField = TRUE;
        if (isset($field['api.filter'])) {
          $filterField = $field['api.filter'] ? TRUE : FALSE;
        }
        if (isset($field['api.return'])) {
          $returnField = $field['api.return'] ? TRUE : FALSE;
        }

        // Check whether this field is a select field (such as event_type_id)
        if (isset($field["pseudoconstant"]) || isset($field['options']) && is_array($field['options'])) {
          $fieldOtions = $this->fetchOptions($api_entity, $api_action, $field_name);
        }

        if (!isset($field['type'])) {
          $field['type'] = 0; // Set to 0 so we assign a default handler
        }
        $return[$field_name]['title'] = $field['title'];
        if (isset($field['description'])) {
          $return[$field_name]['help'] = $field['description'];
        }
        $return[$field_name]['field']['click sortable'] = TRUE;
        switch ($field['type']) {
          case 1: // Integer
            $return[$field_name]['field']['handler'] = 'cmrf_views_handler_field_numeric';
            $return[$field_name]['sort']['handler']  = 'views_handler_sort';
            if (!empty($fieldOtions)) {
              $return[$field_name]['field']['handler'] = 'cmrf_views_handler_field_prerender_list';
              $return[$field_name]['field']['options'] = $fieldOtions;
            }
            if (!empty($fieldOtions) && $filterField) {
              $return[$field_name]['filter']['handler'] = 'cmrf_views_handler_filter_in_operator';
              $return[$field_name]['filter']['options'] = $fieldOtions;
            }
            elseif ($filterField) {
              $return[$field_name]['filter']['handler'] = 'views_handler_filter_numeric';
            }
            $return[$field_name]['argument']['handler'] = 'views_handler_argument';
            break;
          case 4: // Date field
          case 12: // Date and time field
          case 256: // Timestamp field
            $return[$field_name]['field']['handler'] = 'cmrf_views_handler_field_date';
            $return[$field_name]['sort']['handler']  = 'views_handler_sort';
            if (!empty($fieldOtions) && $filterField) {
              $return[$field_name]['filter']['handler'] = 'cmrf_views_handler_filter_in_operator';
              $return[$field_name]['filter']['options'] = $fieldOtions;
            }
            elseif ($filterField) {
              $return[$field_name]['filter']['handler'] = 'views_handler_filter_date';
            }
            $return[$field_name]['argument']['handler'] = 'views_handler_argument_date';
            break;
          case 16: // Boolean
            $return[$field_name]['field']['handler'] = 'views_handler_field_boolean';
            $return[$field_name]['sort']['handler']  = 'views_handler_sort';
            if ($filterField) {
              $return[$field_name]['filter']['handler']   = 'cmrf_views_handler_filter_boolean_operator';
              $return[$field_name]['filter']['use equal'] = TRUE;
              $return[$field_name]['filter']['options']   = $fieldOtions;
            }
            $return[$field_name]['argument']['handler'] = 'views_handler_argument';
            break;
          case 32: // Text and Long Text
            $return[$field_name]['field']['handler'] = 'cmrf_views_handler_field_markup';
            $return[$field_name]['sort']['handler']  = 'views_handler_sort';
            if (!empty($fieldOtions)) {
              $return[$field_name]['field']['handler'] = 'cmrf_views_handler_field_prerender_list';
              $return[$field_name]['field']['options'] = $fieldOtions;
            }
            if (!empty($fieldOtions) && $filterField) {
              $return[$field_name]['filter']['handler'] = 'cmrf_views_handler_filter_in_operator';
              $return[$field_name]['filter']['options'] = $fieldOtions;
            }
            elseif ($filterField) {
              $return[$field_name]['filter']['handler'] = 'views_handler_filter_string';
            }
            $return[$field_name]['argument']['handler'] = 'views_handler_argument';
            break;
          default:
            $return[$field_name]['field']['handler'] = 'cmrf_views_handler_field';
            $return[$field_name]['sort']['handler']  = 'views_handler_sort';
            if (isset($field['data_type']) && $field['data_type'] == 'File') {
              $return[$field_name]['field']['handler'] = 'cmrf_views_handler_field_file';
            }
            else if (!empty($fieldOtions)) {
              $return[$field_name]['field']['handler'] = 'cmrf_views_handler_field_prerender_list';
              $return[$field_name]['field']['options'] = $fieldOtions;
            }
            if (!empty($fieldOtions) && $filterField) {
              $return[$field_name]['filter']['handler'] = 'cmrf_views_handler_filter_in_operator';
              $return[$field_name]['filter']['options'] = $fieldOtions;
            }
            elseif ($filterField) {
              $return[$field_name]['filter']['handler'] = 'views_handler_filter_string';
            }
            $return[$field_name]['argument']['handler'] = 'views_handler_argument';
            break;
        }
      }
    }

    return $return;
  }

}