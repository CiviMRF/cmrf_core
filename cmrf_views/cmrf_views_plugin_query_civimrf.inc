<?php

class cmrf_views_plugin_query_civimrf extends views_plugin_query {

  function build(&$view) {		
		$view->init_pager();
    // Let the pager modify the query to add limits.
    $this->pager->query();
  }

  function execute(&$view) {
    $table_data = views_fetch_data($this->base_table);
    $entity = $table_data['table']['base']['entity'];
    $get_api_action = $table_data['table']['base']['action'];
    $count_api_action = $table_data['table']['base']['count'];
    $profile = $table_data['table']['base']['profile'];
    $dataset_params = json_decode($table_data['table']['base']['params'], true);
    if (!is_array($dataset_params)) {
      $dataset_params = array();
    }
    
    $start = microtime(TRUE);

    $options['cache'] = $view->query->options['cache'];
    if (isset($this->limit)) {
      $options['limit'] = $this->limit;
    } else {
      $options['limit'] = 0;
    }
    if (isset($this->offset)) {
      $options['offset'] = $this->offset;
    }
    $parameters = array();
		
		// Set the return fields
		$parameters['return'] = array();
		foreach($this->fields as $field) {
			$parameters['return'][] = $field['field'];	
		}
		
		//Set the query parameters.
		foreach($this->where as $where) {
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
					$parameters[$where['field']] = array($where['operator'] => $where['value']);
					break;
				case 'in':
					$parameters[$where['field']] = array('IN' => $where['value']);
					break;
				case 'not in':
					$parameters[$where['field']] = array('NOT IN' => $where['value']);
					break;
				default:
					$parameters[$where['field']] = $where['value'];
					break;
			}
		}
		
		// Do sorting
		if (isset($this->orderby) && count($this->orderby) > 0) {
		  $options['sort'] = '';
  		foreach($this->orderby as $orderby) {
        if (strlen($options['sort'])) {
  				$options['sort'] .= ', ';
  			}
  			$options['sort'] .= $orderby['field'].' '.$orderby['direction']; 
  		}
    }

    // Set the parameters from the dataset params options.
    foreach($dataset_params as $key => $value) {
		  $parameters[$key] = $value;
    }

    $call = cmrf_views_sendCall($entity, $get_api_action, $parameters, $options, $profile);
    $result = $call->getReply();
    $view->result = array();
    if (isset($result['values']) && is_array($result['values'])) {
      foreach($result['values'] as $value) {
        $object = json_decode(json_encode($value));
        $view->result[] = $object;
      }
    }
		
		$countOptions['cache'] = $view->query->options['cache'];
		$call = cmrf_views_sendCall($entity, $count_api_action, $parameters, $countOptions, $profile);
    $result = $call->getReply();
		$this->pager->total_items = $result['result'];
		$view->total_rows = $result['result'];

    // Tell pager and views total item count.
    // Create a new pager object.
    $this->pager->update_page_info();
    $view->execute_time = microtime(TRUE) - $start;
  }

  function add_field($table, $field, $alias = '', $params = array()) {
    $alias = $field;

    // Add field info array.
    if (empty($this->fields[$field])) {
      $this->fields[$field] = array(
          'field' => $field,
          'table' => $table,
          'alias' => $alias,
        ) + $params;
    }

    return $field;
  }
	
	/**
   * Add a simple WHERE clause to the query. The caller is responsible for
   * ensuring that all fields are fully qualified (TABLE.FIELD) and that
   * the table already exists in the query.
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
   *   The comparison operator, such as =, <, or >=. It also accepts more complex
   *   options such as IN, LIKE, or BETWEEN. Defaults to IN if $value is an array
   *   = otherwise. If $field is a string you have to use 'formula' here.
   *
   * The $field, $value and $operator arguments can also be passed in with a
   * single DatabaseCondition object, like this:
   * @code
   *   $this->query->add_where(
   *     $this->options['group'],
   *     db_or()
   *       ->condition($field, $value, 'NOT IN')
   *       ->condition($field, $value, 'IS NULL')
   *   );
   * @endcode
   *
   * @see QueryConditionInterface::condition()
   * @see DatabaseCondition
   */
  function add_where($group, $field, $value = NULL, $operator = NULL) {
  	if (strpos($field, '.', 0) === 0) {
  		$field = substr($field, 1);
  	}
    $this->where[] = array(
      'field' => $field,
      'value' => $value,
      'operator' => $operator,
    );
  }

  function option_definition() {
    $options = parent::option_definition();
		$options['cache'] = array('default' => '30 min');
    return $options;
  }

  function options_form(&$form, &$form_state) {		
		$form['cache'] = array(
      '#type' => 'textfield',
      '#title' => t('CiviMRF cachtime'),
      '#multiple' => FALSE,
      '#default_value' => isset($this->options['cache']) ? $this->options['cache'] : '',
      '#required' => TRUE,
    );
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
   *   Any params that should be passed through to the add_field.
   */
  function add_orderby($table, $field = NULL, $order = 'ASC', $alias = '', $params = array()) {
    // Only ensure the table if it's not the special random key.
    // @todo: Maybe it would make sense to just add a add_orderby_rand or something similar.
    if ($table && $table != 'rand') {
      $this->ensure_table($table);
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
      $as = $this->add_field($table, $field, $as, $params);
    }

    $this->orderby[] = array(
      'field' => $as,
      'direction' => strtoupper($order)
    );
  }

}