<?php
defined('ABSPATH') OR exit;
if(!class_exists('Model_Application_Picturefill_WP')){
  class Model_Application_Picturefill_WP{

    private $upload_base_dir;
    private $upload_base_url;
    private $registered_image_sizes;
    private $choosable_image_sizes;
    private $allowed_image_extensions;

    public $registered_sizes = array();

    public $registered_srcsets = array();

    public $image_attachments = array();

    // Static methods to generate the input needed to instatiante the object
    static function syntax_present(DOMDocument $DOMDocument, $html){
      $libxml_previous_error_state = libxml_use_internal_errors(true);
      $DOMDocument->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $html);
      apply_filters('picturefill_wp_syntax_present_libxml_errors', libxml_get_errors(), $html, $DOMDocument);
      libxml_clear_errors();
      libxml_use_internal_errors($libxml_previous_error_state);
      $spans = $DOMDocument->getElementsByTagName('picture');
      if(0 === $spans->length){
        return false;
      }
      return true;
    }

    static function get_images(DOMDocument $DOMDocument, $html){
      $libxml_previous_error_state = libxml_use_internal_errors(true);
      $DOMDocument->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $html);
      apply_filters('picturefill_wp_get_images_libxml_errors', libxml_get_errors(), $html, $DOMDocument);
      libxml_clear_errors();
      libxml_use_internal_errors($libxml_previous_error_state);
      return $DOMDocument->getElementsByTagName('img');
    }

    // Constructor, set the object variables
    public function __construct(){
      $upload_dir_data = wp_upload_dir();

      $this->upload_base_dir = $upload_dir_data['basedir'];
      $this->upload_base_url = $upload_dir_data['baseurl'];
      $this->registered_image_sizes = get_intermediate_image_sizes();
      $this->set_choosable_image_sizes();
      $this->set_allowed_image_extensions();

      $this->register_sizes('default', '100vw');

      $this->register_srcset('all', array_merge($this->registered_image_sizes, array('full')));
      $this->register_srcset('default', array('thumbnail', 'medium', 'large', 'full'));
    }

    public function register_sizes($handle, $sizes_string, $attached = array()){
      if(empty($handle) || empty($sizes_string) || !is_string($handle) || !is_string($sizes_string)){
        return false;
      }

      $this->registered_sizes[$handle] = array(
        'handle' => $handle,
        'sizes_string' => $sizes_string,
        'attached' => $attached
      );

      if(is_array($attached) && !empty($attached)){
        foreach($attached as $image_size){
          $this->image_attachments[$image_size]['sizes'] = $handle;
        }
      }
      return true;
    }

    public function register_srcset($handle, $srcset_array, $attached = array()){
      if(empty($handle) || empty($srcset_array) || !is_string($handle) || !is_array($srcset_array)){
        return false;
      }

      $this->registered_srcsets[$handle] = array(
        'handle' => $handle,
        'srcset_array' => $srcset_array,
        'attached' => $attached
      );

      if(is_array($attached) && !empty($attached)){
        foreach($attached as $image_size){
          $this->image_attachments[$image_size]['srcset'] = $handle;
        }
      }
      return true;
    }

    public function get_upload_base_url(){
      return $this->upload_base_url;
    }

    public function get_upload_base_dir(){
      return $this->upload_base_dir;
    }

    public function get_allowed_image_extensions(){
      return $this->allowed_image_extensions;
    }

    public function get_srcset_by_handle($handle){
      if(!empty($this->registered_srcsets[$handle]['srcset_array'])){
        return $this->registered_srcsets[$handle]['srcset_array'];
      }else{
        return $this->get_srcset_by_size($handle);
      }
    }

    public function get_srcset_by_size($size){
      if(!empty($this->image_attachments[$size]['srcset'])){
        return $this->registered_srcsets[$this->image_attachments[$size]['srcset']]['srcset_array'];
      }else{
        return $this->registered_srcsets['default']['srcset_array'];
      }
    }

    private function set_choosable_image_sizes(){
      $sizes = array();
      $choosable_image_size_names = apply_filters('image_size_names_choose', array(
        'thumbnail' => __('Thumbnail'),
        'medium' => __('Medium'),
        'large' => __('Large'),
        'full' => __('Full Size')
      ));

      foreach($choosable_image_size_names as $size => $name){
        $sizes[] = $size;
      }

      $this->choosable_image_sizes = $sizes;
    }

    private function set_allowed_image_extensions(){
      $image_extensions = array();
      $mime_types = get_allowed_mime_types();

      foreach($mime_types as $extensions => $type){
        if('image/' === substr($type, 0, 6)){
          $image_extensions = array_merge($image_extensions, explode('|', $extensions));
        }
      }

      $this->allowed_image_extensions = $image_extensions;
    }
  }
}
