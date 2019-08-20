<?php namespace Drupal\cmrf_views\Plugin\views\field;

use DateTime;
use Drupal\views\ResultRow;

/**
 * Class File
 *
 * @package Drupal\cmrf_views\Plugin\views\field
 * @ViewsField("cmrf_views_date")
 */
class Date extends \Drupal\views\Plugin\views\field\Date {

  /**
   * {@inheritdoc}
   */
  public function getValue(ResultRow $values, $field = NULL) {
    $alias = isset($field) ? $this->aliases[$field] : $this->field_alias;
    if (isset($values->{$alias})) {
      $date_time = new DateTime($values->{$alias});
      return $date_time->format('U');
    }
  }

}
