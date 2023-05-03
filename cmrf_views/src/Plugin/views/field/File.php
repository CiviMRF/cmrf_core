<?php namespace Drupal\cmrf_views\Plugin\views\field;

use Drupal\cmrf_core\Core;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class File
 *
 * @package Drupal\cmrf_views\Plugin\views\field
 * @ingroup cmrf_views_field_handlers
 * @ViewsField("cmrf_views_file")
 */
class File extends FieldPluginBase {

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
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options                       = parent::defineOptions();
    $options['behaviour']          = ['default' => 'image'];
    $options['image_style']        = ['default' => 'original'];
    $options['image_path']         = ['default' => 'civicrm'];
    $options['image_class']        = ['default' => NULL];
    $options['image_fallback_url'] = ['default' => NULL];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['behaviour'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Show'),
      '#options'       => [
        'download' => $this->t('Link to download'),
        'image'    => $this->t('Image'),
      ],
      '#default_value' => isset($this->options['behaviour']) ? $this->options['behaviour'] : 'image',
    ];

    // Get image styles.
    $image_styles = ImageStyle::loadMultiple();
    if (!empty($image_styles)) {
      $styles = [];
      foreach ($image_styles as $style) {
        $styles[$style->getName()] = $style->label();
      }
    }

    $form['image_style'] = [
      '#type'          => 'select',
      '#title'         => $this->t('Image style'),
      '#options'       => ['original' => $this->t('Original')] + $styles,
      '#description'   => $this->t('Select the image style to show the image'),
      '#states'        => [
        'visible' => [
          ':input[name="options[behaviour]"]' => ['value' => 'image'],
        ],
      ],
      '#default_value' => isset($this->options['image_style']) ? $this->options['image_style'] : 'original',
    ];

    $form['image_path'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Image path'),
      '#description'   => $this->t('Define here the path you want to save the files (inside public://)'),
      '#states'        => [
        'visible' => [
          ':input[name="options[behaviour]"]' => ['value' => 'image'],
        ],
      ],
      '#default_value' => isset($this->options['image_path']) ? $this->options['image_path'] : 'civicrm',
    ];

    $form['image_class'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Image class'),
      '#description'   => $this->t('CSS class to be applied to the image'),
      '#states'        => [
        'visible' => [
          ':input[name="options[behaviour]"]' => ['value' => 'image'],
        ],
      ],
      '#default_value' => isset($this->options['image_class']) ? $this->options['image_class'] : NULL,
    ];

    $form['image_fallback_url'] = [
      '#type'          => 'textfield',
      '#title'         => $this->t('Image fallback URL'),
      '#description'   => $this->t("Fallback image to show when there's no image from the CRM"),
      '#states'        => [
        'visible' => [
          ':input[name="options[behaviour]"]' => ['value' => 'image'],
        ],
      ],
      '#default_value' => isset($this->options['image_fallback_url']) ? $this->options['image_fallback_url'] : NULL,
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    // If we have no file and behaviour is an image.
    if ((empty($values->{$this->field_alias})) &&
        (!empty($this->options['behaviour'])) &&
        ($this->options['behaviour'] == 'image') &&
        (!empty($this->options['image_fallback_url']))) {
      return $this->renderFallbackImage();
    }

    // If we have a file.
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
        }
      }
      elseif (is_string($value)) {
        // The $value contains the URL.
        $attachment = [
          'url' => $value,
          'id' => md5($value),
          'name' => basename($value),
        ];
      }

      // If we get an error, render fallback image.
      if (!empty($attachment['is_error'])) {
        return $this->renderFallbackImage();
      }
      // Check if we have necessary information to generate a link or save/show the file
      if ((!empty($attachment['url'])) && (!empty($attachment['name']))) {
        if (!empty($this->options['behaviour'])) {
          // Save and show image.
          if ($this->options['behaviour'] == 'image') {
            return $this->getImage($attachment);
          }
          // Show download link.
          return $this->getDownloadLink($attachment);
        }
      } elseif (is_string($value)) {
        if (!empty($this->options['behaviour'])) {
          if ($this->options['behaviour'] == 'image') {
            return $this->renderImage($value);
          }
        }
        return $value;
      }
    }

    return NULL;
  }

  private function renderImage($imageUri) {
    $image_style = empty($this->options['image_style']) ? 'original' : $this->options['image_style'];
    if ($image_style == 'original') {
      $image_render = ['#theme' => 'image', '#uri' => $imageUri];
      if (!empty($this->options['image_class'])) {
        $image_render['#attributes'] = ['class' => $this->options['image_class']];
      }
      return $image_render;
    }
    // Get the style.
    $style = \Drupal::entityTypeManager()->getStorage('image_style')->load($image_style);
    // Get the styled image derivative.
    $style_uri_path = $style->buildUri($imageUri);
    // If the derivative doesn't exist yet, create it.
    if (!file_exists($style_uri_path)) {
      $style->createDerivative($imageUri, $style_uri_path);
    }
    // Render the image style.
    $image_render = ['#theme' => 'image', '#uri' => $style_uri_path];
    if (!empty($this->options['image_class'])) {
      $image_render['#attributes'] = ['class' => $this->options['image_class']];
    }
    return $image_render;
  }

  private function getImage($attachment = NULL) {
    if ((!empty($attachment['url'])) && (!empty($attachment['id'])) && (!empty($attachment['name']))) {
      // Set the destination path.
      $image_path = empty($this->options['image_path']) ? NULL : $this->options['image_path'];
      $uri_path   = 'public://' . $image_path;
      $real_path  = \Drupal::service('file_system')->realpath($uri_path);
      // Create destination if it doesn't exist.
      if (!file_exists($real_path)) {
        mkdir($real_path, 0755, TRUE);
      }
      // Download the file and save in the destination.
      if (file_exists($real_path)) {
        // Get file extension.
        $file = pathinfo($attachment['name']);
        if (!empty($file['extension'])) {
          $file_uri_path  = $uri_path . '/' . $attachment['id'] . '.' . $file['extension'];
          $file_real_path = $real_path . '/' . $attachment['id'] . '.' . $file['extension'];
          if (!file_exists($file_real_path)) {
            system_retrieve_file($attachment['url'], $file_uri_path, FALSE, FileSystemInterface::EXISTS_REPLACE);
          }
          if (file_exists($file_real_path)) {
            return $this->renderImage($file_real_path);
          }
          else {
            return $this->renderFallbackImage();
          }
        }
      }
    }
    return NULL;
  }

  private function getDownloadLink($attachment = NULL) {
    if ((!empty($attachment['url'])) && (!empty($attachment['name']))) {
      // Get drupal query string.
      $query_string = \Drupal::request()->query->all();
      // Generate salt and hash.
      $salt = $this->view->getTitle() . Settings::getHashSalt();
      $hash = Crypt::hmacBase64($attachment['url'], $salt);
      // Download the file if we have a valid hash in the query string.
      if ((isset($query_string['civi_file_hash'])) && ($query_string['civi_file_hash'] == $hash)) {
        header('Content-Description: File Transfer');
        //header('Content-Type: application/octet-stream');
        if (!empty($attachment['mime_type'])) {
          header('Content-Type: ' . $attachment['mime_type']);
        }
        header('Content-Disposition: attachment; filename="' . $attachment['name'] . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        echo file_get_contents($attachment['url']);
        exit();
      }
      // Link label.
      $link_label = empty($this->label()) ? t('Download file') : $this->label();
      // Return file link.
      $link = Link::fromTextAndUrl(
        $link_label,
        Url::fromRoute('<current>', [], ['query' => ['civi_file_hash' => $hash], 'absolute' => TRUE])
      );
      return $link->toRenderable();
    }
    return NULL;
  }

  private function renderFallbackImage() {
    // Render fallback image.
    $image_render = ['#theme' => 'image', '#uri' => $this->options['image_fallback_url']];
    if (!empty($this->options['image_class'])) {
      $image_render['#attributes'] = ['class' => $this->options['image_class']];
    }
    return $image_render;
  }

}
