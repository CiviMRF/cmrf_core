<?php namespace Drupal\cmrf_views\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the CiviMRF Views Dataset entity.
 *
 * @ConfigEntityType(
 *   id = "cmrf_dataset",
 *   label = @Translation("CiviMRF Views Dataset"),
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
 *     "params" = "params",
 *     "api_version" = "api_version"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/cmrf/cmrf_views/datasets/manage/{cmrf_dataset}",
 *     "add-form" = "/admin/config/cmrf/cmrf_views/datasets/add",
 *     "edit-form" = "/admin/config/cmrf/cmrf_views/datasets/manage/{cmrf_dataset}/edit",
 *     "delete-form" = "/admin/config/cmrf/cmrf_views/datasets/manage/{cmrf_dataset}/delete",
 *     "collection" = "/admin/config/cmrf/cmrf_views/datasets",
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
 *     "params" = "params",
 *     "api_version" = "api_version"
 *   }
 * )
 */
class CMRFDataset extends ConfigEntityBase implements CMRFDatasetInterface {

  public function __construct(array $values, $entity_type) {
    parent::__construct($values, $entity_type);
  }

}
