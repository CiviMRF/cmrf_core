<?php namespace Drupal\cmrf_views;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of CMRF connector entities.
 */
class CMRFDatasetListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label']      = $this->t('CMRF Dataset');
    $header['id']         = $this->t('Machine name');
    $header['profile']    = $this->t('Profile');
    $header['entity']     = $this->t('Entity');
    $header['action']     = $this->t('Action');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var CMRFDataset $entity */
    $row['label']      = $entity->label();
    $row['id']         = $entity->id();
    $row['profile']    = $entity->profile;
    $row['entity']     = $entity->entity;
    $row['action']     = $entity->action;
    return $row + parent::buildRow($entity);
  }

}
