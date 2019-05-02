<?php

/**
 * @file
 * Definition of views_handler_filter_in_operator.
 */

/**
 * Simple filter to handle matching of multiple options selectable via checkboxes
 *
 * Definition items:
 * - options: array with all the options. If omitted, the options 'Yes' and 'No' will be used.
 *
 * @ingroup views_filter_handlers
 */
class cmrf_views_handler_filter_in_operator extends views_handler_filter_in_operator {

  function option_definition() {
    $options = parent::option_definition();

    $options['reload_options'] = array('default' => 0);

    return $options;
  }

  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);
    $form['reload_options'] = array(
      '#type' => 'checkbox',
      '#title' => t('Reload the options'),
      '#description' => t('Reload the list with possible options when the view is loaded'),
      '#default_value' => $this->options['reload_options'],
    );
  }

  /**
   * Child classes should be used to override this function and set the
   * 'value options', unless 'options callback' is defined as a valid function
   * or static public method to generate these values.
   *
   * This can use a guard to be used to reduce database hits as much as
   * possible.
   *
   * @return
   *   Return the stored values in $this->value_options if someone expects it.
   */
  function get_value_options() {
    if (isset($this->value_options)) {
      return;
    }

    if (isset($this->definition['options']) && is_array($this->definition['options'])) {
      if ($this->options['reload_options']) {
        // Refetch the options for the field
        $table_data = views_fetch_data($this->table);
        $entity = $table_data['table']['base']['entity'];
        $api_action = $table_data['table']['base']['action'];
        $options = cmrf_views_fetch_options($entity, $api_action, $this->field);
        $this->value_options = $options;
      } else {
        $this->value_options = $this->definition['options'];
      }
    }
    else {
      $this->value_options = array(t('Yes'), t('No'));
    }

    return $this->value_options;
  }
}
