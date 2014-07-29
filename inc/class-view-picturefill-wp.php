<?php
defined('ABSPATH') OR exit;
if(!class_exists('View_Picturefill_WP')){
  class View_Picturefill_WP{

    // Object variables
    private $model;
    private $original_image = '';
    private $image_attributes = array();
    private $image_attachment_data = array();

    // Static methods
    static function standardize_img_tags($html){
      return apply_filters('picturefill_wp_html_standardized_img_tags', preg_replace('/(<img[^<]*?)(?:\s*\/*>)/', '$1 />', $html));
    }

    // Constructor, get data from model object
    public function __construct(Model_Image_Picturefill_WP $model){
      $this->model = $model;
      $this->original_image = html_entity_decode(self::standardize_img_tags($this->model->get_image_xml()), ENT_COMPAT, 'UTF-8');
      $this->image_attributes = apply_filters('picturefill_wp_image_attributes', $this->model->get_image_attributes());
      $this->image_attachment_data = $this->model->get_image_attachment_data();
    }

    // Methods to render data in the templates
    public function get_image_attribute_string(){
      $image_attributes = $this->image_attributes;

      $output_string = '';

      $ignore_attributes = array(
        'size',
        'attachment_id',
        'srcset_name',
        'sizes_name'
      );

      if(!apply_filters('picturefill_wp_output_src', false, $this->image_attributes) && false !== $this->image_attributes['attachment_id']){
        $ignore_attributes[] = 'src';
      }

      if(!apply_filters('picturefill_wp_use_explicit_width', true, $this->image_attributes) && false !== $this->image_attributes['attachment_id']){
        $ignore_attributes[] = 'width';
        $ignore_attributes[] = 'height';
      }

      foreach($image_attributes as $attribute => $value){
        $output_string .= !empty($value) && !is_array($value) && !in_array($attribute, $ignore_attributes) ? ' ' . $attribute . '="' . apply_filters('picturefill_wp_image_attribute_' . $attribute, html_entity_decode($value, ENT_COMPAT, 'UTF-8')) . '"' : '';
      }

      return apply_filters('picturefill_wp_image_attribute_string', $output_string);
    }

    public function get_original_image(){
      return $this->original_image;
    }

    public function get_image_src($image_size){
      return $this->image_attachment_data[$image_size]['url'];
    }

    public function format_srcset($sizes){
      $srcset_components = array();

      foreach($sizes as $size){
        $resolution = $this->model->get_srcset_resolution($size);
        $srcset_components[] = apply_filters('picturefill_wp_srcset_url', $this->model->get_image_url($size), $size) . $resolution;
      }

      return implode(', ', $srcset_components);
    }

    public function get_sizes(){
      return ' sizes="' . apply_filters('picturefill_wp_sizes_string_' . $this->image_attributes['size'], $this->model->get_sizes_string(), $this->image_attributes, $this->image_attachment_data) . '"';
    }

    // Render templates
    public function render_template($template, $template_data = array()){
      $template_data = $this->model->get_srcset_array();
      $template_path = apply_filters('picturefill_wp_template_path', PICTUREFILL_WP_PATH . 'inc/templates/');
      $template_file_path = apply_filters('picturefill_wp_' . $template . '_template_file_path', $template_path . $template . '-template.php', $template, $template_path);
      $view = $this;
      $template_data = apply_filters('picturefill_wp_' . $template . '_template_data', $template_data);

      ob_start();
      include($template_file_path);
      return apply_filters('picturefill_wp_' . $template . '_template', ob_get_clean());
    }
  }
}
