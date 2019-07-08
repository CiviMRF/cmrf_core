<?php namespace Drupal\cmrf_core;

use Drupal\cmrf_core\Entity\CMRFConnector;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of CMRF connector entities.
 */
class CMRFConnectorListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label']   = $this->t('CMRF connector');
    $header['id']      = $this->t('Machine name');
    $header['profile'] = $this->t('CMRF profile');
    $header['type']    = $this->t('Connecting module');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var CMRFConnector $entity */
    $row['label']   = $entity->label();
    $row['id']      = $entity->id();
    $row['profile'] = $entity->profile;
    $row['type']    = $entity->type;
    return $row + parent::buildRow($entity);
  }

}
