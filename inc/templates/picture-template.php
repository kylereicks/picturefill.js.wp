<span data-picture<?php echo $view_picturefill_wp->get_picture_attribute_string($image_attributes); ?>>
  <?php echo $view_picturefill_wp->render_template('fallback-source', $template_data); ?>
  <?php echo $view_picturefill_wp->generate_source_list($template_data); ?>
  <?php echo $view_picturefill_wp->render_template('noscript', $template_data); ?>
</span>
