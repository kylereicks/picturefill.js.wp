Picturefill.WP 2
================

**Note: This is an implementation of Picturefill.js version 2.x. For an implementation of Picturefill.js 1.2.x, see the [1.3.x branch of this repository](https://github.com/kylereicks/picturefill.js.wp/tree/1.3.x).**

Picturefill.WP 2 is a simple and option-less plugin to serve HDPI and responsive images on a WordPress website.

This plugin parses post and page content augmenting images with the syntax for the HTML5 `srcset` and `sizes` attributes, then uses [picturefill.js](https://github.com/scottjehl/picturefill) to polyfill the functionality on unsupported browsers.

Details
-------

Picturefill.WP 2 looks through `the_content` to find `<img>` elements like this:

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

###Wordpress Image Sizes

By default, Wordpress creates as many as 3 images of different sizes for each uploaded image ("large", "medium", and "thumbnail"), in addition to the "full" image size.

By default, the plugin lists all of these default sizes in the srcset and instructs the browser to serve up the appropriate image based on browser window width and screen resolution, not exceeding the original image width. 

To use this plugin most effectively, set the default image sizes ("large", "medium", and "thumbnail") to reflect useful breakpoints in your theme design.

###Setting srcsets and sizes

The srcset and sizes attributes can be set a few different ways. These attributes can be set manually in the editor. If the plugin sees the srcset attribute already in an `<img>` tag, it will ignore that image and simply enqueue the picturefill.js script. If the sizes attribute is already set in an `<img>` tag, but the srcset is not present, the plugin will add the srcset registered for that image size but leave the sizes attribute alone.

New srcsets can be registered via the `picturefill_wp_register_srcset` function. Likewise, new sizes attributes can be registered via the `picturefill_wp_register_sizes` function. See Extending and Customizing Picturefill.WP for more detail on these functions.

Once a srcset or sizes attribute is registered, it will apply to the image sizes to which it is attached; however, it can be overridden with a image's class attribute. `srcset-{srcset-handle}` applies the "srcset-handle" srcset to the image, and `sizes-{sizes-handle}` applies the "sizes-handle" sizes attribute to the image.

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

Picturefill.WP 2 includes a number of functions to better interact with the plugin.

For extra safety, it's a good idea to wrap your code that targets Picturefill.WP 2 in a conditional statement so it will only run if the plugin is active.

```php
if(defined('PICTUREFILL_WP_VERSION') && '2' === substr(PICTUREFILL_WP_VERSION, 0, 1)){
  // Add Picturefill.WP 2 specific code here.
}
```

####picturefill_wp_register_srcset($handle, $srcset_array, $attach_to)

This function registers a srcset and optionally assigns it to an image size. It should be called with the `picturefill_wp_register_srcset` action.

#####Parameters

**$handle** (string) (required) Name of the srcset. Should be unique. Can later be assigned to an image via the class `srcset-{$handle}`.

**$srcset_array** (array) (required) An array of image sizes that make up the srcset.

**$attach_to** (string or array) (optional) A single image (string) or list of images (array) to which this srcset should be applied.

#####Example

```php
function register_theme_srcsets(){
  picturefill_wp_register_srcset('medium-min-size', array('medium', 'large', 'full'), 'medium');
}

add_filter('picturefill_wp_register_srcset', 'register_theme_srcsets');
```

####picturefill_wp_register_sizes($handle, $sizes_string, $attach_to)

This function registers a sizes attribute and optionally assigns it to an image size. It should be called with the `picturefill_wp_register_srcset` action.

#####Parameters

**$handle** (string) (required) Name of the sizes attribute. Should be unique. Can later be assigned to an image via the class `sizes-{$handle}`.

**$sizes_string** (string) (required) The sizes attribute.

**$attach_to** (string or array) (optional) A single image (string) or list of images (array) to which this sizes attribute should be applied.

#####Example

```php
function register_theme_sizes(){
  picturefill_wp_register_sizes('theme-medium-sizes', '(max-width: 30em) 100vw, (max-width: 50em) 50vw, calc(33vw - 100px)', 'medium');
}

add_filter('picturefill_wp_register_srcset', 'register_theme_sizes');
```

####picturefill_wp_apply_to_html($html, $cache)

This function applies Picturefill.WP to the passed HTML.

#####Parameters

**$html** (string) (required) HTML string that includes an image.

**$cache** (boolean) (optional) Whether or not the output will be cached.

#####Example

```php
echo picturefill_wp_apply_to_html(get_the_post_thumbnail());
```

####picturefill_wp_apply_to_filter($filter)

This function applies Picturefill.WP to the passed filter.

#####Parameters

**$filter** (string) (required) A WordPress filter.

#####Example

```php
picturefill_wp_apply_to_filter('post_thumbnail_html');
```

###Hooks

Like many WordPress themes and plugins, Picturefill.WP can be altered and extended with action and and filter hooks.

####Actions

* `picturefill_wp_updated`
* `picturefill_wp_register_srcset`
* `picturefill_wp_before_replace_images`
* `picturefill_wp_after_replace_images`

####Filters

* `picturefill_wp_the_content_filter_priority`
* `picturefill_wp_the_replace_images_output`
* `picturefill_wp_cache`
* `picturefill_wp_cache_duration`
* `picturefill_wp_syntax_present_libxml_errors`
* `picturefill_wp_get_images_libxml_errors`
* `picturefill_wp_attachment_id_search_url`
* `picturefill_wp_image_attributes`
* `picturefill_wp_html_standardized_img_tags`
* `picturefill_wp_srcset_array`
* `picturefill_wp_image_attachment_data`
* `picturefill_wp_template_path`
* `picturefill_wp_{$template}_template_file_path`
* `picturefill_wp_{$template}_template_data`
* `picturefill_wp_{$template}_template`
* `picturefill_wp_sizes_string_{$image_size}`
* `picturefill_wp_srcset_url`
* `picturefill_wp_image_attribute_string`
* `picturefill_wp_image_attribute_{$attribute}`
* `picturefill_wp_use_explicit_width`
* `picturefill_wp_output_src`


Use With Other Plugins
----------------------

###Using Picturefill.WP with the [Advanced Custom Fields Plugin](http://wordpress.org/plugins/advanced-custom-fields/)

If you use [Advanced Custom Fields shortcodes](http://www.advancedcustomfields.com/resources/functions/shortcode/) in your post or page content, Picturefill.WP 2 will work automatically. To use Advanced Custom Fields outside of `the_content` in theme files, apply Picturefill.WP 2 to the `acf/format_value_for_api` filter.

```php
picturefill_wp_apply_to_filter('acf/format_value_for_api');
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

###Using Picturefill.WP with the [Infinite Scroll Plugin](http://wordpress.org/plugins/infinite-scroll/)

Picturefill.WP will replace any images that run through `the_content` filters with the picturefill HTML syntax, but picturefill.js is only run on page load and when the browser window is resized. It will not be run on any additional content added to the page after the page is loaded.

To work with an infinite scroll plugin, `window.picturefill();` will need to be added as a callback function, to be called anytime new content has loaded. In the case of the [infinite scroll plugin](http://wordpress.org/plugins/infinite-scroll/) linked at the top, there is a callback field in the plugin options where `window.picturefill();` can be added.

###Using Picturefill.WP with the [NextGen Gallery](http://wordpress.org/plugins/nextgen-gallery/)

NextGen Image Gallery uses custom database tables and custom folders for images, both of which cause trouble with Picturefill.WP. There are a few [ways to make things work with NextGen Gallery < 2.0](http://wordpress.org/support/topic/nextgen-2x-images), but no solution for version 2.0 or greater has come forward.

###Using Picturefill.WP with the [Woocommerce Shopping Cart](http://wordpress.org/plugins/woocommerce/)

The transient caching in Picturefill.WP causes a conflict with the way Woocommerce loads their shopping cart. [See the issue here](http://wordpress.org/support/topic/crashes-woocommerce-shopping-cart). Possible solutions include:

Disabling transient caching altogether.

In functions.php:
```php
add_filter('picturefill_wp_cache', '__return_false');
```

Disabling transient caching on the cart shortcode.

In functions.php:
```php
if(defined('PICTUREFILL_WP_VERSION')){
  add_filter('the_content', 'do_not_cache_woocommerce_cart');
}

function do_not_cache_woocommerce_cart($content){
  if(has_shortcode($content, 'woocommerce_cart')){
    add_filter('picturefill_wp_cache', '__return_false');
  }
  return $content;
}
```
