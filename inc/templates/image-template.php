<img<?php
  echo $view->get_image_attribute_string();
  if(false !== $view->model->get_image_attributes()['attachment_id']){
    echo $view->get_sizes();
    echo ' srcset="' . $view->format_srcset($template_data) . '"';
  }
  ?> />
