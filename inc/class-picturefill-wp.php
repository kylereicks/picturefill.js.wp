<?php
if(!class_exists('Picturefill_WP')){
  class Picturefill_WP{

    // Setup singleton pattern
    public static function get_instance(){
      static $instance;

      if(null === $instance){
        $instance = new self();
      }

      return $instance;
    }

    private function __clone(){
      return null;
    }

    private function __wakeup(){
      return null;
    }

    public static function deactivate(){
      self::clear_picturefill_wp_cache();
    }

    private static function clear_picturefill_wp_cache(){
      global $wpdb;
      $picturefill_wp_transients = $wpdb->get_col('SELECT option_name FROM ' . $wpdb->options . ' WHERE option_name LIKE \'_transient%_picturefill_wp%\'');
      foreach($picturefill_wp_transients as $transient){
        delete_option($transient);
      }
    }

    // Constructor, add actions and filters
    private function __construct(){
      add_action('wp_enqueue_scripts', array($this, 'register_picturefill_scripts'));
      add_action('init', array($this, 'add_image_sizes'));
//      add_filter('the_content', array($this, 'replace_images'), 11);
      add_filter('the_content', array($this, 'cache_picturefill_output'), 11);
    }

    // Filter and action methods
    public function register_picturefill_scripts(){
      wp_register_script('picturefill', PICTUREFILL_WP_URL . 'js/libs/picturefill.min.js', array(), PICTUREFILL_WP_VERSION, true);
    }

    public function cache_picturefill_output($html, $content_type = 'the_content'){
      global $wp_scripts;
      $post_id = get_the_ID();
      $cache_duration = 86400;
      $cached_output = get_transient('picturefill_wp_' . $content_type . '_output_' . $post_id);
      if(!empty($cached_output) && (get_option('_transient_timeout_picturefill_wp_output_' . $post_id) - $cache_duration) > get_the_modified_time('U')){
        wp_enqueue_script('picturefill');
        return $cached_output;
      }else{
        $output = $this->replace_images($html);
        if(in_array('picturefill', $wp_scripts->queue)){
          set_transient('picturefill_wp_' . $content_type . '_output_' . $post_id, $output, $cache_duration);
        }
        return $output;
      }
    }

    public function replace_images($html){
      require_once(PICTUREFILL_WP_PATH . 'inc/class-model-picturefill-wp.php');
      $DOMDocument = Model_Picturefill_WP::get_DOMDocument();
      $images = Model_Picturefill_WP::get_images($DOMDocument, $html);
      if($images->length > 0){
        require_once(PICTUREFILL_WP_PATH . 'inc/class-view-picturefill-wp.php');
        wp_enqueue_script('picturefill');
        $html = View_Picturefill_WP::standardize_img_tags($html);
        foreach($images as $image){
          $model_picturefill_wp = new Model_Picturefill_WP($DOMDocument, $image);
          $view_picturefill_wp = new View_Picturefill_WP($model_picturefill_wp);

          $html = str_replace($view_picturefill_wp->get_original_image(), $view_picturefill_wp->render_template('picture'), $html);
        }
      }
      return apply_filters('picturefill_wp_replace_images_output', $html);
    }

    public function add_image_sizes(){
      add_image_size('thumbnail@2x', get_option('thumbnail_size_w') * 2, get_option('thumbnail_size_h') * 2, get_option('thumbnail_crop'));
      add_image_size('medium@2x', get_option('medium_size_w') * 2, get_option('medium_size_h') * 2, get_option('medium_crop'));
      add_image_size('large@2x', get_option('large_size_w') * 2, get_option('large_size_h') * 2, get_option('large_crop'));
    }
  }
}
