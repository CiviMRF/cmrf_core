<?php

namespace Drupal\cmrf_core\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CMRFConnectorForm.
 */
class CMRFConnectorForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $cmrf_connector = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $cmrf_connector->label(),
      '#description' => $this->t("Label for the CMRF connector."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $cmrf_connector->id(),
      '#machine_name' => [
        'exists' => '\Drupal\cmrf_core\Entity\CMRFConnector::load',
      ],
      '#disabled' => !$cmrf_connector->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $cmrf_connector = $this->entity;
    $status = $cmrf_connector->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label CMRF connector.', [
          '%label' => $cmrf_connector->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label CMRF connector.', [
          '%label' => $cmrf_connector->label(),
        ]));
    }
    $form_state->setRedirectUrl($cmrf_connector->toUrl('collection'));
  }

}
