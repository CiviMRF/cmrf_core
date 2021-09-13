<?php namespace Drupal\cmrf_core\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the CiviMRF Connector entity.
 *
 * @ConfigEntityType(
 *   id = "cmrf_connector",
 *   label = @Translation("CiviMRF Connector"),
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
 *   config_export = {
 *     "id",
 *     "label",
 *     "type",
 *     "profile"
 *   },
 *   links = {
 *     "canonical" = "/admin/config/cmrf/connectors/manage/{cmrf_connector}",
 *     "add-form" = "/admin/config/cmrf/connectors/add",
 *     "edit-form" = "/admin/config/cmrf/connectors/manage/{cmrf_connector}/edit",
 *     "delete-form" = "/admin/config/cmrf/connectors/manage/{cmrf_connector}/delete",
 *     "collection" = "/admin/config/cmrf/connectors"
 *   }
 * )
 */
class CMRFConnector extends ConfigEntityBase implements CMRFConnectorInterface {

  /**
   * The CiviMRF Connector ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The CiviMRF Connector label.
   *
   * @var string
   */
  protected $label;

  /**
   * The referenced CiviMRF Profile.
   *
   * @var string
   */
  public $profile;

  /**
   * The type describing which module is using this connector.
   *
   * @var string
   */
  public $type;

  public function getType() {
    return $this->type;
  }

  public function getAvailableProfiles() {
    $return     = [];
    $query      = \Drupal::entityQuery('cmrf_profile');
    $results    = $query->execute();
    $entity_ids = array_keys($results);

    /** @var CMRFProfile[] $loaded */
    $loaded = CMRFProfile::loadMultiple($entity_ids);

    foreach ($loaded as $entity) {
      $return[$entity->id()] = $entity->label();
    }

    return $return;
  }

}
