<?php

namespace Drupal\cmrf_call_report\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CMRFCallreportController.
 */
class CMRFCallreportController extends ControllerBase {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->database = $container->get('database');
    return $instance;
  }

  /**
   * Viewcall.
   *
   * @return array
   *   Return Hello string.
   */
  public function viewCall($cid) {
    $build = [];
    $call = $this->database->query('select * from {civicrm_api_call} where cid = :cid',[':cid' => $cid])->fetchObject();

    if($call) {

      $date = new \DateTime($call->create_date);
      $date = \Drupal::service('date.formatter')->format($date->getTimestamp());
      $status = $call->status;
      $profile = "";//$core->getConnectionProfile($call->connector_id);
      $request = nl2br(json_encode(json_decode($call->request, true), JSON_PRETTY_PRINT));
      $reply = nl2br(json_encode(json_decode($call->reply,true), JSON_PRETTY_PRINT));
      $metadata = nl2br(json_encode(json_decode($call->metadata,true), JSON_PRETTY_PRINT));
      $scheduled_date = '';
      if (!empty($call->scheduled_date)) {
        $scheduled_date = new \DateTime($call->scheduled_date);
        $scheduled_date = format_date($scheduled_date->getTimestamp());
      }
      $caching_until = '';
      if (!empty($call->cached_until)) {
        $caching_until = new \DateTime($call->cached_until);
        $caching_until = \Drupal::service('date.formatter')->format($caching_until->getTimestamp());
      }
      $retry_count = $call->retry_count;


      $rows = [
        [
          ['data' => t('Call ID'), 'header' => TRUE],
          ['data' => $call->cid],
        ],
        [
          ['data' => t('Date'), 'header' => TRUE],
          ['data' => $date],
        ],
        [
          ['data' => t('Status'), 'header' => TRUE],
          ['data' => $status],
        ],
        [
          ['data' => t('Profile'), 'header' => TRUE],
          ['data' => $profile['label']],
        ],
        [
          ['data' => t('Request'), 'header' => TRUE],
          ['data' => ['#markup' => $request]],
        ],
        [
          ['data' => t('Reply'), 'header' => TRUE],
          ['data' => ['#markup' => $reply]],
        ],
        [
          ['data' => t('Scheduled date'), 'header' => TRUE],
          ['data' => $scheduled_date],
        ],
        [
          ['data' => t('Cached until'), 'header' => TRUE],
          ['data' => $caching_until],
        ],
        [
          ['data' => t('Retry count'), 'header' => TRUE],
          ['data' => $retry_count],
        ],
        [
          ['data' => t('Metadata'), 'header' => TRUE],
          ['data' => [ '#markup' => $metadata]],
        ],
      ];


      $build['apicall_table'] = [
        '#type' => 'table',
        '#rows' => $rows,
        '#attributes' => ['class' => ['dblog-event']],
        '#attached' => [
          'library' => ['dblog/drupal.dblog'],
        ],
      ];
    }
    return $build;
  }
}
