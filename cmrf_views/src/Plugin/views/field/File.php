<?php namespace Drupal\cmrf_views\Plugin\views\field;

use Drupal\cmrf_core\Core;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\Exception\InvalidStreamWrapperException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use GuzzleHttp\Exception\TransferException;
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
          $file_api_entity = $views_data[$this->realField]['cmrf_original_definition']['file_api.entity'] ?? 'Attachment';
          $file_api_action = $views_data[$this->realField]['cmrf_original_definition']['file_api.action'] ?? 'getsingle';
          $file_api_version = $views_data[$this->realField]['cmrf_original_definition']['file_api.version'] ?? '3';
          $file_api_id_param = $views_data[$this->realField]['cmrf_original_definition']['file_api.id_param'] ?? 'id';
          $file_api_url_param = $views_data[$this->realField]['cmrf_original_definition']['file_api.url_param'] ?? 'url';
          $file_api_name_param = $views_data[$this->realField]['cmrf_original_definition']['file_api.name_param'] ?? 'name';
          $file_api_attached_entity_param = $views_data[$this->realField]['cmrf_original_definition']['file_api.attached_entity_param'] ?? NULL;
          $file_api_attached_entity = $views_data[$this->realField]['cmrf_original_definition']['file_api.attached_entity']
            ?? $views_data[$this->realField]['cmrf_original_definition']['entity']
            ?? NULL;

          if ('4' === $file_api_version) {
            $file_api_params = [
              'select' => [
                $file_api_name_param,
                $file_api_url_param,
              ],
              'where' => [
                [$file_api_id_param, '=', $value],
              ],
            ];
            // Join EntityFile for URL generation for File entities.
            if (isset($file_api_attached_entity)) {
              if ('File' === $file_api_entity) {
                $file_api_params['join'] = [
                  [
                    sprintf('%s AS %s', $file_api_attached_entity, lcfirst($file_api_attached_entity)),
                    'LEFT',
                    'EntityFile',
                  ],
                ];
              }
              // Pass the attached entity with the corresponding API parameter.
              elseif (isset($file_api_attached_entity_param)) {
                $file_api_params['where'][] = [$file_api_attached_entity_param, '=', $file_api_attached_entity];
              }
            }
          }
          elseif ('3' === $file_api_version) {
            $file_api_params = [
              $file_api_id_param => $value,
              'return' => [
                $file_api_id_param,
                $file_api_url_param,
                $file_api_name_param,
              ]
            ];
            if (isset($file_api_attached_entity) && isset($file_api_attached_entity_param)) {
              $file_api_params[$file_api_attached_entity_param] = $file_api_attached_entity;
            }
          }

          $file = $this->core->createCall(
            $views_data['table']['base']['connector'],
            $file_api_entity,
            $file_api_action,
            $file_api_params,
            ['cache' => '30 minutes'],
            NULL,
            $file_api_version
          );
          $this->core->executeCall($file);

          if ($file->getStatus() === $file::STATUS_DONE) {
            // Get reply.
            $result = $file->getReply();
            if (empty($result['is_error'])) {
              $file = null;
              if (isset($result['count']) && isset($result['values']) && is_array($result['values']) && count($result['values']) > 0) {
                $file = reset($result['values']);
              } elseif (!isset($result['count']) && !isset($result['values'])) {
                $file = $result;
              }
              if (is_array($file)) {
                $attachment = [
                  'url' => $result[$file_api_url_param],
                  'id' => $result[$file_api_id_param],
                  'name' => $result[$file_api_name_param],
                ];
              }
            }
          }
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
      if (!empty($result['is_error'])) {
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
      $image = $this->retrieveImage($attachment);
      if (file_exists($image['real_path'])) {
        return $this->renderImage($image['uri']);
      }
      else {
        return $this->renderFallbackImage();
      }
    }
    return NULL;
  }

  private function getDownloadLink($attachment = NULL) {
    if ((!empty($attachment['url'])) && (!empty($attachment['name']))) {
      $image = $this->retrieveImage($attachment);
      if (file_exists($image['real_path'])) {
        $url = \Drupal::service('file_url_generator')->generate($image['uri']);
      }

      // Link label.
      $link_label = empty($this->label()) ? t('Download file') : $this->label();
      // Return file link.
      $link = Link::fromTextAndUrl($link_label, $url);
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

  private function retrieveImage($attachment = NULL) {
    $image_path = empty($this->options['image_path']) ? NULL : $this->options['image_path'];
    $uri_path   = 'public://' . $image_path;
    $real_path  = \Drupal::service('file_system')->realpath($uri_path);
    // Create destination if it doesn't exist.
    if (!file_exists($real_path)) {
      mkdir($real_path, 0755, TRUE);
    }

    // Get file extension.
    $ext = '';
    $file = pathinfo($attachment['url']);
    if (!empty($file['extension'])) {
      $ext = '.' . $file['extension'];
    }
    if (empty($ext) && isset($attachment['name'])) {
      $file = pathinfo($attachment['name']);
      if (!empty($file['extension'])) {
        $ext = '.' . $file['extension'];
      }
    }
    $file_uri_path  = $uri_path . '/' . $attachment['id'] . $ext;
    $file_real_path = $real_path . '/' . $attachment['id'] . $ext;
    if (!file_exists($file_real_path)) {
      try {
        $data = (string) \Drupal::httpClient()->get($url)->getBody();
        $file_uri_path = \Drupal::service('file_system')->saveData($data, $file_uri_path, FileSystemInterface::EXISTS_REPLACE);
      }
      catch (TransferException $exception) {
        \Drupal::messenger()->addError(t('Failed to fetch file due to error "%error"', ['%error' => $exception->getMessage()]));
      }
      catch (FileException | InvalidStreamWrapperException $e) {
        \Drupal::messenger()->addError(t('Failed to save file due to error "%error"', ['%error' => $e->getMessage()]));
      }
    }
    return [
      'uri' => $file_uri_path,
      'real_path' => $file_real_path
    ];
  }

}
