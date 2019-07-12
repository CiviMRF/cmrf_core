<?php

namespace Drupal\cmrf_webform\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\cmrf_webform\OptionSetInterface;

/**
 * Defines the OptionSet entity.
 *
 * @ConfigEntityType(
 *   id = "cmrf_webform_option_set",
 *   label = @Translation("CiviCRM Webform integration option set"),
 *   handlers = {
 *     "list_builder" = "Drupal\cmrf_webform\Controller\CmrfWebformListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cmrf_webform\Form\OptionSetForm",
 *       "edit" = "Drupal\cmrf_webform\Form\OptionSetForm",
 *       "delete" = "Drupal\cmrf_webform\Form\OptionSetDeleteForm",
 *     }
 *   },
 *   config_prefix = "cmrf_webform_option_set",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label"
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/cmrf_webform_option_set/{cmrf_webform_option_set}",
 *     "delete-form" = "/admin/config/system/cmrf_webform_option_set/{cmrf_webform_option_set}/delete",
 *   }
 * )
 */
class OptionSet extends ConfigEntityBase implements OptionSetInterface {

  /**
   * The option set ID.
   *
   * @var string
   */
  public $id;

  /**
   * The option set label.
   *
   * @var string
   */
  public $label;

  // todo
}
