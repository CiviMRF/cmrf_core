<?php

namespace Drupal\cmrf_core\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CMRFCredentialsForm.
 */
class CMRFCredentialsForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $cmrf_credentials = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $cmrf_credentials->label(),
      '#description' => $this->t("Label for the CMRF."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $cmrf_credentials->id(),
      '#machine_name' => [
        'exists' => '\Drupal\cmrf_core\Entity\CMRFCredentials::load',
      ],
      '#disabled' => !$cmrf_credentials->isNew(),
    ];

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $cmrf_credentials = $this->entity;
    $status = $cmrf_credentials->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label CMRF.', [
          '%label' => $cmrf_credentials->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label CMRF.', [
          '%label' => $cmrf_credentials->label(),
        ]));
    }
    $form_state->setRedirectUrl($cmrf_credentials->toUrl('collection'));
  }

}
