<?php namespace Drupal\cmrf_views;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\views\EntityViewsDataInterface;

class CMRFEntityViewsData implements EntityViewsDataInterface {

  public function getViewsData(): array {
    /** @var \Drupal\cmrf_views\CMRFViews $views */
    $views = \Drupal::service('cmrf_views.views');
    return $views->getViewsData();
  }

  public function getViewsTableForEntityType(EntityTypeInterface $entity_type) {
    // TODO: Implement getViewsTableForEntityType() method.
  }

}
