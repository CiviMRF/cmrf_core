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

   /**
    * {@inheritdoc}
    */
   public function render($values) {
     $value = $this->get_value($values);

     // Hiding should happen before rounding or adding prefix/suffix.
     if ($this->options['hide_empty'] && empty($value) && ($value !== 0 || $this->options['empty_zero'])) {
       return '';
     }

     if (!empty($this->options['set_precision'])) {
       $value = number_format($value, $this->options['precision'], $this->options['decimal'], $this->options['separator']);
     }
     elseif (is_numeric($value)) {
       $remainder = abs($value) - intval(abs($value));
       $value = $value > 0 ? floor($value) : ceil($value);
       $value = number_format($value, 0, '', $this->options['separator']);
       if ($remainder) {
         // The substr may not be locale safe.
         $value .= $this->options['decimal'] . substr($remainder, 2);
       }
     }

     // Should we format as a plural.
     if (!empty($this->options['format_plural'])) {
       $value = format_plural($value, $this->options['format_plural_singular'], $this->options['format_plural_plural']);
     }

     return $this->sanitize_value($this->options['prefix'], 'xss')
       . $this->sanitize_value($value)
       . $this->sanitize_value($this->options['suffix'], 'xss');
   }
   
 }
