<?php
	$filen = metadata('file', 'filename');
	$filepath = BASE_DIR.'/files/original/'. $filen;
	$fichier = pathinfo($filepath);
	$ext = strtolower($fichier['extension']);
	$fileSize =  round(filesize($filepath) / 1024 / 1024, 2);
	$fileFormat =  mime_content_type($filepath);
	$fileOriginal = metadata('file', 'original_filename');

	if (in_array($ext, array('jpg', 'jpeg', 'png'))) {
		$size = getimagesize($filepath);
	}
	
    $fileTitle = metadata('file', array('Dublin Core', 'Title')) ? strip_formatting(metadata('file', array('Dublin Core', 'Title'))) : metadata('file', 'original filename');

    if ($fileTitle != '') {
        $fileTitle = ': &quot;' . $fileTitle . '&quot; ';
    } else {
        $fileTitle = '';
    }
	$fileTitle = __('Fichier ') . $fileTitle;
	
?>
<!--    $fileTitle = __('File #%s', metadata('file', 'id')) . $fileTitle; -->
<?php queue_js_file('jquery.elevateZoom-3.0.8.min'); ?>

<?php echo head(array('title' => $fileTitle, 'bodyclass'=>'files show primary-secondary')); ?>

<h1><?php 
		if(metadata('file', array('Dublin Core', 'Title'))) {
				$titres = metadata('file', array('Dublin Core', 'Title'), array('all' => true));
				foreach ($titres as $i => $titre) {
					echo "<h1>$titre</h1>";
				}
		} else {
				echo $fileTitle; 
		}?>
<span style="float:right"><a href="<?php echo WEB_ROOT;?>/items/show/<?php echo metadata('file', 'item_id'); ?>"><- Retour à la notice</a></span>
</h1>
<div id="primary">

<div id="primaryImage">
		<?php if (isset($size) && $size[1] > 1000) : ?>
    	<em>Passez le curseur sur l'image pour zoomer.</em>
    	<?php endif ?>
		<?php
			// Fullsize is always jpg
			$filename = str_replace(array('png', 'JPG', 'jpeg'), 'jpg' , $file->filename); 
			echo '<div class="panzoom">' . file_markup($file, array('imageSize'=>'fullsize', 'linkToFile' => false, 'imgAttributes'=>array('class'=>'zoomImage', 'data-zoom-image'=> WEB_ROOT . '/files/fullsize/' . $filename))) . '</div>'; 
		?>
</div>
<?php if (plugin_is_active('Transcript')) : ?>
<div id="textTranscription" type="text/template" style="display:none";>
	<?php echo metadata('file', array('Transcript', 'Transcription'), array('no_escape' => true)); ?>
</div>
<div id="titleTranscription" style="display:none;">
	<?php echo $fileTitle; ?>
</div>
<?php 
	$xmlFileName =  substr($file->filename, 0, strpos($file->filename, '.')) . '.xml'; 
	if (file_exists('teibp/transcriptions/' . $xmlFileName)) :
?>
		<div id="teibpTranscription">
		<button id="display-overlay" style="width:100%;">Afficher la transcription</button>
		</div>
<?php 
	endif;	
endif; ?>
<style>
.show #content #primary #teibpTranscription { width: 100%;  margin:20px 0; padding: 0; overflow: hidden; }
#transcription { width: 1005px; height: 1500px; border: 0px; }
#transcription {
    zoom: 0.5;
    -moz-transform: scale(0.5);
    -moz-transform-origin: 0 0;
    -o-transform: scale(0.5);
    -o-transform-origin: 0 0;
    -webkit-transform: scale(0.5);
    -webkit-transform-origin: 0 0;
}
#transcription-full {

}
@media screen and (-webkit-min-device-pixel-ratio:0) {
 #scaled-frame  { zoom: 1;  }
}
</style>

<div id="filesInfos">
<h2 style='margin-top:0;'><?php echo $titrechamps; ?></h2>
		<?php if (metadata('file', array('Dublin Core', 'Subject'))) :?> 
			<?php echo UiTemplatesPlugin::displayObjectDC($file, 'Subject', $dcsubject); ?> 			
	  <?php  endif; ?>
  
		<?php if (metadata('file', array('Dublin Core', 'Relation'))) :?> 
			<?php echo UiTemplatesPlugin::displayObjectDC($file, 'Relation', $dcrelation); ?>		
		<?php  endif; ?>

	<?php if (metadata('file', array('Dublin Core', 'Type'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($file, 'Type', $dctype); ?>				
	<?php  endif; ?>

	<?php if (metadata('file', array('Dublin Core', 'Rights'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($file, 'Rights', $dcrights); ?>				
	<?php  endif; ?>

	<?php if (metadata('file', array('Dublin Core', 'Language'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($file, 'Language', $dclanguage); ?>				
	<?php  endif; ?>

	<?php if (metadata('file', array('Dublin Core', 'Source'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($file, 'Source', $dcsource); ?>				
	<?php  endif; ?>	

	<?php if (metadata('file', array('Dublin Core', 'Format'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($file, 'Format', $dcformat); ?>				
	<?php  endif; ?>

   <?php if (metadata('file', array('Dublin Core', 'Coverage'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($file, 'Coverage', $dccoverage); ?>				
	<?php  endif; ?>        

	<?php if (metadata('file', array('Dublin Core', 'Creator'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($file, 'Creator', $dccreator); ?>				
	<?php  endif; ?>  
	
		<?php if (metadata('file', array('Dublin Core', 'Contributor'))) :?> 
			<?php echo UiTemplatesPlugin::displayObjectDC($file, 'Contributor', $dccontributor); ?>		
		<?php  endif; ?>

		<?php if (metadata('file', array('Dublin Core', 'Publisher'))) :?> 
			<?php echo UiTemplatesPlugin::displayObjectDC($file, 'Publisher', $dcpublisher); ?>		
    <?php  endif; ?>
    
		<?php if (metadata('file', array('Dublin Core', 'Date'))) :?> 
			<?php echo UiTemplatesPlugin::displayObjectDC($file, 'Date', $dcdate); ?>		
    <?php  endif; ?>    
		<p><span  class="dclabel"><strong>Date de cr&eacute;ation de la notice</strong> : <?php echo date('d/m/Y', strtotime(metadata('file', 'added'))); ?></span></p>    
        
</div>

</div>

<aside id="sidebar">

<?php if(metadata('file', array('Dublin Core', 'Description'))) : ?>
<div id="transcription-info">
<h2 style='margin-top:0;'><?php echo $titretranscription; ?></h2>
	<?php
		$descriptions = metadata('file', array('Dublin Core', 'Description'), array('all' => true));
		foreach ($descriptions as $description) {
			print $description . '<hr />'; 						
		}	
	?>
<span style="float:right;display:block;clear:both;height:20px;"><?php echo $auteurtrans . " : " . metadata($file, array('Dublin Core', 'Contributor')); ?></span>
<br />
</div>
<?php endif; ?>

<div id="filesInfos">

<h2 style='margin-top:0;'><?php echo $titreinfos; ?></h2>
<?php

echo "$nomoriginal : <a href='" . WEB_ROOT . "/files/original/$filen'>$fileOriginal</a><br/>";
echo "$format : $fileFormat <br/>";
echo "$poids : $fileSize Mo<br />";

if (in_array($ext, array('jpg', 'jpeg', 'png'))) {
		echo "$taille : " . round($size[0]).' x '.round($size[1]) . ' px<br/>';
}
?>
</div>
<div>
	<h2 style='margin-top:0;'><?php echo $titrecitation;?></h2>
	<?php echo $citation; ?>
</div>
<div>
	<h2 style='margin-top:0;'><?php echo $titresocial; ?></h2>
<?php       
	$url = record_url($file, 'show', true);
	$title = strip_formatting(metadata($file, array('Dublin Core', 'Title')));
	$description = strip_formatting(metadata($file, array('Dublin Core', 'Description')));
	echo social_bookmarking_toolbar($url, $title, $description);
?>
</div>
<div>
<!-- export PDF et autre format -->
<div id="exports" class="element">
<div style='clear:both;'><h2 style='margin-top:0;'><?php echo $titreexports; ?></h2></div>
<span id="jspdf" style="float:left;clear:left;margin-right:15px;"><img src="<?php echo WEB_ROOT; ?>/themes/eman/images/pdf.png" alt="pdf"/></span>
<?php echo $exports; ?>
</div>
</div>
<?php echo $comments; ?>
    <!--<div id="format-metadata">
        <h2><?php echo __('Format Metadata'); ?></h2>
        <div id="original-filename" class="element">
            <h3><?php echo __('Original Filename'); ?></h3>
            <div class="element-text"><?php echo metadata('file', 'Original Filename'); ?></div>
        </div>
    
        <div id="file-size" class="element">
            <h3><?php echo __('File Size'); ?></h3>
            <div class="element-text"><?php echo __('%s bytes', metadata('file', 'Size')); ?></div>
        </div>

        <div id="authentication" class="element">
            <h3><?php echo __('Authentication'); ?></h3>
            <div class="element-text"><?php echo metadata('file', 'Authentication'); ?></div>
        </div>
    </div><!-- end format-metadata -->
    
    <!--<div id="type-metadata" class="section">
        <h2><?php echo __('Type Metadata'); ?></h2>
        <div id="mime-type-browser" class="element">
            <h3><?php echo __('Mime Type'); ?></h3>
            <div class="element-text"><?php echo metadata('file', 'MIME Type'); ?></div>
        </div>
        <div id="file-type-os" class="element">
            <h3><?php echo __('File Type / OS'); ?></h3>
            <div class="element-text"><?php echo metadata('file', 'Type OS'); ?></div>
        </div>
    </div><!-- end type-metadata -->
</div>
		
</aside>
<?php if (isset($size) && $size[1] > 1000) : ?>
<script type="text/javascript">
jQuery(function ()  {
	jQuery('.zoomImage').elevateZoom({
		cursor:'crosshair', 
		loadingIcon:'<?php echo WEB_ROOT . '/'; ?>/themes/eman/images/spinner.gif',
		tint:true,
		tintColour:'#000033',
		tintOpacity:.4,
		borderSize:2,
    zoomWindowFadeIn: 250,
    zoomWindowFadeOut: 250,	
		zoomWindowWidth:'1020',
		zoomWindowHeight:'120',	
    zoomWindowPosition:'14',
		zoomWindowOffetx:-486,
		zoomWindowOffety:-65
	});
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
#right iframe {
	width:100%;
	clear:both;
	overflow:visible;
}
</style>
<script>
$ = jQuery;
$('#display-overlay').on('click', function() {
	$('#overlay').show();
	document.getElementById('transcription-full').contentWindow.location.reload(true);
});

$('body').on('click', 'button#overlay-close', function() {
	$('#overlay').hide();
});

</script> 
<?php echo $pager; ?>

<?php echo foot();?>
<script>
  function resizeIframe(obj) {
    obj.style.height = obj.contentWindow.document.firstChild.scrollHeight + 'px';
  }
</script>
<div id="overlay">
	<div id="left"><img src="<?php echo WEB_ROOT ?>/files/original/<?php echo $filen ?>" /></div>
	<div id="right"><iframe id="transcription-full" src='<?php echo WEB_ROOT . '/teibp/transcriptions/' . $xmlFileName ?>' frameborder="0" scrolling="no" onload="resizeIframe(this)" ></iframe></div>
	<div style="position:relative;margin-left:20%;clear:both;">
		<h2><em>Affichage optimisé pour Firefox. Les expressions MathML seront mal rendues sous les autres navigateurs.</em></h3>
	</div>	
	<button id="overlay-close">Fermer la fen&ecirc;tre</button>
</div>