<?php
/**
 * Helper functions for Picturefill.WP
 */
require_once(PICTUREFILL_WP_PATH . 'inc/class-picturefill-wp.php');

function picturefill_wp_disable_cache($priority = 11){
  remove_filter('the_content', array(Picturefill_WP::get_instance(), 'picturefill_wp_apply_to_html'), apply_filters('picturefill_wp_the_content_filter_priority', 11));
  add_filter('the_content', array(Picturefill_WP::get_instance(), 'replace_images'), $priority);
}

function picturefill_wp_apply_to_filter($filter, $cache = true, $priority = 11){
  if(true === $cache){
    add_filter($filter, array(Picturefill_WP::get_instance(), 'cache_picturefill_output'), $priority);
  }else{
    add_filter($filter, array(Picturefill_WP::get_instance(), 'replace_images'), $priority);
  }
}

function picturefill_wp_apply_to_html($html, $cache = true){
  if(true === $cache){
    return Picturefill_WP::get_instance()->cache_picturefill_output($html);
  }else{
    return Picturefill_WP::get_instance()->replace_images($html);
  }
}

function register_srcset($handle, $srcset_array, $attach_to){
  return Picturefill_WP::get_instance()->register_srcset($handle, $srcset_array, $attach_to);
}

function register_sizes($handle, $sizes_string, $attach_to){
  return Picturefill_WP::get_instance()->register_sizes($handle, $sizes_string, $attach_to);
}
