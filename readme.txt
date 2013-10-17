=== Picturefill.WP ===
Contributors: kylereicks
Donate link: http://shakopee.dollarsforscholars.org/
Tags: images, retina, retina images, responsive images, picturefill, picturefillJS, picturefill.js, HDPI, High DPI
Requires at least: 3.2
Tested up to: 3.6.1
Stable tag: 1.2.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Wordpress plugin to use picturefill.js to load responsive/retina images, mimicking the proposed HTML5 picture spec.

== Description ==

This plugin uses and adapted version of [picturefill.js](https://github.com/scottjehl/picturefill) that adds additional attributes such as `id`, `class`, and `title`.

Picturefill.wp looks through `the_content` to find `<img>` elements like this:

    <img class="alignnone size-large wp-image-123" alt="Accessible alternate text for the image" title="A title that displays on hover" src="http://sitename.com/wp-content/uploads/2013/4/image-770x577.jpg" width="770" height="577" />

then replaces them with something like this:

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

To improve performance, especially in image heavy posts, the output of Picturefill.WP is cached after it is generated. The cache will be refreshed automatically every time a post is updated or Picturefill.WP is updated. The cache can be manually refreshed by deactivating and reactivating Picturefill.WP from the plugins menu.

If you suspect that Picturefill.WP's caching is causing trouble with another plugin or theme feature, first try deactivating and reactivating Picturefill.WP. If problems persist, try lowering the priority for Picturefill.WP to be executed by adding the following to your functions.php file:

    remove_filter('the_content', array(Picturefill_WP::get_instance(), 'apply_picturefill_wp_to_the_content'), 11);
    add_filter('the_content', array(Picturefill_WP::get_instance(), 'cache_picturefill_output'), 9999);

If you still encounter problems with other plugins or theme features, you may want to disable caching all together. See the subsection on how to disable caching under the "Extending Picturefill.WP" section of the [GitHub repository](https://github.com/kylereicks/picturefill.js.wp).

###Errors and Warnings

When using Picturefill.WP with your website, you may occasionally notice a warning very much like the following:

    [Mon Jan 01 12:00:00 2000] [error] [client 999.999.99.99] PHP Warning: DOMDocument::loadHTML() [domdocument.loadhtml]: Unexpected end tag : a in Entity, line: 17 in /server/www/wp-content/plugins/picturefill/inc/class-model-picturefill-wp.php on line 20

This error indicates improperly formed HTML. In this case, the `Unexpected end tag : a` comes from nested links. If you are seeing errors like this on your WordPress site, you may want to consider implementing an [error logging](http://codex.wordpress.org/Editing_wp-config.php#Configure_Error_Logging) system, or alternatively suppressing errors by adding `error_reporting(0);` and `@ini_set('display_errors', 0);` to `wp-config.php`.

Additionally, the PHP DOM parser `DOMDocument` often has trouble with HTML5 elements and may throw an error if your post includes a `<canvas>` element, or a `<section>` element, for example. All the more reason to implement an error handling system on production sites.

###Extending Picturefill.WP

See the [Extending Picturefill.WP](https://github.com/kylereicks/picturefill.js.wp#extending-picturefillwp) subsection of the GitHub repository for a list of plugin hooks and examples.

== Installation ==

1. First, make sure that the image sizes set in your media settings reflect useful breakpoints in your design.
2. Upload the plugin folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Where are the plugin options? =

There aren't any. Breakpoints, as well as retina and responsive images are created based on the image sizes in your media settings.

= Is this plugin on GitHub? =

Yes it is. [Picturefill.WP](https://github.com/kylereicks/picturefill.js.wp)

= Where can I find information about extending the plugin? =

See the [Extending Picturefill.WP](https://github.com/kylereicks/picturefill.js.wp#extending-picturefillwp) subsection of the GitHub repository for a list of plugin hooks and examples.

= Can I use this plugin with the [Advanced Custom Fields Plugin](http://wordpress.org/plugins/advanced-custom-fields/)? =

Yes. If you use [Advanced Custom Fields shortcodes](http://www.advancedcustomfields.com/resources/functions/shortcode/) in your post or page content, Picturefill.WP will work automatically. To use Advanced Custom Fields outside of `the_content` in theme files, see the subsection of the GitHub documentation [using Picturefill.WP with Advaned Custom Fields](https://github.com/kylereicks/picturefill.js.wp#using-picturefillwp-with-the-advanced-custom-fields-plugin).

== Changelog ==

= 1.2.6 =
* Fix bug to output the correct width for @2x images.

= 1.2.5 =
* Update minified version picturefill.js

= 1.2.4 =
* Fix error to allow for images with an attachment id but no declared width
* Loop through image attributes on the server and in the browser so that all the attributes in the original image are included in the generated image

= 1.2.3 =
* Fix error in template hooks
* Add minified templates
* Add example to minify HTML ouput

= 1.2.2 =
* Account for post pagination

= 1.2.1 =
* Update handling image sizes
* Make the `$content_type` attribute required in the `cache_picturefill_output_method`
* Update documentation

= 1.2.0 =
* Update picturefill.js to reflect changes to [@scottjehl's original](https://github.com/scottjehl/picturefill)
* Add output caching
* Add hooks
* Add extension examples in the [github repository](https://github.com/kylereicks/picturefill.js.wp)

= 1.1.3 =
* Correct encoding bug

= 1.1.2 =
* Reorganized code and file structure

= 1.1.1 =
* Bug-fix to allow special characters in `title` and `alt` attributes.

= 1.1 =
* Allow for responsive images for non-standard sizes
* Add a `min-size` class to limit the resposiveness of an image.

= 1.0 =
* Release 1.0.

== Upgrade Notice ==

= 1.2.6 =
This version update fixes a bug which was preventing @2x images from responding as expected.
