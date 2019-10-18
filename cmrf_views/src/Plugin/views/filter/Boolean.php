<?php namespace Drupal\cmrf_views\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\BooleanOperator;

/**
 * Filter to handle dates stored as a timestamp.
 *
 * @ingroup crmf_views_filter_handlers
 *
 * @ViewsFilter("cmrf_views_filter_boolean")
 */
class Boolean extends BooleanOperator {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $field = $this->realField;
    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      call_user_func([$this, $info[$this->operator]['method']], $field, $info[$this->operator]['query_operator']);
    }
  }

}
