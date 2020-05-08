<?php namespace Drupal\cmrf_views\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Filter to handle dates stored as a timestamp.
 *
 * @ingroup crmf_views_filter_handlers
 *
 * @ViewsFilter("cmrf_views_filter_optionlist")
 */
class OptionList extends InOperator {

  public function getValueOptions() {
    if (isset($this->valueOptions)) {
      return $this->valueOptions;
    } else {
      $this->valueOptions = empty($this->definition['options'])?[]:$this->definition['options'];
      return $this->valueOptions;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $field = "$this->realField";
    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

  protected function opSimple() {
    if (empty($this->value)) {
      return;
    }
    $this->ensureMyTable();

    // We use array_values() because the checkboxes keep keys and that can cause
    // array addition problems.
    $this->query->addWhere($this->options['group'], "$this->realField", array_values($this->value), $this->operator);
  }

}
