<?php namespace Drupal\cmrf_views\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Filter to handle dates stored as a timestamp.
 *
 * @ingroup crmf_views_filter_handlers
 *
 * @ViewsFilter("cmrf_views_filter_optionlist")
 */
class OptionList extends InOperator {
  /**
   * Array cache for refreshed field data, indexed by dataset ID.
   *
   * Used to prevent having to fetch field data multiple times per request if
   * multiple filters belonging to the same dataset bypass caching of field
   * option values.
   *
   * @var array<string,mixed>
   */
  protected static $_refreshedDatasetFields = [];

  public function getValueOptions() {
    if ($this->options['expose']['bypass_cache'] ?? FALSE) {
      $this->refreshValueOptions();
    }

    if (isset($this->valueOptions)) {
      return $this->valueOptions;
    } else {
      $this->valueOptions = empty($this->definition['options'])?[]:$this->definition['options'];
      return $this->valueOptions;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensureMyTable();
    $field = "$this->realField";
    $info = $this->operators();
    if (!empty($info[$this->operator]['method'])) {
      $this->{$info[$this->operator]['method']}($field);
    }
  }

  protected function opSimple() {
    if (empty($this->value)) {
      return;
    }
    $this->ensureMyTable();

    // We use array_values() because the checkboxes keep keys and that can cause
    // array addition problems.
    $this->query->addWhere($this->options['group'], "$this->realField", array_values($this->value), $this->operator);
  }

  public function defaultExposeOptions() {
    parent::defaultExposeOptions();
    $this->options['expose']['bypass_cache'] = FALSE;
  }

  public function buildExposeForm(&$form, FormStateInterface $form_state) {
    parent::buildExposeForm($form, $form_state);

    $form['expose']['bypass_cache'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Bypass cache'),
      '#description' => $this->t(
        'If checked, the available options will be refreshed each time the view is loaded. ' .
        'This is useful if your filter options are dynamic. ' .
        'It is not recommended to combine this with view caching, as it could lead to undesirable/unpredicable results.'
      ),
      '#default_value' => !empty($this->options['expose']['bypass_cache']),
    ];
  }

  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['expose']['contains']['bypass_cache'] = ['default' => FALSE];

    return $options;
  }

  protected function refreshValueOptions():  void {
    // Our dataset ID is the uid minus the prefix, which is the table this filter thinks it belongs to
    $datasetId = preg_replace('/^cmrf_views_/', '', $this->table);
    // Our field ID is the real field this filter belongs to
    $fieldId = $this->realField;

    // If fields have not been refreshed for this dataset, do that now
    if (!($fields = self::$_refreshedDatasetFields[$datasetId] ?? null)) {
      /** @var \Drupal\cmrf_views\CMRFViews $cmrfViews */
      $cmrfViews = \Drupal::service('cmrf_views.views');

      // Look up dataset for given dataset ID
      $datasets = $cmrfViews->getDatasets();
      if (!($dataset = $datasets[$datasetId] ?? null)) {
        return;
      }

      // Look up fields for given dataset, bypassing cache
      $fields = self::$_refreshedDatasetFields[$datasetId] = $cmrfViews->getFields($dataset);
    }

    if (!($field = $fields[$fieldId] ?? null)) {
      return;
    }

    // Replace current filter's value options with refreshed filter options from CiviCRM
    $filterOptions = $field['filter']['options'] ?? [];
    $this->valueOptions = $filterOptions;
  }
}
