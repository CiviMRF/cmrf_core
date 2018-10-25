<?php

/**
 * Class CiviMRFRulesCallWrapper
 *
 * Wrapper class for CiviMRF calls as Rules variables.
 *
 * @see cmrf_rules_rules_data_info().
 */
class CiviMRFRulesCallWrapper extends EntityStructureWrapper {

  /**
   * @inheritdoc
   */
  public function getPropertyValue($name, &$info) {
    /* @var \CMRF\Core\Call $call */
    $call = $this->data;
    $core = cmrf_core_get_core();

    switch ($name) {
      case 'cid':
        $value = $call->getID();
        break;
      case 'date':
        $value = format_date($call->getDate()->getTimestamp());
        break;
      case 'scheduled_date':
        if (!empty($call->getScheduledDate())) {
          $value = format_date($call->getScheduledDate()->getTimestamp());
        } else {
          $value = '';
        }
        break;
      case 'reply_date':
        if (!empty($call->getReplyDate())) {
          $value = format_date($call->getReplyDate()->getTimestamp());
        } else {
          $value = '';
        }
        break;
      case 'cached_until':
        if (!empty($call->getCachedUntil())) {
          $value = format_date($call->getCachedUntil()->getTimestamp());
        } else {
          $value = '';
        }
        break;
      case 'status':
        $value = $call->getStatus();
        break;
      case 'profile':
        $profile = $core->getConnectionProfile($call->getConnectorID());
        $value = $profile['label'];
        break;
      case 'request':
        $value = json_encode($call->getRequest(), JSON_PRETTY_PRINT);
        break;
      case 'reply':
        $value = json_encode($call->getReply(), JSON_PRETTY_PRINT);
        break;
      case 'metadata':
        $value = json_encode($call->getMetadata(), JSON_PRETTY_PRINT);
        break;
      case 'retry_count':
        $value = $call->getRetryCount();
        break;
      default:
        $value = NULL;
    }

    return $value;
  }

}
