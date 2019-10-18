<?php namespace Drupal\cmrf_views\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\StringFilter;

/**
 * Filter to handle dates stored as a timestamp.
 *
 * @ingroup crmf_views_filter_handlers
 *
 * @ViewsFilter("cmrf_views_filter_text")
 */
class Text extends StringFilter {

  /**
   * Add this filter to the query.
   *
   * Due to the nature of fapi, the value and the operator have an unintended
   * level of indirection. You will find them in $this->operator
   * and $this->value respectively.
   */
  public function query() {
    $this->ensureMyTable();
    $field = "$this->realField";
    $info = $this->operators();
    var_dump($info);
    die();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

}
