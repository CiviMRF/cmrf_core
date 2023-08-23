<?php namespace Drupal\cmrf_views\Plugin\views\filter;

use Drupal\cmrf_views\Plugin\views\query\API;
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
    if ($this->isApiv3()) {
      $this->query->addWhere($this->options['group'], $this->realField, (int) $this->value, $this->operator);
    } else {
      $this->query->addWhere($this->options['group'], $this->realField, (bool) $this->value, $this->operator);
    }
  }

  private function isApiv3(): bool {
    return $this->query instanceof API;
  }

}
