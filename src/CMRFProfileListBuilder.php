<?php namespace Drupal\cmrf_core;

use Drupal\cmrf_core\Entity\CMRFProfile;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of CMRF entities.
 */
class CMRFProfileListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('CMRF profile');
    $header['id']    = $this->t('Machine name');
    $header['url']   = $this->t('URL');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var CMRFProfile $entity */
    $row['label'] = $entity->label();
    $row['id']    = $entity->id();
    $row['url']   = $entity->url;
    return $row + parent::buildRow($entity);
  }

}
