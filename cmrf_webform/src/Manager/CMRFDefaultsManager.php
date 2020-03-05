<?php

namespace Drupal\cmrf_webform\Manager;

use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\cmrf_webform\Entity\DefaultValue;
use Symfony\Component\HttpFoundation\ParameterBag;
use RuntimeException;

class CMRFDefaultsManager extends CMRFManagerBase {

  protected function getIdParameter(ParameterBag $parameters) {
    return $parameters->get("hash");
  }

  protected function queryApi(DefaultValue $entity, $hash) {
    $connector = $entity->getConnector();
    $parameters = $entity->getDecodedParameters();
    $parameters['hash'] = $hash;
    $options = $entity->getDecodedOptions();

    return $this->sendApiRequest(
      $connector,
      $entity->getEntity(),
      $entity->getAction(),
      $parameters,
      $options,
      NULL // get entire response
    );
  }

  public function fetch(WebformSubmissionInterface $submission, ParameterBag $parameters) {
    $webform = $submission->getWebform();
    $cmrf_handler = DefaultValue::getForWebform($webform);

    $defaults = array();
    if ($cmrf_handler) {
      if (is_array($cmrf_handler)) {
        foreach ($cmrf_handler as $handler) {
          $hash = $handler->getFieldKey();
          if (!isset($hash) || $hash == '') {
            $hash = $this->getIdParameter($parameters);
          }
          $default = $this->queryApi($handler, $hash);
          $this->setDefaultsArray($default, $defaults, $hash);
        }
      }
    }
    return $defaults;
  }

  /**
   * Normalize defaults fetched from CiviCRM API request and return it like:
   * {
   *    'key': 'value'
   * }
   * 
   * Default values returned from CiviCRM API response, some times include element as an array key
   * and sometimes only [values]
   * 
   * Responses like one of the following:
   * { 
   *    'kid': 123456789
   *    'errors': null
   * }
   * Or:
   * {
   *    'values': array(....),
   *    'count': 1
   * }
   * 
   */

  public function setDefaultsArray($default, &$defaults, $hash) {
    if (isset($default) && is_array($default)) {
      if (isset($default[$hash])) {
        $defaults[$hash] = $default[$hash];
      } elseif (isset($default['values'])) {
        $defaults[$hash] = $default['values'];
      } else {
        $defaults = array();
      }
    } else {
      $defaults = array();
    }
  }
  

  public function deleteWebformHandler(WebformInterface $webform) {
    $handler = DefaultValue::getForWebform($webform);
    if ($handler) {
      $handler->delete();
    }
  }

}
