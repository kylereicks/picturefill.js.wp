<?php
if(!class_exists('Picturefill_WP')){
  class Picturefill_WP{

    public static function get_instance(){
      static $instance;

      if(null === $instance){
        $instance = new self();
      }

      return $instance;
    }

    private function __construct(){
      add_action('wp_enqueue_scripts', array($this, 'picturefill_scripts'));
      add_action('init', array($this, 'add_image_sizes'));
      add_filter('the_content', array($this, 'replace_images'), 11);
    }

    public function picturefill_scripts(){
      wp_register_script('picturefill', PICTUREFILL_WP_URL . 'js/libs/picturefill.min.js', array(), false, true);
    }

    public function replace_images($html){
      require_once(PICTUREFILL_WP_PATH . 'inc/class-model-picturefill-wp.php');
      $model_picturefill_wp = new Model_Picturefill_WP($html);
      $images = $model_picturefill_wp->get_images();
      if($images->length > 0){
        require_once(PICTUREFILL_WP_PATH . 'inc/class-view-picturefill-wp.php');
        wp_enqueue_script('picturefill');
        $html = View_Picturefill_WP::standardize_img_tags($html);
        foreach($images as $image){
          $original_image_xml = $model_picturefill_wp->save_xml($image);
          $image_attributes = $model_picturefill_wp->get_image_attributes($image);
          $image_attachment_data = $model_picturefill_wp->get_image_attachment_data($image_attributes['attachment_id']);
          $view_picturefill_wp = new View_Picturefill_WP($image_attributes);

          $template_data = array(
            'original_image_xml' => $original_image_xml,
            'image_attributes' => $image_attributes,
            'image_attachment_data' => $image_attachment_data
          );

          $html = str_replace($view_picturefill_wp->get_original_image($original_image_xml), $view_picturefill_wp->render_template('picture', $template_data), $html);
        }
      }
      return $html;
    }

    public function add_image_sizes(){
      add_image_size('thumbnail@2x', get_option('thumbnail_size_w') * 2, get_option('thumbnail_size_h') * 2, get_option('thumbnail_crop'));
      add_image_size('medium@2x', get_option('medium_size_w') * 2, get_option('medium_size_h') * 2, get_option('medium_crop'));
      add_image_size('large@2x', get_option('large_size_w') * 2, get_option('large_size_h') * 2, get_option('large_crop'));
    }
  }
}
