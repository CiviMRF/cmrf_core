<?php namespace Drupal\cmrf_views\Form;

use Drupal\cmrf_core\Core;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CMRFDatasetRelationshipForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Get datasets.
    $datasets = \Drupal::entityTypeManager()
      ->getStorage('cmrf_dataset')
      ->loadMultiple();
    array_walk($datasets, function (&$dataset) {
      /* @var \Drupal\cmrf_views\Entity\CMRFDataset $dataset */
      $dataset = $dataset->label();
    });

    //$form_state['dataset'] = $dataset;
    $entity = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => 'Label',
      '#default_value' => empty($entity->label()) ? NULL : $entity->label(),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#description' => t('The name is used in URLs for this relationship. Use only lowercase alphanumeric characters, underscores (_), and hyphens (-).'),
      '#default_value' => empty($entity->id()) ? NULL : $entity->id(),
      '#required' => TRUE,
      '#machine_name' => [
        'exists' => [$this, 'exists'],
        'replace_pattern' => '[^a-z0-9_\-.]+',
      ],
    ];

    $form['referenced_dataset'] = [
      '#type' => 'select',
      '#title' => t('Referenced CiviMRF Dataset'),
      '#description' => t('The CiviMRF dataset to create a relationship to.'),
      '#options' => $datasets,
      '#default_value' => empty($entity->referenced_dataset) ? NULL : $entity->referenced_dataset,
      '#required' => TRUE,
    ];

    $form['referenced_key'] = [
      '#type' => 'textfield',
      '#title' => t('Referenced dataset key'),
      '#description' => t('The key field referenced in the selected relationship dataset.'),
      '#default_value' => empty($entity->referenced_key) ? 'id' : $entity->referenced_key,
      '#required' => TRUE,
    ];

    $form['referencing_dataset'] = [
      '#type' => 'value',
      '#value' => $entity->referencing_dataset,
    ];

    $form['referencing_key'] = [
      '#type' => 'textfield',
      '#title' => t('Foreign key'),
      '#description' => t('The field referencing the selected relationship dataset.'),
      '#default_value' => empty($entity->referencing_key) ? NULL : $entity->referencing_key,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $saved = parent::save($form, $form_state);
    $context = [
      '@type' => $this->entity->bundle(),
      '%label' => $this->entity->label(),
      'link' => $this->entity->toLink($this->t('View'))->toString(),
    ];
    $logger = $this->logger('CMRF Views');
    $t_args = [
      '@type' => $this->entity->label(),
      '%label' => $this->entity->toLink($this->entity->label())
        ->toString(),
    ];

    if ($saved === SAVED_NEW) {
      $logger->notice('@type: added %label.', $context);
      $this->messenger()->addStatus($this->t('@type %label has been created.',
        $t_args));
    }
    else {
      $logger->notice('@type: updated %label.', $context);
      $this->messenger()->addStatus($this->t('@type %label has been updated.',
        $t_args));
    }

    // Redirect the user to the relationship overview if the user has the
    // appropriate permission. If not, redirect to the canonical URL of the
    // relationship item.
    if ($this->currentUser()->hasPermission('administer site configuration')) {
      $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    }
    else {
      $form_state->setRedirectUrl($this->entity->toUrl());
    }

    return $saved;
  }

}
