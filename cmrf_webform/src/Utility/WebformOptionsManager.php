<?php

namespace Drupal\cmrf_webform\Utility;

use Drupal;
use Drupal\cmrf_webform\OptionSetInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

class WebformOptionsManager implements ContainerInjectionInterface {

  protected $configFactory;
  protected $core;

  public function __construct($core, $configFactory) {
    $this->core = $core;
    $this->configFactory = $configFactory;
  }

  public static function create($container = NULL) {
    if ($container === NULL) {
      $container = Drupal::getContainer();
    }
    return new static(
      $container->get('cmrf_core.core'),
      $container->get('config.factory')
    );
  }

  protected function getConfigurationObject(OptionSetInterface $entity) {
    return $this->configFactory->getEditable('webform.webform_options.' . $entity->id());
  }

  protected function fetchPredefinedOptions(OptionSetInterface $entity) {
    return [];
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
