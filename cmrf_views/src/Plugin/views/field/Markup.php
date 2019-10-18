<?php namespace Drupal\cmrf_views\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * A handler to run a field through check_markup, using a companion
 * format field.
 *
 * - format: (REQUIRED) Either a string format id to use for this field or an
 *           array('field' => {$field}) where $field is the field in this table
 *           used to control the format such as the 'format' field in the node,
 *           which goes with the 'body' field.
 *
 * @ingroup cmrf_views_field_handlers
 *
 * @ViewsField("cmrf_views_markup")
 */
class Markup extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options                = parent::defineOptions();
    $options['text_format'] = ['default' => filter_fallback_format()];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['text_format'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Text format'),
      '#options'       => $this->get_text_formats(),
      '#default_value' => isset($this->options['text_format']) ? $this->options['text_format'] : filter_fallback_format(),
    ];
    parent::buildOptionsForm($form, $form_state);
  }

  public function render(ResultRow $values) {
    $value = $this->getValue($values);
    if ($value) {
      $value = str_replace('<!--break-->', '', $value);
      return check_markup($value, $this->options['text_format']);
    }
  }

  private function get_text_formats() {
    $formats = [];
    foreach (filter_formats() as $format) {
      $formats[$format->get('format')] = $format->get('name');
    }
    return $formats;
  }

}
