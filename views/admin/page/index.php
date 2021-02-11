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
  <form id="fileinfo" enctype="multipart/form-data" method="post" name="fileinfo">
      <input type="file" id="file-browse" class="file" name="file" required />
      <label for="file"><?php echo __('Import config file') ?></label>
  </form>
  <a class='add button small red' id="uitemplates-import-options" value="Importer les options">Importer les options</a>
  <a class='add button small red' id="uitemplates-import-cancel" value="Annuler">Annuler</a>
  <div id="output"></div>
</div>

<script>
$ = jQuery;
$(document).ready(function() {
  $('.uitemplates-fieldset h2').parent().siblings().toggle();
  $('#uitemplates-import-options').hide();
  var destination = $('.uitemplates-form').offset();
  $('.uitemplates-form #submit').css({top: destination.top, left: destination.left + 850});
  $('.uitemplates-form #submit').parent().find('label').hide();

  $('.uitemplates-fieldset h2').click(function() {
      $(this).parent().siblings().toggle();
      return false;
  });

	$('#uitemplates-export-options').click(function() {
     window.open('<?php echo WEB_ROOT; ?>/admin/uitemplate/export');
     return false;
	});
	$('#fileinfo').change(function(e) {
  	$('#uitemplates-import-options').text('Importer le fichier "' + $(e.target.files[0]).attr('name') + '"');
  	$('#uitemplates-import-options').show();
  	$('#uitemplates-import-cancel').show();
  	$(this).hide();
	});

  $('#uitemplates-import-cancel').click(function() {
  	$('#uitemplates-import-options').hide();
  	$('#uitemplates-import-cancel').hide();
  	$('#fileinfo').show();
  });
  $('#fileinfo label').on('click', function() {
    $('#file-browse').trigger('click');
  });

  $('#uitemplates-import-options').on('click', function(){
    var formData = new FormData();
    formData.append('file', $('input[type=file]')[0].files[0]);
    $.ajax({
      url: '<?php echo WEB_ROOT; ?>/admin/uitemplate/import',
      type: 'POST',
      data: formData,
      success:function(data){
        $('#output').html(data);
        $(window).off('beforeunload');
        window.location.href = '<?php echo WEB_ROOT; ?>/admin/uitemplate/item';
      },
      cache: false,
      contentType: false,
      processData: false
    });
  });
});

</script>
<?php

echo $form;

echo foot();

?>

