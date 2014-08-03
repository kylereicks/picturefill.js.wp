<?php
defined('ABSPATH') OR exit;
/*
Plugin Name: Picturefill.WP 2
Plugin URI: http://github.com/kylereicks/picturefill.js.wp
Description: A wordpress plugin to load responsive/retina images via picturefill.js.
Author: Kyle Reicks
Version: 2.0.0
Author URI: http://github.com/kylereicks/
*/

define('PICTUREFILL_WP_PATH', plugin_dir_path(__FILE__));
define('PICTUREFILL_WP_URL', plugins_url('/', __FILE__));
define('PICTUREFILL_WP_VERSION', '2.0.0');
define('PICTUREFILL_JS_VERSION', '2.1.0');

require_once(PICTUREFILL_WP_PATH . 'inc/class-picturefill-wp.php');

require_once(PICTUREFILL_WP_PATH . 'inc/functions-picturefill-wp.php');

register_deactivation_hook(__FILE__, array('Picturefill_WP', 'deactivate'));

add_action('picturefill_wp_init', array('Picturefill_WP', 'init'));

do_action('picturefill_wp_init');
