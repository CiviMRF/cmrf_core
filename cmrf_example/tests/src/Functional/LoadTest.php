<?php

namespace Drupal\Tests\cmrf_example\Functional;

use Drupal\cmrf_core\Entity\CMRFProfile;
use Drupal\Core\Url;
use Drupal\Tests\BrowserTestBase;

/**
 * Simple test to ensure that main page loads with module enabled.
 *
 * @group cmrf_example
 */
class LoadTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['cmrf_example'];

  /**
   * A user with permission to administer site configuration.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $user;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $entity=CMRFProfile::load('default');
    //this test will fail until you set up those properties correctly.
    $entity->url='PATH_TO_CIVI_INSTALLATION';
    $entity->api_key='???';
    $entity->site_key='???';
    $entity->save();
  }

  public function testService() {

    /** @var \Drupal\cmrf_example\CiviClient $client */
    $client = \Drupal::service('cmrf_example.client');
    $this->assertTrue($client != NULL);
    $this->assertTrue($client->core != NULL);
    $this->assertTrue($client->core->getDefaultProfile()['url'] != 'PATH_TO_CIVI_INSTALLATION','You have to provide Civi Credentials to this test.');
    $data=$client->getContactIds();
    $this->assertNotTrue($data['is_error']);
    $this->assertTrue(isset($data['values']));
  }

}
