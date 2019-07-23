<?php

namespace Drupal\cmrf_webform\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OptionSetForm extends EntityForm {

  /**
   * Constructs an ExampleForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public static function defaultValues() {
    return [
      'entity' => 'OptionSet',
      'action' => 'get',
      'parameters' => json_encode([
        'is_active' => 1,
        'option_group_id' => 'FOO',
        'return' => 'value,label',
      ]),
      'cache' => 0,
      'key_property' => 'label',
      'value_property' => 'value',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $entity = $this->entity;

    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#maxlength' => 255,
      '#default_value' => $entity->getTitle(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $entity->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
      '#disabled' => !$entity->isNew(),
    ];

    $form['entity'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Entity'),
      '#maxlength' => 255,
      '#default_value' => $entity->getEntity(),
      '#required' => TRUE,
    ];

    $form['action'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Action'),
      '#maxlength' => 255,
      '#default_value' => $entity->getAction(),
      '#required' => TRUE,
    ];

    $form['parameters'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Parameters'),
      '#default_value' => $entity->getParameters(),
      '#description' => $this->t("JSON-formatted object with API parameters for the entity and action entered above."),
    ];

    $form['key_property'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Key property'),
      '#maxlength' => 255,
      '#default_value' => $entity->getKeyProperty(),
      '#description' => $this->t("A property of the queried entity to use as the key for the dedicated select option."),
      '#required' => TRUE,
    ];

    $form['value_property'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value property'),
      '#maxlength' => 255,
      '#default_value' => $entity->getValueProperty(),
      '#description' => $this->t("A property of the queried entity to use as the content (i.e. the label/value) for the dedicated select option."),
      '#required' => TRUE,
    ];

    $form['cache'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cache'),
      '#maxlength' => 255,
      '#default_value' => $entity->getCache(),
      '#description' => $this->t("A relative date/time format that the PHP datetime parser understands, e.g. `1 week`. Defaults to `0` (no caching)."),
    ];

    if ($entity->isNew()) {
      $defaults = static::defaultValues();
      foreach ($defaults as $key => $value) {
        $form[$key]['#default_value'] = $value;
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    $php_parameters = json_decode($form_state->getValue('parameters'));
    if ($php_parameters === NULL) {
      $form_state->setError($form['parameters'], $this->t('Parameters field does not contain a valid JSON'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $options = $this->entity;
    $status = $options->save();

    if ($status) {
      $this->messenger()->addMessage($this->t('Saved the %title Option set.', [
        '%title' => $options->getTitle(),
      ]));
    }

    $form_state->setRedirect('entity.cmrf_webform_option_set.collection');
  }

  /**
   * Helper function to check whether an Example configuration entity exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('cmrf_webform_option_set')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
