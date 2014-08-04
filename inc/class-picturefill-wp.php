<?php
/**
 * File that defines the controller class for the plugin.
 *
 * @since      1.1.1
 *
 * @package    Picturefill_WP
 * @subpackage Picturefill_WP/inc
 */

defined('ABSPATH') OR exit;

if(!class_exists('Picturefill_WP')){

  /**
   * Controller class for Picturefill.WP.
   *
   * Sets up the plugin and filters the_content.
   *
   * @since      1.1.1
   * @package    Picturefill_WP
   * @subpackage Picturefill_WP/inc
   */
  class Picturefill_WP{

    /**
     * The instance of this class.
     *
     * @since  1.1.1
     * @access private
     * @var    class Picturefill_WP
     */
    private static $instance;

    /**
     * Stores the WordPress global $wpdb.
     *
     * @since  2.0.0
     * @access private
     * @var    class $wpdb
     */
    public static $wpdb;

    /**
     * The application model.
     *
     * Sets up data that will be used across the application.
     *
     * @since  2.0.0
     * @access private
     * @var    class Model_Application_Picturefill_WP
     */
    private $model;

    /**
     * Initialize a single instance of this class.
     *
     * @since 2.0.0
     */
    public static function init(){
      if(null === self::$instance){
        self::$instance = new self();
      }
    }

    /**
     * Get the instance of this class.
     *
     * @since 1.1.1
     */
    public static function get_instance(){
      self::init();
      return self::$instance;
    }

    private function __clone(){
      return null;
    }

    private function __wakeup(){
      return null;
    }

    /**
     * Set the local instance or $wpdb from the global instance of $wpdb.
     *
     * @since 2.0.0
     */
    public static function set_wpdb(){
      global $wpdb;
      self::$wpdb = $wpdb;
    }

    /**
     * Clear all plugin options on deactivation.
     *
     * @since 1.1.3
     */
    public static function deactivate(){
      self::picturefill_wp_clear_options();
    }

    /**
     * Clear all plugin options.
     *
     * @since 1.1.3
     */
    public static function picturefill_wp_clear_options(){
      $picturefill_wp_transients = self::$wpdb->get_col('SELECT option_name FROM ' . self::$wpdb->options . ' WHERE option_name LIKE \'%picturefill_wp%\'');
      foreach($picturefill_wp_transients as $transient){
        delete_option($transient);
      }
    }

    /**
     * Clear all plugin transients.
     *
     * @since 1.1.3
     */
    public static function picturefill_wp_clear_transients(){
      $picturefill_wp_transients = self::$wpdb->get_col('SELECT option_name FROM ' . self::$wpdb->options . ' WHERE option_name LIKE \'%_picturefill_wp%\'');
      foreach($picturefill_wp_transients as $transient){
        delete_option($transient);
      }
    }

    /**
     * Initialize the default functionality of the plugin.
     *
     * On init, set the local instance of $wpdb and run the add_update_hook method.
     * On wp_loaded, set up the application model.
     * On wp_enqueue_scripts register picturefill.js.
     * On picturefill_wp_updated, clear all plugin transients.
     * Filter the_content with picturefill_wp_apply_to_html.
     *
     * @since  1.1.1
     * @access private
     */
    private function __construct(){
      add_action('init', array('Picturefill_WP', 'set_wpdb'));
      add_action('init', array($this, 'add_update_hook'));
      add_action('wp_loaded', array($this, 'set_application_model'));
      add_action('wp_enqueue_scripts', array($this, 'register_picturefill_scripts'));
      add_filter('the_content', array($this, 'picturefill_wp_apply_to_html'), apply_filters('picturefill_wp_the_content_filter_priority', 11));
      add_action('picturefill_wp_updated', array('Picturefill_WP', 'picturefill_wp_clear_transients'));
    }

    /**
     * Include and instantiate the application model.
     *
     * Do action picturefill_wp_register_srcset
     *
     * @since 2.0.0
     */
    public function set_application_model(){
      require_once(PICTUREFILL_WP_PATH . 'inc/class-model-application-picturefill-wp.php');
      $this->model = new Model_Application_Picturefill_WP();
      do_action('picturefill_wp_register_srcset');
    }

    /**
     * Register picturefill.js.
     *
     * Register the minified script if not in debug mode.
     *
     * @since 1.1.1
     */
    public function register_picturefill_scripts(){
      if(WP_DEBUG){
        wp_register_script('picturefill', PICTUREFILL_WP_URL . 'js/libs/picturefill.js', array(), PICTUREFILL_JS_VERSION, true);
      }else{
        wp_register_script('picturefill', PICTUREFILL_WP_URL . 'js/libs/picturefill.min.js', array(), PICTUREFILL_JS_VERSION, true);
      }
    }

    /**
     * Replace the images in the passed HTML with processed img tags
     * that include srcset and sizes attributes.
     *
     * @since  2.0.0
     * @param  string $html An HTML string
     * @param  bool   $cache By default defers to WP_DEBUG
     * @return string The processed HTML
     */
    public function picturefill_wp_apply_to_html($html, $cache = null){
      if(!isset($cache)){
        $cache = !WP_DEBUG;
      }

      if(apply_filters('picturefill_wp_cache', $cache)){
        return $this->cache_picturefill_output($html);
      }else{
        return $this->replace_images($html);
      }
    }

    /**
     * Return the cached output for the passed HTML if it is available.
     *
     * @since  1.1.3
     * @param  string $html An HTML string
     * @return string The cached output if it is available, the processed HTML if not
     */
    public function cache_picturefill_output($html){
      $html_hash = md5($html);
      $cache_duration = apply_filters('picturefill_wp_cache_duration', 86400);
      $cached_output = get_transient('picturefill_wp_output_' . $html_hash);
      if(!empty($cached_output)){
        wp_enqueue_script('picturefill');
        return $cached_output;
      }else{
        $output = $this->replace_images($html);
        if($output !== $html){
          set_transient('picturefill_wp_output_' . $html_hash, $output, $cache_duration);
        }
        return $output;
      }
    }

    /**
     * Replace the img tags in the passed HTML with img tags
     * that include srcset and sizes.
     *
     * @since  1.1.1
     * @param  string $html An HTML string
     * @return string The processed HTML
     */
    public function replace_images($html){
      do_action('picturefill_wp_before_replace_images');
      require_once(PICTUREFILL_WP_PATH . 'inc/class-model-image-picturefill-wp.php');
      $DOMDocument = new DOMDocument();
      $images = Model_Application_Picturefill_WP::get_images($DOMDocument, $html);
      if($images->length > 0){
        require_once(PICTUREFILL_WP_PATH . 'inc/class-view-picturefill-wp.php');
        wp_enqueue_script('picturefill');
        $html = View_Picturefill_WP::standardize_img_tags($html);
        foreach($images as $image){
          if('picture' !== $image->parentNode->tagName && !$image->hasAttribute('data-picturefill-wp-ignore') && !$image->hasAttribute('srcset')){
            $model_image_picturefill_wp = new Model_Image_Picturefill_WP($this->model, $DOMDocument, $image);
            $view_picturefill_wp = new View_Picturefill_WP($model_image_picturefill_wp);

            $html = str_replace($view_picturefill_wp->get_original_image(), $view_picturefill_wp->render_template('image'), $html);
          }elseif($image->hasAttribute('srcset')){
            wp_enqueue_script('picturefill');
          }
        }
      }elseif(true === Model_Application_Picturefill_WP::syntax_present($DOMDocument, $html)){
        wp_enqueue_script('picturefill');
      }
      do_action('picturefill_wp_after_replace_images');
      return apply_filters('picturefill_wp_replace_images_output', $html);
    }

    /**
     * Register srcset
     *
     * @since  2.0.0
     * @param  string       $handle Unique identifier
     * @param  array        $srcset_array Array of URLs that make up the srcset
     * @param  string|array The image or sizes size to which the srcset should be applied
     * @return true on success
     */
    public function register_srcset($handle, $srcset_array, $attached = array()){
      if(is_string($attached)){
        return $this->model->register_srcset($handle, $srcset_array, array($attached));
      }
      return $this->model->register_srcset($handle, $srcset_array, $attached);
    }

    /**
     * Register sizes
     *
     * @since  2.0.0
     * @param  string       $handle Unique identifier
     * @param  string       $sizes_string A string for the sizes attribute
     * @param  string|array The image or sizes size to which the sizes attribute should be applied
     * @return true on success
     */
    public function register_sizes($handle, $sizes_string, $attached = array()){
      if(is_string($attached)){
        return $this->model->register_sizes($handle, $sizes_string, array($attached));
      }
      return $this->model->register_sizes($handle, $sizes_string, $attached);
    }

    /**
     * Add an action to be fired when the plugin is updated.
     *
     * @since 1.1.3
     */
    public function add_update_hook(){
      if(get_option('picturefill_wp_version') !== PICTUREFILL_WP_VERSION){
        do_action('picturefill_wp_updated');
        update_option('picturefill_wp_update_timestamp', time());
        update_option('picturefill_wp_version', PICTUREFILL_WP_VERSION);
      }
    }
  }
}
