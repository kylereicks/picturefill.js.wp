<?php
if(!class_exists('Model_Picturefill_WP')){
  class Model_Picturefill_WP{

    private $content;

    public function __construct($html){
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      $this->content = new DOMDocument();
      $this->content->loadHTML('<?xml encoding="UTF-8">' . $html);
    }

    public function get_images(){
      return $this->content->getElementsByTagName('img');
    }

    public function save_xml($DOMDocument_element){
      return $this->content->saveXML($DOMDocument_element);
    }

    public function get_image_attributes($DOMDocumnet_image){
      $attributes = array(
        'src' => $DOMDocumnet_image->getAttribute('src'),
        'alt' => $DOMDocumnet_image->getAttribute('alt'),
        'title' => $DOMDocumnet_image->getAttribute('title'),
        'class' => $DOMDocumnet_image->getAttribute('class'),
        'id' => $DOMDocumnet_image->getAttribute('id'),
        'width' => $DOMDocumnet_image->getAttribute('width'),
        'height' => $DOMDocumnet_image->getAttribute('height')
      );

      preg_match('/(?:(?:^|\s)size-)(\w+)/', $attributes['class'], $attributes['size']);
      preg_match('/(?:(?:^|\s)wp-image-)(\w+)/', $attributes['class'], $attributes['attachment_id']);
      preg_match('/(?:(?:^|\s)min-size-)(\w+)/', $attributes['class'], $attributes['min_size']);

      if(!empty($attributes['attachment_id'])){
        if(empty($attributes['size'])){
          $attributes['size'] = $this->get_unadjusted_size($this->get_image_attachment_data($attributes['attachment_id']), $attributes['src']);
        }
      }
      return $attributes;
    }

    public function get_image_attachment_data($attachment_id = null){
      if(!empty($attachment_id)){
        $image_attachment_data = array(
          'full' => wp_get_attachment_image_src($attachment_id[1], 'full'),
          'thumbnail' => wp_get_attachment_image_src($attachment_id[1], 'thumbnail'),
          'thumbnail@2x' => wp_get_attachment_image_src($attachment_id[1], 'thumbnail@2x'),
          'medium' => wp_get_attachment_image_src($attachment_id[1], 'medium'),
          'medium@2x' => wp_get_attachment_image_src($attachment_id[1], 'medium@2x'),
          'large' => wp_get_attachment_image_src($attachment_id[1], 'large'),
          'large@2x' => wp_get_attachment_image_src($attachment_id[1], 'large@2x')
        );

        foreach($image_attachment_data as $attachment_size => $attachment_data){
          if($image_attachment_data['full'][0] === $attachment_data[0] && $image_attachment_data['full'][1] > $attachment_data[1] && $image_attachment_data['full'][2] > $attachment_data[2]){
            $new_meta_data = wp_generate_attachment_metadata($attachment_id, get_attached_file($attachment_id));
            wp_update_attachment_metadata($attachment_id, $new_meta_data);
            $image_attachment_data[$attachment_size] = wp_get_attachment_image_src($attachment_id, $attachment_size);
          }
        }

        return $image_attachment_data;
      }else{
        return false;
      }
    }

    private function get_unadjusted_size($image_attachment_data, $src){
      foreach($image_attachment_data as $attachment_size => $attachment_data){
        if($attachment_data[0] === $src){
          return array('adjusted', $attachment_size);
        }
      }
      return false;
    }
  }
}
