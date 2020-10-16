<?php namespace Drupal\cmrf_views\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

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
   * @var array
   *   A static array of all CMRFDatasetRelationship entity objects, keyed by
   *   their ID and grouped by their referencing CMRFDataset.
   */
  protected static $_relationships;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);

    if (empty($values['referencing_dataset'])) {
      $values['referencing_dataset'] = \Drupal::routeMatch()->getParameter('cmrf_dataset');
    }
    $this->referencing_dataset = $values['referencing_dataset'];
  }

  /**
   * {@inheritDoc}
   */
  protected function urlRouteParameters($rel) {
    $parameters = parent::urlRouteParameters($rel);
    $parameters['cmrf_dataset'] = $this->referencing_dataset;
    return $parameters;
  }

  /**
   * Loads entities by a given CiviMRFDataset entity ID.
   *
   * @param $dataset_id
   *   The CiviMRFDataset entity ID.
   *
   * @return array
   *   An array of entity objects indexed by their IDs.
   */
  public static function loadByDataset($dataset_id) {
    if (!isset(self::$_relationships[$dataset_id])) {
      foreach (self::loadMultiple() as $relationship_id => $relationship) {
        self::$_relationships[$relationship->referencing_dataset][$relationship_id] = $relationship;
      }
    }
    return (isset(self::$_relationships[$dataset_id]) ? self::$_relationships[$dataset_id] : []);
  }

}
