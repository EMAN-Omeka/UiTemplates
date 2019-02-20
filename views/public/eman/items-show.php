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
		}?>

<?php echo get_specific_plugin_hook_output('Coins', 'public_items_show', array('view' => $this, 'item' => $item));?>

<?php if (metadata('item', array('Dublin Core', 'Creator'))) :?> 
<br /><span  class="dclabel">Auteur</span><!--  (DC.Creator) --> : <?php echo metadata('item', array('Dublin Core', 'Creator'), array('delimiter'=>' ; '));?><br />
<?php  endif; ?>	
<?php echo $collection_link; ?>	

	
<?php echo $content; 
  $url = $_SERVER["REQUEST_URI"];
  $pos = strpos($url, '/', 1);
  $url = substr($url, $pos);
//   echo $url;
  ?>

<span  class="dclabel" style='float:right;clear:right;'>Notice cr&eacute;&eacute;e le <?php echo date('d/m/Y', strtotime(metadata('item', 'added'))); ?> </span>
<!-- 
<ul class="item-pagination navigation">
<li id="previous-item" class="previous"><?php echo link_to_previous_item_show(); ?></li>
    <li id="next-item" class="next"><?php echo link_to_next_item_show(); ?></li>
</ul>
 -->
 <script>
 $ = jQuery;
 
 $(document).ready(function(){
/*
   $('#comment-form-body').show();
   $('#comment-form-body').css('visibility' ,'visible !important');
   $('#comment-form-body').css('display' ,'block !important');
*/
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
