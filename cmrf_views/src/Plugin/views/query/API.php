<?php namespace Drupal\cmrf_views\Plugin\views\query;

use Drupal\cmrf_core\Call;
use Drupal\cmrf_core\Core;
use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;
use Drupal\views\ViewsData;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CMRF CiviCRM views query plugin which wraps calls to the API in order to
 * expose the results to views.
 *
 * @ViewsQuery(
 *   id = "civicrm_api",
 *   title = @Translation("CMRF Views API"),
 *   help = @Translation("Query against the CiviCRM API.")
 * )
 */
class API extends QueryPluginBase {

  /**
   * @var \Drupal\cmrf_core\Core
   */
  protected $core;

  /**
   * @var \Drupal\views\ViewsData
   */
  protected $viewsData;

  /**
   * API constructor.
   *
   * @param array                   $configuration
   * @param                         $plugin_id
   * @param                         $plugin_definition
   * @param \Drupal\cmrf_core\Core  $core
   * @param \Drupal\views\ViewsData $views_data
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    Core $core,
    ViewsData $views_data
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->core      = $core;
    $this->viewsData = $views_data;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cmrf_core.core'),
      $container->get('views.views_data')
    );
  }

  /**
   * Method to trick views, because it expects an SQL backend.
   *
   * @param      $table
   * @param null $relationship
   *
   * @return string
   */
  public function ensureTable($table, $relationship = NULL) {
    return '';
  }

  /**
   * Method to trick views, because it expects an SQL backend.
   *
   * @param        $table
   * @param        $field
   * @param string $alias
   * @param array  $params
   *
   * @return mixed
   */
  public function addField($table, $field, $alias = '', $params = []) {
    return $field;
  }

  public function execute(ViewExecutable $view) {
    $table_data = $this->viewsData->get($view->storage->get('base_table'));
    if (!empty($table_data)) {
      $api_entity       = $table_data['table']['base']['entity'];
      $api_action       = $table_data['table']['base']['action'];
      $api_count_action = $table_data['table']['base']['count'];
      $connector        = $table_data['table']['base']['connector'];
      $dataset_params   = json_decode($table_data['table']['base']['params'], TRUE);
      if (!is_array($dataset_params)) {
        $dataset_params = [];
      }

      $start            = microtime(TRUE);
      $options['cache'] = $view->query->options['cache'];
      if (isset($this->limit)) {
        $options['limit'] = $this->limit;
      }
      else {
        $options['limit'] = 0;
      }
      if (isset($this->offset)) {
        $options['offset'] = $this->offset;
      }
      $parameters = [];

      // Set the return fields
      $parameters['return'] = [];
      foreach ($this->fields as $field) {
        $parameters['return'][] = $field['field'];
      }

      // Set the query parameters.
      if (!isset($this->where) || !is_array($this->where)) {
        $this->where = [];
      }
      foreach ($this->where as $where) {
        if (empty($where['field'])) {
          continue;
        }
        switch ($where['operator']) {
          case '>':
          case '>=':
          case '<=':
          case '<':
          case '!=':
          case 'BETWEEN':
          case 'NOT BETWEEN':
          case 'LIKE':
          case 'NOT LIKE':
            $parameters[$where['field']] = [$where['operator'] => $where['value']];
            break;
          case 'in':
            $parameters[$where['field']] = ['IN' => $where['value']];
            break;
          case 'not in':
            $parameters[$where['field']] = ['NOT IN' => $where['value']];
            break;
          default:
            $parameters[$where['field']] = $where['value'];
            break;
        }
      }

      // Do sorting
      if (isset($this->orderby) && count($this->orderby) > 0) {
        $options['sort'] = '';
        foreach ($this->orderby as $orderby) {
          if (strlen($options['sort'])) {
            $options['sort'] .= ', ';
          }
          $options['sort'] .= $orderby['field'] . ' ' . $orderby['direction'];
        }
      }

      // Set the parameters from the dataset params options.
      if (!empty($dataset_params)) {
        foreach ($dataset_params as $key => $value) {
          $parameters[$key] = $value;
        }
      }

      // View result init.
      $view->result = [];

      // Data API call.
      $call = $this->core->createCall($connector, $api_entity, $api_action, $parameters, $options);
      $this->core->executeCall($call);
      if ($call->getStatus() == Call::STATUS_DONE) {
        $result = $call->getReply();
        if ((!empty($result['values'])) && (is_array($result['values']))) {
          $index = 0;
          foreach ($result['values'] as $value) {
            // Row data.
            $row = json_decode(json_encode($value), TRUE);
            // Mandatory field for views rows.
            $row['index'] = $index++;
            // Add row to view result.
            $view->result[] = new ResultRow($row);
          }
        }
      }

      // Count options.
      $countOptions['cache'] = $view->query->options['cache'];

      // Count API call.
      $call = $this->core->createCall($connector, $api_entity, $api_count_action, $parameters, $options);
      $this->core->executeCall($call);
      if ($call->getStatus() == Call::STATUS_DONE) {
        $result = $call->getReply();
        if (!empty($result['result'])) {
          $view->getPager()->total_items = $result['result'];
          $view->total_rows              = $result['result'];
        }
      }


      // Update pager.
      $view->getPager()->updatePageInfo();

      // Execute time.
      $view->execute_time = microtime(TRUE) - $start;
    }
  }

}
