<?php namespace Drupal\cmrf_views\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the CMRF dataset entity.
 *
 * @ConfigEntityType(
 *   id = "cmrf_dataset",
 *   label = @Translation("CMRF Datasets for Views"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cmrf_views\CMRFDatasetListBuilder",
 *     "views_data" = "Drupal\cmrf_views\CMRFEntityViewsData",
 *     "route_provider" = {
 *       "html" = "Drupal\cmrf_views\CMRFDatasetHtmlRouteProvider",
 *     },
 *     "form" = {
 *       "add" = "Drupal\cmrf_views\Form\CMRFDatasetForm",
 *       "edit" = "Drupal\cmrf_views\Form\CMRFDatasetForm",
 *       "delete" = "Drupal\cmrf_views\Form\CMRFDatasetDeleteForm"
 *     },
 *   },
 *   config_prefix = "cmrf_dataset",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "connector" = "connector",
 *     "entity" = "entity",
 *     "action" = "action",
 *     "getcount" = "getcount",
 *     "getfields" = "getfields",
 *     "params" = "params"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/cmrf/dataset/{cmrf_dataset}",
 *     "add-form" = "/admin/config/cmrf/dataset/add",
 *     "edit-form" = "/admin/config/cmrf/dataset/{cmrf_dataset}/edit",
 *     "delete-form" = "/admin/config/cmrf/dataset/{cmrf_dataset}/delete",
 *     "collection" = "/admin/config/cmrf/dataset",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "connector" = "connector",
 *     "entity" = "entity",
 *     "action" = "action",
 *     "getcount" = "getcount",
 *     "getfields" = "getfields",
 *     "params" = "params"
 *   }
 * )
 */
class CMRFDataset extends ConfigEntityBase implements CMRFDatasetInterface {

  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
  }

}
