<?php namespace Drupal\cmrf_views\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\NumericFilter;

/**
 * Filter to handle dates stored as a timestamp.
 *
 * @ingroup crmf_views_filter_handlers
 *
 * @ViewsFilter("cmrf_views_filter_numeric")
 */
class Numeric extends NumericFilter {

  public function query() {
    $this->ensureMyTable();
    $field = $this->realField;
    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

}
