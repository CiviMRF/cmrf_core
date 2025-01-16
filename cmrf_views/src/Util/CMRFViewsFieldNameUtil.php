<?php
declare(strict_types = 1);

namespace Drupal\cmrf_views\Util;

final class CMRFViewsFieldNameUtil {

  public static function normalize(string $fieldName): string {
    // Field names have to be valid (Twig) variable names.
    // https://www.drupal.org/project/drupal/issues/3473064
    return preg_replace('/[^a-zA-Z0-9_\x7f-\xff]/', '__', $fieldName);
  }

}
