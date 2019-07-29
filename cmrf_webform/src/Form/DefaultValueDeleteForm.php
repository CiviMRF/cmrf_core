<?php

namespace Drupal\cmrf_webform\Form;

use Drupal\Core\Url;

class DefaultValueDeleteForm extends CMRFWebformDeleteFormBase {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.cmrf_webform_default_value.collection');
  }

}
