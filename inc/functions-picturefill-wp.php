<?php
/**
 * Helper functions for Picturefill.WP
 */
require_once(PICTUREFILL_WP_PATH . 'inc/class-picturefill-wp.php');
require_once(PICTUREFILL_WP_PATH . 'inc/class-picturefill-wp-function-helpers.php');

function apply_picturefill_wp($filter, $cache = true){
  if(true === $cache){
    $picturefill_wp_helpers = new Picturefill_WP_Function_Helpers();
    $picturefill_wp_helpers->apply_to_filter($filter);
  }else{
    add_filter($filter, array(Picturefill_WP::get_instance(), 'replace_images'), 11);
  }
}

function disable_picturefill_wp_cache(){
  remove_filter('the_content', array(Picturefill_WP::get_instance(), 'apply_picturefill_wp_to_the_content'), 11);
  add_filter('the_content', array(Picturefill_WP::get_instance(), 'replace_images'), 11);
}

function set_picturefill_wp_cache_duration($cache_duration_in_seconds){
  $picturefill_wp_helpers = new Picturefill_WP_Function_Helpers();
  $picturefill_wp_helpers->set_cache_duration($cache_duration_in_seconds);
}

function picturefill_wp_retina_only(){
  add_filter('picturefill_wp_image_sizes', array('Picturefill_WP_Function_Helpers', 'retina_only'), 10, 2);
  add_filter('picturefill_wp_media_query_breakpoint', array('Picturefill_WP_Function_Helpers', 'remove_breakpoints'));
}

function picturefill_wp_remove_image_from_responsive_list($image_size){
  $picturefill_wp_helpers = new Picturefill_WP_Function_Helpers();
  $picturefill_wp_helpers->remove_image_from_responsive_list($image_size);
}

function picturefill_wp_add_image_size($name, $width = 0, $height = 0, $crop = false, $insert_before = 'thumbnail'){
  if('@2x' === substr($name, -3)){
    return false;
  }
  add_image_size($name, $width, $height, $crop);
  add_image_size($name . '@2x', $width * 2, $height * 2, $crop);

  $picturefill_wp_helpers = new Picturefill_WP_Function_Helpers();
  $picturefill_wp_helpers->add_image_to_responsive_queue($image_size, $insert_before);
}
