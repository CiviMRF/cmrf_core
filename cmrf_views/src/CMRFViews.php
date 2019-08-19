<?php namespace Drupal\cmrf_views;

use Drupal\cmrf_core\Call;
use Drupal\cmrf_core\Core;
use Drupal\cmrf_views\Entity\CMRFDataset;

class CMRFViews {

  protected $core;

  public function __construct(Core $core) {
    $this->core = $core;
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
      $datasets = $this->getDatasets();
      if (!empty($datasets)) {
        foreach ($datasets as $dataset_id => $dataset_prop) {
          if ((!empty($dataset_prop['connector'])) && (!empty($dataset_prop['entity']) && (!empty($dataset_prop['action'])))) {
            $fields = $this->getFields($dataset_prop);
            if ((!empty($fields)) && (is_array($fields))) {
              // Unique identifier for this group.
              $uid = 'cmrf_views_' . $dataset_prop['connector'] . '_' . $dataset_id;
              // Base data.
              $data[$uid] = $this->getBaseData($dataset_prop);
              // Fields (from the getEntityFields function).
              $data[$uid] = array_merge($fields, $data['cmrf_views_' . $dataset_prop['connector'] . '_' . $dataset_id]);
            }
          }
        }
      }

      //variable_set('cmrf_views_entities', json_encode($data));
    }

    return $data;
  }

  /**
   * Generate base table for views data.
   *
   * @param $dataset
   *
   * @return mixed
   */
  private function getBaseData($dataset) {
    https://simulator.advancecare.pt/umbraco/Surface/Search/Results?q=&sort=relevance&currentPage=1&numSpecialties=120&numClinics=120&numDistricts=120&numProcedures=120&numCounties=120&numNetworks=120&policy=&lang=pt-PT&lat=32.6478443&lng=-16.907875&providerId=40284&practiceSeq=2&providerIdParent=501882766&practiceSeqParent=20&procedure=&specialty=&clinic=&district=Regi%C3%A3o%20Aut.%20da%20Madeira&network=&county=#
    $base_data['table'] = [];

    if (!empty($dataset['label'])) {
      $base_data['table']['group'] = $dataset['label'];
      $base_data['table']['base']  = [
        'title'    => $dataset['label'],
        'help'     => $dataset['label'] . ' provided by CiviCRM API',
        'query_id' => 'civicrm_api',
      ];
    }

    if (!empty($dataset['entity'])) {
      $base_data['table']['base']['entity'] = $dataset['entity'];
    }

    if (!empty($dataset['action'])) {
      $base_data['table']['base']['action'] = $dataset['action'];
    }

    if (!empty($dataset['getcount'])) {
      $base_data['table']['base']['getcount'] = $dataset['getcount'];
    }

    if (!empty($dataset['connector'])) {
      $base_data['table']['base']['connector'] = $dataset['connector'];
    }

    if (!empty($dataset['params'])) {
      $base_data['table']['base']['params'] = json_encode(isset($dataset_prop['params']) ? $dataset_prop['params'] : '');
    }

    return $base_data;
  }

  /**
   * Retrieve all the fields for an entity in the form of Drupal views.
   *
   * @param $api_entity
   * @param $api_action
   *
   * @return array
   */
  public function getFields($dataset) {

    if ((!empty($dataset['connector'])) && (!empty($dataset['entity'])) && (!empty($dataset['action']))) {

      // API Call to retrieve the fields.
      $call = $this->core->createCall($dataset['connector'], $dataset['entity'], 'getfields', ['api_action' => $dataset['action']], ['limit' => 0]);
      $this->core->executeCall($call);
      if ($call->getStatus() != Call::STATUS_DONE) {
        return [];
      }

      // Get fields value.
      $fields = $call->getReply();
      if (empty($fields['values'])) {
        return [];
      }

      // Loop through each field to create the appropriate structure for views data.
      $views_fields = [];
      foreach ($fields['values'] as $field_name => $field_prop) {

        // If we don't have a field type, set it to 0.
        if (!isset($field_prop['type'])) {
          $field_prop['type'] = 0;
        }

        // Set field handler, filter, sort, etc.
        switch ($field_prop['type']) {
          case 1: // Integer field.
          case 1024: // Money field.
            $views_fields[$field_name] = $this->getNumericField($field_prop);
            break;
          case 4: // Date field.
          case 12: // Date and time field.
          case 256: // Timestamp field.
            $views_fields[$field_name] = $this->getDateField($field_prop);
            break;
          case 16: // Boolean field.
            $views_fields[$field_name] = $this->getBooleanField($field_prop);
            break;
          case 32: // Markup field.
            $views_fields[$field_name] = $this->getMarkupField($field_prop);
            break;
          default: // Fallback standard field.
            $views_fields[$field_name] = $this->getStandardField($field_prop);
            break;
        }

        // Set field basic information.
        $views_fields[$field_name]['title'] = empty($field_prop['title']) ? '' : $field_prop['title'];
        $views_fields[$field_name]['help']  = empty($field_prop['description']) ? '' : $field_prop['description'];
        $views_fields[$field_name]['help']  = empty($field_prop['description']) ? '' : $field_prop['description'];

        // Set click sortable to 'true' by default.
        $views_fields[$field_name]['field']['click sortable'] = TRUE;

      }

      return $views_fields;
    }

    return [];
  }

  /**
   * Generate numeric field for views data.
   *
   * @param $prop
   *
   * @return mixed
   */
  private function getNumericField($prop) {

    // Default.
    $field['field']['id']    = 'numeric';
    $field['sort']['id']     = 'standard';
    $field['argument']['id'] = 'standard';

    // If 'type' is 1024 (Money).
    if ((!empty($prop['data_type'])) && ($prop['type'] == 1024)) {
      $field['field']['float'] = TRUE;
    }

    // Add filter to the field.
    if (!empty($prop['api.filter'])) {
      $field['filter']['id'] = ($prop['type'] == 1024) ? 'string' : 'numeric';
    }

    // If 'data_type' is file.
    if ((!empty($prop['data_type'])) && ($prop['data_type'] == 'File')) {
      $field['field']['id'] = 'cmrf_views_file';
    }

    // TODO: Understand the multiple options field and where it should be a list or a single value
    //       Also check 'money' field 1024 fieldOptions.
    //if (!empty($fieldOtions)) {
    //  $field['field']['id'] = 'cmrf_views_prerender_list';
    //  $field['field']['options'] = $fieldOtions;
    //}
    //if (!empty($fieldOtions) && $filterField) {
    //  $field['filter']['id'] = 'cmrf_views_handler_filter_in_operator';
    //  $field['filter']['options'] = $fieldOtions;
    //}

    return $field;
  }

  /**
   * Generate date field for views data.
   *
   * @param $prop
   *
   * @return mixed
   */
  private function getDateField($prop) {

    // Default.
    $field['field']['id']    = 'date';
    $field['sort']['id']     = 'standard';
    $field['argument']['id'] = 'date';

    // Add filter to the field.
    if (!empty($prop['api.filter'])) {
      $field['filter']['id'] = 'date';
    }

    // TODO: Multiple value field date.
    //if (!empty($fieldOtions) && $filterField) {
    //  $field['filter']['id'] = 'cmrf_views_handler_filter_in_operator';
    //  $field['filter']['options'] = $fieldOtions;
    //}

    return $field;
  }

  /**
   * Generate boolean field for views data.
   *
   * @param $prop
   *
   * @return mixed
   */
  private function getBooleanField($prop) {

    // Default.
    $field['field']['id']    = 'boolean';
    $field['sort']['id']     = 'standard';
    $field['argument']['id'] = 'date';

    // Add filter to the field.
    if (!empty($prop['api.filter'])) {
      $field['filter']['id'] = 'boolean';
    }

    // TODO: Check 'use equal' and 'options'
    //if ($filterField) {
    //  $field['filter']['id'] = 'cmrf_views_handler_filter_boolean_operator';
    //  $field['filter']['use equal'] = TRUE;
    //  $field['filter']['options'] = $fieldOtions;
    //}

    return $field;
  }

  /**
   * Generate markup field for views data.
   *
   * @param $prop
   *
   * @return mixed
   */
  private function getMarkupField($prop) {

    // Default.
    $field['field']['id']    = 'markup';
    $field['sort']['id']     = 'standard';
    $field['argument']['id'] = 'standard';

    // Add filter to the field.
    if (!empty($prop['api.filter'])) {
      $field['filter']['id'] = 'string';
    }

    // TODO: Check prerender_list and multiple options
    //if (!empty($fieldOtions)) {
    //  $field['field']['id'] = 'cmrf_views_prerender_list';
    //  $field['field']['options'] = $fieldOtions;
    //}
    //if (!empty($fieldOtions) && $filterField) {
    //  $field['filter']['id'] = 'cmrf_views_handler_filter_in_operator';
    //  $field['filter']['options'] = $fieldOtions;
    //}


    return $field;
  }

  /**
   * Generate standard field for views data.
   *
   * @param $prop
   *
   * @return mixed
   */
  private function getStandardField($prop) {

    // Default.
    $field['field']['id']    = 'standard';
    $field['sort']['id']     = 'standard';
    $field['argument']['id'] = 'standard';

    // Add filter to the field.
    if (!empty($prop['api.filter'])) {
      $field['filter']['id'] = 'string';
    }

    // If 'data_type' is file.
    if ((!empty($prop['data_type'])) && ($prop['data_type'] == 'File')) {
      $field['field']['id'] = 'cmrf_views_file';
    }

    // TODO: Check prerender_list and multiple options
    //else if (!empty($fieldOtions)) {
    //  $field['field']['id'] = 'cmrf_views_prerender_list';
    //  $field['field']['options'] = $fieldOtions;
    //}
    //if (!empty($fieldOtions) && $filterField) {
    //  $field['filter']['id'] = 'cmrf_views_handler_filter_in_operator';
    //  $field['filter']['options'] = $fieldOtions;
    //}

    return $field;
  }

  /**
   * Fetch field options.
   *
   * @param $api_entity
   * @param $api_action
   * @param $field_name
   *
   * @return array
   */
  private function fetchOptions($api_entity, $api_action, $field_name) {

    // Get field options API call.
    $call = $this->core->createCall(
      $this->connector,
      $api_entity,
      'getoptions',
      ['field' => $field_name],
      ['limit' => 0, 'cache' => '5 minutes']
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
          'label'     => $entity->label(),
          'connector' => $entity->connector,
          'entity'    => $entity->entity,
          'action'    => $entity->action,
          'getcount'  => $entity->getcount,
        ];
        $params                          = json_decode($entity->params, TRUE);
        $return[$entity->id()]['params'] = empty($params) ? [] : $params;
      }
    }

    return $return;
  }

}
