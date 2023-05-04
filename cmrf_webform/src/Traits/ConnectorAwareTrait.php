<?php

namespace Drupal\cmrf_webform\Traits;

use Drupal\cmrf_core\Entity\CMRFConnector;

trait ConnectorAwareTrait {

  protected function getConnectorEntities($module = 'cmrf_webform') {
    $connectors = CMRFConnector::loadMultiple();
    $ret = [];
    foreach ($connectors as $entity) {
      //if ($entity->getType() == $module) {
        $ret[$entity->id()] = $entity->label();
      //}
    }

    return $ret;
  }

}
