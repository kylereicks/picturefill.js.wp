<?php
defined('ABSPATH') OR exit;
if(!class_exists('Model_Application_Picturefill_WP')){
  class Model_Application_Picturefill_WP{

    private $options;
    private $upload_base_dir;
    private $upload_base_url;
    private $registered_image_sizes;
    private $choosable_image_sizes;

    public $registered_sizes = array();

    public $registered_srcsets = array();

    public $image_attachments = array();

    // Constructor, set the object variables
    public function __construct(){
      $upload_dir_data = wp_upload_dir();

      $this->options = array(
        'output_src' => apply_filters('picturefill_wp_output_src', false),
        'use_explicit_width' => apply_filters('picturefill_wp_use_explicit_width', true)
      );

      $this->upload_base_dir = $upload_dir_data['basedir'];
      $this->upload_base_url = $upload_dir_data['baseurl'];
      $this->registered_image_sizes = get_intermediate_image_sizes();
      $this->choosable_image_sizes = self::set_choosable_image_sizes();

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

      if(!empty($attached)){
        foreach($attached as $image_size){
          $this->image_attachments[$image_size]['sizes'] = $handle;
        }
      }
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

      if(!empty($attached)){
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

    public function get_options(){
      return $this->options;
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

    private static function set_choosable_image_sizes(){
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

      return $sizes;
    }
  }
}
