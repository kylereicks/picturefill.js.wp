<?php
defined('ABSPATH') OR exit;
if(!class_exists('Model_Application_Picturefill_WP')){
  class Model_Application_Picturefill_WP{

    private $options;
    private $upload_base_dir;
    private $upload_base_url;
    private $registered_image_sizes;
    private $choosable_image_sizes;
//    private $source_sets;

    public $registered_sizes = array();

    public $registered_srcsets = array();

    public $image_attachments = array();

    // Constructor, set the object variables
    public function __construct(){
      $upload_dir_data = wp_upload_dir();

      $this->options = array(
        'use_sizes' => apply_filters('picturefill_wp_use_sizes', true),
        'output_src' => apply_filters('picturefill_wp_output_src', false),
        'use_explicit_width' => apply_filters('picturefill_wp_use_explicit_width', true)
//        'create_missing_images' => apply_filters('picturefill_wp_create_missing_images', false)
      );

      $this->upload_base_dir = $upload_dir_data['basedir'];
      $this->upload_base_url = $upload_dir_data['baseurl'];
      $this->registered_image_sizes = get_intermediate_image_sizes();
      $this->choosable_image_sizes = self::set_choosable_image_sizes();
//      $this->source_sets = $this->set_source_sets();

      $this->register_sizes('default', '100vw');

      $this->register_srcset('all', array_merge($this->registered_image_sizes, array('full')));
      $this->register_srcset('default', array('thumbnail', 'medium', 'large', 'full'));


//      print_r($this);
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

    public function get_source_set($size, $options = null){
      $options = !empty($options) ? $options : $this->options;
      $sets = array();

      if(!empty($this->image_attachments[$size]['srcset'])){
        return $this->registered_srcsets[$this->image_attachments[$size]['srcset']]['srcset_array'];
      }else{
        return $this->registered_srcsets['default']['srcset_array'];
        /*
        if(true === $options['use_sizes']){
          return $this->registered_srcsets['all']['srcset_array'];
        }else{
          foreach($this->source_sets as $set_size => $source_set){
            $sets[] = $source_set['media']['srcset'];
            if($size === $set_size){
              return $sets;
            }
          }
        }
         */
      }
    }

    /*
    public function get_sizes_string($image_size){
      return $this->source_sets[$image_size]['sizes']['sizes_string'];
    }
     */

    /*
    private function set_source_sets(){
      $source_sets = array();

      foreach($this->choosable_image_sizes as $size){
        $source_sets[$size] = array(
          'media' => array(
            'media_string' => apply_filters('picturefill_wp_default_media_string_' . $size, ''),
            'srcset' => array(
              $size,
              $size . '@2x'
            )
          ),
          'sizes' => array(
            'sizes_string' => apply_filters('picturefill_wp_default_sizes_string_' . $size, ''),
            'srcset' => self::set_default_source_set_sizes($size)
          )
        );
      }

      $source_sets['full']['media']['srcset'] = array('full');

      return $source_sets;
    }
     */

    /*
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
     */

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
