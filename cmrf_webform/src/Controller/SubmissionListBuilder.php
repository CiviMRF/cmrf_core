<?php

namespace Drupal\cmrf_webform\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

class SubmissionListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Submission');
    $header['id'] = $this->t('Machine name');
    $header['entity'] = $this->t('Entity name');
    $header['action'] = $this->t('Action name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();

    $row['entity'] = $entity->getEntity();
    $row['action'] = $entity->getAction();

    return $row + parent::buildRow($entity);
  }

}
