<?php
echo head(array('title' => "UI Templates $type page"));
echo flash();
?>
<div id='uit-menu'>
  <a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/uitemplate/item'>Items</a>
  <a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/uitemplate/collection'>Collections</a>
  <a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/uitemplate/file'>Files</a>
  <a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/uitemplate/options'>Options Générales</a>
</div>

<script>
$ = jQuery;
$(document).ready(function() {
  $('.uitemplates-fieldset h2').parent().siblings().toggle();
  var destination = $('.uitemplates-form').offset();
  $('.uitemplates-form #submit').css({top: destination.top, left: destination.left + 850});
  $('.uitemplates-form #submit').parent().find('label').hide();

  $('.uitemplates-fieldset h2').click(function() {
      $(this).parent().siblings().toggle();
      return false;
  });
});

</script>

<style>
.uitemplates-form {
/*   position:relative; */
}
.uitemplates-form #submit {
  position:fixed;
}
.uit-menu, uit-form {
  display:block;
  clear:both;
}
#submit-label > label {
  display:none;
}
</style>
<?php

echo $form;

echo foot();

?>

