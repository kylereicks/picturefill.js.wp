<?php
defined('ABSPATH') OR exit;
if(!class_exists('Model_Image_Picturefill_WP')){
  class Model_Image_Picturefill_WP{

    // Input variables
    private $DOMDocument;
    private $image;

    // Object variables
    private $parent_model;
    private $options = array();
    private $image_attributes = array();
    private $image_attachment_data = array();
    private $image_sizes = array();
    private $srcset_array = array();
    private $upload_subdir = '';

    // Static methods to generate the input needed to instatiante the object
    static function get_DOMDocument(){
      return new DOMDocument();
    }

    static function syntax_present($DOMDocument, $html){
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

    static function get_images($DOMDocument, $html){
      $libxml_previous_error_state = libxml_use_internal_errors(true);
      $DOMDocument->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $html);
      apply_filters('picturefill_wp_get_images_libxml_errors', libxml_get_errors(), $html, $DOMDocument);
      libxml_clear_errors();
      libxml_use_internal_errors($libxml_previous_error_state);
      return $DOMDocument->getElementsByTagName('img');
    }

    // Constructor, set the object variables
    public function __construct($parent_model, $DOMDocument, $image){
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      $this->parent_model = $parent_model;
      $this->DOMDocument = $DOMDocument;
      $this->image = $image;
      $this->set_default_options();
      $this->set_image_attributes();
      $this->set_image_attachment_data($this->image_attributes['attachment_id']);
      $this->set_unadjusted_image_size();
//      $this->set_image_sizes();
      $this->set_srcset_array();
//      print_r($this->parent_model);
//      print_r($this);
    }

    // Methods to retrieve object data
    public function get_image_attributes(){
      return $this->image_attributes;
    }

    public function get_image_attachment_data(){
      return $this->image_attachment_data;
    }

    public function get_image_sizes(){
      return $this->image_sizes;
    }

    public function get_image_xml(){
      return $this->DOMDocument->saveXML($this->image);
    }

    public function get_srcset_array(){
      return $this->srcset_array;
    }

    public function get_image_url($size){
      return $this->image_attachment_data[$size]['url'];
    }

    public function get_srcset_resolution($image_size){
      $resolution_match = null;
      if($this->options['use_sizes']){
        return ' ' . $this->image_attachment_data[$image_size]['width'] . 'w';
      }else{
        if(preg_match('/@([\d\.]+)x$/', $image_size, $resolution_match)){
          return ' ' . $resolution_match[1] . 'x';
        }
      }
      return '';
    }

    public function get_option($option_name){
      return $this->options[$option_name];
    }

    // Methods to set object data
    private function set_default_options(){
      $this->options = apply_filters('picturefill_wp_image_default_options', $this->parent_model->get_options());
    }

    private function set_image_attributes(){
      $DOMDocument_image = $this->image;

      $attributes = array(
        'attachment_id' => null,
        'srcset_method' => null,
        'src' => null,
        'alt' => null,
        'title' => null,
        'class' => null,
        'id' => null,
        'style' => null,
        'width' => null,
        'height' => null
      );

      foreach($DOMDocument_image->attributes as $attr => $node){
        $attributes[$attr] = $node->nodeValue;
      }

      $attributes['attachment_id'] = self::url_to_attachment_id($attributes['src']);

      preg_match('/(?:(?:^|\s)size-)([\w|-]+)/', $attributes['class'], $attributes['size']);
      preg_match('/(?:(?:^|\s)min-size-)([\w|-]+)/', $attributes['class'], $attributes['min_size']);

      $this->image_attributes = $attributes;
    }

    private function set_unadjusted_image_size(){
      if(false !== $this->image_attributes['attachment_id']){
        if(empty($this->image_attributes['size'])){
          $this->image_attributes['size'] = $this->get_unadjusted_size($this->image_attachment_data, $this->image_attributes);
        }
      }
    }

    private function set_image_attachment_data($attachment_id){
      if(false !== $attachment_id){
        $image_attachment_metadata = wp_get_attachment_metadata($attachment_id);
        $this->upload_subdir = '/' . substr($image_attachment_metadata['file'], 0, 7);
        $image_attachment_data = $image_attachment_metadata['sizes'];
        $image_attachment_data['full'] = array(
          'file' => substr($image_attachment_metadata['file'], 8),
          'width' => $image_attachment_metadata['width'],
          'height' => $image_attachment_metadata['height']
        );

        foreach($image_attachment_data as $image_size => $image_data){
          $image_attachment_data[$image_size]['url'] = $this->parent_model->get_upload_base_url() . $this->upload_subdir . '/' . $image_data['file'];
        }

        $image_attachment_data = apply_filters('picturefill_wp_image_attachment_data', $image_attachment_data, $attachment_id);

        if($this->options['create_missing_images']){
          foreach($image_attachment_data as $attachment_size => $attachment_data){
            if($this->image_needs_to_be_created($image_attachment_data, $attachment_size, $attachment_data)){
              $new_meta_data = wp_generate_attachment_metadata($attachment_id, get_attached_file($attachment_id));
              wp_update_attachment_metadata($attachment_id, $new_meta_data);
              $image_attachment_data[$attachment_size] = wp_get_attachment_image_src($attachment_id, $attachment_size);
            }
          }
        }

        $this->image_attachment_data = $image_attachment_data;
      }else{
        $this->image_attachment_data = false;
      }
    }

    private function image_needs_to_be_created($image_attachment_data, $attachment_size, $attachment_data){
      global $_wp_additional_image_sizes;

      if(empty($attachment_data)){
        return true;
      }

      if('full' === $attachment_size){
        return false;
      }
      
      $attachment_data = $this->get_image_width_height($attachment_data);

      if(array_key_exists($attachment_size, $_wp_additional_image_sizes)){
        if(($_wp_additional_image_sizes[$attachment_size]['width'] == $attachment_data[1] && $_wp_additional_image_sizes[$attachment_size]['height'] >= $attachment_data[2]) || ($_wp_additional_image_sizes[$attachment_size]['height'] == $attachment_data[2] && $_wp_additional_image_sizes[$attachment_size]['width'] >= $attachment_data[1])){
          return false;
        }

        if($image_attachment_data['full'][1] < $_wp_additional_image_sizes[$attachment_size]['width'] && $image_attachment_data['full'][2] < $_wp_additional_image_sizes[$attachment_size]['height']){
          return false;
        }

      }elseif(in_array($attachment_size, array('thumbnail', 'medium', 'large'))){
        $crop_setting = get_option($attachment_size . '_crop');
        $width_setting = get_option($attachment_size . '_size_w');
        $height_setting = get_option($attachment_size . '_size_h');

        if(($width_setting == $attachment_data[1] && $height_setting >= $attachment_data[2]) || ($height_setting == $attachment_data[2] && $width_setting >= $attachment_data[1])){
          return false;
        }

        if($image_attachment_data['full'][1] < $width_setting && $image_attachment_data['full'][2] < $height_setting){
          return false;
        }
      }

      return true;
    }

    private function get_unadjusted_size($image_attachment_data, $image_attributes){
      if(empty($image_attributes['width'])){
        $image_attributes_url_width_height = array($image_attributes['src'], $image_attributes['width'], $image_attributes['height']);
        $image_attributes_url_width_height = $this->get_image_width_height($image_attributes_url_width_height);
        $image_attributes['width'] = $image_attributes_url_width_height[1];
        $image_attributes['height'] = $image_attributes_url_width_height[2];

        $this->image_attributes = $image_attributes;
      }
      foreach($image_attachment_data as $attachment_size => $attachment_data){
        if($attachment_data['url'] === $image_attributes['src'] && false === strstr($attachment_size, '@2x')){
          return array('adjusted', $attachment_size);
        }

        $attachment_data = $this->get_image_width_height($attachment_data);

        if($attachment_data['width'] >= $image_attributes['width'] && false === strstr($attachment_size, '@2x')){
          return array('adjusted', $attachment_size);
        }
      }
      return false;
    }

    private function get_image_width_height($attachment_data){
      if(ini_get('allow_url_fopen')){
        $image_size = getimagesize($attachment_data['url']);

        if(!empty($image_size)){
          $attachment_data['width'] = $image_size[0];
          $attachment_data['height'] = $image_size[1];
        }
      }else{
        preg_match('/^(?:.+?)(?:-(\d+)x(\d+))\.(?:jpg|jpeg|png|gif)(?:(?:\?|#).+)?$/i', $attachment_data['url'], $image_width_height);
        if(!empty($image_width_height)){
          $attachment_data['width'] = $image_width_height[1];
          $attachment_data['width'] = $image_width_height[2];
        }
      }

      return $attachment_data;
    }

    /*
    private function set_image_sizes(){
      $image_attributes = $this->image_attributes;

      if(false === $image_attributes['attachment_id']){
        return false;
      }

      $image_sizes = array(
        'full',
//        'large@2x',
        'large',
//        'medium@2x',
        'medium',
//        'thumbnail@2x',
        'thumbnail'
      );

      if(!empty($image_attributes['size'])){
        if('full' === $image_attributes['size'][1]){
          $image_attachment_data = $this->image_attachment_data;
          array_shift($image_sizes);
          foreach($image_sizes as $size){
            if(!empty($image_attachment_data[$size]) && $image_attachment_data['full']['width'] > $image_attachment_data[$size]['width']){
              break;
            }
            if(false === strstr($size, '@2x')){
              array_shift($image_sizes);
              array_shift($image_sizes);
            }
          }
          array_unshift($image_sizes, 'full');
        }else{
          foreach($image_sizes as $size){
            if($image_attributes['size'][1] === $size || $image_attributes['size'][1] . '@2x' === $size){
              break;
            }
            array_shift($image_sizes);
          }
        }

        $image_sizes = array_reverse($image_sizes);

        if(!empty($image_attributes['min_size'])){
          foreach($image_sizes as $size){
            if($image_attributes['min_size'][1] === $size){
              break;
            }
            array_shift($image_sizes);
          }
        }

        $this->image_sizes = apply_filters('picturefill_wp_image_sizes', array_reverse($image_sizes), $image_attributes);
      }
    }
     */

    private function set_srcset_array(){
      $image_attributes = $this->image_attributes;
      $source_sets = array();
      $single_source_set = array();
      $set_length = 0;
      $longest_source_set = 0;

      if(false === $image_attributes['attachment_id']){
        return false;
      }

      if(!empty($image_attributes['size'])){

        $source_sets = $this->parent_model->get_source_set($this->image_attributes['size'][1], $this->image_attributes['srcset_method']);

        foreach($source_sets as $index => $set){
          foreach($set as $i => $size){
            if(!array_key_exists($size, $this->image_attachment_data)){
              unset($source_sets[$index][$i]);
            }
          }
          if(empty($source_sets[$index])){
            unset($source_sets[$index]);
          }else{
            $set_length = count($source_sets[$index]);
            if($longest_source_set < $set_length){
              $longest_source_set = $set_length;
            }
          }
        }

        if(!empty($image_attributes['min_size'])){
          foreach($source_sets as $set){
            if($image_attributes['min_size'][1] === $set[0]){
              break;
            }
            array_shift($source_sets);
          }
        }

        if(1 === $longest_source_set){
          $this->options['use_sizes'] = true;
          $single_source_set = array();
          foreach($source_sets as $set){
            $single_source_set[0][] = $set[0];
          }
          $this->srcset_array = apply_filters('picturefill_wp_image_sizes', array_reverse($single_source_set), $image_attributes);
        }else{
          $this->srcset_array = apply_filters('picturefill_wp_image_sizes', array_reverse($source_sets), $image_attributes);
        }
      }
    }

    public static function url_to_attachment_id($image_url){
      global $wpdb;
      $original_image_url = $image_url;
      $image_url = preg_replace('/^(.+?)(-\d+x\d+)?\.(jpg|jpeg|png|gif)((?:\?|#).+)?$/i', '$1.$3', $image_url);
      $prefix = $wpdb->prefix;
      $attachment_id = $wpdb->get_col($wpdb->prepare("SELECT ID FROM " . $prefix . "posts" . " WHERE guid='%s';", $image_url ));
      if(!empty($attachment_id)){
        return $attachment_id[0];
      }else{
        $attachment_id = $wpdb->get_col($wpdb->prepare("SELECT ID FROM " . $prefix . "posts" . " WHERE guid='%s';", $original_image_url ));
      }
      return !empty($attachment_id) ? $attachment_id[0] : false;
    }
  }
}
