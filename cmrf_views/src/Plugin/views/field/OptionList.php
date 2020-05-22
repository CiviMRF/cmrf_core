<?php namespace Drupal\cmrf_views\Plugin\views\field;

use Drupal\views\ResultRow;

/**
 * Shows the label instead of the value for field that has an option list
 *
 * @ingroup cmrf_views_field_handlers
 *
 * @ViewsField("cmrf_views_optionlist")
 */
class OptionList extends \Drupal\views\Plugin\views\field\Standard {

  public function getValue(ResultRow $values, $field = NULL) {
    $alias = isset($field) ? $this->aliases[$field] : $this->field_alias;
    $options = $this->definition['options'];
    $key = $values->{$alias};
    if (key_exists($key, $options)) {
      return $options[$key];
    }
    else {
      // if the is not found show nothing
      return FALSE;
    }
  }

}
