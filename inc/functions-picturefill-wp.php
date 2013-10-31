<?php
/**
 * Helper functions for Picturefill.WP
 */
require_once(PICTUREFILL_WP_PATH . 'inc/class-picturefill-wp.php');
require_once(PICTUREFILL_WP_PATH . 'inc/class-picturefill-wp-function-helpers.php');

function apply_picturefill_wp($filter, $cache = true){
  if(true === $cache){
    $picturefill_wp_helper = new Picturefill_WP_Function_Helper();
    $picturefill_wp_helper->apply_to_filter($filter);
  }else{
    add_filter($filter, array(Picturefill_WP::get_instance(), 'replace_images'), 11);
  }
}

function disable_picturefill_wp_cache(){
  remove_filter('the_content', array(Picturefill_WP::get_instance(), 'apply_picturefill_wp_to_the_content'), 11);
  add_filter('the_content', array(Picturefill_WP::get_instance(), 'replace_images'), 11);
}

function set_picturefill_wp_cache_duration($cache_duration_in_seconds){
  $picturefill_wp_helper = new Picturefill_WP_Function_Helper();
  $picturefill_wp_helper->set_cache_duration($cache_duration_in_seconds);
}

function picturefill_wp_retina_only(){
  add_filter('picturefill_wp_image_sizes', array('Picturefill_WP_Function_Helper', 'retina_only'), 10, 2);
  add_filter('picturefill_wp_media_query_breakpoint', array('Picturefill_WP_Function_Helper', 'remove_breakpoints'));
}
