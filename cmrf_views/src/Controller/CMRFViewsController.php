<?php namespace Drupal\cmrf_views\Controller;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

class CMRFViewsController {

  public function invalidateViewsCache() {

    // Clear drupal cache.
    drupal_flush_all_caches();
    \Drupal::messenger()->addStatus(t('The views cache has been cleared.'));

    // Redirect to dataset list.
    return new RedirectResponse(Url::fromRoute('entity.cmrf_dataset.collection')
      ->toString());
  }

}
