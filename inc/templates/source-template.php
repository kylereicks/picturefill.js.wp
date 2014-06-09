<?php
/*
<source srcset="<?php echo $view->get_image_srcset($image_size); ?>" media="<?php echo $view->get_media_query($image_size); ?>">
 */
?>
<source srcset="<?php echo $view->format_srcset($template_data); ?>" media="<?php echo $view->get_media_query($template_data); ?>">
