<?php namespace Drupal\cmrf_views\Plugin\views\field;

use Drupal\views\Plugin\views\field\PrerenderList;
use Drupal\views\ResultRow;

/**
 * Field handler to display all taxonomy terms of a node.
 *
 * @ingroup cmrf_views_field_handlers
 *
 * @ViewsField("cmrf_views_prerender_list")
 */
class CPrerenderList extends PrerenderList {

  public function preRender(&$values) {
    $this->items[] = $this->definition['options'];
    //
    //if (!empty($this->definition['options'])) {
    //  foreach ($values as $val) {
    //    foreach ($this->definition['options'] as $key => $value) {
    //      $options[$key] = ['name' => $value];
    //    }
    //  }
    //}
  }

  public function render_item($count, $item) {
    return $item;
  }

}
