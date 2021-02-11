<?php
queue_js_file('jquery.elevateZoom-3.0.8.min');
echo head(array('title' => metadata('file', array('Dublin Core', 'Title')),'bodyclass' => 'files show ' . $bodyColumns)); ?>

<?php
  $titre = "";
		if(metadata('file', array('Dublin Core', 'Title'))) {
				$titres = metadata('file', array('Dublin Core', 'Title'), array('all' => true));
				foreach ($titres as $i => $titre) {
          echo "<span id='doc-title' style='font-size:$title_size_files; clear:both;display:block;'>$titre</span>";
				}
		} else {
  		echo "<span id='doc-title' style='font-size:$title_size_files; clear:both;display:block;'>$titre</span>";
		}
  ?>
  <?php $itemId = metadata('file', 'item_id');
    $item = get_record_by_id('item', $itemId);
    $itemTitle = metadata($item, array('Dublin Core', 'Title'));
  ?>
<?php if ($auteurs = metadata('file', array('Dublin Core', 'Creator'), array('delimiter'=>' ; '))) :
  $label =
  strpos($auteurs, ';') ? $label = $this->controller->t('Auteurs') : $label = $this->controller->t('Auteur');
  $label = $this->controller->t($author_name_files);
?>
<br /><span class="dclabel" style="font-size:<?php echo $author_size_files ?>;"><?php echo $label; ?></span><!--  (DC.Creator) --> : <span class="dcvalue" style="font-size:<?php echo $author_size_items ?>;"><?php echo $auteurs;?></span><br />
<?php endif; ?>


<?php
  $translations = unserialize(base64_decode(get_option('ui_templates_translations')));
?>

<span style="float:left"><?php echo $this->controller->t('Notice') ?> : <a href="<?php echo WEB_ROOT;?>/items/show/<?php echo $itemId; ?>"><?php echo $itemTitle ?></a></span>

<?php echo get_specific_plugin_hook_output('Coins', 'public_files_show', array('view' => $this, 'file' => $file));?>

<?php echo $content; ?>
<span  class="dclabel" style='float:right;clear:right;'><?php echo str_replace('Fichier créé par', $this->controller->t('Fichier créé par'), get_specific_plugin_hook_output('Bookmarks', 'public_items_show', array('view' => $this, 'item' => $file))); ?></span><span  class="dclabel" style='float:right;clear:right;'><?php echo $this->controller->t('Fichier créé le') . ' ' . date('d/m/Y', strtotime(metadata('file', 'added'))); ?> </span>
<span  class="dclabel" style='float:right;clear:right;'><?php echo $this->controller->t('Dernière modification le') . ' ' . date('d/m/Y', strtotime(metadata('file', 'modified'))); ?> </span>
</br ></br ></span>

<?php if (isset($size) && $size[1] > 1000) : ?>
<script type="text/javascript">
  function zoom() {
  	jQuery('.zoomImage').elevateZoom({
    	zoomType: "inner",
  		cursor:'zoom-in',
  	});
  }
jQuery(function ()  {
  zoom();
});

</script>
<?php endif ?>

<script>
$ = jQuery;
$('#display-overlay').on('click', function() {
  if ($(this).text() === 'AFFICHER LA TRANSCRIPTION') {
  	$('#plugin_transcript iframe').show();
    $('#primary').width('29%');
    $('#sidebar').width('69%');
    zoom();
  	$(this).text('MASQUER LA TRANSCRIPTION');
  	document.getElementById('transcription-full').contentWindow.location.reload(true);
  } else {
    $('#primary').width('49%');
    $('#sidebar').width('49%');
  	$(this).text('AFFICHER LA TRANSCRIPTION');
  	$('#plugin_transcript iframe').hide();
    zoom();
    }
});
</script>
<?php
 // Previous / Next file Pager complet ?
  $fichier = get_current_record('file');
  $maxFiles = 25;

  $files = get_records('File', array('item' => $fichier->item_id, 'sort_field' => 'order', 'sort_dir' => 'asc'), 100);

  $nbFiles = count($files);
  if ($nbFiles > $maxFiles) {
	  // Seach index of current file in all files
	  $i = 0;
	  foreach($files as $struct) {
	  	if ($fichier->id == $struct->id) {
	  		$currentFile = $struct;
	  		break;
	  	}
	  	$i++;
	  }
		// Slice array to contain only 25 files with current in the middle
		$i > $maxFiles / 2 ? $start = round($i - $maxFiles / 2) : $start = 0;

		$files = array_slice($files, $start, $maxFiles);
  }

  set_loop_records('files', $files);

  $pager = array();
  $i = $current = 0;
  $prec = $suiv = "";
  foreach (loop('files') as $f) {
    $classes = "eman-file-link";
    if ($f->id == $fichier->id) {
    	$classes .= ' eman-file-link-current';
    	$current = $i;
    	if ($i > 0) {
				$prec = link_to_file_show(array('class' => $classes), '<span style="font-size:32px;">&loarr;</span> ', $files[$i-1]);
    	}
    	if ($i < count($files) - 1) {
				$suiv = link_to_file_show(array('class' => $classes), ' <span style="font-size:32px;">&roarr;</span>', $files[$i+1]);
    	}
    }
    $pager[] = link_to_file_show(array('class' => $classes), metadata($f, array('Dublin Core', 'Title')), $f);
  	$i++;
  }

  $pager = "<div id='eman-pager-files'><h3>" . $this->controller->t('Autres fichiers de la notice') . "</h3>" . $prec . implode('<div class="eman-files-sep"> | </div>', $pager) . $suiv . "</div>";

?>
<style>
.suite, .replier {
  cursor: pointer;
  font-style:italic;
  font-weight: bold;
  clear:both;
  display:block;
  float:right;
}
.field-uitemplates {
  overflow: auto;
}
</style>

<script>
 $ = jQuery;

$(document).ready(function(){
  $('.suite').click(function() {
    $(this).parent().parent().find('.fieldcontentcomplet').show();
    $(this).parent().parent().find('.fieldcontentshort').hide();
  });
  $('.replier').click(function() {
    $(this).parent().parent().find('.fieldcontentcomplet').hide();
    $(this).parent().parent().find('.fieldcontentshort').show();
  });
});

function resizeIframe(obj) {
  obj.style.height = obj.contentWindow.document.firstChild.scrollHeight + 'px';
}
</script>

<?php
  echo $pager;
  echo foot(); ?>
