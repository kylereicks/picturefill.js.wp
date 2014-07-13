<?php
/**
 * Helper functions for Picturefill.WP
 */
require_once(PICTUREFILL_WP_PATH . 'inc/class-picturefill-wp.php');

function picturefill_wp_apply_to_filter($filter){
  add_filter($filter, array(Picturefill_WP::get_instance(), 'picturefill_wp_apply_to_html'));
}

function picturefill_wp_apply_to_html($html, $cache = null){
  return Picturefill_WP::get_instance()->picturefill_wp_apply_to_html($html, $cache);
}

function picturefill_wp_register_srcset($handle, $srcset_array, $attach_to = null){
  return Picturefill_WP::get_instance()->register_srcset($handle, $srcset_array, $attach_to);
}

function picturefill_wp_register_sizes($handle, $sizes_string, $attach_to = null){
  return Picturefill_WP::get_instance()->register_sizes($handle, $sizes_string, $attach_to);
}
