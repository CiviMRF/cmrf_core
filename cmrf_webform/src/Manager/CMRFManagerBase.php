<?php

namespace Drupal\cmrf_webform\Manager;

use Drupal\cmrf_core\Entity\CMRFConnector;
use RuntimeException;

abstract class CMRFManagerBase {

  protected $core;

  public function __construct($core, $translation) {
    $this->core = $core;
    $this->stringTranslation = $translation;
  }

  protected function sendApiRequest($connector, $api_entity, $api_action, $parameters, $options, $get = "values") {
    $call = $this->core->createCall($connector, $api_entity, $api_action, $parameters, $options);
    $this->core->executeCall($call);

    if ($call->getStatus() == get_class($call)::STATUS_DONE) {
      $reply = $call->getReply();

      if (!empty($reply['is_error'])) {
        throw new RuntimeException('CMRF API call returned error.' . $call->getStatus()  . ' - Meta: ' . json_encode($call->getMetadata()) . ' - Reply: ' . json_encode($call->getReply()));
      }
      if ($get === NULL) {
        return $reply;
      }
      else {
        if (!isset($reply[$get]) || !is_array($reply[$get])) {
          throw new RuntimeException('Malformed CMRF API call response.' . $call->getStatus()  . ' - Meta: ' . json_encode($call->getMetadata()) . ' - Reply: ' . json_encode($call->getReply()));
        }

        return $reply[$get];
      }
    }
    else {
      throw new RuntimeException("CMRF Api call was unsuccessful ($api_entity/$api_action) - " . $call->getStatus()  . ' - Meta: ' . json_encode($call->getMetadata()) . ' - Reply: ' . json_encode($call->getReply()) );
    }
  }
}
