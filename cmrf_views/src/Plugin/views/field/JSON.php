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
   * @inheritDoc
   */
  public function render_item($count, $item) {
    $render = [
      // Use a special overrideable template for each top-level JSON item.
      '#theme' => 'cmrf_views_field_json_item',
      '#field' => $this,
      '#count' => $count,
      '#item' => $item,
      // Render an item list from the JSON structure as default markup.
      '#item_list' => $this->render_item_item_list($item),
    ];
    return render($render);
  }

  public function render_item_item_list($item) {
    $render = [
      '#theme' => 'item_list',
    ];
    foreach ($item as $attribute => $value) {
      if (is_array($value)) {
        $value = $this->render_item_item_list($value);
      }
      $render['#items'][] = $value ;
    }
    return $render;
  }

  /**
   * @inheritDoc
   */
  public function getItems(ResultRow $values) {
    return \Drupal\Component\Serialization\Json::decode($values->{$this->field_alias});
  }

  public function getValue(ResultRow $values, $field = NULL) {
    return \Drupal\Component\Serialization\Json::decode($values->{$this->field_alias});
  }

}
