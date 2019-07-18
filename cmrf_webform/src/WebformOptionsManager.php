<?php

namespace Drupal\cmrf_webform;

use Drupal;
use Drupal\cmrf_webform\OptionSetInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class WebformOptionsManager {

  protected $configFactory;
  protected $core;

  public function __construct($core, $configFactory) {
    $this->core = $core;
    $this->configFactory = $configFactory;
  }

  protected function getConfigurationObject(OptionSetInterface $entity) {
    return $this->configFactory->getEditable('webform.webform_options.' . $entity->id());
  }

  protected function fetchPredefinedOptions(OptionSetInterface $entity) {
    $profile = 'default';# $this->core->getDefaultProfile();
    $api_entity = $entity->getEntity();
    $api_action = $entity->getAction();
    $parameters = json_decode($entity->getParameters(), true);
    $call = $this->core->createCall($profile, $api_entity, $api_action, $parameters);
    $this->core->executeCall($call);

    if ($call->getStatus() == Call::STATUS_DONE) {
      return [];
    }
    else {
      throw new \Exception($this->t('CMRF Api call was unsuccessful (%entity/%action)', [
        '%entity' => $api_entity,
        '%action' => $api_action,
      ]));
    }
  }

  protected function createPropertiesArray(OptionSetInterface $entity) {
    $properties = [
      'langcode' => 'en',
      'status' => 'true',
      'dependencies' => [
        'enforced' => [
          'module' => [
            'webform',
          ],
        ],
      ],
      'id' => $entity->id(),
      'label' => $entity->getTitle(),
      'category' => 'CiviCRM integrated sets',
      'likert' => false,
      'options' => $this->fetchPredefinedOptions($entity),
    ];

    return $properties;
  }

  public function add(OptionSetInterface $entity) {
    $option_set = $this->getConfigurationObject($entity);
    $properties = $this->createPropertiesArray($entity);
    $option_set->setData($properties)->save();
  }

  public function delete(OptionSetInterface $entity) {
    $option_set = $this->getConfigurationObject($entity);
    $option_set->delete();
  }

}
