Picturefill.wp
==============

A Wordpress plugin to use picturefill.js to load responsive/retina images, mimicking the proposed HTML5 picture spec.

This plugin uses and adapted version of [picturefill.js](https://github.com/scottjehl/picturefill) that adds additional attributes such as `id`, `class`, and `title`.

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

The responsiveness of an image can be limited by adding the class `min-size-{image size}`. For example, an image with the class `min-size-medium` will not load an image smaller than size `medium`.

###Caching

To improve performance, especially in image heavy posts, the output of Picturefill.WP is cached with WordPress transients after it is generated. The cache will be refreshed automatically every time a post is updated or Picturefill.WP is updated. The cache can be manually refreshed by deactivating and reactivating Picturefill.WP from the plugins menu.

If you suspect that Picturefill.WP's caching is causing trouble with another plugin or theme feature, first try deactivating and reactivating Picturefill.WP. If problems persist, try lowering the priority for Picturefill.WP to be executed by adding the following to your functions.php file:

```php
remove_filter('the_content', array(Picturefill_WP::get_instance(), 'apply_picturefill_wp_to_the_content'), 11);
add_filter('the_content', array(Picturefill_WP::get_instance(), 'apply_picturefill_wp_to_the_content'), 9999);
```

If you still encounter problems with other plugins or theme features, you may want to disable caching all together. See the subsection on how to disable caching under the "Extending Picturefill.WP" section.

###Errors and Warnings

When using Picturefill.WP with your website, you may occasionally notice a warning very much like the following:

```php
[Mon Jan 01 12:00:00 2000] [error] [client 999.999.99.99] PHP Warning: DOMDocument::loadHTML() [domdocument.loadhtml]: Unexpected end tag : a in Entity, line: 17 in /server/www/wp-content/plugins/picturefill/inc/class-model-picturefill-wp.php on line 20
```

This error indicates improperly formed HTML. In this case, the `Unexpected end tag : a` comes from nested links. If you are seeing errors like this on your WordPress site, you may want to consider implementing an [error logging](http://codex.wordpress.org/Editing_wp-config.php#Configure_Error_Logging) system, or alternatively suppressing errors by adding `error_reporting(0);` and `@ini_set('display_errors', 0);` to `wp-config.php`.

Additionally, the PHP DOM parser `DOMDocument` often has trouble with HTML5 elements and may throw an error if your post includes a `<canvas>` element, or a `<section>` element, for example. All the more reason to implement an error handling system on production sites.

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

* [Apply Picturefill.WP outside `the_content`](https://github.com/kylereicks/picturefill.js.wp#apply-picturefill_wp-outside-the_content)
* [To disable caching](https://github.com/kylereicks/picturefill.js.wp#to-disable-caching)
* [To cache for 100 years (give or take a day or so, depending on when those 100 years land)](https://github.com/kylereicks/picturefill.js.wp#to-cache-for-100-years-give-or-take-a-day-or-so-depending-on-when-those-100-years-land)
* [Retina only: Disable browser width responsiveness](https://github.com/kylereicks/picturefill.js.wp#retina-only-disable-browser-width-responsiveness)
* [Respond to custom image sizes](https://github.com/kylereicks/picturefill.js.wp#respond-to-custom-image-sizes)
* [Remove the 20px buffer from the media query breakpoints](https://github.com/kylereicks/picturefill.js.wp#remove-the-20px-buffer-from-the-media-query-breakpoints)
* [Using Picturefill.WP to load post-thumbnails in a theme](https://github.com/kylereicks/picturefill.js.wp#using-picturefillwp-to-load-post-thumbnails-in-a-theme)
* [Using Picturefill.WP with the Advanced Custom Fields Plugin](https://github.com/kylereicks/picturefill.js.wp#using-picturefillwp-with-the-advanced-custom-fields-plugin)

####Apply Picturefill.WP outside `the_content`

To apply Picturefill.WP outside of `the_content`, call the `cache_picturefill_output` on the desired filter. See the following example.

```php
add_filter('filter', 'theme_function_to_apply_picturefill_wp_to_filter');

function theme_function_to_apply_picturefill_wp_to_filter($content){
  return Picturefill_WP::get_instance()->cache_picturefill_output($content, 'name_of_the_filter');
}
```

####To disable caching

```php
remove_filter('the_content', array(Picturefill_WP::get_instance(), 'apply_picturefill_wp_to_the_content'), 11);
add_filter('the_content', array(Picturefill_WP::get_instance(), 'replace_images'), 11);
```

####To cache for 100 years (give or take a day or so, depending on when those 100 years land)

```php
add_filter('picturefill_wp_cache_duration', 'cache_for_one_hundred_years');

function cache_for_one_hundred_years($cache_duration){
  return (100 * 365 + 24) * 24 * 60 * 60;
}
```

####Retina only: Disable browser width responsiveness

```php
add_filter('picturefill_wp_image_sizes', 'theme_picturefill_retina_only', 10, 2);
add_filter('picturefill_wp_media_query_breakpoint', 'theme_picturefill_remove_breakpoints');

function theme_picturefill_retina_only($default_image_sizes, $image_attributes){
  return array(
    $image_attributes['size'][1],
    $image_attributes['size'][1] . '@2x'
  );
}

function theme_picturefill_remove_breakpoints($breakpoint){
  return 1;
}
```

####Respond to custom image sizes

As an example, let's say that your theme uses a very small thumbnail image size (maybe 16px by 16px) for a custom image gallery. This gallery probably looks great, but 16px may be too small for the images in your posts resond down to on a mobile device. The following example replaces the thumbnail with a new image size when responding to browser width.

```php
// Remove thumbnail from responsive image list
add_filter('picturefill_wp_image_sizes', 'theme_remove_thumbnail_from_responsive_image_list', 11, 2);

function theme_remove_thumbnail_from_responsive_image_list($image_sizes, $image_attributes){
  return array_diff($image_sizes, array('thumbnail', 'thumbnail@2x'));
}

// Add a new small image size for an image to respnd to, in the place of the thmbnail
add_action('init', 'theme_add_new_small_image_size');

function theme_add_new_small_image_size(){
  add_image_size('new_small_size', 320, 480);
  add_image_size('new_small_size@2x', 640, 960);
}

// Make sure Picturefill.WP has the attachment data for the new image size
add_filter('picturefill_wp_image_attachment_data', 'theme_picturefill_new_small_size_attachment_data', 10, 2);

function theme_picturefill_new_small_size_attachment_data($attachment_data, $attachment_id){
 $new_small_size_data = array(
   'new_small_size' => wp_get_attachment_image_src($attachment_id, 'new_small_size'),
   'new_small_size@2x' => wp_get_attachment_image_src($attachment_id, 'new_small_size@2x')
 );
 return array_merge($attachment_data, $new_small_size_data);
}

// Add the new image size to the responsive queue
add_filter('picturefill_wp_image_sizes', 'theme_add_new_small_size_to_responsive_image_list', 11, 2);

function theme_add_new_small_size_to_responsive_image_list($image_sizes, $image_attributes){
  if(!in_array($image_attributes['min_size'], array('medium', 'large', 'full'))){
    return array_merge(array('new_small_size', 'new_small_size@2x'), $image_sizes);
  }else{
    return $image_sizes;
  }
}

// Set the breakpoint for the new image as the new smallest size
add_filter('picturefill_wp_media_query_breakpoint', 'theme_picturefill_new_small_size_breakpoint', 10, 3);

function theme_picturefill_new_small_size_breakpoint($breakpoint, $image_size, $width){
  return in_array($image_size, array('new_small_size', 'new_small_size@2x')) ? 1 : $breakpoint;
}
```

####Remove the 20px buffer from the media query breakpoints

```php
add_filter('picturefill_wp_media_query_breakpoint', 'remove_picturefill_wp_breakpoint_buffer', 10, 3);

function remove_picturefill_wp_breakpoint_buffer($breakpoint, $image_size, $width){
  if('thumbnail' !== $image_size){
    return $width;
  }else{
    return $breakpoint;
  }
}
```

####Using Picturefill.WP to load post-thumbnails in a theme

The following assumes that both `add_theme_support('post-thumbnails')` and `set_post_thumbnail_size()` have been added and set.

```php
add_action('init', 'add_retina_post_thumbnail');
add_filter('post_thumbnail_html', 'theme_picturefill_post_thumbnail', 10, 5);
add_filter('post_thumbnail_html', 'add_size_to_post_thumbnail_class', 9, 5);

function add_size_to_post_thumbnail_class($html, $post_id, $post_thumbnail_id, $size, $attr){
  return preg_replace('/class="([^"]+)"/', 'class="$1 size-' . $size . '"', $html);
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

####Using Picturefill.WP with the [Advanced Custom Fields Plugin](http://wordpress.org/plugins/advanced-custom-fields/)

If you use [Advanced Custom Fields shortcodes](http://www.advancedcustomfields.com/resources/functions/shortcode/) in your post or page content, Picturefill.WP will work automatically. To use Advanced Custom Fields outside of `the_content` in theme files, apply Picturefill.WP to the `acf/format_value_for_api` filter.

```php
add_filter('acf/format_value_for_api', 'theme_function_picturefill_for_acf', 11, 3);

function theme_function_picturefill_for_acf($content, $post_id, $field){
  if(in_array($field['type'], array('textarea', 'wysiwyg', text))){
    return Picturefill_WP::get_instance()->cache_picturefill_output($content, $field['name']);
  }else{
    return $content;
  }
}
```

For image fields, you will need to wrap the image output in a custom filter.

In your theme file:
```php
<?php
$image_object = get_field('image');
$image_output = '<img src="' . $image_object['sizes']['medium'] . '" title="' . $image_object['title'] . '" alt="' . $image_object['alt'] . '" />';
echo apply_filters('theme_acf_image', $image_output, 'name_of_the_image_field');
?>
```

In functions.php:
```php
add_filter('theme_acf_image', 'theme_function_for_acf_image', 10, 2);

function theme_function_for_acf_image($content, $name_of_the_image_field){
  return Picturefill_WP::get_instance()->cache_picturefill_output($content, $name_of_the_image_field);
}
```
