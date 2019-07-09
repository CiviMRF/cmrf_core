<?php

class cmrf_views_handler_filter_date extends views_handler_filter_date {

  /**
   *
   */
  public function op_between($field) {
    // Use the substitutions to ensure a consistent timestamp.
    $query_substitutions = views_views_query_substitutions($this->view);
    $a = intval(strtotime($this->value['min'], $query_substitutions['***CURRENT_TIME***']));
    $b = intval(strtotime($this->value['max'], $query_substitutions['***CURRENT_TIME***']));
    $date1 = new \DateTime();
    $date1->setTimestamp($a);
    $date2 = new \DateTime();
    $date2->setTimestamp($b);
    $value = array(
      $date1->format("Ymdhis"),
      $date2->format("Ymdhis")
    );

    // This is safe because we are manually scrubbing the values. It is
    // necessary to do it this way because $a and $b are formulas when using an
    // offset.
    $operator = strtoupper($this->operator);
    $this->query->add_where($this->options['group'], $field, $value, $operator);
  }

  /**
   *
   */
  public function op_simple($field) {
    // Use the substitutions to ensure a consistent timestamp.
    $query_substitutions = views_views_query_substitutions($this->view);
    $value = intval(strtotime($this->value['value'], $query_substitutions['***CURRENT_TIME***']));
    $date = new \DateTime();
    $date->setTimestamp($value);

    // This is safe because we are manually scrubbing the value. It is
    // necessary to do it this way because $value is a formula when using an
    // offset.
    $this->query->add_where($this->options['group'], $field, $date->format('Ymdhis'), $this->operator);
  }

}