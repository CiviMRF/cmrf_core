<?php

namespace Drupal\cmrf_core\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CMRFCredentialForm.
 */
class CMRFCredentialForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $cmrf_credential = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $cmrf_credential->label(),
      '#description' => $this->t("Label for the CMRF."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $cmrf_credential->id(),
      '#machine_name' => [
        'exists' => '\Drupal\cmrf_core\Entity\CMRFCredential::load',
      ],
      '#disabled' => !$cmrf_credential->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $cmrf_credential = $this->entity;
    $status = $cmrf_credential->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label CMRF.', [
          '%label' => $cmrf_credential->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label CMRF.', [
          '%label' => $cmrf_credential->label(),
        ]));
    }
    $form_state->setRedirectUrl($cmrf_credential->toUrl('collection'));
  }

}
