<?php namespace Drupal\cmrf_views\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;

class CMRFViewsController {

  public function invalidateViewsCache() {

    // Clear drupal cache.
    drupal_flush_all_caches();
    \Drupal::messenger()->addStatus(t('The views cache has been cleared.'));

    // Redirect to dataset list.
    return new RedirectResponse(\Drupal::url('entity.cmrf_dataset.collection'));
  }

}
