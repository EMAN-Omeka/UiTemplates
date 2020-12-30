<?php
queue_js_file('jquery.elevateZoom-3.0.8.min');
echo head(array('title' => metadata('file', array('Dublin Core', 'Title')),'bodyclass' => 'files show ' . $bodyColumns)); ?>

<h1><?php
  $title = "";
		if(metadata('file', array('Dublin Core', 'Title'))) {
				$titres = metadata('file', array('Dublin Core', 'Title'), array('all' => true));
				foreach ($titres as $i => $titre) {
					echo "<h1>$titre</h1>";
				}
		} else {
				echo $title;
		}
  ?>
  <?php $itemId = metadata('file', 'item_id');
    $item = get_record_by_id('item', $itemId);
    $itemTitle = metadata($item, array('Dublin Core', 'Title'));
  ?>
</h1>
<?php
  $auteurs = metadata('file', array('Dublin Core', 'Creator'), array('delimiter'=>' ; '));
  if ($auteurs) {
    strpos($auteurs, ';') ? $label = $this->controller->t('Auteurs') : $label = $this->controller->t('Auteur');
  } else {
    $label = "";
  }
?>


<?php
  $translations = unserialize(base64_decode(get_option('ui_templates_translations')));
?>


<span class="dclabel" style="float:left;clear:left;"><?php echo $label;?> <!--  (DC.Creator) --> <?php echo $auteurs;?></span><br />
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


<style>
#overlay {
 	display:none;
	width:99.9%;
 	position:absolute;
/* 	min-height:2000px; */
	top:0;
	left:0;
	background:#eee;
	z-index:10000000;
	border:#111 1px solid;
}
#overlay-close {
	position:absolute;
	right:10px;
	bottom:10px;
}
#left, #right {
	position:relative;
	width:49.5%;
	border:#222 1px solid;
	top:10px;
	overflow:visible;
	display:block;
	margin-bottom:50px;
}
#left {
	margin-left:5px;
	clear:left;
	float:left;
}
#right {
	right:5px;
	clear:both;
	clear:right;
	float:right;
}
#left img {
	width:100%;
}
#plugin_transcript iframe {
	width:100%;
	clear:both;
	overflow:visible;
	display:none;
}
</style>
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
