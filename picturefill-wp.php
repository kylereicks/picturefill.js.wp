<?php
defined('ABSPATH') OR exit;
/*
Plugin Name: Picturefill.WP
Plugin URI: http://github.com/kylereicks/picturefill.js.wp
Description: A wordpress plugin to load responsive/retina images via picturefill.js.
Author: Kyle Reicks
Version: 1.3.1
Author URI: http://github.com/kylereicks/
*/

define('PICTUREFILL_WP_PATH', plugin_dir_path(__FILE__));
define('PICTUREFILL_WP_URL', plugins_url('/', __FILE__));
define('PICTUREFILL_WP_VERSION', '1.3.1');

require_once(PICTUREFILL_WP_PATH . 'inc/class-picturefill-wp.php');

require_once(PICTUREFILL_WP_PATH . 'inc/functions-picturefill-wp.php');

register_deactivation_hook(__FILE__, array('Picturefill_WP', 'deactivate'));

add_action('plugins_loaded', array('Picturefill_WP', 'get_instance'));
