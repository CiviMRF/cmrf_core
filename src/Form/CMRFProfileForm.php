<?php namespace Drupal\cmrf_core\Form;

use Drupal\cmrf_core\Entity\CMRFProfile;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;

/**
 * Class CMRFProfileForm.
 */
class CMRFProfileForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var CMRFProfile $cmrf_profile */
    $cmrf_profile = $this->entity;

    $form['label'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Label'),
      '#maxlength'     => 255,
      '#default_value' => $cmrf_profile->label(),
      '#description'   => $this->t("Label for this profile."),
      '#required'      => TRUE,
    ];

    $form['id'] = [
      '#type'          => 'machine_name',
      '#default_value' => $cmrf_profile->id(),
      '#machine_name'  => [
        'exists' => '\Drupal\cmrf_core\Entity\CMRFProfile::load',
      ],
      '#disabled'      => !$cmrf_profile->isNew(),
    ];

    $form['url'] = [
      '#type'          => 'url',
      '#title'         => $this->t('URL'),
      '#default_value' => $cmrf_profile->url,
      '#description'   => $this->t('The URL to your CiviCRM installation e.g. https://civi.my.site/sites/all/modules/civicrm/extern/rest.php.'),
      '#required'      => TRUE,
    ];

    $form['site_key'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Site key'),
      '#default_value' => $cmrf_profile->site_key,
      '#maxlength'     => 255,
      '#description'   => $this->t('The site key of your civicrm installation.'),
      '#required'      => TRUE,
    ];

    $form['api_key'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('API key'),
      '#default_value' => $cmrf_profile->api_key,
      '#maxlength'     => 255,
      '#description'   => $this->t('The api key of your civicrm installation.'),
      '#required'      => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $cmrf_profile = $this->entity;
    $status       = $cmrf_profile->save();
    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label CMRF.', [
          '%label' => $cmrf_profile->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label CMRF.', [
          '%label' => $cmrf_profile->label(),
        ]));
    }
    $form_state->setRedirectUrl($cmrf_profile->toUrl('collection'));
  }

}
