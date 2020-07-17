<?php

namespace Drupal\cmrf_views\Plugin\views\relationship;

use Drupal\cmrf_views\Entity\CMRFDatasetRelationship;
use Drupal\views\Plugin\views\relationship\RelationshipPluginBase;
use Drupal\views\Views;

/**
 * Relationship handler to return the CMRFDataset configured for the view's
 * base Dataset.
 *
 * @ingroup views_relationship_handlers
 *
 * @ViewsRelationship("cmrf_dataset_relationship")
 */
class DatasetRelationship extends RelationshipPluginBase {

  /**
   * {@inheritDoc}
   */
  public function query() {
    $this->ensureMyTable();
    // Add both, the referencing and the referenced key as fields, so they are
    // always there for matching.
    $this->query->addField($this->view->storage->get('base_table'), $this->options['id']);
    $this->query->addField($this->table, $this->field);
  }

  /**
   * Retrieves the base "table" of this relationship, which is the referenced
   * CMRFDataset entity ID.
   *
   * @return string
   */
  public function getBase() {
    return $this->configuration['base'];
  }

  /**
   * Retrieves the base "field" of this relationship, which is the referenced
   * key of the CiviCRM entity the referenced CMRFDataset fetches.
   *
   * @return string
   */
  public function getBaseField() {
    return $this->configuration['base field'];
  }

  /**
   * Retrieves the CMRFDatasetRelationship entity ID this relationship is using.
   *
   * @return string
   */
  public function getDatasetRelationshipId() {
    return $this->configuration['cmrf_dataset_relationship'];
  }

  /**
   * Retrieves the CMRFDatasetRelationship entity this relationship is using.
   *
   * @return CMRFDatasetRelationship
   */
  public function getDatasetRelationship() {
    return CMRFDatasetRelationship::load($this->getDatasetRelationshipId());
  }

}
