<?php


namespace Drupal\cmrf_views;


use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  /**
   * @param \Symfony\Component\Routing\RouteCollection $collection
   */
  public function alterRoutes(RouteCollection $collection) {
//    if ($route = $collection->get('entity.cmrf_dataset_relationship.canonical')) {
//      $route->addOptions([
//        'parameters' => [
//          'cmrf_dataset' => [
//            'type' => 'entity:cmrf_dataset',
//          ]
//        ]
//      ]);
//    }
  }

}