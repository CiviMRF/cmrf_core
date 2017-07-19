<?php

namespace Drupal\cmrf_core\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the CMRF connector entity.
 *
 * @ConfigEntityType(
 *   id = "cmrf_connector",
 *   label = @Translation("CMRF connector"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cmrf_core\CMRFConnectorListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cmrf_core\Form\CMRFConnectorForm",
 *       "edit" = "Drupal\cmrf_core\Form\CMRFConnectorForm",
 *       "delete" = "Drupal\cmrf_core\Form\CMRFConnectorDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cmrf_core\CMRFConnectorHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cmrf_connector",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/cmrf_connector/{cmrf_connector}",
 *     "add-form" = "/admin/config/cmrf_connector/add",
 *     "edit-form" = "/admin/config/cmrf_connector/{cmrf_connector}/edit",
 *     "delete-form" = "/admin/config/cmrf_connector/{cmrf_connector}/delete",
 *     "collection" = "/admin/config/cmrf_connector"
 *   }
 * )
 */
class CMRFConnector extends ConfigEntityBase implements CMRFConnectorInterface {

  /**
   * The CMRF connector ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The CMRF connector label.
   *
   * @var string
   */
  protected $label;

}
