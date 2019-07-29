<?php

namespace Drupal\cmrf_webform;

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

    if ($cmrf_handler && $hash = $this->getIdParameter($parameters)) {
      return $this->queryApi($cmrf_handler, $hash);
    }
    return false;
  }

  public function deleteWebformHandler(WebformInterface $webform) {
    $handler = DefaultValue::getForWebform($webform);
    if ($handler) {
      $handler->delete();
    }
  }

}
