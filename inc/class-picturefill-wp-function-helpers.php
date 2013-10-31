<?php
if(!class_exists('Picturefill_WP_Function_Helpers')){
  class Picturefill_WP_Function_Helpers{

    private $filter = '';
    private $cache_duration = 86400;

    public static function retina_only($default_image_sizes, $image_attributes){
      if('full' === $image_attributes['size'][1]){
        return array($image_attributes['size'][1]);
      }else{
        return array(
          $image_attributes['size'][1],
          $image_attributes['size'][1] . '@2x'
        );
      }
    }

    public static function remove_breakpoints($breakpoint){
      return 1;
    }

    public function apply_to_filter($filter){
      $this->filter = $filter;
      add_filter($filter, array($this, '_apply_picturefill_wp_to_filter'));
    }

    public function set_cache_duration($cache_duration){
      $this->cache_duration = $cache_duration;
      add_filter('picturefill_wp_cache_duration', array($this, '_set_cache_duration'));

    }

    public function _apply_picturefill_wp_to_filter($content){
      return Picturefill_WP::get_instance()->cache_picturefill_output($content, $this->filter);
    }

    public function _set_cache_duration($old_cache_duration){
      return $this->cache_duration;
    }
  }
}
