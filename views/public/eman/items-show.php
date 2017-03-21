<?php
echo head(array('title' => metadata('item', array('Dublin Core', 'Title')),'bodyclass' => 'items show')); ?>

<?php echo $edit_link; ?>

<h1><?php echo metadata('item', array('Dublin Core', 'Title')); ?></h1>
<?php echo get_specific_plugin_hook_output('Coins', 'public_items_show', array('view' => $this, 'item' => $item));?>

<?php echo $collection_link; ?>	
<?php if (metadata('item', array('Dublin Core', 'Creator'))) :?> 
<span  class="dclabel">Auteur</span><!--  (DC.Creator) --> : <?php echo metadata('item', array('Dublin Core', 'Creator'), array('delimiter'=>', '));?><br />
<?php  endif; ?>	
<span  class="dclabel">Notice cr&eacute;&eacute;e le <?php echo date('d/m/Y', strtotime(metadata('item', 'added'))); ?> </span>
	
<?php echo $content; ?>

<!-- 
<ul class="item-pagination navigation">
<li id="previous-item" class="previous"><?php echo link_to_previous_item_show(); ?></li>
    <li id="next-item" class="next"><?php echo link_to_next_item_show(); ?></li>
</ul>
 -->
<?php echo foot(); ?>
