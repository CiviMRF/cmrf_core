<?php namespace Drupal\cmrf_views\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Class File
 *
 * @package Drupal\cmrf_views\Plugin\views\field
 * @ViewsField("cmrf_views_file")
 */
class File extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    // TODO: Need a working example from a file to test this field plugin.
    $value        = $this->get_value($values);
    $query_params = drupal_get_query_parameters();
    $salt         = $this->view->name . drupal_get_hash_salt();
    if ($value && is_object($value) && isset($value->url)) {
      $hash = drupal_hmac_base64($value->url, $salt);
      if (isset($query_params['cmrf_views_handler_field_file_hash']) && $query_params['cmrf_views_handler_field_file_hash'] == $hash) {
        header('Content-Description: File Transfer');
        //header('Content-Type: application/octet-stream');
        header('Content-Type: ' . $value->mime_type);
        header('Content-Disposition: attachment; filename="' . $value->name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        echo file_get_contents($value->url);
        exit();
      }

      $link_title = $value->name;
      if (empty($link_title)) {
        $link_title = $this->label();
      }
      $path     = request_path();
      $rendered = l($link_title, $path, [
        'query'    => ['cmrf_views_handler_field_file_hash' => $hash],
        'absolute' => TRUE,
      ]);
      return $rendered;
    }
    elseif ($value && is_numeric($value)) {
      $attachmentOptions['cache'] = $this->view->query->options['cache'];
      $attachmentParams['id']     = $value;
      $table_data                 = views_fetch_data($this->view->base_table);
      $profile                    = $table_data['table']['base']['profile'];
      $result                     = cmrf_views_sendCall('Attachment', 'getsingle', $attachmentParams, $attachmentOptions, $profile);
      $attachment                 = $result->getReply();
      $hash                       = drupal_hmac_base64($attachment['url'], $salt);
      if (isset($query_params['cmrf_views_handler_field_file_hash']) && $query_params['cmrf_views_handler_field_file_hash'] == $hash) {
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

      $link_title = $attachment['name'];
      if (empty($link_title)) {
        $link_title = $this->label();
      }
      $path     = request_path();
      $rendered = l($link_title, $path, [
        'query'    => ['cmrf_views_handler_field_file_hash' => $hash],
        'absolute' => TRUE,
      ]);
      return $rendered;
    }
    return $this->sanitize_value($value);
  }

}