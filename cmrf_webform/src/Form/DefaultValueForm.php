<?php

namespace Drupal\cmrf_webform\Form;

use Drupal\cmrf_webform\Entity\DefaultValue;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cmrf_webform\Traits\ConnectorAwareTrait;
use Drupal\cmrf_webform\Traits\WebformAwareTrait;
use Drupal\webform\Entity\Webform;

class DefaultValueForm extends CMRFWebformFormBase {

  use WebformAwareTrait;
  use ConnectorAwareTrait;

  public static function defaultValues() {
    return [
      'entity' => 'Contact',
      'action' => 'getsingle',
      'parameters' => '{}',
      'options' => '{}',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $entity = $this->entity;

    $form['details'] = [
      '#type' => 'html_tag',
      '#tag' => 'p',
      '#value' => $this->t('You may define a CiviCRM API action to use for retrieving default values for components in this Webform. When this is enabled, and a CiviCRM contact hash is present as a URL query parameter <i>hash</i>, the default value for each component in this Webform with the name of an attribute in the API call response will be set to the retrieved value for this attribute.'),
    ];

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $entity->label(),
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

    $form['connector'] = [
      '#type' => 'select',
      '#title' => $this->t('Connector entity'),
      '#description' => $this->t('The connector to use for this call'),
      '#options' => $this->getConnectorEntities(),
      '#required' => true,
      '#default_value' => $entity->getConnector(),
    ];

    $form['webform'] = [
      '#type' => 'select',
      '#title' => $this->t('Webform entity'),
      '#description' => $this->t('The webform entity item to use'),
      '#options' => $this->getWebformEntities(),
      '#required' => true,
      '#default_value' => $entity->getWebform(),
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

    $form['field_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Element machine name'),
      '#description' => $this->t('Machine name of element to be prefilled'),
      '#maxlength' => 255,
      '#default_value' => $entity->getFieldKey(),
      '#required' => TRUE,
    ];

    $form['parameters'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Parameters'),
      '#default_value' => $entity->getParameters(),
      '#description' => $this->t("JSON-formatted CiviCRM API parameters. The parameter hash will be added automatically from the URL query parameter <i>hash</i>."),
    ];

    $form['options'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Options'),
      '#default_value' => $entity->getOptions(),
      '#description' => $this->t("JSON-formatted CiviCRM API options"),
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

    foreach (['parameters', 'options'] as $field) {
      $php_parameters = json_decode($form_state->getValue($field));
      if ($php_parameters === NULL) {
        $form_state->setError($form[$field], $this->t(ucfirst($field) . 'field does not contain a valid JSON'));
      }
    }

    // $webform = Webform::load($form_state->getValue('webform'));
    // $exists = DefaultValue::getForWebform($webform);
    // if ($exists !== NULL && $exists->id() != $form_state->getValue('id')) {
    //   $form_state->setError($form['webform'], $this->t('Default value handler already exists for this form'));
    // }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = $this->entity->save();

    if ($status) {
      $this->messenger()->addMessage($this->t('Saved the %label Default values.', [
        '%label' => $this->entity->label(),
      ]));
    }

    $form_state->setRedirect('entity.cmrf_webform_default_value.collection');
  }

  /**
   * Helper function to check whether an Example configuration entity exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('cmrf_webform_default_value')->getQuery()
      ->accessCheck(TRUE)
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
