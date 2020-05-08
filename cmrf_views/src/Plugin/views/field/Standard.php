<?php namespace Drupal\cmrf_views\Plugin\views\field;

use Drupal\views\ResultRow;

/**
 * Default implementation of the base field plugin.
 *
 * @ingroup cmrf_views_field_handlers
 *
 * @ViewsField("cmrf_views_standard")
 */
class Standard extends \Drupal\views\Plugin\views\field\Standard {

  /**
   * {@inheritdoc}
   */
  public function getValue(ResultRow $values, $field = NULL) {
    $alias = isset($field) ? $this->aliases[$field] : $this->field_alias;
    if (isset($values->{$alias})) {
      // Loop through the array and merge by 'display_name' if possible.
      if (is_array($values->{$alias})) {
        return '';
        $merge = '';
        foreach ($values->{$alias} as $key => $value) {
          if ($key == 'display_name') {
            $merge .= $value;
          }
        }
        $values->{$alias} = NULL;
        $values->{$alias} = $merge;

      }
      return $values->{$alias};
    }
  }

}
