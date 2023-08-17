<?php namespace Drupal\cmrf_views;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\cmrf_views\Entity\CMRFDatasetRelationship;

/**
 * Provides a listing of CiviMRF Connector entities.
 */
class CMRFDatasetRelationshipListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Relationship');
    $header['id'] = $this->t('Machine name');
    $header['referenced_dataset'] = $this->t('Referenced CiviMRF dataset');
    $header['referenced_key'] = $this->t('Referenced key');
    $header['referencing_key'] = $this->t('Referencing key');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var CMRFDatasetRelationship $entity */
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['referenced_dataset'] = $entity->referenced_dataset;
    $row['referenced_key'] = $entity->referenced_key;
    $row['referencing_key'] = $entity->referencing_key;
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritDoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->accessCheck(TRUE)
      ->sort($this->entityType->getKey('id'))
      // Filter for current cmrf_dataset.
      ->condition(
        'referencing_dataset',
        \Drupal::routeMatch()->getParameter('cmrf_dataset')
      );

    if ($this->limit) {
      $query->pager($this->limit);
    }
    return $query->execute();
  }

}
