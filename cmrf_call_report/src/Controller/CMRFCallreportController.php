<?php

namespace Drupal\cmrf_call_report\Controller;

use Drupal;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
   * @var \Drupal\cmrf_core\Core
   */
  protected $core;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->database = $container->get('database');
    $instance->core = $container->get('cmrf_core.core');
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
      $profile = $this->core->getConnectionProfile($call->connector_id);
      $request = json_decode($call->request, true);
      $entity = $call->entity ?? $request['entity'] ?? NULL;
      $action = $call->action ?? $request['action'] ?? NULL;
      $request = json_encode($request, JSON_PRETTY_PRINT);
      $reply = json_encode(json_decode($call->reply,true), JSON_PRETTY_PRINT);
      $metadata = json_encode(json_decode($call->metadata,true), JSON_PRETTY_PRINT);
      $scheduled_date = '';
      if (!empty($call->scheduled_date)) {
        $scheduled_date = new \DateTime($call->scheduled_date);
        $scheduled_date = Drupal::service('date.formatter')->format($scheduled_date->getTimestamp());
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
          ['data' => t('Entity'), 'header' => TRUE],
          ['data' => ['#markup' => '<pre>' . $entity . '</pre>']],
        ],
        [
          ['data' => t('Action'), 'header' => TRUE],
          ['data' => ['#markup' => '<pre>' . $action . '</pre>']],
        ],
        [
          ['data' => t('Request'), 'header' => TRUE],
          ['data' => ['#markup' => '<pre>' . $request . '</pre>']],
        ],
        [
          ['data' => t('Reply'), 'header' => TRUE],
          ['data' => ['#markup' => '<pre>' . $reply . '</pre>']],
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
          ['data' => [ '#markup' => '<pre>' . $metadata . '</pre>']],
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
    $resubmitLink = new Link('Resubmit', Url::fromRoute('cmrf_call_report.resubmit_call',['cid' => $cid]));
    $build['link'] = $resubmitLink->toRenderable();
    $build['link']['#attributes'] = ['class' => ['button']];
    return $build;
  }

  /**
   * Resubmits an existing request (usefull of testing and failed messages)
   * @param $cid
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function resubmit($cid){
    $resubmitCall = $this->database->query('select * from {civicrm_api_call} where cid = :cid',[':cid' => $cid])->fetchObject();
    if($resubmitCall) {
      $request = json_decode($resubmitCall->request,true);
      $entity = $request['entity']; unset($request['entity']);
      $action = $request['action']; unset($request['action']);
      $options = $request['options']; unset($request['options']);
      $call = $this->core->createCall($resubmitCall->connector_id,$entity,$action,$request,$options);
      $this->core->executeCall($call);
    }
    // and go to the newly created call
    return new RedirectResponse(Url::fromRoute('cmrf_call_report.view_call',['cid' => $call->getID()])->toString());
  }

}
