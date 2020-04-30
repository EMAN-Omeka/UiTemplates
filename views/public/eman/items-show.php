<?php
echo head(array('title' => metadata('item', array('Dublin Core', 'Title')),'bodyclass' => 'items show')); ?>

<?php 
if(metadata('item', array('Dublin Core', 'Title'))) {
	$titres = metadata('item', array('Dublin Core', 'Title'), array('all' => true));
	foreach ($titres as $i => $titre) {
		echo "<h1>$titre</h1>";
	}
} else {
	echo "<h1>$title</h1>"; 
}
?>
		  
<?php echo get_specific_plugin_hook_output('Coins', 'public_items_show', array('view' => $this, 'item' => $item));?>

<?php if (metadata('item', array('Dublin Core', 'Creator'))) : ?> 
<?php 
  $auteurs = metadata('item', array('Dublin Core', 'Creator'), array('delimiter'=>' ; ')); 
  strpos($auteurs, ';') ? $label = $this->controller->t('Auteurs') : $label = $this->controller->t('Auteur');       
?>
<br /><span  class="dclabel"><?php echo $label; ?></span><!--  (DC.Creator) --> : <?php echo $auteurs;?><br />
<?php  endif; ?>	

<?php echo $collection_link; ?>	

<?php echo $content; ?>
<span  class="dclabel" style='float:right;clear:right;'><?php echo str_replace('Notice créée par', $this->controller->t('Notice créée par'), get_specific_plugin_hook_output('Bookmarks', 'public_items_show', array('view' => $this, 'item' => $item))); ?></span>
<span  class="dclabel" style='float:right;clear:right;'><?php echo $this->controller->t('Notice créée le') . ' ' . date('d/m/Y', strtotime(metadata('item', 'added'))); ?> </span>
<span  class="dclabel" style='float:right;clear:right;'><?php echo $this->controller->t('Dernière modification le') . ' ' . date('d/m/Y', strtotime(metadata('item', 'modified'))); ?> </span>
<br /><br /><hr />
<?php echo get_specific_plugin_hook_output('Bnfmashup', 'public_items_show', array('view' => $this, 'item' => $item)); ?>

<ul class="item-pagination navigation">

<?php
 // Previous / Next file Pager complet ?
 if (isset($collection)) {
   
  $collection = get_current_record('collection');
  
  $maxItems = 25;

  $items = get_records('Item', array('collection' => $collection), 1000);

  $nbItems = count($items);
  if ($nbItems > $maxItems) { 
	  // Seach index of current file in all files
	  $i = 0;
	  foreach($items as $struct) {
	  	if ($item->id == $struct->id) {
	  		$currentItem = $struct;
	  		break;
	  	}
	  	$i++;
	  }

		// Slice array to contain only 25 files with current in the middle
		$i > $maxItems / 2 ? $start = round($i - $maxItems / 2) : $start = 0;

		$items = array_slice($items, $start, $maxItems);
  }
  
  $pager = array();
  $i = $current = 0;
  $prec = $suiv = "";
  
  set_loop_records('item', $items);  
  foreach (loop('items') as $loopItem) {
    $classes = "eman-item-link";
    if ($loopItem->id == $item->id) {
    	$classes .= ' eman-item-link-current';
    	$current = $i;
    	if ($i > 0) {
				$prec = link_to_item('<span style="font-size:32px;float:left;">&loarr;</span> ', array(), 'show', $items[$i-1]);
    	}    	
    	if ($i < count($items) - 1) {
				$suiv = link_to_item('<span style="font-size:32px;">&roarr;</span>', array(), 'show', $items[$i+1]);
    	}    	
    }    
    $pager[] = link_to_item(metadata($loopItem, array('Dublin Core', 'Title')), array('class' => $classes), 'show', $items[$i]);
  	$i++;
  }

  $pager = "<div id='eman-pager-items'><h3>" . $this->controller->t('Autres notices de la collection') . "</h3>" . $prec . implode('<div class="eman-items-sep"> | </div>', $pager) . $suiv . "</div>";


// fire_plugin_hook('public_items_show', array('view' => $this, 'item' => $item));
echo get_specific_plugin_hook_output('UserProfiles', 'public_items_show', array('view' => $this, 'item' => $item));
echo $pager;

?>
</ul>
<?php } ?>
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
/*
   $('#comment-form-body').show();
   $('#comment-form-body').css('visibility' ,'visible !important');
   $('#comment-form-body').css('display' ,'block !important');
*/
    $('.suite').click(function() {
      $(this).parent().parent().find('.fieldcontentcomplet').show();
      $(this).parent().parent().find('.fieldcontentshort').hide();
//       $(this).hide();
    });
    $('.replier').click(function() {
//       $(this).parent().hide();
      $(this).parent().parent().find('.fieldcontentcomplet').hide();
      $(this).parent().parent().find('.fieldcontentshort').show();
    });
    
   $('#files-carousel').slick({ 
//     autoplay: true,
//     autoplaySpeed: 2000,
		dots: true,
		appendDots: '#plugin_gallery > span',
    rows: 4,
    slidesPerRow: 4,
//     lazyLoad: 'progressive',
    pauseOnFocus: true,
  });
});
 </script> 
   <script src='/pdf/pdfmake/build/pdfmake.min.js'></script>
  <script src='/pdf/pdfmake/build/vfs_fonts.js'></script>
  <script>
    function doPDF() {
      var lehtml = $('#content').html();
      var docDefinition = { content: lehtml };
      pdfMake.createPdf(docDefinition).open();      
    }
  </script>
<!--   <button onclick="doPDF();">PDF</button> -->
<?php echo foot(); ?>
