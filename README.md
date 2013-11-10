Picturefill.wp
==============

A Wordpress plugin to use picturefill.js to load responsive/retina images, mimicking the proposed HTML5 picture spec.

This plugin uses an adapted version of [picturefill.js](https://github.com/scottjehl/picturefill) that adds additional attributes such as `id`, `class`, and `title`.

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

* `picturefill_wp_the_content_filter_priority`
* `picturefill_wp_content_html`
* `picturefill_wp_image_attributes`
* `picturefill_wp_image_attachment_data`
* `picturefill_wp_image_sizes`
* `picturefill_wp_source_list`
* `picturefill_wp_picture_attribute_string`
* `picturefill_wp_media_query_breakpoint`
* `picturefill_wp_media_query_resolution_query`
* `picturefill_wp_template_path`
* `picturefill_wp_template_path`
* `picturefill_wp_{$template}_template_file_path`
* `picturefill_wp_{$template}_template_data`
* `picturefill_wp_{$template}_template`
* `picturefill_wp_the_content_output`
* `picturefill_wp_cache_duration`


###Helper Functions

####apply_picturefill_wp($filter, [$cache, $priority])

####disable_picturefill_wp_cache([$priority])

####set_picturefill_wp_cache_duration($cache_duration_in_seconds)

####picturefill_wp_retina_only()

####picturefill_wp_remove_image_from_responsive_list($image_size)

####picturefill_wp_add_image_size($name, $width, $height, $crop, $insert_before)

####apply_picturefill_wp_to_post_thumbnail()

####minimize_picturefill_wp_output()


###Using Picturefill.WP with the [Advanced Custom Fields Plugin](http://wordpress.org/plugins/advanced-custom-fields/)

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
