<?php
if(!class_exists('Picturefill_WP_Function_Helpers')){
  class Picturefill_WP_Function_Helpers{

    private $filter = '';
    private $cache_duration = 86400;

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
