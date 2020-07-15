<?php

namespace Drupal\cmrf_views\Plugin\views\relationship;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\relationship\RelationshipPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Relationship handler to return the CiviMRF Dataset configured for the view's
 * base Dataset.
 *
 * @ingroup views_relationship_handlers
 *
 * @ViewsRelationship("cmrf_dataset_relationship")
 */

class DatasetRelationship extends RelationshipPluginBase {

}
