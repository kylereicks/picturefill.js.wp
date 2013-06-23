Picturefill.wp
==============

A Wordpress plugin to use picturefill.js to load responsive/retina images, mimicking the proposed HTML5 picture spec.

This plugin uses and adapted version of [picturefill.js](https://github.com/scottjehl/picturefill) that uses `span` instead of `div` and adds additional attributes such as `id`, `class`, and `title`.

Download
--------

The latest stable version of Picturefill.WP is available in the Wordpress.org plugin directory [http://wordpress.org/extend/plugins/picturefillwp/](http://wordpress.org/extend/plugins/picturefillwp/).

Details
-------

Picturefill.wp looks through `the_content` to find `<img>` elements like this:

```html
<img class="alignnone size-large wp-image-123" alt="Accessible alternate text for the image" title="A title that displays on hover" src="http://sitename.com/wp-content/uploads/2013/4/image-770x577.jpg" width="770" height="577" />
```

then replaces them with something like this:

```html
<span data-picture data-class="alignnone size-large wp-image-123" dat-alt="Accessible alternate text for the image" data-title="A title that displays on hover" data-width="770" data-height="577">
  <span data-src="http://sitename.com/wp-content/uploads/2013/4/image-770x577.jpg"></span>
  <span data-src="http://sitename.com/wp-content/uploads/2013/4/image-150x150.jpg" data-width="150" data-height="150" data-media="(min-width: 1px)" class="picturefill-wp-source thumbnail"></span>
  <span data-src="http://sitename.com/wp-content/uploads/2013/4/image-300x300.jpg" data-width="150" data-height="150" data-media="(min-width: 1px) and (-webkit-min-device-pixel-ratio: 1.5),(min-resolution: 144dpi),(min-resolution: 1.5dppx)" class="picturefill-wp-source retina thumbnail"></span>
  <span data-src="http://sitename.com/wp-content/uploads/2013/4/image-400x300.jpg" data-width="400" data-height="300" data-media="(min-width: 420px)" class="picturefill-wp-source medium"></span>
  <span data-src="http://sitename.com/wp-content/uploads/2013/4/image-800x600.jpg" data-width="400" data-height="300" data-media="(min-width: 420px) and (-webkit-min-device-pixel-ratio: 1.5),(min-resolution: 144dpi),(min-resolution: 1.5dppx)" class="picturefill-wp-source retina medium"></span>
  <span data-src="http://sitename.com/wp-content/uploads/2013/4/image-770x577.jpg" data-width="770" data-height="577" data-media="(min-width: 790px)" class="picturefill-wp-source large"></span>
  <span data-src="http://sitename.com/wp-content/uploads/2013/4/image-1540x1155.jpg" data-width="770" data-height="577" data-media="(min-width: 790px) and (-webkit-min-device-pixel-ratio: 1.5),(min-resolution: 144dpi),(min-resolution: 1.5dppx)" class="picturefill-wp-source retina large"></span>
  <noscript>
    <img class="alignnone size-large wp-image-123" alt="Accessible alternate text for the image" title="A title that displays on hover" src="http://sitename.com/wp-content/uploads/2013/4/image-770x577.jpg" width="770" height="577" />
  </noscript>
</span>
```

The adapted version of picturefill.js then looks for the last `data-src` listed where the associated `data-media` matches the device and browser, and loads the appropriate image inside the matched `<span>` element.

###Heights and Widths and Breakpoints

One of the goals of this plugin is to be completely "plug and play" i.e. no setup and no options. Just turn it on and it works. To do this, the plugin relies on several Wordpress defaults and conventions.

####Wordpress Image Sizes

By default, Wordpress creates as many as 3 images of different sizes for each uploaded image ("large", "medium", and "thumbnail"), in addition to the "full" image size.

This plugin adds responsive breakpoints based on the width of the image. The largest available image will display unless the browser width is less than the image width + 20px, in which case the next size down is displayed.

To use this plugin most effectively, set the default image sizes ("large", "medium", and "thumbnail") to reflect useful breakpoints in your theme design.

####Wordpress Image Classes

This plugin uses the default Wordpress image class `wp-image-{image id}` as a source of information. It will not work effectively if this class is removed. The original image will still be loaded, but it will not be responsive.

The responsiveness of an image can be limited by adding the class `min-size-{image size}`. For example, an image with the class `min-size-medium` will not load an image smaller than size `medium`.

Extending Picturefill.WP
------------------------

Like many WordPress themes and plugins, Picturefill.WP can be altered and extended with action and and filter hooks.

###Actions

* `picturefill_wp_updated`
* `picturefill_wp_before_replace_images`
* `picturefill_wp_after_replace_images`

###Filters

* `picturefill_wp_image_attributes`
* `picturefill_wp_image_attachment_data`
* `picturefill_wp_image_sizes`
* `picturefill_wp_source_list`
* `picturefill_wp_picture_attribute_string`
* `picturefill_wp_media_query_breakpoint`
* `picturefill_wp_media_query_resolution_query`
* `picturefill_wp_template_path`
* `picturefill_wp_{$template}_template_data`
* `picturefill_wp_{$template}_template`
* `picturefill_wp_the_content_output`
* `picturefill_wp_cache_duration`

###Examples

The following are examples of how Picturefill.WP can be extended from a theme's `functions.php` file.

####To disable caching

```php
remove_filter('the_content', array(Picturefill_WP::get_instance(), 'cache_picturefill_output'), 11);
add_filter('the_content', array(Picturefill_WP::get_instance(), 'replace_images'), 11);
```

####Using Picturefill.WP to load post-thumbnails in a theme

The following assumes that both `add_theme_support('post-thumbnails')` and `set_post_thumbnail_size()` have been added and set.

```php
add_action('init', 'add_retina_post_thumbnail');
add_filter('post_thumbnail_html', 'theme_picturefill_post_thumbnail', 10, 5);
add_filter('post_thumbnail_html', 'add_attachment_id_to_post_thumbnail_class', 9, 5);

function add_attachment_id_to_post_thumbnail_class($html, $post_id, $post_thumbnail_id, $size, $attr){
  return preg_replace('/class="([^"]+)"/', 'class="$1 size-' . $size . ' wp-image-' . $post_thumbnail_id . '"',$html);
}

function add_retina_post_thumbnail(){
  global $_wp_additional_image_sizes;
  add_image_size('post-thumbnail@2x', $_wp_additional_image_sizes['post-thumbnail']['width'] * 2, $_wp_additional_image_sizes['post-thumbnail']['height'] * 2, $_wp_additional_image_sizes['post-thumbnail']['crop']);
}

function theme_picturefill_post_thumbnail($html, $post_id, $post_thumbnail_id, $size, $attr){
  add_filter('picturefill_wp_image_attachment_data', 'theme_picturefill_post_thumbnail_attachment_data', 10, 2);
  add_filter('picturefill_wp_image_sizes', 'theme_picturefill_post_thumbnail_sizes', 10, 2);
  add_filter('picturefill_wp_media_query_breakpoint', 'theme_picturefill_post_thumbnail_breakpoint', 10, 3);
  return Picturefill_WP::get_instance()->cache_picturefill_output($html, 'post_thumbnail');
}

function theme_picturefill_post_thumbnail_attachment_data($initial_array, $attachment_id){
  $post_thumbnail_data = array(
    'post-thumbnail' => wp_get_attachment_image_src($attachment_id, 'post-thumbnail'),
    'post-thumbnail@2x' => wp_get_attachment_image_src($attachment_id, 'post-thumbnail@2x')
  );
  return array_merge($initial_array, $post_thumbnail_data);
}

function theme_picturefill_post_thumbnail_sizes($default_image_sizes, $image_attributes){
  return 'post-thumbnail' === $image_attributes['size'][1] ? array(
    'post-thumbnail',
    'post-thumbnail@2x'
  ) : $default_image_sizes;
}

function theme_picturefill_post_thumbnail_breakpoint($breakpoint, $image_size, $width){
  return 'post-thumbnail' === $image_size ? 1 : $breakpoint;
}
```
