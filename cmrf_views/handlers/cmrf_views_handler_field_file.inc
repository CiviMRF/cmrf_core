<?php

/**
 * @file
 * Definition of cmrf_views_handler_field
 */
 
 class cmrf_views_handler_field_file extends cmrf_views_handler_field {
   
 /**
   * Get the value that's supposed to be rendered.
   *
   * This api exists so that other modules can easy set the values of the field
   * without having the need to change the render method as well.
   *
   * @param $values
   *   An object containing all retrieved values.
   * @param $field
   *   Optional name of the field where the value is stored.
   */
  function get_value($values, $field = NULL) {
    $alias = isset($field) ? $this->aliases[$field] : $this->field_alias;
    if (isset($values->{$alias})) {
      $value = $values->{$alias};
      if (isset($this->definition['options']) && is_array($this->definition['options']) && isset($this->definition['options'][$value])) {
        $value = $this->definition['options'][$value];
      }
      return $value;
    }
  }

   /**
    * Render the field.
    *
    * @param array $values
    *   The values retrieved from the database.
    */
   public function render($values) {
     $value = $this->get_value($values);
     $query_params = drupal_get_query_parameters();
     $salt = $this->view->name.drupal_get_hash_salt();
     if ($value && is_object($value) && isset($value->url)) {
       $hash = drupal_hmac_base64($value->url, $salt);
       if (isset($query_params['cmrf_views_handler_field_file_hash']) && $query_params['cmrf_views_handler_field_file_hash'] == $hash) {
         header('Content-Description: File Transfer');
         //header('Content-Type: application/octet-stream');
         header('Content-Type: '.$value->mime_type);
         header('Content-Disposition: attachment; filename="'.$value->name.'"');
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
       $path = request_path();
       $rendered = l($link_title, $path, array(
         'query' => array('cmrf_views_handler_field_file_hash' => $hash),
         'absolute' => true,
       ));
       return $rendered;
     } elseif ($value && is_numeric($value)) {
       $attachmentOptions['cache'] = $this->view->query->options['cache'];
       $attachmentParams['id'] = $value;
       $table_data = views_fetch_data($this->view->base_table);
       $profile = $table_data['table']['base']['profile'];
       $result = cmrf_views_sendCall('Attachment', 'getsingle', $attachmentParams, $attachmentOptions, $profile);
       $attachment = $result->getReply();
       $hash = drupal_hmac_base64($attachment['url'], $salt);
       if (isset($query_params['cmrf_views_handler_field_file_hash']) && $query_params['cmrf_views_handler_field_file_hash'] == $hash) {
         header('Content-Description: File Transfer');
         //header('Content-Type: application/octet-stream');
         header('Content-Type: '.$attachment['mime_type']);
         header('Content-Disposition: attachment; filename="'.$attachment['name'].'"');
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
       $path = request_path();
       $rendered = l($link_title, $path, array(
         'query' => array('cmrf_views_handler_field_file_hash' => $hash),
         'absolute' => true,
       ));
       return $rendered;
     }
     return $this->sanitize_value($value);
   }
   
 }
