<?php
if(!class_exists('View_Picturefill_WP')){
  class View_Picturefill_WP{

    private $image_sizes = array();

    static function standardize_img_tags($html){
      return preg_replace('/(<img[^<]*?)(?:>|\/>|\s\/>)/', '$1 />', $html);
    }

    public function __construct($image_attributes){
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

        $this->image_sizes = array_reverse($image_sizes);
      }
    }

    public function generate_source_list($template_data){
      $output = '';
      $image_output_queue = $this->image_sizes;

      while(!empty($image_output_queue)){
        $image_size = array_shift($image_output_queue);
        $template_data['image_size'] = $image_size;
        $output .= $this->render_template('source', $template_data);
      }

      return $output;
    }

    public function get_picture_attribute_string($image_attributes){
      $output_string = '';

      foreach($image_attributes as $attribute => $value){
        $output_string .= !empty($value) && !is_array($value) ? ' data-' . $attribute . '="' . $value . '"' : '';
      }

      return $output_string;
    }

    public function get_original_image_src($image_attributes){
      return $image_attributes['src'];
    }

    public function get_original_image($original_image_xml){
      return html_entity_decode(self::standardize_img_tags($original_image_xml), ENT_COMPAT, 'UTF-8');
    }

    public function get_image_src($image_size, $image_attachment_data){
      return $image_attachment_data[$image_size][0];
    }

    public function get_image_width($image_size, $image_attachment_data, $image_attributes){
      return $image_size === $image_attributes['size'][1] ? $image_attributes['width'] : $image_attachment_data[$image_size][1];
    }

    public function get_image_height($image_size, $image_attachment_data, $image_attributes){
      return $image_size === $image_attributes['size'][1] ? $image_attributes['height'] : $image_attachment_data[$image_size][2];
    }

    public function get_media_query($image_size, $image_attachment_data, $image_attributes){
      $width = $image_size === $image_attributes['size'][1] ? $image_attributes['width'] : $image_attachment_data[$image_size][1];
      $breakpoint = 'thumbnail' === $image_size || 'thumbnail@2x' === $image_size ? 1 : $width + 20;
      $resolution_query = '@2x' === substr($image_size, -3) ? ' and (-webkit-min-device-pixel-ratio: 1.5),(min-resolution: 144dpi),(min-resolution: 1.5dppx)' : '';
      return '(min-width: ' . $breakpoint . 'px)' . $resolution_query;
    }

    public function render_template($template, $template_data = array()){
      $template = PICTUREFILL_WP_PATH . 'inc/templates/' . $template . '-template.php';
      $view_picturefill_wp = $this;
      extract($template_data);
      ob_start();
      include($template);
      return ob_get_clean();
    }

  }
}
