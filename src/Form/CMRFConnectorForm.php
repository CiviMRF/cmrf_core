<?php namespace Drupal\cmrf_core\Form;

use Drupal\cmrf_core\Entity\CMRFConnector;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;

/**
 * Class CMRFConnectorForm.
 */
class CMRFConnectorForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var CMRFConnector $cmrf_connector */
    $cmrf_connector = $this->entity;

    $form['label'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Label'),
      '#maxlength'     => 255,
      '#default_value' => $cmrf_connector->label(),
      '#description'   => $this->t("Label for the CiviMRF Connector."),
      '#required'      => TRUE,
    ];

    $form['id'] = [
      '#type'          => 'machine_name',
      '#default_value' => $cmrf_connector->id(),
      '#machine_name'  => [
        'exists' => '\Drupal\cmrf_core\Entity\CMRFConnector::load',
      ],
      '#disabled'      => !$cmrf_connector->isNew(),
    ];

    $form['type'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Connecting module'),
      '#maxlength'     => 255,
      '#default_value' => $cmrf_connector->type,
      '#description'   => $this->t('Module initiating and using the connection.'),
      '#required'      => TRUE,
    ];


    if (\Drupal::hasService('civicrm')) {
      $form['connectiontype'] = [
        '#type' => 'select',
        '#title' => $this->t('Connection Type'),
        '#options' => [
          'local' => $this->t('Local'),
          'remote' => $this->t('Remote'),
        ],
        '#description' => $this->t('<em>Remote</em> connect by REST or <em>local</em> by api call'),
        '#default_value' => $cmrf_connector->connectiontype ?? 'remote',
        '#required' => TRUE,
      ];

      $form['conneciontypemarkup'] = [
        '#type' => 'markup',
        '#markup' =>
          $this->t('<em>Remote</em> connects to CiviCRM using the REST Api. So the calls are '.
                   'always that the calls are always made by the same user'.
                   '<br/>'.
                   '<em>Local</em> uses the logged-in user calling the CiviCRM api directly. '.
                   'So be aware that in the local situation all the users have the correct permissions')
      ];
    }
    else {
      $form['connectiontype'] = [
        '#type' => 'hidden',
        '#default_value' => $cmrf_connector->connectiontype ?? 'remote',
      ];
    }

    $form['profile'] = [
      '#type' => 'select',
      '#options' => $cmrf_connector->getAvailableProfiles(),
      '#title' => $this->t('Profile'),
      '#default_value' => $cmrf_connector->profile,
      '#description' => $this->t('Name of the referenced CiviMRF Profile.'),
    ];

    if (\Drupal::hasService('civicrm')) {
      $form['profile'] += [
        '#required' => FALSE,
        '#states' => [
          'visible' => [
            ':input[name="connectiontype"]' => ['value' => 'remote'],
          ],
          'required' => [
            ':input[name="connectiontype"]' => ['value' => 'remote'],
          ],
        ],
      ];
    }
    else {
      $form['profile'] += ['#required' => TRUE];
    }


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $cmrf_connector = $this->entity;
    $status         = $cmrf_connector->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label CiviMRF Connector.', [
          '%label' => $cmrf_connector->label(),
        ]));
        break;
      default:
        $this->messenger()->addMessage($this->t('Saved the %label CiviMRF Connector.', [
          '%label' => $cmrf_connector->label(),
        ]));
    }
    $form_state->setRedirectUrl($cmrf_connector->toUrl('collection'));
  }

}
