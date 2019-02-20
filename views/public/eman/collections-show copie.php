<?php
  
    // Id collection courante
  $collectionId = metadata('collection', 'id');
  

  $collectionTitle = strip_formatting(metadata('collection', array('Dublin Core', 'Title')));
 	echo head(array('title'=> $collectionTitle, 'bodyclass' => 'collections show')); 
?>

<!-- en premier collection parente ou retour à l'accueil -->

<?php 
  // Arbre Parent : vide si collection mère
  $childCollection = get_db()->getTable('CollectionTree')->getAncestorTree($collectionId);  
	$parentId = get_db()->getTable('CollectionTree')->getParentCollection($collectionId);
	if ($parentId <> 0) {
		$parentCollection = get_record_by_id('Collection', $parentId);
		$parentName = metadata($parentCollection, array('Dublin Core', 'Title'));		
	}
?>	

<h1><?php 
		if(metadata('collection', array('Dublin Core', 'Title'))) {
				$titres = metadata('collection', array('Dublin Core', 'Title'), array('all' => true));
				foreach ($titres as $i => $titre) {
					echo "$titre";
				}
		} else {
				echo $title; 
		}?>
</h1>
<?php
    $auteur = metadata('collection', array('Dublin Core', 'Creator'));
    // Nom du jpeg = nom de l'auteur en minuscule sans espace
    $image = strtolower(strstr($auteur, ',', true)) . '.jpg';
    $linkCollection = "collections/show/" . metadata($collection, 'id');
    echo "<img class='eman-auteur' style='display:blockhright:100px;;max-width:100px;float:right;clear:right;margin:10px;' src='" . WEB_ROOT . "/files/original/$image' />";
    echo "<br />";
?>
<span  class="dclabel">Auteur<!--  (DC.Creator) --> : <?php echo metadata('collection', array('Dublin Core', 'Creator'), array('delimiter'=>', '));?></span><br />
<?php if ($childCollection) { ?>
	Collection parente <a href="<?php echo WEB_ROOT; ?>/collections/show/<?php echo $parentId; ?>"><?php echo $parentName; ?></a> 
<?php } else { ?>
	<a href="<?php echo WEB_ROOT; ?>">Revenir &agrave; l'accueil</a> 
<?php } ?>
<span class="dclabel" style="clear:both;display:block;">Collection cr&eacute;&eacute;e le <?php echo date('d/m/Y', strtotime(metadata('collection', 'added'))); ?> 
</span>

<?php echo get_specific_plugin_hook_output('Coins', 'public_collections_show', array('view' => $this, 'collection' => $collection));?>

<?php if (metadata('collection', array('Dublin Core', 'Creator'))) :?> 

<?php  endif; ?>	
	
<?php echo $content; ?>

<?php echo foot(); ?>
