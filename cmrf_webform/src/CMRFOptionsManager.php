<?php

namespace Drupal\cmrf_webform;

use Drupal\cmrf_webform\OptionSetInterface;
use Drupal\webform\Entity\WebformOptions;
use Drupal\Core\Entity\EntityStorageException;

class CMRFOptionsManager extends CMRFManager {

  protected function fetchPredefinedOptions(OptionSetInterface $entity) {
    $connector = $entity->getConnector();
    $parameters = $entity->getDecodedParameters();
    $options = $parameters['options'] ?? [];
    $key_property = $entity->getKeyProperty();
    $value_property = $entity->getValueProperty();

    $reply = $this->sendApiRequest(
      $connector,
      $entity->getEntity(),
      $entity->getAction(),
      $parameters,
      $options
    );

    $values = [];
    foreach ($reply as $row) {
      if (isset($row[$key_property], $row[$value_property])) {
        $key = $row[$key_property];
        $value = $row[$value_property];

        $values[$key] = $value;
      }
    }

    return $values;
  }

  protected function setOptionProperties(WebformOptions $option_set, OptionSetInterface $entity) {
    $option_set->set('id', $entity->getWebformId());
    $option_set->set('label', $entity->label());
    $option_set->set('category', 'CiviCRM integrated sets');
    $option_set->set('likert', false);
    $option_set->setOptions($this->fetchPredefinedOptions($entity));
  }

  protected function saveOptions(WebformOptions $entity) {
    try {
      $entity->save();
      return true;
    }
    catch (EntityStorageException $e) {
      return false;
    }
  }

  protected function deleteOptions(WebformOptions $entity) {
    try {
      $entity->delete();
      return true;
    }
    catch (EntityStorageException $e) {
      return false;
    }
  }

  public function getConfigurationObject(OptionSetInterface $entity, $create = true) {
    $existing = WebformOptions::load($entity->getWebformId());

    if ($existing === NULL && $create) {
      return WebformOptions::create();
    }
    else {
      return $existing;
    }
  }

  public function add(OptionSetInterface $entity) {
    $option_set = $this->getConfigurationObject($entity);
    $this->setOptionProperties($option_set, $entity);
    if ($this->saveOptions($option_set)) {
      $entity->setRecached();
      return true;
    }
    else {
      return false;
    }
  }

  public function update(OptionSetInterface $entity) {
    $option_set = $this->getConfigurationObject($entity);
    if ($entity->needsRecaching()) {
      $this->setOptionProperties($option_set, $entity);
      if ($this->saveOptions($option_set)) {
        $entity->setRecached();
        return true;
      }
      else {
        return false;
      }
    }
  }

  public function delete(OptionSetInterface $entity) {
    $option_set = $this->getConfigurationObject($entity, false);
    return $this->deleteOptions($option_set);
  }

}
