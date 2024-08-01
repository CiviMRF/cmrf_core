<?php

declare(strict_types=1);

namespace Drupal\cmrf_core\Controller;

use Drupal\cmrf_core\Core;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for CiviMRF Core routes.
 */
final class CMRFConnectorTester extends ControllerBase {

  /**
   * The controller constructor.
   */
  public function __construct(
    private readonly Core $core,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('cmrf_core.core'),
    );
  }

  /**
   *
   * Builds the response.
   */
  public function test(string $cmrf_connector): array {
    $call = $this->core->createCall($cmrf_connector, 'Entity', 'get', []);
    $this->core->executeCall($call);
    $reply = $call->getReply();

    $build['description'] = [
      '#type' => 'item',
      '#markup' => $this->t("Executes the api call <b>civicrm_api('Entity','get')</b> using this connector."
        . "Use it to check if this connector is configured property"),
    ];

    $build['reply'] = [
      '#type' => 'item',
      '#markup' => $this->t("<pre>".print_r($reply,true)."</pre>"),
    ];

    return $build;
  }

}
