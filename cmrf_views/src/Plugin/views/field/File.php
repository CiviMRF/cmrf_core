<?php namespace Drupal\cmrf_views\Plugin\views\field;

use Drupal\cmrf_core\Core;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\Link;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class File
 *
 * @package Drupal\cmrf_views\Plugin\views\field
 * @ViewsField("cmrf_views_file")
 */
class File extends FieldPluginBase {

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array                                                     $configuration
   * @param string                                                    $plugin_id
   * @param mixed                                                     $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cmrf_core.core')
    );
  }

  /**
   * @param array                                      $configuration
   * @param string                                     $plugin_id
   * @param mixed                                      $plugin_definition
   * @param \Drupal\Core\Session\AccountProxyInterface $account
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Core $core) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->core = $core;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if (!empty($values->{$this->field_alias})) {
      $value = $values->{$this->field_alias};
      if (is_numeric($value)) {
        // Get the connector from the base table.
        $views_data = $this->viewsData->get($this->table);
        if (!empty($views_data['table']['base']['connector'])) {
          // Calls the CRM API to get the attachment URL.
          $file = $this->core->createCall(
            $views_data['table']['base']['connector'],
            'Attachment',
            'getsingle',
            ['id' => $value],
            ['cache' => '30 minutes']
          );
          $this->core->executeCall($file);
          // Get reply.
          $attachment = $file->getReply();
          // Download and show the picture.
          if ((!empty($attachment['url'])) && (!empty($attachment['mime_type'])) && (!empty($attachment['name']))) {

            // Get drupal query string.
            $query_string = \Drupal::request()->query->all();
            // Generate salt and hash.
            $salt = $this->view->getTitle() . Settings::getHashSalt();
            $hash = Crypt::hmacBase64($attachment['url'], $salt);
            // Download the file if we have a valid hash in the query string.
            if ((isset($query_string['civi_file_hash'])) && ($query_string['civi_file_hash'] == $hash)) {
              header('Content-Description: File Transfer');
              //header('Content-Type: application/octet-stream');
              header('Content-Type: ' . $attachment['mime_type']);
              header('Content-Disposition: attachment; filename="' . $attachment['name'] . '"');
              header('Expires: 0');
              header('Cache-Control: must-revalidate');
              header('Pragma: public');
              echo file_get_contents($attachment['url']);
              exit();
            }

            // Return file link.
            $link = Link::fromTextAndUrl($this->label(), Url::fromRoute('<current>', [], ['query' => ['civi_file_hash' => $hash], 'absolute' => TRUE]));
            return $link->toRenderable();
          }
        }
      }
    }

  }

}
