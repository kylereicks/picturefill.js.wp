<?php
defined('ABSPATH') OR exit;
if(!class_exists('Model_Picturefill_WP')){
  class Model_Picturefill_WP{

    private $options;
    private $upload_base_dir;
    private $upload_base_url;
    private $registered_image_sizes;
    private $choosable_image_sizes;
    private $source_sets;

    // Constructor, set the object variables
    public function __construct(){
      $upload_dir_data = wp_upload_dir();

      $this->options = array(
        'use_sizes' => false,
        'output_src' => false,
        'explicit_width' => false,
        'create_missing_images' => false
      );

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

    public function get_options(){
      return $this->options;
    }

    public function get_source_set($size, $options = null){
      $options = !empty($options) ? $options : $this->options;
      $sets = array();

      if(true === $options['use_sizes']){
        return $this->source_sets[$size]['sizes']['srcset'];
      }else{
        foreach($this->source_sets as $set_size => $source_set){
          $sets[] = $source_set['media']['srcset'];
          if($size === $set_size){
            return $sets;
          }
        }
      }
    }

    public function get_sizes_string($image_size){
      return $this->source_sets[$image_size]['sizes']['sizes_string'];
    }

    private function set_source_sets(){
      $source_sets = array();

      foreach($this->choosable_image_sizes as $size){
        $source_sets[$size] = array(
          'media' => array(
            'media_string' => '',
            'srcset' => array(
              $size,
              $size . '@2x'
            )
          ),
          'sizes' => array(
            'sizes_string' => '',
            'srcset' => self::set_default_source_set_sizes($size)
          )
        );
      }

      $source_sets['full']['media']['srcset'] = array('full');

      return $source_sets;
    }

    private static function set_default_source_set_sizes($size){
      $sizes = null;

      switch($size){
        case 'full':
          $sizes = array(
            'thumbnail',
            'thumbnail@2x',
            'medium',
            'medium@2x',
            'large',
            'large@2x',
            'full'
          );
          break;
        case 'large':
          $sizes = array(
            'thumbnail',
            'thumbnail@2x',
            'medium',
            'medium@2x',
            'large',
            'large@2x',
          );
          break;
        case 'medium':
          $sizes = array(
            'thumbnail',
            'thumbnail@2x',
            'medium',
            'medium@2x',
          );
          break;
        case 'thumbnail':
          $sizes = array(
            'thumbnail',
            'thumbnail@2x'
          );
          break;
        default:
          $sizes = null;
          break;
      }

      return apply_filters('picturefill_wp_' . $size . '_srcset_sizes', $sizes);
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
