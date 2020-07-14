<?php namespace Drupal\cmrf_views\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Defines the CMRF dataset relationship entity.
 *
 * @ConfigEntityType(
 *   id = "cmrf_dataset_relationship",
 *   label = @Translation("CMRF Dataset Relationships for Views"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cmrf_views\CMRFDatasetRelationshipListBuilder",
 *     "views_data" = "Drupal\cmrf_views\CMRFEntityViewsData",
 *     "route_provider" = {
 *       "html" = "Drupal\cmrf_views\CMRFDatasetHtmlRouteProvider",
 *     },
 *     "form" = {
 *       "add" = "Drupal\cmrf_views\Form\CMRFDatasetRelationshipForm",
 *       "edit" = "Drupal\cmrf_views\Form\CMRFDatasetRelationshipForm",
 *       "delete" = "Drupal\cmrf_views\Form\CMRFDatasetRelationshipDeleteForm"
 *     },
 *   },
 *   config_prefix = "cmrf_dataset_relationship",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "referenced_dataset" = "referenced_dataset",
 *     "referenced_key" = "referenced_key",
 *     "referencing_dataset" = "referencing_dataset",
 *     "referencing_key" = "referencing_key",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/cmrf/dataset/{cmrf_dataset}/relationship/{cmrf_dataset_relationship}",
 *     "add-form" = "/admin/config/cmrf/dataset/{cmrf_dataset}/relationship/add",
 *     "edit-form" = "/admin/config/cmrf/dataset/{cmrf_dataset}/relationship/{cmrf_dataset_relationship}/edit",
 *     "delete-form" = "/admin/config/cmrf/dataset/{cmrf_dataset}/relationship/{cmrf_dataset_relationship}/delete",
 *     "collection" = "/admin/config/cmrf/dataset/{cmrf_dataset}/relationship"
 *   }
 * )
 */
class CMRFDatasetRelationship extends ConfigEntityBase implements CMRFDatasetRelationshipInterface {

  /**
   * @var \Drupal\cmrf_views\Entity\CMRFDataset
   */
  public $referencing_dataset;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);

    $datasets = $this->referencedEntities();
    $this->referencing_dataset = reset($datasets);
  }

  /**
   * {@inheritDoc}
   */
  public function urlRouteParameters($rel) {
    $parameters = parent::urlRouteParameters($rel);
    $parameters['cmrf_dataset'] = $this->get('referencing_dataset');
    return $parameters;
  }

  /**
   * {@inheritDoc}
   */
  public function referencedEntities() {
    $referenced_entities = [];

    // TODO: This might not yield the expected result.
    $cmrf_dataset_id = \Drupal::routeMatch()
      ->getParameter('cmrf_dataset');
    $referenced_entities[$cmrf_dataset_id] = $cmrf_dataset_id;

    return $referenced_entities;
  }

}
