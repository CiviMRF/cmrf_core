<?php

namespace Drupal\cmrf_core\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the CMRF entity.
 *
 * @ConfigEntityType(
 *   id = "cmrf_credential",
 *   label = @Translation("CMRF"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cmrf_core\CMRFCredentialListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cmrf_core\Form\CMRFCredentialForm",
 *       "edit" = "Drupal\cmrf_core\Form\CMRFCredentialForm",
 *       "delete" = "Drupal\cmrf_core\Form\CMRFCredentialDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cmrf_core\CMRFCredentialHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cmrf_credential",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/cmrf_credential/{cmrf_credential}",
 *     "add-form" = "/admin/config/cmrf_credential/add",
 *     "edit-form" = "/admin/config/cmrf_credential/{cmrf_credential}/edit",
 *     "delete-form" = "/admin/config/cmrf_credential/{cmrf_credential}/delete",
 *     "collection" = "/admin/config/cmrf_credential"
 *   }
 * )
 */
class CMRFCredential extends ConfigEntityBase implements CMRFCredentialInterface {

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
