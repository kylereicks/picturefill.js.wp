<?php
/*
Plugin Name: Picturefill.WP
Plugin URI: http://github.com/kylereicks/picturefill.js.wp
Description: A wordpress plugin to load images via picturefill.js.
Author: Kyle Reicks
Version: 0.1
Author URI: http://kylereicks.me
*/

if(!class_exists('Picturefill_WP')){
  class Picturefill_WP{

    function __construct(){
      require_once(ABSPATH . 'wp-admin/includes/image.php');
      add_action('wp_enqueue_scripts', array($this, 'picturefill_scripts'));
      add_action('init', array($this, 'add_image_sizes'));
      add_filter('the_content', array($this, 'replace_images'), 999);
    }

    function picturefill_scripts(){
      wp_register_script('picturefill', plugins_url('js/libs/picturefill.min.js', __FILE__), array(), false, true);
    }

    function replace_images($html){
      global $_wp_additional_image_sizes;
      $content = new DOMDocument();
      $content->loadHTML($html);
      $images = $content->getElementsByTagName('img');
      if($images->length > 0){
        wp_enqueue_script('picturefill');
        foreach($images as $image){
          $original_image = $content->saveHTML($image);
          $original_image = substr($original_image, 0, strlen($original_image) - 1) . ' />';
          $src = $image->getAttribute('src');
          $alt = $image->getAttribute('alt');
          $title = $image->getAttribute('title');
          $class = $image->getAttribute('class');
          $id = $image->getAttribute('id');
          $width = $image->getAttribute('width');
          $height = $image->getAttribute('height');

          preg_match('/(?:size-)(\w+)/', $class, $size);
          preg_match('/(?:wp-image-)(\w+)/', $class, $attachment_id);

          $picture = '<div data-picture';
          $picture .= !empty($alt) ? ' data-alt="' . $alt . '"' : '';
          $picture .= !empty($title) ? ' title="' . $title . '"' : '';
          $picture .= !empty($id) ? ' id="' . $id . '"' : '';
          $picture .= !empty($class) ? ' class="' . $class . '"' : '';
          $picture .= !empty($width) ? ' width="' . $width . '"' : '';
          $picture .= !empty($height) ? ' height="' . $height . '"' : '';
          $picture .= '>';

          $picture .= '<div data-src="' . $src . '"></div>';

          if(!empty($size) && !empty($attachment_id) && in_array($size[1] . 'x2', get_intermediate_image_sizes())){
            $image_attachment_data = array(
              'original' => wp_get_attachment_image_src($attachment_id[1], 'full'),
              $size[1] . 'x2' => wp_get_attachment_image_src($attachment_id[1], $size[1] . 'x2')
            );

            if($image_attachment_data['original'][0] === $image_attachment_data[$size[1] . 'x2'][0] && $image_attachment_data['original'][1] > $image_attachment_data[$size[1] . 'x2'][1] && $image_attachment_data['original'][2] > $image_attachment_data[$size[1] . 'x2'][2]){
              $new_meta_data = wp_generate_attachment_metadata($attachment_id[1], get_attached_file($attachment_id[1]));
              wp_update_attachment_metadata($attachment_id[1], $new_meta_data);
              $image_attachment_data[$size[1] . 'x2'] = wp_get_attachment_image_src($attachment_id[1], $size[1] . 'x2');
            }

            if($image_attachment_data !== false){
              $picture .= '<div data-src="' . $image_attachment_data[$size[1] . 'x2'][0] . '" data-media="min-device-pixel-ratio:2.0"></div>';
            }
          }

          $picture .= '<noscript>' . $original_image . '</noscript>';
          $picture .= '</div>';

          $html = str_replace($original_image, $picture, $html);
        }
      }
        return $html;
    }

    function add_image_sizes(){
      add_image_size('thumbnailx2', get_option('thumbnail_size_w') * 2, get_option('thumbnail_size_h') * 2, get_option('thumbnail_crop'));
      add_image_size('mediumx2', get_option('medium_size_w') * 2, get_option('medium_size_h') * 2, get_option('medium_crop'));
      add_image_size('largex2', get_option('large_size_w') * 2, get_option('large_size_h') * 2, get_option('large_crop'));
    }
  }
  $picturefill_wp = new Picturefill_WP();
}
