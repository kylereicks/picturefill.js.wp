<?php
defined('ABSPATH') OR exit;
if(!class_exists('View_Picturefill_WP')){
  class View_Picturefill_WP{

    // Object variables
    private $image_sizes = array();
    private $original_image = '';
    private $image_attributes = array();
    private $image_attachment_data = array();

    // Static methods
    static function standardize_img_tags($html){
      return apply_filters('picturefill_wp_content_html', preg_replace('/(<img[^<]*?)(?:>|\/>|\s\/>)/', '$1 />', $html));
    }

    // Constructor, get data from model object
    public function __construct($model_picturefill_wp){
      $this->original_image = html_entity_decode(self::standardize_img_tags($model_picturefill_wp->get_image_xml()), ENT_COMPAT, 'UTF-8');
      $this->image_attributes = apply_filters('picturefill_wp_image_attributes', $model_picturefill_wp->get_image_attributes());
      $this->image_attachment_data = $model_picturefill_wp->get_image_attachment_data();
      $this->image_sizes = $model_picturefill_wp->get_image_sizes();
    }

    // Methods to render data in the templates
    public function generate_source_list(){
      $output = '';
      $template_data = array();
      $image_output_queue = $this->image_sizes;

      while(!empty($image_output_queue)){
        $image_size = array_shift($image_output_queue);
        $template_data['image_size'] = $image_size;
        $output .= $this->render_template('source', $template_data);
      }

      return apply_filters('picturefill_wp_source_list', $output);
    }

    public function get_picture_attribute_string(){
      $image_attributes = $this->image_attributes;

      $output_string = '';

      $ignore_attributes = array(
        'src',
        'attachment_id'
      );

      if(!empty($image_attributes['attachment_id'])){
        $ignore_attributes[] = 'width';
        $ignore_attributes[] = 'height';
      }

      foreach($image_attributes as $attribute => $value){
        $output_string .= !empty($value) && !is_array($value) && !in_array($attribute, $ignore_attributes) ? ' data-' . $attribute . '="' . html_entity_decode($value, ENT_COMPAT, 'UTF-8') . '"' : '';
      }

      return apply_filters('picturefill_wp_picture_attribute_string', $output_string);
    }

    public function get_original_image_src(){
      return $this->image_attributes['src'];
    }

    public function get_original_image(){
      return $this->original_image;
    }

    public function get_image_src($image_size){
      return $this->image_attachment_data[$image_size][0];
    }

    public function get_image_width($image_size){
      if('@2x' === substr($image_size, -3)){
        $image_size = substr($image_size, 0, -3);
      }
      return $image_size === $this->image_attributes['size'][1] ? $this->image_attributes['width'] : $this->image_attachment_data[$image_size][1];
    }

    public function get_image_height($image_size){
      if('@2x' === substr($image_size, -3)){
        $image_size = substr($image_size, 0, -3);
      }
      return $image_size === $this->image_attributes['size'][1] ? $this->image_attributes['height'] : $this->image_attachment_data[$image_size][2];
    }

    public function get_source_class($image_size){
      $class = 'picturefill-wp-source';
      if('@2x' === substr($image_size, -3)){
        $class .= ' retina';
        $class .= ' ' . substr($image_size, 0, strlen($image_size) - 3);
      }else{
        $class .= ' ' . $image_size;
      }
      return $class;
    }

    public function get_media_query($image_size){
      if('@2x' === substr($image_size, -3)){
        $width = substr($image_size, 0, -3) === $this->image_attributes['size'][1] ? $this->image_attributes['width'] : $this->image_attachment_data[substr($image_size, 0, -3)][1];
        $breakpoint = 0 === array_search(substr($image_size, 0, -3), $this->image_sizes) ? 1 : $width + 20;
      }else{
        $width = $image_size === $this->image_attributes['size'][1] ? $this->image_attributes['width'] : $this->image_attachment_data[$image_size][1];
        $breakpoint = 0 === array_search($image_size, $this->image_sizes) ? 1 : $width + 20;
      }
      $resolution_query = '@2x' === substr($image_size, -3) ? ' and (-webkit-min-device-pixel-ratio: 1.5),(min-resolution: 144dpi),(min-resolution: 1.5dppx)' : '';
      return '(min-width: ' . apply_filters('picturefill_wp_media_query_breakpoint', $breakpoint, $image_size, $width, $this->image_attributes, $this->image_attachment_data, $this->image_sizes) . 'px)' . apply_filters('picturefill_wp_media_query_resolution_query', $resolution_query, $image_size);
    }

    // Render templates
    public function render_template($template, $template_data = array()){
      $template_path = apply_filters('picturefill_wp_template_path', PICTUREFILL_WP_PATH . 'inc/templates/');
      $template_file_path = apply_filters('picturefill_wp_' . $template . '_template_file_path', $template_path . $template . '-template.php', $template, $template_path);
      $view_picturefill_wp = $this;
      $template_data = apply_filters('picturefill_wp_' . $template . '_template_data', $template_data);
      if(!empty($template_data)){
        extract($template_data);
      }
      ob_start();
      include($template_file_path);
      return apply_filters('picturefill_wp_' . $template . '_template', ob_get_clean());
    }
  }
}
