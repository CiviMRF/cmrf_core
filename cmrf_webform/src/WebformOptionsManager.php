<?php

namespace Drupal\cmrf_webform;

use Drupal;
use Drupal\cmrf_webform\OptionSetInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\cmrf_core\Entity\CMRFConnector;
use Drupal\webform\Entity\WebformOptions;
use Drupal\Core\Entity\EntityStorageException;

class WebformOptionsManager {

  use StringTranslationTrait;

  protected $core;

  public function __construct($core, $translation) {
    $this->core = $core;
    $this->stringTranslation = $translation;
  }

  protected function getModuleConnector($module = 'cmrf_webform') {
    $list = CMRFConnector::loadMultiple();
    foreach ($list as $id => $item) {
      if ($item->getType() == $module) {
        return $id;
      }
    }
    throw new \Exception($this->t("No connector for module $module was found"));
  }

  protected function fetchPredefinedOptions(OptionSetInterface $entity) {
    // todo: throw subclassed exceptions
    // todo: use a service which will parse API results along with version
    $connector = $this->getModuleConnector();
    $api_entity = $entity->getEntity();
    $api_action = $entity->getAction();
    $parameters = json_decode($entity->getParameters(), true);
    $options = $parameters['options'] ?? [];
    $call = $this->core->createCall($connector, $api_entity, $api_action, $parameters, $options);
    $this->core->executeCall($call);

    if ($call->getStatus() == get_class($call)::STATUS_DONE) {
      $reply = $call->getReply();
      if (!empty($reply['is_error'])) {
        throw new \Exception($this->t('CMRF API call returned error'));
      }
      elseif (isset($reply['values']) && is_array($reply['values'])) {
        $key_property = $entity->getKeyProperty();
        $value_property = $entity->getValueProperty();
        $values = [];
        foreach ($reply['values'] as $row) {
          if (isset($row[$key_property], $row[$value_property])) {
            $key = $row[$key_property];
            $value = $row[$value_property];

            $values[$key] = $value;
          }
        }
        return $values;
      }
      else {
        throw new \Exception($this->t('Malformed CMRF API call response'));
      }
    }
    else {
      throw new \Exception($this->t('CMRF Api call was unsuccessful (%entity/%action) - %status', [
        '%entity' => $api_entity,
        '%action' => $api_action,
        '%status' => $call->getStatus(),
      ]));
    }
  }

  protected function setOptionProperties(WebformOptions $option_set, OptionSetInterface $entity) {
    $option_set->set('id', $entity->getWebformId());
    $option_set->set('label', $entity->getTitle());
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
    $this->setOptionProperties($option_set, $entity, $options);
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
