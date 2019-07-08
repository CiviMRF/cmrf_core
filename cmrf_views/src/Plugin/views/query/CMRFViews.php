<?php namespace Drupal\cmrf_views\Plugin\views\query;

use Drupal\views\Plugin\views\query\QueryPluginBase;
use Drupal\views\ViewExecutable;

/**
 * CMRF CiviCRM views query plugin which wraps calls to the API in order to
 * expose the results to views.
 *
 * @ViewsQuery(
 *   id = "cmrf_views",
 *   title = @Translation("CMRF Views"),
 *   help = @Translation("Query against the CiviCRM API.")
 * )
 */
class CMRFViews extends QueryPluginBase {

  public function ensureTable($table, $relationship = NULL) {
    return '';
  }

  public function addField($table, $field, $alias = '', $params = []) {
    return $field;
  }

  public function exec1ute(ViewExecutable $view) {
    parent::execute($view);
    $table_data       = drupal_container()
      ->get('views.views_data')
      ->get($this->base_table);
    $entity           = $table_data['table']['base']['entity'];
    $get_api_action   = $table_data['table']['base']['action'];
    $count_api_action = $table_data['table']['base']['count'];
    $profile          = $table_data['table']['base']['profile'];
    $dataset_params   = json_decode($table_data['table']['base']['params'],
      TRUE);
    if (!is_array($dataset_params)) {
      $dataset_params = [];
    }

    $start = microtime(TRUE);

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

    //Set the query parameters.
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
    foreach ($dataset_params as $key => $value) {
      $parameters[$key] = $value;
    }

    $call         = cmrf_views_sendCall($entity, $get_api_action, $parameters,
      $options, $profile);
    $result       = $call->getReply();
    $view->result = [];
    if (isset($result['values']) && is_array($result['values'])) {
      foreach ($result['values'] as $value) {
        $object         = json_decode(json_encode($value));
        $view->result[] = $object;
      }
    }

    $countOptions['cache']    = $view->query->options['cache'];
    $call                     = cmrf_views_sendCall($entity, $count_api_action,
      $parameters, $countOptions, $profile);
    $result                   = $call->getReply();
    $this->pager->total_items = $result['result'];
    $view->total_rows         = $result['result'];

    // Tell pager and views total item count.
    // Create a new pager object.
    $this->pager->update_page_info();
    $view->execute_time = microtime(TRUE) - $start;
  }

}