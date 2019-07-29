<?php

namespace Drupal\cmrf_webform\Traits;

use Drupal\webform\Entity\Webform;

trait WebformAwareTrait {

  protected function getWebformEntities() {
    $webforms = Webform::loadMultiple();
    $ret = [];
    foreach ($webforms as $entity) {
      $ret[$entity->id()] = $entity->label();
    }

    return $ret;
  }

}
