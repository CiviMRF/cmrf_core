<?php

namespace Drupal\cmrf_core\Form;

use Drupal\cmrf_core\Entity\CMRFCredentials;
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

    /** @var CMRFCredentials $cmrf_credentials */
    $cmrf_credentials = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $cmrf_credentials->label(),
      '#description' => $this->t("Label for this set of credentials."),
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

    $form['url'] = [
        '#type' => 'url',
        '#title' => $this->t('URL'),
        '#default_value' => $cmrf_credentials->url,
        '#description' => $this->t('The URL to your CiviCRM installation e.g. https://civi.my.site/sites/all/modules/civicrm/extern/rest.php.'),
        '#required' => TRUE,
    ];

    $form['site_key'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Site key'),
        '#default_value' => $cmrf_credentials->site_key,
        '#maxlength' => 255,
        '#description' => $this->t('The site key of your civicrm installation.'),
        '#required' => TRUE,
    ];

    $form['api_key'] = [
        '#type' => 'textfield',
        '#title' => $this->t('API key'),
        '#default_value' => $cmrf_credentials->api_key,
        '#maxlength' => 255,
        '#description' => $this->t('The api key of your civicrm installation.'),
        '#required' => TRUE,
    ];

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
