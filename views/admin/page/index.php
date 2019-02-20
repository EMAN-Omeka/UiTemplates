<?php
echo head(array('title' => "UI Templates $type page"));
echo flash(); 
?>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/uitemplate/item'>Items</a>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/uitemplate/collection'>Collections</a>
<a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/uitemplate/file'>Files</a>
<?php 

echo $form;

echo foot(); 

?>

