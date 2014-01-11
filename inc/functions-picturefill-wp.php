<?php
/**
 * Helper functions for Picturefill.WP
 */
require_once(PICTUREFILL_WP_PATH . 'inc/class-picturefill-wp.php');
require_once(PICTUREFILL_WP_PATH . 'inc/class-picturefill-wp-function-helpers.php');

function apply_picturefill_wp($filter, $cache = true, $priority = 11){
  if(true === $cache){
    $picturefill_wp_helpers = new Picturefill_WP_Function_Helpers();
    $picturefill_wp_helpers->apply_to_filter($filter);
  }else{
    add_filter($filter, array(Picturefill_WP::get_instance(), 'replace_images'), $priority);
  }
}

function disable_picturefill_wp_cache($priority = 11){
  remove_filter('the_content', array(Picturefill_WP::get_instance(), 'apply_picturefill_wp_to_the_content'), apply_filters('picturefill_wp_the_content_filter_priority', 11));
  add_filter('the_content', array(Picturefill_WP::get_instance(), 'replace_images'), $priority);
}

function set_picturefill_wp_cache_duration($cache_duration_in_seconds){
  $picturefill_wp_helpers = new Picturefill_WP_Function_Helpers();
  $picturefill_wp_helpers->set_cache_duration($cache_duration_in_seconds);
}

function picturefill_wp_retina_only(){
  add_filter('picturefill_wp_image_sizes', array('Picturefill_WP_Function_Helpers', 'retina_only'), 10, 2);
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

function picturefill_wp_set_responsive_image_sizes($image_size_array){
  $picturefill_wp_helpers = new Picturefill_WP_Function_Helpers();
  $picturefill_wp_helpers->set_responsive_image_sizes($image_size_array);
}

function apply_picturefill_wp_to_post_thumbnail(){
  $picturefill_wp_helpers = new Picturefill_WP_Function_Helpers();
  $picturefill_wp_helpers->apply_to_post_thumbnail();
}

function minimize_picturefill_wp_output(){
  add_filter('picturefill_wp_picture_template_file_path', array('Picturefill_WP_Function_Helpers', 'min_template'), 10, 3);
  add_filter('picturefill_wp_source_template_file_path', array('Picturefill_WP_Function_Helpers', 'min_template'), 10, 3);
  add_filter('picturefill_wp_picture_template', array('Picturefill_WP_Function_Helpers', 'remove_line_breaks'), 10, 2);
  add_filter('picturefill_wp_image_sizes', array('Picturefill_WP_Function_Helpers', 'retina_only'), 10, 2);
}

function picturefill_wp_exclude_post_type($post_type){
  $picturefill_wp_helpers = new Picturefill_WP_Function_Helpers();
  $picturefill_wp_helpers->post_type_to_exclude = $post_type;

  add_action('the_post', array($picturefill_wp_helpers, 'exclude_post_type'));
}

function picturefill_wp_exclude_post_id($post_id){
  $picturefill_wp_helpers = new Picturefill_WP_Function_Helpers();
  $picturefill_wp_helpers->post_id_to_exclude = $post_id;

  add_action('the_post', array($picturefill_wp_helpers, 'exclude_post_id'));
}

function picturefill_wp_exclude_post_slug($post_slug){
  $picturefill_wp_helpers = new Picturefill_WP_Function_Helpers();
  $picturefill_wp_helpers->post_slug_to_exclude = $post_slug;

  add_action('the_post', array($picturefill_wp_helpers, 'exclude_post_slug'));
}

function picturefill_wp_exclude_post_tag($post_tag){
  $picturefill_wp_helpers = new Picturefill_WP_Function_Helpers();
  $picturefill_wp_helpers->post_tag_to_exclude = $post_tag;

  add_action('the_post', array($picturefill_wp_helpers, 'exclude_post_tag'));
}

function picturefill_wp_exclude_post_category($post_category){
  $picturefill_wp_helpers = new Picturefill_WP_Function_Helpers();
  $picturefill_wp_helpers->post_category_to_exclude = $post_category;

  add_action('the_post', array($picturefill_wp_helpers, 'exclude_post_category'));
}
