<?php

class cmrf_views_handler_field_prerender_list extends cmrf_views_handler_field {

  function option_definition() {
    $options = parent::option_definition();

    $options['type'] = array('default' => 'separator');
    $options['separator'] = array('default' => ', ');
    $options['separator_data'] = array('default' => ',');

    return $options;
  }

  function options_form(&$form, &$form_state) {
    $form['separator_data'] = array(
      '#type' => 'textfield',
      '#title' => t('Separator'),
      '#description' => t('Split each item based on this character'),
      '#default_value' => $this->options['separator_data'],
    );

    $form['type'] = array(
      '#type' => 'radios',
      '#title' => t('Display type'),
      '#options' => array(
        'ul' => t('Unordered list'),
        'ol' => t('Ordered list'),
        'per_line' => t('Each item on a new line'),
        'separator' => t('Simple separator'),
      ),
      '#default_value' => $this->options['type'],
    );

    $form['separator'] = array(
      '#type' => 'textfield',
      '#title' => t('Separator'),
      '#default_value' => $this->options['separator'],
      '#dependency' => array('radio:options[type]' => array('separator')),
    );
    parent::options_form($form, $form_state);
  }

  /**
   * Render the field.
   *
   * This function is deprecated, but left in for older systems that have not
   * yet or won't update their prerender list fields. If a render_item method
   * exists, this will not get used by advanced_render.
   */
  function render($values) {
    $field = $this->get_value($values);
    if (is_array($field)) {
      $items = $field;
    } else {
      $items = explode($this->options['separator_data'], $field);
    }
    if (!empty($field) && !empty($items)) {
      if ($this->options['type'] == 'per_line') {
        return implode("<br />\n", $items);
      } elseif ($this->options['type'] == 'separator') {
        return implode($this->sanitize_value($this->options['separator']), $items);
      }
      else {
        return theme('item_list',
          array(
            'items' => $items,
            'title' => NULL,
            'type' => $this->options['type']
          ));
      }
    }
  }

}