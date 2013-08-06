<?php
if(!class_exists('Model_Picturefill_WP')){
  class Model_Picturefill_WP{

    // Input variables
    private $DOMDocument;
    private $image;

    // Object variables
    private $image_attributes = array();
    private $image_attachment_data = array();
    private $image_sizes = array();

    // Static methods to generate the input needed to instatiante the object
    static function get_DOMDocument(){
      return new DOMDocument();
    }

    static function get_images($DOMDocument, $html){
      $DOMDocument->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />' . $html);
      return $DOMDocument->getElementsByTagName('img');
    }

    // Constructor, set the object variables
    public function __construct($DOMDocument, $image){
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      $this->DOMDocument = $DOMDocument;
      $this->image = $image;
      $this->set_image_attributes();
      $this->set_image_attachment_data($this->image_attributes['attachment_id']);
      $this->set_unadjusted_image_size();
      $this->set_image_sizes();
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

    // Methods to set object data
    private function set_image_attributes(){
      $DOMDocument_image = $this->image;

      $attributes = array(
        'src' => $DOMDocument_image->getAttribute('src'),
        'alt' => $DOMDocument_image->getAttribute('alt'),
        'title' => $DOMDocument_image->getAttribute('title'),
        'class' => $DOMDocument_image->getAttribute('class'),
        'id' => $DOMDocument_image->getAttribute('id'),
        'width' => $DOMDocument_image->getAttribute('width'),
        'height' => $DOMDocument_image->getAttribute('height')
      );

      $attributes['attachment_id'] = self::url_to_attachment_id($attributes['src']);

      preg_match('/(?:(?:^|\s)size-)([\w|-]+)/', $attributes['class'], $attributes['size']);
      preg_match('/(?:(?:^|\s)min-size-)([\w|-]+)/', $attributes['class'], $attributes['min_size']);

      $this->image_attributes = $attributes;
    }

    private function set_unadjusted_image_size(){
      if(false !== $this->image_attributes['attachment_id']){
        if(empty($this->image_attributes['size'])){
          $this->image_attributes['size'] = $this->get_unadjusted_size($this->image_attachment_data, $this->image_attributes['src']);
        }
      }
    }

    private function set_image_attachment_data($attachment_id){
      if(false !== $attachment_id){
        $image_attachment_data = array(
          'full' => wp_get_attachment_image_src($attachment_id, 'full'),
          'thumbnail' => wp_get_attachment_image_src($attachment_id, 'thumbnail'),
          'thumbnail@2x' => wp_get_attachment_image_src($attachment_id, 'thumbnail@2x'),
          'medium' => wp_get_attachment_image_src($attachment_id, 'medium'),
          'medium@2x' => wp_get_attachment_image_src($attachment_id, 'medium@2x'),
          'large' => wp_get_attachment_image_src($attachment_id, 'large'),
          'large@2x' => wp_get_attachment_image_src($attachment_id, 'large@2x')
        );

        $image_attachment_data = apply_filters('picturefill_wp_image_attachment_data', $image_attachment_data, $attachment_id);

        foreach($image_attachment_data as $attachment_size => $attachment_data){
          if($this->image_needs_to_be_created($image_attachment_data, $attachment_size, $attachment_data)){
            $new_meta_data = wp_generate_attachment_metadata($attachment_id, get_attached_file($attachment_id));
            wp_update_attachment_metadata($attachment_id, $new_meta_data);
            $image_attachment_data[$attachment_size] = wp_get_attachment_image_src($attachment_id, $attachment_size);
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
      
      $image_size = getimagesize($attachment_data[0]);

      if(!empty($image_size)){
        $attachment_data[1] = $image_size[0];
        $attachment_data[2] = $image_size[1];
      }

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

    private function get_unadjusted_size($image_attachment_data, $src){
      foreach($image_attachment_data as $attachment_size => $attachment_data){
        if($attachment_data[0] === $src){
          return array('adjusted', $attachment_size);
        }
      }
      return false;
    }

    private function set_image_sizes(){
      $image_attributes = $this->image_attributes;

      if(false === $image_attributes['attachment_id']){
        return false;
      }

      $image_sizes = array(
        'full',
        'large@2x',
        'large',
        'medium@2x',
        'medium',
        'thumbnail@2x',
        'thumbnail'
      );

      if(!empty($image_attributes['size'])){
        foreach($image_sizes as $size){
          if($image_attributes['size'][1] === $size || $image_attributes['size'][1] . '@2x' === $size){
            break;
          }
          array_shift($image_sizes);
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

        $this->image_sizes = apply_filters('picturefill_wp_image_sizes', $image_sizes, $image_attributes);
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
