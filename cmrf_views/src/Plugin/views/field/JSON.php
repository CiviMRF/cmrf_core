<?php


namespace Drupal\cmrf_views\Plugin\views\field;


use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\MultiItemsFieldHandlerInterface;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * Iplementation for JSON field plugin.
 *
 * @ingroup cmrf_views_field_handlers
 *
 * @ViewsField("cmrf_views_json")
 */
class JSON extends \Drupal\views\Plugin\views\field\Standard implements MultiItemsFieldHandlerInterface {

  /**
   * Does the field supports multiple field values.
   *
   * @var bool
   */
  public $multiple;

  /**
   * Does the rendered fields get limited.
   *
   * @var bool
   */
  public $limit_values;

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);

    $this->multiple = FALSE;
    $this->limit_values = FALSE;
    $this->multiple = TRUE;

    // If "First and last only" is chosen, limit the values
    if (!empty($this->options['delta_first_last'])) {
      $this->limit_values = TRUE;
    }

    // We only limit values if the user hasn't selected "all" or 0.
    if ($this->options['delta_limit'] > 0 || intval($this->options['delta_offset'])) {
      $this->limit_values = TRUE;
    }
  }

  /**
   * @inheritDoc
   */
  public function render_item($count, $item) {
    // TODO: Provide a separate (overrideable) template?
    $render = [
      '#theme' => 'item_list',
    ];
    $i = 0;
    foreach ($item as $attribute => $value) {
      if (is_array($value)) {
        $value = $this->render_item($i, $value);
      }
      $render['#items'][] = $value ;
      $i++;
    }
    return render($render);
  }

  /**
   * @inheritDoc
   */
  public function getItems(ResultRow $values) {
    return \Drupal\Component\Serialization\Json::decode($values->{$this->field_alias});
  }

  /**
   * @inheritDoc
   */
  public function renderItems($items) {
    $items = $this->prepareItemsByDelta($items);
    if (!empty($items)) {
      if ($this->options['multi_type'] == 'separator') {
        $separator = $this->options['multi_type'] == 'separator' ? Xss::filterAdmin($this->options['separator']) : '';
        $build = [
          '#type' => 'inline_template',
          '#template' => '{{ items | safe_join(separator) }}',
          '#context' => ['separator' => $separator, 'items' => $items],
        ];
      }
      else {
        $build = [
          '#theme' => 'item_list',
          '#items' => $items,
          '#title' => NULL,
          '#list_type' => $this->options['multi_type'],
        ];
      }
      return $this->renderer->render($build);
    }
  }

  /**
   * @inheritDoc
   */
  public function defineOptions() {
    $options = parent::defineOptions();
    // Options used for multiple value fields.
    // Default to 'all'.
    $options['delta_limit'] = [
      'default' => 0,
    ];
    $options['delta_offset'] = [
      'default' => 0,
    ];
    $options['delta_reversed'] = [
      'default' => FALSE,
    ];
    $options['delta_first_last'] = [
      'default' => FALSE,
    ];

    $options['multi_type'] = [
      'default' => 'separator',
    ];
    $options['separator'] = [
      'default' => ', ',
    ];

    return $options;
  }

  /**
   * @inheritDoc
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);

    $this->multiple_options_form($form, $form_state);
  }

  /**
   * Provide options for multiple value fields.
   */
  public function multiple_options_form(&$form, FormStateInterface $form_state) {
    $form['multiple_field_settings'] = [
      '#type' => 'details',
      '#title' => $this->t('Multiple field settings'),
      '#weight' => 5,
    ];

    // Make the string translatable by keeping it as a whole rather than
    // translating prefix and suffix separately.
    [$prefix, $suffix] = explode('@count', $this->t('Display @count value(s)'));

    $form['multi_type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Display type'),
      '#options' => [
        'ul' => $this->t('Unordered list'),
        'ol' => $this->t('Ordered list'),
        'separator' => $this->t('Simple separator'),
      ],
      '#default_value' => $this->options['multi_type'],
      '#fieldset' => 'multiple_field_settings',
    ];

    $form['separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Separator'),
      '#default_value' => $this->options['separator'],
      '#states' => [
        'visible' => [
          ':input[name="options[multi_type]"]' => ['value' => 'separator'],
        ],
      ],
      '#fieldset' => 'multiple_field_settings',
    ];

    $form['delta_limit'] = [
      '#type' => 'textfield',
      '#size' => 5,
      '#field_prefix' => $prefix,
      '#field_suffix' => $suffix,
      '#default_value' => $this->options['delta_limit'],
      '#prefix' => '<div class="container-inline">',
      '#fieldset' => 'multiple_field_settings',
    ];

    [$prefix, $suffix] = explode('@count', $this->t('starting from @count'));
    $form['delta_offset'] = [
      '#type' => 'textfield',
      '#size' => 5,
      '#field_prefix' => $prefix,
      '#field_suffix' => $suffix,
      '#default_value' => $this->options['delta_offset'],
      '#description' => $this->t('(first item is 0)'),
      '#fieldset' => 'multiple_field_settings',
    ];
    $form['delta_reversed'] = [
      '#title' => $this->t('Reversed'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['delta_reversed'],
      '#suffix' => $suffix,
      '#description' => $this->t('(start from last values)'),
      '#fieldset' => 'multiple_field_settings',
    ];
    $form['delta_first_last'] = [
      '#title' => $this->t('First and last only'),
      '#type' => 'checkbox',
      '#default_value' => $this->options['delta_first_last'],
      '#suffix' => '</div>',
      '#fieldset' => 'multiple_field_settings',
    ];
  }

  /**
   * Adapts the $items according to the delta configuration.
   *
   * This selects displayed deltas, reorders items, and takes offsets into
   * account.
   *
   * @param array $all_values
   *   The items for individual rendering.
   *
   * @return array
   *   The manipulated items.
   */
  protected function prepareItemsByDelta(array $all_values) {
    if ($this->options['delta_reversed']) {
      $all_values = array_reverse($all_values);
    }

    // We are supposed to show only certain deltas.
    if ($this->limit_values) {
      $delta_limit = $this->options['delta_limit'];
      $offset = intval($this->options['delta_offset']);
      if ($delta_limit == 0) {
        $delta_limit = count($all_values) - $offset;
      }

      // Determine if only the first and last values should be shown.
      $delta_first_last = $this->options['delta_first_last'];

      $new_values = [];
      for ($i = 0; $i < $delta_limit; $i++) {
        $new_delta = $offset + $i;

        if (isset($all_values[$new_delta])) {
          // If first-last option was selected, only use the first and last
          // values.
          if (!$delta_first_last
            // Use the first value.
            || $new_delta == $offset
            // Use the last value.
            || $new_delta == ($delta_limit + $offset - 1)) {
            $new_values[] = $all_values[$new_delta];
          }
        }
      }
      $all_values = $new_values;
    }

    return $all_values;
  }

}
