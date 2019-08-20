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

  /**
   * Executes the query and fills the associated view object with according
   * values.
   *
   * Values to set: $view->result, $view->total_rows, $view->execute_time,
   * $view->current_page.
   */
  public function execute(ViewExecutable $view) {
    $table_data = $this->viewsData->get($view->storage->get('base_table'));
    if (!empty($table_data)) {
      $api_entity       = $table_data['table']['base']['entity'];
      $api_action       = $table_data['table']['base']['action'];
      $api_count_action = $table_data['table']['base']['getcount'];
      $connector        = $table_data['table']['base']['connector'];
      $dataset_params   = json_decode($table_data['table']['base']['params'], TRUE);
      if (!is_array($dataset_params)) {
        $dataset_params = [];
      }

      $parameters = [];
      $start      = microtime(TRUE);

      // Count options.
      $options['cache'] = empty($view->query->options['cache']) ? NULL : $view->query->options['cache'];
      $options['limit'] = 0;

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

      // TODO: verify views cache.
      $options['limit']  = $view->getPager()->getItemsPerPage();
      $options['offset'] = $view->getCurrentPage() * $view->getPager()->getItemsPerPage();

      // Set the return fields
      $parameters['return'] = [];

      // Set the query parameters.
      if (!empty($this->where)) {
        foreach ($this->where as $where_group) {
          if (empty($where_group['conditions'])) {
            continue;
          }
          foreach ($where_group['conditions'] as $condition) {
            switch ($condition['operator']) {
              case '>':
              case '>=':
              case '<=':
              case '<':
              case '!=':
              case 'BETWEEN':
              case 'NOT BETWEEN':
              case 'LIKE':
              case 'NOT LIKE':
                $parameters[$condition['field']] = [$condition['operator'] => $condition['value']];
                break;
              case 'in':
                $parameters[$condition['field']] = ['IN' => $condition['value']];
                break;
              case 'not in':
                $parameters[$condition['field']] = ['NOT IN' => $condition['value']];
                break;
              default:
                $parameters[$condition['field']] = $condition['value'];
                break;
            }
          }
        }
      }

      // Do sorting
      if (!empty($this->orderby)) {
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


      // Execute time.
      $view->execute_time = microtime(TRUE) - $start;
    }
  }

  /**
   * Add a simple WHERE clause to the query. The caller is responsible for
   * ensuring that all fields are fully qualified (TABLE.FIELD) and that
   * the table already exists in the query.
   *
   * The $field, $value and $operator arguments can also be passed in with a
   * single DatabaseCondition object, like this:
   *
   * @code
   * $this->query->addWhere(
   *   $this->options['group'],
   *   (new Condition('OR'))
   *     ->condition($field, $value, 'NOT IN')
   *     ->condition($field, $value, 'IS NULL')
   * );
   * @endcode
   *
   * @param $group
   *   The WHERE group to add these to; groups are used to create AND/OR
   *   sections. Groups cannot be nested. Use 0 as the default group.
   *   If the group does not yet exist it will be created as an AND group.
   * @param $field
   *   The name of the field to check.
   * @param $value
   *   The value to test the field against. In most cases, this is a scalar. For more
   *   complex options, it is an array. The meaning of each element in the array is
   *   dependent on the $operator.
   * @param $operator
   *   The comparison operator, such as =, <, or >=. It also accepts more
   *   complex options such as IN, LIKE, LIKE BINARY, or BETWEEN. Defaults to =.
   *   If $field is a string you have to use 'formula' here.
   *
   * @see \Drupal\Core\Database\Query\ConditionInterface::condition()
   * @see \Drupal\Core\Database\Query\Condition
   */
  public function addWhere($group, $field, $value = NULL, $operator = NULL) {
    // Ensure all variants of 0 are actually 0. Thus '', 0 and NULL are all
    // the default group.
    if (empty($group)) {
      $group = 0;
    }

    // Check for a group.
    if (!isset($this->where[$group])) {
      $this->setWhereGroup('AND', $group);
    }

    $this->where[$group]['conditions'][] = [
      'field'    => str_replace('.', '', $field),
      'value'    => $value,
      'operator' => $operator,
    ];
  }

  /**
   * Generates a unique placeholder used in the API query.
   */
  public function placeholder($base = 'views') {
    static $placeholders = [];
    if (!isset($placeholders[$base])) {
      $placeholders[$base] = 0;
      return ':' . $base;
    }
    else {
      return ':' . $base . ++$placeholders[$base];
    }
  }

  /**
   * Add a complex WHERE clause to the query.
   *
   * The caller is responsible for ensuring that all fields are fully qualified
   * (TABLE.FIELD) and that the table already exists in the query.
   * Internally the dbtng method "where" is used.
   *
   * @param $group
   *   The WHERE group to add these to; groups are used to create AND/OR
   *   sections. Groups cannot be nested. Use 0 as the default group.
   *   If the group does not yet exist it will be created as an AND group.
   * @param $snippet
   *   The snippet to check. This can be either a column or
   *   a complex expression like "UPPER(table.field) = 'value'"
   * @param $args
   *   An associative array of arguments.
   *
   * @see QueryConditionInterface::where()
   */
  public function addWhereExpression($group, $snippet, $args = []) {
    // Ensure all variants of 0 are actually 0. Thus '', 0 and NULL are all
    // the default group.
    if (empty($group)) {
      $group = 0;
    }

    // Check for a group.
    if (!isset($this->where[$group])) {
      $this->setWhereGroup('AND', $group);
    }

    $this->where[$group]['conditions'][] = [
      'field'    => $snippet,
      'value'    => $args,
      'operator' => 'formula',
    ];
  }

  /**
   * Add an ORDER BY clause to the query.
   *
   * @param $table
   *   The table this field is part of. If a formula, enter NULL.
   *   If you want to orderby random use "rand" as table and nothing else.
   * @param $field
   *   The field or formula to sort on. If already a field, enter NULL
   *   and put in the alias.
   * @param $order
   *   Either ASC or DESC.
   * @param $alias
   *   The alias to add the field as. In SQL, all fields in the order by
   *   must also be in the SELECT portion. If an $alias isn't specified
   *   one will be generated for from the $field; however, if the
   *   $field is a formula, this alias will likely fail.
   * @param $params
   *   Any params that should be passed through to the addField.
   */
  public function addOrderBy($table, $field = NULL, $order = 'ASC', $alias = '', $params = []) {
    // Only ensure the table if it's not the special random key.
    // @todo: Maybe it would make sense to just add an addOrderByRand or something similar.
    if ($table && $table != 'rand') {
      $this->ensureTable($table);
    }

    // Only fill out this aliasing if there is a table;
    // otherwise we assume it is a formula.
    if (!$alias && $table) {
      $as = $table . '_' . $field;
    }
    else {
      $as = $alias;
    }

    if ($field) {
      $as = $this->addField($table, $field, $as, $params);
    }

    $this->orderby[] = [
      'field'     => $as,
      'direction' => strtoupper($order),
    ];
  }

}
