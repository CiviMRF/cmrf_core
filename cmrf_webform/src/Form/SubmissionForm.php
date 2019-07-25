<?php

namespace Drupal\cmrf_webform\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\webform\Entity\Webform;

class SubmissionForm extends EntityForm {

  protected function getWebformEntities() {
    $webforms = Webform::loadMultiple();
    $ret = [];
    foreach ($webforms as $entity) {
      $ret[$entity->id()] = $entity->label();
    }

    return $ret;
  }

  /**
   * Constructs a Submission object.
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
      'delete_submission' => false,
      'submit_in_background' => false,
      'entity' => 'Submission',
      'action' => 'post',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $entity = $this->entity;

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

    $form['webform'] = [
      '#type' => 'select',
      '#title' => $this->t('Webform entity'),
      '#description' => $this->t('The webform entity item to use'),
      '#options' => $this->getWebformEntities(),
      '#required' => true,
      '#default_value' => $entity->getWebform(),
    ];

    $form['delete_submission'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Delete the submission after processing'),
      '#description' => $this->t('Deletes the submission form the webform results after the data has been submitted to CiviCRM.'),
      '#default_value' => $entity->getDeleteSubmission(),
    ];

    $form['submit_in_background'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Handle the submission in the background'),
      '#description' => $this->t('Submit this webform in the background. This means that the user does not have to wait till the submission is processed. You have to enable the cron to get this working.'),
      '#default_value' => $entity->getSubmitInBackground(),
    ];

    $form['entity'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Entity'),
      '#maxlength' => 255,
      '#default_value' => $entity->getEntity(),
      '#description' => $this->t('CiviMRF works with submitting data to the CiviCRM API. This field specifies which entity to use.'),
      '#required' => TRUE,
    ];

    $form['action'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Action'),
      '#maxlength' => 255,
      '#default_value' => $entity->getAction(),
      '#description' => $this->t('CiviMRF works with submitting data to the CiviCRM API. This field specifies which action to use.'),
      '#required' => TRUE,
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
  public function save(array $form, FormStateInterface $form_state) {
    $handler = $this->entity;
    $status = $handler->save();

    if ($status) {
      $this->messenger()->addMessage($this->t('Saved the %title submission handler.', [
        '%label' => $handler->label(),
      ]));
    }

    $form_state->setRedirect('entity.cmrf_webform_submission.collection');
  }

  /**
   * Helper function to check whether an Example configuration entity exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('cmrf_webform_submission')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
