<?php

namespace Drupal\cmrf_views;

use Drupal\views\ResultRow;

/**
 * A class representing a view result row for CiviMRF-based views.
 *
 * @package Drupal\cmrf_views
 */
class CMRFViewsResultRow extends ResultRow {

  /**
   * Add values to the row.
   *
   * @param array $values
   *   An array of values to add as properties on the object.
   * @param bool $override
   *   Whether to override already existing values.
   */
  public function addValues(array $values, $override = FALSE) {
    foreach ($values as $key => $value) {
      if (!isset($this->{$key}) || $override) {
        $this->{$key} = $value;
      }
    }
  }

}
