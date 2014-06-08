<?php
defined('ABSPATH') OR exit;
if(!class_exists('Model_Picturefill_WP')){
  class Model_Picturefill_WP{

    private $upload_base_dir;
    private $upload_base_url;
    private $registered_image_sizes;
    private $choosable_image_sizes;
    private $source_sets;

    // Constructor, set the object variables
    public function __construct(){
      $upload_dir_data = wp_upload_dir();

      $this->upload_base_dir = $upload_dir_data['basedir'];
      $this->upload_base_url = $upload_dir_data['baseurl'];
      $this->registered_image_sizes = get_intermediate_image_sizes();
      $this->choosable_image_sizes = self::set_choosable_image_sizes();
      $this->source_sets = $this->set_source_sets();
    }

    public function get_upload_base_url(){
      return $this->upload_base_url;
    }

    public function get_upload_base_dir(){
      return $this->upload_base_dir;
    }

    public function set_source_sets(){
      $source_sets = array();

      foreach($this->choosable_image_sizes as $size){
      }

      return $source_sets;
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
