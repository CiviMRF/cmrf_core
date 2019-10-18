<?php namespace Drupal\cmrf_views\Plugin\views\argument;

/**
 * Default implementation of the base argument plugin.
 *
 * @ingroup cmrf_views_argument_handlers
 *
 * @ViewsArgument("cmrf_views_argument_standard")
 */
class Standard extends \Drupal\views\Plugin\views\argument\Standard {

  /**
   * Set up the query for this argument.
   *
   * The argument sent may be found at $this->argument.
   */
  public function query($group_by = FALSE) {
    $this->ensureMyTable();
    $this->query->addWhere(0, $this->realField, $this->argument);
  }

}
