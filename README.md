**Important! The functions, actions, and filters used to extend and customize Picturefill.WP 1 (Picturefill.WP 1.3.x) are not compatible with Picturefill.WP 2 (Picturefill.WP 2.0 and above).**

Picturefill.WP 2
================

Picturefill.WP 2 is a simple and option-less plugin to serve HDPI and responsive images on a WordPress website.

This plugin parses post and page content replacing images with the syntax for the HTML5 `picture` element, then uses [picturefill.js](https://github.com/scottjehl/picturefill) to pollyfill the functionality on unsuported browsers.

Download
--------

The latest stable version of Picturefill.WP is available in the Wordpress.org plugin directory [http://wordpress.org/extend/plugins/picturefillwp/](http://wordpress.org/extend/plugins/picturefillwp/).

Details
-------

Picturefill.wp looks through `the_content` to find `<img>` elements like this:

```html
<img class="alignnone size-large wp-image-123"
  alt="Accessible alternate text for the image"
  title="A title that displays on hover"
  src="http://sitename.com/wp-content/uploads/2013/4/image-700x525.jpg"
  width="700" height="525" />
```

then replaces them with something like this:

```html
<img alt="Accessible alternate text for the image"
  title="A title that displays on hover"
  class="alignnone size-large wp-image-123"
  width="700" height="525"
  sizes="(max-width: 700px) 100vw, 700px"
  srcset="http://sitename.com/wp-content/uploads/2013/04/image-150x150.jpg 150w, http://sitename.com/wp-content/uploads/2013/04/image-300x225.jpg 300w, http://sitename.com/wp-content/uploads/2013/04/image-700x525.jpg 700w, http://sitename.com/wp-content/uploads/2013/04/image.jpg 2048w" />
```

One of the goals of this plugin is to be completely "plug and play" i.e. no setup and no options. Just turn it on and it works. To do this, the plugin relies on several Wordpress defaults and conventions.

###Wordpress Image Sizes

By default, Wordpress creates as many as 3 images of different sizes for each uploaded image ("large", "medium", and "thumbnail"), in addition to the "full" image size.

By default, the plugin lists all of these default sizes in the srcset and instructs the browser to serve up the apropreate image based on browser window width and screen resolution, not exceeding the original image width. 

To use this plugin most effectively, set the default image sizes ("large", "medium", and "thumbnail") to reflect useful breakpoints in your theme design.

###Setting srcsets and sizes

The srcset and sizes attributes can be set a few different ways. These attributes can be set manually in the edditor. If the plugin sees the srcset attribute already in an `<img>` tag, it will ignore that image and simply enqueue the picturefill.js script. If the sizes attribute is already set in an `<img>` tag, but the srcset is not present, the plugin will add the srcset registered for that image size but leave the sizes attribute alone.

New srcsets can be registered via the `picturefill_wp_register_srcset` function. Likewise, new sizes attributes can be registered via the `picturefill_wp_register_sizes` function. See Extending and Customizing Picturefill.WP for more detaill on these functions.

Once a srcset or sizes attribute is registered, it will apply to the image sizes to wich it is attached; however, it can be overridden with a image's class attribute. `srcset-{srcset-handle}` applies the "srcset-handle" srcset to the image, and `sizes-{sizes-handle}` applies the "sizes-handle" sizes attribute to the image.

For example, in the following image the srcset attribute is set with the `srcset-` class, and the sizes attribute is set manually.

```html
<img class="alignnone size-large wp-image-123 srcset-special-theme-srcset"
  sizes="(max-width: 30em) 100vw, (max-width: 50em) 50vw, calc(33vw - 100px)"
  alt="Accessible alternate text for the image"
  title="A title that displays on hover"
  src="http://sitename.com/wp-content/uploads/2013/4/image-700x525.jpg"
  width="700" height="525" />
```

###Caching

To improve performance, especially in image heavy posts, the output of Picturefill.WP 2 is cached with WordPress transients after it is generated. The cache will be refreshed automatically every time a post is updated or Picturefill.WP 2 is updated. The cache can be manually refreshed by deactivating and reactivating Picturefill.WP from the plugins menu.

Caching is disabled when `WP_DEBUG` is set to true, and can be disabled manually with the helper function `picturefill_wp_disable_cache`.

###Errors and Warnings

Picturefill.WP 2 suppresses errors and warnings in parsing the DOM. Errors and warnings can now be collected via the `picturefill_wp_syntax_present_libxml_errors` and `picturefill_wp_get_images_libxml_errors` filters.

```php
add_filter('picturefill_wp_get_images_libxml_errors', 'handle_errors');

function handle_errors($errors){
  foreach($errors as $error){
    // Handle errors here.
  }
}
```

Extending and Customizing Picturefill.WP
------------------------

###Functions

Picturefill.WP 2 includes a number of functions to simplify common customizations.

For extra safety, it's a good idea to wrap your code that targets Picturefill.WP in a conditional statement so it will only run if the plugin is active.

```php
if(defined('PICTUREFILL_WP_VERSION')){
  // Add Picturefill.WP specific code here.
}
```

###Hooks

Like many WordPress themes and plugins, Picturefill.WP can be altered and extended with action and and filter hooks.

####Actions

* `picturefill_wp_updated`
* `picturefill_wp_before_replace_images`
* `picturefill_wp_after_replace_images`

####Filters

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
* `picturefill_wp_{$template}_template_file_path`
* `picturefill_wp_{$template}_template_data`
* `picturefill_wp_{$template}_template`
* `picturefill_wp_the_content_output`
* `picturefill_wp_cache_duration`
* `picturefill_wp_syntax_present_libxml_errors`
* `picturefill_wp_get_images_libxml_errors`


Use With Other Plugins
----------------------

###Using Picturefill.WP with the [Advanced Custom Fields Plugin](http://wordpress.org/plugins/advanced-custom-fields/)

If you use [Advanced Custom Fields shortcodes](http://www.advancedcustomfields.com/resources/functions/shortcode/) in your post or page content, Picturefill.WP will work automatically. To use Advanced Custom Fields outside of `the_content` in theme files, apply Picturefill.WP to the `acf/format_value_for_api` filter.

```php
add_filter('acf/format_value_for_api', 'theme_function_picturefill_for_acf', 11, 3);

function theme_function_picturefill_for_acf($content, $post_id, $field){
  if(in_array($field['type'], array('textarea', 'wysiwyg', text))){
    return picturefill_wp_apply_to_html($content);
  }else{
    return $content;
  }
}
```

For image fields.

In your theme file:
```php
<?php
$image_object = get_field('image');
$image_output = '<img src="' . $image_object['sizes']['medium'] . '" title="' . $image_object['title'] . '" alt="' . $image_object['alt'] . '" />';
echo picturefill_wp_apply_to_html($image_output);
?>
```

In functions.php:
```php
add_filter('theme_acf_image', 'theme_function_for_acf_image', 10, 2);

function theme_function_for_acf_image($content, $name_of_the_image_field){
  return Picturefill_WP::get_instance()->cache_picturefill_output($content, $name_of_the_image_field);
}
```

###Using Picturefill.WP with the [Infinite Scroll Plugin](http://wordpress.org/plugins/infinite-scroll/)

Picturefill.WP will replace any images that run through `the_content` filters with the picturefill HTML syntax, but picturefill.js is only run on page load and when the browser window is resized. It will not be run on any additional content added to the page after the page is loaded.

To work with an infinate scroll plugin, `window.picturefill();` will need to be added as a callback function, to be called anytime new content has loaded. In the case of the [infinite scroll plugin](http://wordpress.org/plugins/infinite-scroll/) linked at the top, there is a callback field in the plugin options where `window.picturefill();` can be added.

###Using Picturefill.WP with the [NextGen Gallery](http://wordpress.org/plugins/nextgen-gallery/)

NextGen Image Gallery uses custom database tables and custom folders for images, both of which cause trouble with Picturefill.WP. There are a few [ways to make things work with NextGen Gallery < 2.0](http://wordpress.org/support/topic/nextgen-2x-images), but no solution for version 2.0 or greater has come forward.

###Using Picturefill.WP with the [Woocommerce Shopping Cart](http://wordpress.org/plugins/woocommerce/)

The transient caching in Picturefill.WP causes a conflict with the way Woocommerce loads their shopping cart. [See the issue here](http://wordpress.org/support/topic/crashes-woocommerce-shopping-cart). Possible solutions include:

Disabling transient caching altogether.

In functions.php:
```php
if(defined('PICTUREFILL_WP_VERSION')){
  disable_picturefill_wp_cache();
}
```

Disabling transient caching on the cart shortcode.

In functions.php:
```php
if(defined('PICTUREFILL_WP_VERSION')){
  add_filter('the_content', 'do_not_cache_woocommerce_cart');
}

function do_not_cache_woocommerce_cart($content){
  if(has_shortcode($content, 'woocommerce_cart')){
    disable_picturefill_wp_cache();
  }
  return $content;
}
```

Telling Picturefill.WP to ignore the shopping cart.

In functions.php:
```php
if(defined('PICTUREFILL_WP_VERSION')){
  picturefill_wp_exclude_post_slug('cart');
}
```
