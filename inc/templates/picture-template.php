<picture>
<?php
$number_of_srcsets = count($this->model->get_srcset_array()) - 1;
foreach($this->model->get_srcset_array() as $index => $source_array){
  if($index < $number_of_srcsets){
    echo $view->render_template('source', $source_array);
  }else{
    echo $view->render_template('image', $source_array);
  }
}
?>
</picture>
