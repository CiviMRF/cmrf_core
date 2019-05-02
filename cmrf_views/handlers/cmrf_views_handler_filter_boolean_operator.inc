<?php

class cmrf_views_handler_filter_boolean_operator extends views_handler_filter_boolean_operator {

  /**
   * Return the possible options for this filter.
   *
   * Child classes should override this function to set the possible values
   * for the filter.  Since this is a boolean filter, the array should have
   * two possible keys: 1 for "True" and 0 for "False", although the labels
   * can be whatever makes sense for the filter.  These values are used for
   * configuring the filter, when the filter is exposed, and in the admin
   * summary of the filter.  Normally, this should be static data, but if it's
   * dynamic for some reason, child classes should use a guard to reduce
   * database hits as much as possible.
   */
  public function get_value_options() {
    if (isset($this->definition['options']) && is_array($this->definition['options'])) {
      $this->value_options = $this->definition['options'];
    } elseif (isset($this->definition['type'])) {
      if ($this->definition['type'] == 'yes-no') {
        $this->value_options = array(1 => t('Yes'), 0 => t('No'));
      }
      if ($this->definition['type'] == 'on-off') {
        $this->value_options = array(1 => t('On'), 0 => t('Off'));
      }
      if ($this->definition['type'] == 'enabled-disabled') {
        $this->value_options = array(1 => t('Enabled'), 0 => t('Disabled'));
      }
    }

    // Provide a fallback if the above didn't set anything.
    if (!isset($this->value_options)) {
      $this->value_options = array(1 => t('True'), 0 => t('False'));
    }
  }

}