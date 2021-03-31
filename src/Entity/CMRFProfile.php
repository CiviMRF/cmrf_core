<?php namespace Drupal\cmrf_core\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the CMRF entity.
 *
 * @ConfigEntityType(
 *   id = "cmrf_profile",
 *   label = @Translation("CMRF profile"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\cmrf_core\CMRFProfileListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cmrf_core\Form\CMRFProfileForm",
 *       "edit" = "Drupal\cmrf_core\Form\CMRFProfileForm",
 *       "delete" = "Drupal\cmrf_core\Form\CMRFProfileDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cmrf_core\CMRFProfileHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cmrf_profile",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "url",
 *     "site_key",
 *     "api_key"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/cmrf_profile/{cmrf_profile}",
 *     "add-form" = "/admin/config/cmrf_profile/add",
 *     "edit-form" = "/admin/config/cmrf_profile/{cmrf_profile}/edit",
 *     "delete-form" = "/admin/config/cmrf_profile/{cmrf_profile}/delete",
 *     "collection" = "/admin/config/cmrf_profile"
 *   }
 * )
 */
class CMRFProfile extends ConfigEntityBase implements CMRFProfileInterface {

  //TODO: we need to add the connection type (remote, local) to the entity

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

  /**
   * The URL of the CiviCRM installation
   *
   * @var string
   */
  public $url;

  /**
   * The site key for the CiviCRM installation
   *
   * @var string
   */
  public $site_key;

  /**
   * The API key for the CiviCRM installation
   *
   * @var string
   */
  public $api_key;

}
