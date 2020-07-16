<?php

namespace Drupal\cmrf_views\Plugin\views\relationship;

use Drupal\views\Plugin\views\relationship\RelationshipPluginBase;

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
    // Do nothing here.
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
   * Retrieves the CMRFDatasetRelationship ID this relationship is using.
   *
   * @return string
   */
  public function getDatasetRelationshipId() {
    return $this->configuration['cmrf_dataset_relationship'];
  }

}
