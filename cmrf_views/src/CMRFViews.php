<?php namespace Drupal\cmrf_views;

use Drupal\cmrf_core\Call;
use Drupal\cmrf_core\Core;
use Drupal\cmrf_views\Entity\CMRFDataset;
use Drupal\cmrf_views\Entity\CMRFDatasetRelationship;

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
    $data     = [];
    $datasets = $this->getDatasets();
    if (!empty($datasets)) {
      foreach ($datasets as $dataset_id => $dataset_prop) {
        if ((!empty($dataset_prop['connector'])) && (!empty($dataset_prop['entity']) && (!empty($dataset_prop['action'])))) {
          $dataset_prop['id'] = $dataset_id;
          $fields = $this->getFields($dataset_prop);
          // Unique identifier for this group.
          $uid = 'cmrf_views_' . $dataset_id;
          // Base data.
          $data[$uid] = $this->getBaseData($dataset_prop);
          if ((!empty($fields)) && (is_array($fields))) {
            // Fields (from the getEntityFields function).
            $data[$uid] = array_merge($fields, $data[$uid]);
          }
        }
      }
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
      $base_data['table']['base']['params'] = $dataset['params'];
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
      $call = $this->core->createCall(
        $dataset['connector'],
        $dataset['entity'],
        $dataset['getfields'],
        ['api_action' => $dataset['action']],
        ['limit' => 0]
      );
      $this->core->executeCall($call);
      if ($call->getStatus() != Call::STATUS_DONE) {
        return [];
      }

      // Get fields value.
      $fields = $call->getReply();
      if (empty($fields['values'])) {
        return [];
      }

      // Retrieve available relationships available for the current dataset.
      $dataset_relationships = CMRFDatasetRelationship::loadByDataset($dataset['id']);

      // Loop through each field to create the appropriate structure for views data.
      $views_fields = [];
      foreach ($fields['values'] as $field_name => $field_prop) {
        $original_field_name = $field_name;
        $field_name = str_replace('.', '__', $field_name);

        // If we don't have a field type, set it to 0.
        if (!isset($field_prop['type'])) {
          $field_prop['type'] = 0;
        }

        // Set default for "api.filter".
        if (!isset($field_prop['api.filter'])) {
          $field_prop['api.filter'] = 1;
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
          case 2: // String field
            if ($field_prop['format'] == 'json') {
              $views_fields[$field_name] = $this->getJSONField($field_prop);
              break;
            }
            // No "break" statement for other string types falling through.
          default: // Fallback standard field.
            $views_fields[$field_name] = $this->getStandardField($field_prop);
            break;
        }

        // Set field basic information.
        $views_fields[$field_name]['title'] = empty($field_prop['title']) ? '' : $field_prop['title'];
        $views_fields[$field_name]['help']  = empty($field_prop['description']) ? '' : $field_prop['description'];
        $views_fields[$field_name]['group'] = $dataset['label'];
        $views_fields[$field_name]['cmrf_original_definition'] = $field_prop;

        // Make sorting use the correct field names, i.e. without dots replaced.
        if (!empty($views_fields[$field_name]['sort'])) {
          $views_fields[$field_name]['sort']['field'] = $original_field_name;
        }

        // Set click sortable to 'true' by default.
        $views_fields[$field_name]['field']['click sortable'] = TRUE;

        // Add relationship properties when configured for this field.
        foreach ($dataset_relationships as $dataset_relationship) {
          if ($dataset_relationship->referencing_key == $field_name) {
            $views_fields[$field_name]['relationship'] = [
              'base' => 'cmrf_views_' . $dataset_relationship->referenced_dataset,
              'base field' => $dataset_relationship->referenced_key,
              'id' => 'cmrf_dataset_relationship',
              'label' => $dataset_relationship->label,
              'cmrf_dataset_relationship' => $dataset_relationship->id,
              'relationship table' => 'cmrf_views_' . $dataset_relationship->referenced_dataset,
              'relationship field' => $dataset_relationship->referenced_key,
            ];
          }
        }
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

    if (!empty($prop['options'])){
      $field['field']['options'] = $prop['options'];
      $field['field']['id']    = 'cmrf_views_optionlist';
    } else {
      $field['field']['id'] = 'numeric';
    }
    $field['sort']['id']     = 'standard';
    $field['argument']['id'] = 'cmrf_views_argument_standard';

    // If 'type' is 1024 (Money).
    if ((!empty($prop['data_type'])) && ($prop['type'] == 1024)) {
      $field['field']['float'] = TRUE;
    }

    // Add filter to the field.
    if (!empty($prop['api.filter'])) {
      if (!empty($prop['options'])) {
        $field['filter']['id'] = 'cmrf_views_filter_optionlist';
        $field['filter']['options'] = $prop['options'];
      } else {
        $field['filter']['id'] = ($prop['type'] == 1024) ? 'cmrf_views_filter_text' : 'cmrf_views_filter_numeric';
      }
    }

    // If 'data_type' is file.
    if ((!empty($prop['data_type'])) && ($prop['data_type'] == 'File')) {
      $field['field']['id'] = 'cmrf_views_file';
    }

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
    $field['field']['id']    = 'cmrf_views_date';
    $field['sort']['id']     = 'standard';
    $field['argument']['id'] = 'date';

    // Add filter to the field.
    if (!empty($prop['api.filter'])) {
      $field['filter']['id'] = 'cmrf_views_filter_date';
      if (!empty($prop['options'])) {
        $field['filter']['id']      = 'cmrf_views_filter_optionlist';
        $field['filter']['options'] = $prop['options'];
      }
    }

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
      $field['filter']['id'] = 'cmrf_views_filter_boolean';
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
    $field['field']['id']    = 'cmrf_views_markup';
    $field['sort']['id']     = 'standard';
    $field['argument']['id'] = 'cmrf_views_argument_standard';

    // Add filter to the field.
    if (!empty($prop['api.filter'])) {
      $field['filter']['id'] = 'cmrf_views_filter_text';
      if (!empty($prop['options'])) {
        $field['filter']['id']      = 'cmrf_views_filter_optionlist';
        $field['filter']['options'] = $prop['options'];
      }
    }

    return $field;
  }

  /**
   * Generates JSON field for views data.
   *
   * @param $prop
   */
  private function getJSONField($prop) {
    $field['field']['id']    = 'cmrf_views_json';

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
    if (!empty($prop['options'])){
      $field['field']['options'] = $prop['options'];
      $field['field']['id']    = 'cmrf_views_optionlist';
    } else {
      $field['field']['id'] = 'cmrf_views_standard';
    }
    $field['sort']['id']     = 'standard';
    $field['argument']['id'] = 'cmrf_views_argument_standard';
    if (!empty($prop['options'])){
      $field['field']['options'] = $prop['options'];
    }

    // Add filter to the field.
    if (!empty($prop['api.filter'])) {
      $field['filter']['id'] = 'cmrf_views_filter_text';
      if (!empty($prop['options'])) {
        $field['filter']['id']      = 'cmrf_views_filter_optionlist';
        $field['filter']['options'] = $prop['options'];
      }
    }

    // If 'data_type' is file.
    if ((!empty($prop['data_type'])) && ($prop['data_type'] == 'File')) {
      $field['field']['id'] = 'cmrf_views_file';
    }

    return $field;
  }

  /**
   * Fetch field options.
   *
   * @param $connector
   * @param $api_entity
   * @param $api_action
   * @param $field_name
   *
   * @return array
   */
  private function fetchOptions($connector, $api_entity, $api_action, $field_name) {

    // Get field options API call.
    $call = $this->core->createCall(
      $connector,
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
      $connector,
      $api_entity,
      'getfields', // TODO: Use "getfields" property of the Dataset?
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
    $return = [];
    foreach (CMRFDataset::loadMultiple() as $dataset_id => $dataset) {
      $return[$dataset_id] = $dataset->toArray();
    }
    return $return;
  }

}
