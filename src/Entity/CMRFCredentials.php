<?php

namespace Drupal\cmrf_core\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the CMRF entity.
 *
 * @ConfigEntityType(
 *   id = "cmrf_credentials",
 *   label = @Translation("CMRF"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cmrf_core\CMRFCredentialsListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cmrf_core\Form\CMRFCredentialsForm",
 *       "edit" = "Drupal\cmrf_core\Form\CMRFCredentialsForm",
 *       "delete" = "Drupal\cmrf_core\Form\CMRFCredentialsDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cmrf_core\CMRFCredentialsHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cmrf_credentials",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/cmrf_credentials/{cmrf_credentials}",
 *     "add-form" = "/admin/config/cmrf_credentials/add",
 *     "edit-form" = "/admin/config/cmrf_credentials/{cmrf_credentials}/edit",
 *     "delete-form" = "/admin/config/cmrf_credentials/{cmrf_credentials}/delete",
 *     "collection" = "/admin/config/cmrf_credentials"
 *   }
 * )
 */
class CMRFCredentials extends ConfigEntityBase implements CMRFCredentialsInterface {

  /**
   * The CMRF ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The CMRF label.
   *
   * @var string
   */
  protected $label;

}
