<?php namespace Drupal\cmrf_core\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the CiviMRF Profile entity.
 *
 * @ConfigEntityType(
 *   id = "cmrf_profile",
 *   label = @Translation("CiviMRF Profile"),
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
 *     "api_key",
 *     "cache_expire_days",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/cmrf/profiles/manage/{cmrf_profile}",
 *     "add-form" = "/admin/config/cmrf/profiles/add",
 *     "edit-form" = "/admin/config/cmrf/profiles/manage/{cmrf_profile}/edit",
 *     "delete-form" = "/admin/config/cmrf/profiles/manage/{cmrf_profile}/delete",
 *     "collection" = "/admin/config/cmrf/profiles"
 *   }
 * )
 */
class CMRFProfile extends ConfigEntityBase implements CMRFProfileInterface {

  //TODO: we need to add the connection type (remote, local) to the entity

  /**
   * The CiviMRF Profile ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The CiviMRF Profile label.
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
  /**
   * The time that the messages in the call log are stored before they are
   * deleted.
   *
   * @var string
   */
  public $cache_expire_days;

}
