<?php
echo head(array('title' => "UI Templates $type page"));
echo flash();
?>
<div id='uit-menu'>
  <a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/uitemplate/item'>Items</a>
  <a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/uitemplate/collection'>Collections</a>
  <a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/uitemplate/file'>Files</a>
  <a class='add button small green' href='<?php echo WEB_ROOT; ?>/admin/uitemplate/options'>Options Générales</a>
  <a class='add button small green' id='uitemplates-export-options'>Exporter les options</a>
  <a class='add button small red' id='uitemplates-import-options'>Importer les options</a>
<form id="fileinfo" enctype="multipart/form-data" method="post" name="fileinfo">
    <label>File to stash:</label>
    <input type="file" name="file" required />
</form>
<input type="button" id="go-button" value="Stash the file!"></input>
<div id="output"></div>
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

	$('#uitemplates-export-options').click(function() {
     window.open('<?php echo WEB_ROOT; ?>/admin/uitemplate/export', '_blank');
     return false;
	});

	$('#uitemplates-import-options').click(function() {
     window.open('<?php echo WEB_ROOT; ?>/admin/uitemplate/import', '_blank');
     return false;
	});
  $('#go-button').on('click', function(){
//     var fd = new FormData($("#fileinfo"));
    var formData = new FormData();
    formData.append('file', $('input[type=file]')[0].files[0]);
    //fd.append("CustomField", "This is some extra data");
    $.ajax({
      url: '<?php echo WEB_ROOT; ?>/admin/uitemplate/import',
      type: 'POST',
      data: formData,
      success:function(data){
        $('#output').html(data);
      },
      cache: false,
      contentType: false,
      processData: false
    });
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

