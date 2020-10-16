<?php namespace Drupal\cmrf_views\Form;

use Drupal\cmrf_core\Core;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CMRFDatasetForm extends EntityForm {

  /** @var $core */
  public $core;

  public function __construct(Core $core) {
    $this->core = $core;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    // Load required services.
      $container->get('cmrf_core.core')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Get connectors from the core.
    $connectors = $this->core->getConnectors();

    //$form_state['dataset'] = $dataset;
    $entity = $this->entity;

    $form['label'] = [
      '#type'          => 'textfield',
      '#title'         => 'Label',
      '#default_value' => empty($entity->label()) ? NULL : $entity->label(),
      '#required'      => TRUE,
    ];

    $form['id'] = [
      '#type'          => 'machine_name',
      '#description'   => t('The name is used in URLs for this dataset. Use only lowercase alphanumeric characters, underscores (_), and hyphens (-).'),
      '#default_value' => empty($entity->id()) ? NULL : $entity->id(),
      '#required'      => TRUE,
      '#machine_name'  => [
        'exists'          => [$this, 'exists'],
        'replace_pattern' => '[^a-z0-9_\-.]+',
      ],
    ];

    $form['connector'] = [
      '#type'          => 'select',
      '#title'         => t('CiviMRF Connector'),
      '#options'       => $connectors,
      '#default_value' => empty($entity->connector) ? NULL : $entity->connector,
      '#required'      => TRUE,
    ];

    $form['entity'] = [
      '#type'          => 'textfield',
      '#title'         => t('Entity'),
      '#default_value' => empty($entity->entity) ? NULL : $entity->entity,
      '#required'      => TRUE,
    ];

    $form['action'] = [
      '#type'          => 'textfield',
      '#title'         => t('Action'),
      '#default_value' => empty($entity->action) ? 'get' : $entity->action,
      '#required'      => TRUE,
    ];

    $form['getcount'] = [
      '#type'          => 'textfield',
      '#title'         => t('Getcount api action'),
      '#default_value' => empty($entity->getcount) ? 'getcount' : $entity->getcount,
      '#required'      => TRUE,
    ];

    $form['params'] = [
      '#type'          => 'textarea',
      '#title'         => t('API Parameters'),
      '#description'   => t('Enter the api parameters in JSON format. E.g. {"contact_sub_type": "Student", "is_deleted": "0", "is_deceased": "0"}'),
      '#default_value' => empty($entity->params) ? NULL : $entity->params,
      '#required'      => FALSE,
    ];

    // Provide a token browser.
    $form['token_tree'] = [
      '#theme' => 'token_tree_link',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $saved   = parent::save($form, $form_state);
    $context = ['@type'  => $this->entity->bundle(),
                '%label' => $this->entity->label(),
                'link'   => $this->entity->toLink($this->t('View'))->toString(),
    ];
    $logger  = $this->logger('CMRF Views');
    $t_args  = ['@type'  => $this->entity->label(),
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

    // Redirect the user to the media overview if the user has the 'access media
    // overview' permission. If not, redirect to the canonical URL of the media
    // item.
    if ($this->currentUser()->hasPermission('administer site configuration')) {
      $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    }
    else {
      $form_state->setRedirectUrl($this->entity->toUrl());
    }

    return $saved;
  }

}
