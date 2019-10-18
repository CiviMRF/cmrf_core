<?php namespace Drupal\cmrf_views\Plugin\views\filter;


use MathParser\StdMathParser;

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
    $value = intval(strtotime($this->value['value'], 0));
    if (!empty($this->value['type']) && $this->value['type'] == 'offset') {
      // Keep sign.
      $value = REQUEST_TIME . sprintf('%+d', $value);
      $math  = new StdMathParser();
      $value = (string) $math->parse($value);
    }
    // Convert timestamp to 'YmdHis' date.
    $value = date('YmdHis', $value);
    // Add field values to the query.
    $this->query->addWhere($this->options['group'], $field, $value, $this->operator);
  }

}
