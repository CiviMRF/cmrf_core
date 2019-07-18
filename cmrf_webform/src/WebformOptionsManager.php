<?php

namespace Drupal\cmrf_webform;

use Drupal;
use Drupal\cmrf_webform\OptionSetInterface;

class WebformOptionsManager {

  public function __construct
  protected static function getConfigurationObject(OptionSetInterface $entity) {
    $config_factory = Drupal::configFactory();   
    return $config_factory->getEditable('webform.webform_options.' . $entity->id());
  }

  protected static function createPropertiesArray(OptionSetInterface $entity) {
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
      'options' => [], // todo
    ];

    return $properties;
  }

  public static function add(OptionSetInterface $entity) {
    $option_set = self::getConfigurationObject($entity);
    $properties = self::createPropertiesArray($entity);
    $option_set->setData($properties)->save();
  }

  public static function delete(OptionSetInterface $entity) {
    $option_set = self::getConfigurationObject($entity);
    $option_set->delete();
  }

}
