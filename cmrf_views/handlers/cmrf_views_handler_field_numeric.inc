<?php

/**
 * @file
 * Definition of cmrf_views_handler_field
 */
 
 class cmrf_views_handler_field_numeric extends views_handler_field_numeric {
   
 /**
   * Get the value that's supposed to be rendered.
   *
   * This api exists so that other modules can easy set the values of the field
   * without having the need to change the render method as well.
   *
   * @param $values
   *   An object containing all retrieved values.
   * @param $field
   *   Optional name of the field where the value is stored.
   */
  function get_value($values, $field = NULL) {
    $alias = isset($field) ? $this->aliases[$field] : $this->field_alias;
    if (isset($values->{$alias})) {
      $value = $values->{$alias};
      if (isset($this->definition['options']) && is_array($this->definition['options']) && isset($this->definition['options'][$value])) {
        $value = $this->definition['options'][$value];
      }
      return $value;
    }
  }
   
 }
