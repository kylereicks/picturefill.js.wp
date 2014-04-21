<picture<?php echo $view_picturefill_wp->get_picture_attribute_string(); ?>>
  <?php /* echo $view_picturefill_wp->render_template('fallback-source');*/ ?>
  <?php echo $view_picturefill_wp->generate_source_list(); ?>
  <?php /* echo $view_picturefill_wp->render_template('noscript');*/ ?>
  <?php echo preg_replace('/\s(?:width|height)="[^"]+"/', '', str_replace(' src="', ' srcset="', $view_picturefill_wp->get_original_image())); ?>
</picture>
