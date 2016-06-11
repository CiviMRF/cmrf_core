<?php

/**
 * Drupal-based implementation of a CMRF Core
 *
 * @author Björn Endres, SYSTOPIA (endres@systopia.de)
 */

namespace CMRF\Drupal;

include_once('CMRF/Local/Core.php');
include_once('CMRF/Connection/Curl.php');

use CMRF\Local\Core      as LocalCore;
use CMRF\Connection\Curl as CurlConnection;

class Core extends LocalCore
{
  public function getConnectionProfile($profile_name) {
    return array(
      'url'      => 'http://<mysite>/sites/all/modules/civicrm/extern/rest.php',
      'api_key'  => '<mykey>',
      'site_key' => '<mykey>',
    );
  }

  public function _createConnection($connection_id, $connector_id) {
    return new CurlConnection($connection_id, $this, $connector_id);
  }

}
