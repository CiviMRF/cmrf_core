<?php

namespace Drupal\cmrf_webform\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\cmrf_core\Entity\CMRFConnector;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ConnectorAwareForm extends EntityForm {

  protected function getConnectorEntities($module = 'cmrf_webform') {
    $connectors = CMRFConnector::loadMultiple();
    $ret = [];
    foreach ($connectors as $entity) {
      if ($entity->getType() == $module) {
        $ret[$entity->id()] = $entity->label();
      }
    }

    return $ret;
  }

  /**
   * Constructs a Option Set object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

}
