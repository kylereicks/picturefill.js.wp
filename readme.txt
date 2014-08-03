=== Picturefill.WP ===
Contributors: kylereicks
Donate link: http://shakopee.dollarsforscholars.org/
Tags: images, retina, retina images, responsive images, picturefill, picturefillJS, picturefill.js, HDPI, High DPI
Requires at least: 3.2
Tested up to: 3.9.1
Stable tag: 1.3.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Wordpress plugin to use picturefill.js to load responsive/retina images, mimicking the proposed HTML5 picture spec.

== Description ==

**Note: This is an implementation of [Picturefill.js version 1.2.x](https://github.com/scottjehl/picturefill/tree/1.2). For an implementation of [Picturefill.js 2.x](http://scottjehl.github.io/picturefill/), see the [master branch of the GitHub repository](https://github.com/kylereicks/picturefill.js.wp/tree/master).**

Picturefill.WP is a simple and option-less plugin to serve HDPI and responsive images on a WordPress website.

This plugin parses post and page content replacing images with a special syntax similar to the proposed syntax for the HTML5 `picture` element, then uses an adapted version of [picturefill.js](https://github.com/scottjehl/picturefill) to load the appropriate image to the browser.

###Considerations before installing

####Slow Loading on Activation

The first time a page or post is loaded after activating Picturefill.WP, new `@2x` size images will need to be created for the images in the post or page content. This can take several seconds and will take longer on image heavy posts. Once these images are created, posts should load at least as fast or faster than they do without the plugin.

If you are installing Picturefill.WP on a large and image heavy site, you may want to consider using another plugin like [Regenerate Thumbnails](http://wordpress.org/plugins/regenerate-thumbnails/) to create the new image sizes for existing posts and pages.

####500 or 504 server errors

These errors are related to the slow loading listed above. If the server reaches its timeout limit before it is finished processing new images, it will return a 500 or 504 error. Refreshing the page usually gives the server the time it needs to finish processing the images. On some image-heavy posts, it may take more than one refresh.

####Image Sizes

By default, Wordpress creates as many as 3 images of different sizes for each uploaded image ("large", "medium", and "thumbnail"), in addition to the "full" image size.

This plugin adds responsive breakpoints based on the width of the image. The largest available image will display unless the browser width is less than the image width + 20px, in which case the next size down is displayed.

To use this plugin most effectively, set the default image sizes ("large", "medium", and "thumbnail") to reflect useful breakpoints in your theme design.

####Errors and Warnings

As of version 1.3.3 Picturefill.WP suppresses errors and warnings in parsing the DOM. Errors and warnings can now be collected via the `picturefill_wp_syntax_present_libxml_errors` and `picturefill_wp_get_images_libxml_errors` filters.

    add_filter('picturefill_wp_get_images_libxml_errors', 'handle_errors');

    function handle_errors($errors){
      foreach($errors as $error){
        // Handle errors here.
      }
    }

####Theme CSS

As described in the [Details section](https://wordpress.org/plugins/picturefillwp/other_notes/), the picturefill.js syntax uses nested `span` elements. If a theme's CSS applies styles to un-classed `span` elements, you may notice some of these `span`s showing up unexpectedly on the page after activating Picturefill.WP. If possible, it is best to remove the offending code from your theme files, but adding the flowing to the bottom of your theme's CSS file should also work to reset these styles.

    span[data-picture]{display:inline;margin:0;padding:0;border:0;}
    span[data-picture] span{display:inline;margin:0;padding:0;border:0;}

####Caching

To improve performance, especially in image heavy posts, Picturefill.WP uses transient caching. The cache will be refreshed automatically every time a post is updated or Picturefill.WP is updated. The cache can be manually refreshed by deactivating and reactivating Picturefill.WP from the plugins menu.

If you suspect that Picturefill.WP's caching is causing trouble with another plugin or theme feature, first try deactivating and reactivating Picturefill.WP. If problems persist, try lowering the priority for Picturefill.WP to be executed by adding the following to your functions.php file:

    remove_filter('the_content', array(Picturefill_WP::get_instance(), 'apply_picturefill_wp_to_the_content'), 11);
    add_filter('the_content', array(Picturefill_WP::get_instance(), 'cache_picturefill_output'), 9999);

If you still encounter problems with other plugins or theme features, you may want to disable caching all together. See the subsection on how to disable caching under the "Extending Picturefill.WP" section of the [GitHub repository](https://github.com/kylereicks/picturefill.js.wp).

== Details ==

Picturefill.WP looks through `the_content` to find `<img>` elements like this:

    <img class="alignnone size-large wp-image-123" alt="Accessible alternate text for the image" title="A title that displays on hover" src="http://sitename.com/wp-content/uploads/2013/4/image-770x577.jpg" width="770" height="577" />

then replaces them with something like this (visit the [GitHub repository](https://github.com/kylereicks/picturefill.js.wp#details) for a breakdown of the syntax):

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

###Extending Picturefill.WP

See the [Extending Picturefill.WP](https://github.com/kylereicks/picturefill.js.wp#extending-picturefillwp) subsection of the GitHub repository for a list of plugin hooks and examples.

== Installation ==

1. First, make sure that the image sizes set in your media settings reflect useful breakpoints in your design.
2. Upload the plugin folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Where are the plugin options? =

There aren't any. Breakpoints, as well as retina and responsive images are created based on the image sizes in your media settings. See [Extending and Customizing Picturefill.WP 1](https://github.com/kylereicks/picturefill.js.wp/tree/1.3.x#extending-and-customizing-picturefillwp-1) For information on customizing the plugin.

= Should I use this plugin (1.3.x), or the implementation of <a href="https://github.com/kylereicks/picturefill.js.wp/tree/master">Picturefill 2.0 on GitHub</a>? =

There are a number of reasons why you might want to use either. The Picturefill project site provides a basic [breakdown of the two versions](http://scottjehl.github.io/picturefill/#download), as well as some [support caveats to keep in mind for version 2](http://scottjehl.github.io/picturefill/#gotchas). 

= Is this plugin on GitHub? =

Yes it is. [Picturefill.WP](https://github.com/kylereicks/picturefill.js.wp/tree/1.3.x)

= Where can I find information about extending the plugin? =

See the [Extending Picturefill.WP](https://github.com/kylereicks/picturefill.js.wp/tree/1.3.x#extending-and-customizing-picturefillwp-1) subsection of the GitHub repository for a list of plugin hooks and examples.

= Can I use this plugin with another plugin that I always use? =

Probably. See the [using Picturefill.WP with other plugins](https://github.com/kylereicks/picturefill.js.wp/tree/1.3.x#use-with-other-plugins) section of the documentation on GitHub for a few of the plugins that have been discovered may need special consideration.

= Why does this plugin use an "adapted" version of picturefill.js =

The standard version of [picturefill.js](https://github.com/scottjehl/picturefill) will work well enough with Picturefill.WP; however, Picturefill.WP has a slightly different goal than picturefill.js. Picturefill.js aims to pollyfill the proposed `<picture>` element. It expects a special `<picture>` like markup, and outputs the appropriate `<img>`, but the resulting `<img>` does not include a class, id or other attribute. The generated `<img>` tags can only be targeted by the attributes of its parent elements. Picturefill.WP aims to take an `<img>` and then output an `<img>` exactly like it, apart form the width or pixel density. This way, `<img>` tags can be targeted without regard to the `<picture>` syntax.

= Are there any plans to update this plugin to Picturefill 2.0 =

The plugin in this repository will remain an implementation of Picturefill 1.2. If you are looking for an implementation of Picturefill 2.0, please see the [master branch of the GitHub repository](https://github.com/kylereicks/picturefill.js.wp/tree/master).

== Advanced Use ==

###Markup Tricks

####Limit Responsiveness

The responsiveness of an image can be limited by adding the class `min-size-{image size}`. For example, an image with the class `min-size-medium` will not load an image smaller than size `medium`.

####Skip Images

To skip images and load them normally add the attribute `data-picturefill-wp-ignore` to the `<img>` tag.

###Helper Functions

See the [helper functions](https://github.com/kylereicks/picturefill.js.wp#helper-functions) section of the documentation on GitHub.

== Changelog ==

= 1.3.5 =
* Bug fixes. Typos in variable names.

= 1.3.4 =
* Bug fixes in helper functions GitHub issues [#32](https://github.com/kylereicks/picturefill.js.wp/issues/32) and [#33](https://github.com/kylereicks/picturefill.js.wp/issues/33).

= 1.3.3 =
* [Suppress warnings](https://github.com/kylereicks/picturefill.js.wp/issues/29) from DOM parsing
* [Update transient caching](https://github.com/kylereicks/picturefill.js.wp/issues/31) to use a hash as an identifier. 

= 1.3.1 =
* Correct an IE7 JavaScript issue.
* Check the filtered content for the picturefill syntax instead of images only.

= 1.3.1 =
* Hotfix to correct an error in the `picturefill_wp_set_responsive_image_sizes` helper function.

= 1.3.0 =
* Add a number of helper functions to simplify common customizations
* Run picturefill.js even when deferred until after the page is loaded

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
* Add example to minify HTML output

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
* Add a `min-size` class to limit the responsiveness of an image.

= 1.0 =
* Release 1.0.

== Upgrade Notice ==

= 1.3.5 =
Bug fixes. Typos in variable names.
