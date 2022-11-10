<?php namespace Drupal\cmrf_views\Plugin\views\filter;

 /**
 * Filter to handle dates stored as a timestamp.
 *
 * @ingroup crmf_views_filter_handlers
 *
 * @ViewsFilter("cmrf_views_filter_date")
 */
class Date extends \Drupal\views\Plugin\views\filter\Date {

  public function query() {
    $this->ensureMyTable();
    $field = $this->realField;
    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

  protected function opSimple($field) {
    // Convert time string (absolute or relative) to 'YmdHis' date.
    $value = date('YmdHis', strtotime($this->value['value']));
    // Add field values to the query.
    $this->query->addWhere($this->options['group'], $field, $value, $this->operator);
  }

  protected function opBetween($field) {
    // Convert time strings (absolute or relative) to 'YmdHis' date.
    $value = [
      $a = date('YmdHis', strtotime($this->value['min'])),
      $b = date('YmdHis', strtotime($this->value['max'])),
    ];

    $operator = strtoupper($this->operator);
    $this->query->addWhere($this->options['group'], $field, $value, $operator);
  }

}
