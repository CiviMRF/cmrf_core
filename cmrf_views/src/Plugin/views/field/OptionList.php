<?php namespace Drupal\cmrf_views\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\field\MultiItemsFieldHandlerInterface;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * Shows the label instead of the value for field that has an option list
 *
 * @ingroup cmrf_views_field_handlers
 *
 * @ViewsField("cmrf_views_optionlist")
 */
class OptionList extends \Drupal\views\Plugin\views\field\Standard implements MultiItemsFieldHandlerInterface {

  use MultiItemsFieldHandler {
    getItems as MultiItemsFieldHandler_getItems;
  }

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
   * @inheritDoc
   */
  public function getItems(ResultRow $values) {
    if (!isset($values->{$this->field_alias})) {
      $values->{$this->field_alias} = [];
    }
    if (!is_array($values->{$this->field_alias})) {
      $values->{$this->field_alias} = [$values->{$this->field_alias}];
    }
    return $this->MultiItemsFieldHandler_getItems($values);
  }

  public function getValue(ResultRow $values, $field = NULL) {
    $alias = isset($field) ? $this->aliases[$field] : $this->field_alias;
    $options = $this->definition['options'];
    $key = $values->{$alias};
    if (key_exists($key, $options)) {
      return $options[$key];
    }
    elseif (in_array($key, $options)) {
      return $key;
    }
    else {
      // if the is not found show nothing
      return FALSE;
    }
  }

}
