<?php namespace Drupal\cmrf_views\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * Default implementation of the base field plugin.
 *
 * @ingroup cmrf_views_field_handlers
 *
 * @ViewsField("cmrf_views_standard")
 */
class Standard extends \Drupal\views\Plugin\views\field\Standard {

  use MultiItemsFieldHandler;

  /**
   * @inheritDoc
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $this->initMultiple($view, $display, $options);
  }

  /**
   * @inheritDoc
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    parent::buildOptionsForm($form, $form_state);
    $this->buildMultipleOptionsForm($form, $form_state);
  }

  /**
   * @inheritDoc
   */
  public function defineOptions() {
    return parent::defineOptions() + $this->defineMultipleOptions();
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(ResultRow $values, $field = NULL) {
    $alias = isset($field) ? $this->aliases[$field] : $this->field_alias;
    if (isset($values->{$alias})) {
      // Loop through the array and merge by 'display_name' if possible.
      if (is_array($values->{$alias})) {
        return '';
        $merge = '';
        foreach ($values->{$alias} as $key => $value) {
          if ($key == 'display_name') {
            $merge .= $value;
          }
        }
        $values->{$alias} = NULL;
        $values->{$alias} = $merge;

      }
      return $values->{$alias};
    }
  }

}
