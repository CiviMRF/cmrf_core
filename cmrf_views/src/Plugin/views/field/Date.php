<?php namespace Drupal\cmrf_views\Plugin\views\field;

use DateTime;
use Drupal\views\ResultRow;

/**
 * Class File
 *
 * @package Drupal\cmrf_views\Plugin\views\field
 * @ingroup cmrf_views_field_handlers
 * @ViewsField("cmrf_views_date")
 */
class Date extends \Drupal\views\Plugin\views\field\Date {

  /**
   * {@inheritdoc}
   */
  public function getValue(ResultRow $values, $field = NULL) {
    $alias = isset($field) ? $this->aliases[$field] : $this->field_alias;
    if (!empty($values->{$alias})) {
      $date_time = new DateTime($values->{$alias});
      return $date_time->format('U');
    }
    return NULL;
  }

  protected function allowAdvancedRender() {
    return FALSE;
  }

}
