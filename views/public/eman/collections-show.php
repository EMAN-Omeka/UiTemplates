<?php
echo head(array('title' => metadata('collection', array('Dublin Core', 'Title')),'bodyclass' => 'collections show ' . $bodyColumns)); ?>

<?php
  // Arbre Parent : vide si collection mère
  $childCollection = get_db()->getTable('CollectionTree')->getAncestorTree($collectionId);
	$parentId = get_db()->getTable('CollectionTree')->getParentCollection($collectionId);
	if ($parentId <> 0) {
		$parentCollection = get_record_by_id('Collection', $parentId);
		$parentName = metadata($parentCollection, array('Dublin Core', 'Title'));
	}
  $translations = unserialize(base64_decode(get_option('ui_templates_translations')));
?>

<h1><?php
		if(metadata('collection', array('Dublin Core', 'Title'))) {
				$titres = metadata('collection', array('Dublin Core', 'Title'), array('all' => true));
				foreach ($titres as $i => $titre) {
					echo "<h1>$titre</h1>";
				}
		} else {
				echo $title;
		}?>
</h1>
<?php
    $auteur = metadata('collection', array('Dublin Core', 'Identifier'));
    // Nom du jpeg = nom de l'auteur en minuscule sans espace
    $image = strtolower(strstr($auteur, ',', true)) . '.jpg';
    $linkCollection = "collections/show/" . metadata($collection, 'id');
    $path = FILES_DIR . "/original/$image";
    if (file_exists($path)) {
      $path = WEB_ROOT . "/files/original/$image";
      echo "<img class='eman-auteur' style='display:block;right:100px;max-width:100px;float:right;clear:right;margin:10px;' src='" . $path . "' />";
    }
    echo "<br />";
?>
<?php
  $auteurs = metadata('collection', array('Dublin Core', 'Creator'), array('delimiter'=>' ; '));
  strpos($auteurs, ';') ? $label = $this->controller->t('Auteurs') : $label = $this->controller->t('Auteur');
  if ($auteurs) { ?>
    <span  class="dclabel"><?php echo $label; ?><!--  (DC.Creator) --> : <?php echo $auteurs;?></span><br />
  <?php } ?>
<span class="uit-link">
  <?php if ($childCollection) { ?>
  	<?php echo $this->controller->t('Collection parente') ?> : <a href="<?php echo WEB_ROOT; ?>/collections/show/<?php echo $parentId; ?>"><?php echo $parentName; ?></a>
  <?php } else { ?>
  	<a href="<?php echo WEB_ROOT; ?>"><?php echo $this->controller->t('Revenir à l\'accueil') ?></a>
  <?php } ?>
</span>

<?php echo get_specific_plugin_hook_output('Coins', 'public_collections_show', array('view' => $this, 'collection' => $collection));?>

<?php if (metadata('collection', array('Dublin Core', 'Creator'))) :?>

<?php  endif; ?>

<?php echo $content; ?>

<span class="dclabel" style='float:right;clear:right;'><?php echo str_replace('Collection créée par', $this->controller->t('Collection créée par'), get_specific_plugin_hook_output('Bookmarks', 'public_collections_show', array('view' => $this, 'collection' => $collection))); ?></span>
<span class="dclabel" style="clear:both;display:block;float:right;"><?php echo $this->controller->t('Collection créée le') . ' ' . date('d/m/Y', strtotime(metadata('collection', 'added'))); ?>  </span>
<span class="dclabel" style="clear:both;display:block;float:right;"><?php echo $this->controller->t('Dernière modification le') . ' ' . date('d/m/Y', strtotime(metadata('collection', 'modified'))); ?> </span>

<style>
.suite, .replier {
  cursor: pointer;
  font-style:italic;
  font-weight: bold;
  clear:both;
  display:block;
  float:right;
}
.uit-link {
  display:block;
  clear:both;
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
</script>

<?php echo foot(); ?>