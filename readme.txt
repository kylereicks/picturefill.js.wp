=== Picturefill.WP ===
Contributors: kylereicks
Donate link: http://shakopee.dollarsforscholars.org/
Tags: images, picture, srcset, sizes, retina, retina images, responsive images, picturefill, picturefillJS, picturefill.js, HDPI, High DPI
Requires at least: 3.2
Tested up to: 3.9.1
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Wordpress plugin to use picturefill.js to load responsive/retina images with the new HTML5 picture/srcset/sizes spec.

== Description ==

Picturefill.WP 2 is a simple and option-less plugin to serve HDPI and responsive images on a WordPress website.

This plugin parses post and page content augmenting images with the syntax for the HTML5 `srcset` and `sizes` attributes, then uses [picturefill.js](https://github.com/scottjehl/picturefill) to polyfill the functionality on unsupported browsers.

###Considerations before installing

####Image Sizes

By default, Wordpress creates as many as 3 images of different sizes for each uploaded image ("large", "medium", and "thumbnail"), in addition to the "full" image size.

By default, the plugin lists all of these default sizes in the srcset and instructs the browser to serve up the appropriate image based on browser window width and screen resolution, not exceeding the original image width. 

To use this plugin most effectively, set the default image sizes ("large", "medium", and "thumbnail") to reflect useful breakpoints in your theme design.

== Details ==

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

Browsers that have implemented the picture/srcset/sizes spec will respond natively by loading the appropriate image. Other browsers will rely on Picturefill.js to polyfill this behavior.

###Extending Picturefill.WP

See the [Extending Picturefill.WP](https://github.com/kylereicks/picturefill.js.wp#extending-picturefillwp) subsection of the GitHub repository for a list of plugin hooks and examples.

== Installation ==

1. First, make sure that the image sizes set in your media settings reflect useful breakpoints in your design.
2. Upload the plugin folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Where are the plugin options? =

There aren't any. See the [documentation](https://github.com/kylereicks/picturefill.js.wp#extending-picturefillwp) for information on how to customize the plugin's functionality.

= Is this plugin on GitHub? =

Yes it is. [Picturefill.WP](https://github.com/kylereicks/picturefill.js.wp)

= Where can I find information about extending the plugin? =

See the [Extending Picturefill.WP](https://github.com/kylereicks/picturefill.js.wp#extending-picturefillwp) subsection of the GitHub repository for a list of plugin hooks and examples.

= Can I use this plugin with another plugin that I always use? =

Probably. See the [using Picturefill.WP with other plugins](https://github.com/kylereicks/picturefill.js.wp#use-with-other-plugins) section of the documentation on GitHub for a few of the plugins that have been discovered may need special consideration.

= Where is the old Picturefill.WP? =

It's exactly where it always was [http://wordpress.org/plugins/picturefillwp/](http://wordpress.org/plugins/picturefillwp/). On GitHub, the project is living on the [1.3.x branch](https://github.com/kylereicks/picturefill.js.wp/tree/1.3.x). There are a number of reasons why you may want to continue to use Picturefill.WP 1. It will continue to be maintained, but new features are unlikely to be added.

= Why a new plugin for Picturefill.WP 2? Why not just update the original Picturefill.WP? =

The syntax from Picturefill.js 2.0 is so significantly diferent from the syntax for Picturefill.js 1.2 that a seemless update would have been very difficult, and trying to do so would have harmed the quality of the project.

Instead, I decided to make a clean break. Both plugins will continue to be maintained, and there are a number of reasond why you might use either one.

== Changelog ==

= 2.0.0 =
* It may seem strange to have 2.0 be an initial release, but that is what is happening.

== Upgrade Notice ==

= 2.0.0 =
Initial release.
