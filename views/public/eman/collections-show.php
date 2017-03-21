<?php
	// Nous avons besoin du code de Simple Pages
  use SimplePages\SimplePagesPlugin;
  
  // Id collection courante
  $collectionId = metadata('collection', 'id');
  
  // Arbre Parent : vide si collection mère
  $childCollection = get_db()->getTable('CollectionTree')->getAncestorTree($collectionId);
  $collectionTitle = strip_formatting(metadata('collection', array('Dublin Core', 'Title')));
 	echo head(array('title'=> $collectionTitle, 'bodyclass' => 'collections show')); 
?>

<!-- en premier collection parente ou retour à l'accueil -->

<?php 
	$parentId = get_db()->getTable('CollectionTree')->getParentCollection($collectionId);
	if ($parentId <> 0) {
		$parentCollection = get_record_by_id('Collection', $parentId);
		$parentName = metadata($parentCollection, array('Dublin Core', 'Title'));		
	}
?>

<?php if ($childCollection) { ?>
	Collection <a href="<?php echo WEB_ROOT; ?>/collections/show/<?php echo $parentId; ?>"><?php echo $parentName; ?><P></a> 
<?php } else { ?>
	<a href="<?php echo WEB_ROOT; ?>">Revenir à l'accueil<P></a> 
<?php } ?>	

  <?php
      $auteur = metadata($collection, array('Dublin Core', 'Creator'));
      // Nom du jpeg = nom de l'auteur en minuscule sans espace
      $image = strtolower(strstr($auteur, ',', true)) . '.jpg';
      $linkCollection = "collections/show/" . metadata($collection, 'id');
      echo "<img class='eman-auteur' style='display:blockhright:100px;;max-width:100px;float:right;clear:right;margin:10px;' src='" . WEB_ROOT . "/files/original/$image'>";
      echo "<br />";
  ?>

<!-- titre de la collection -->

<h1><B><?php	echo $collectionTitle; ?></B></h1>

    <?php
				$identifiers = metadata('collection', array('Dublin Core', 'Identifier'), array('all' => true));
				if ($identifiers) {
  				$identifiers = explode( ',', $identifiers[0]);  				
  				$sqlIn = '';
  				foreach ($identifiers as $index => $identifier) {
    				$sqlIn .= "'" . trim($identifier) . "',";
  				}
  				$sqlIn = rtrim($sqlIn, ",");
					$db = get_db();
					$query = "SELECT slug, title FROM `$db->SimplePagesPages` WHERE slug IN ($sqlIn)";
					$simplepages = $db->query($query)->fetchAll();
 					$simplepageLinks = "<ul class='auteur-onglets' >";
	        foreach ($simplepages as $nb => $simplepage) {  	          
	         	$simplepageLinks .= "<li><a href='" . WEB_ROOT . '/' . $simplepage['slug'] . "'>" . $simplepage['title'] . "</a></li>";
	        }
	        $simplepageLinks .= "</ul>";
	        echo $simplepageLinks;
				}
    ?>

<!-- affichage de la présentation & auteur de la fiche de la collection -->

    <div class="collection-description">
        <h2><?php echo $dcdescription ?></h2>
    <?php if (metadata('collection', array('Dublin Core', 'Description'))): ?>
        <!--[selon le projet la formule est dossier ou collection] -->
        <?php echo text_to_paragraphs(implode("\n", metadata('collection', array('Dublin Core', 'Description'), array('all' => true)))); ?> 
    <?php endif; ?>
	
    <?php if (metadata('collection', array('Dublin Core', 'Contributor'))) :?> 
			<div style="float:right"><strong>Editeur scientifique de la collection</strong> : 
			<?php echo metadata('collection', array('Dublin Core', 'Contributor'));?></div>
		<?php  endif; ?>
  </div>  
  
<!-- affichage des collections enfants -->
  <?php 
		// Collections enfants
		$enfants = get_db()->getTable('CollectionTree')->getDescendantTree(metadata('collection', 'id'));
		$descendants = "";
		foreach ($enfants as $id => $enfant) {
			$descendants .= "<li><a href='" . WEB_ROOT . "/collections/show/" . $enfant['id'] . "'>" . $enfant['name'] . "</a></li>";			
		} 
		if ($descendants) { ?>
			<div class="collection-enfants">
			 <h2><?php echo $dossiers; ?></h2>  
				<ul><?php echo $descendants; ?></ul>				
		 </div> 		
		<?php } ?>


<div id="collection-items">
<h2><?php echo $documents; ?> : <?php echo link_to_items_browse(__('Consulter', $collectionTitle), array('collection' => metadata('collection', 'id'))); ?></h2>

</div> 

<div id="collection-items">

 <h2><?php echo $titrechamps; ?></h2>  

	<?php if (metadata('collection', array('Dublin Core', 'Title'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($collection, 'Title', $dctitle); ?>
	<?php endif; ?>	

	<?php if (metadata('collection', array('Dublin Core', 'Creator'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($collection, 'Creator', $dccreator); ?>
	<?php  endif; ?>	
		
	<?php if (metadata('collection', array('Dublin Core', 'Date'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($collection, 'Date', $dcdate); ?>
	<?php  endif; ?>	
        
	<?php if (metadata('collection', array('Dublin Core', 'Type'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($collection, 'Type', $dctype); ?>
	<?php  endif; ?>
   
	<?php if (metadata('collection', array('Dublin Core', 'Subject'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($collection, 'Subject', $dcsubject); ?>
  <?php  endif; ?>

	<?php if (metadata('collection', array('Dublin Core', 'Language'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($collection, 'Language', $dclanguage); ?>
	<?php  endif; ?>

	<?php if (metadata('collection', array('Dublin Core', 'Source'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($collection, 'Source', $dcsource); ?>
	<?php  endif; ?>	

	<?php if (metadata('collection', array('Dublin Core', 'Format'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($collection, 'Format', $dcformat); ?>
  <?php  endif; ?>

	<?php if (metadata('collection', array('Dublin Core', 'Relation'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($collection, 'Relation', $dcrelation); ?>
	<?php  endif; ?>

	<?php if (metadata('collection', array('Dublin Core', 'Coverage'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($collection, 'Coverage', $dccoverage); ?>
	<?php  endif; ?>

 	<?php if (metadata('collection', array('Dublin Core', 'Contributor'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($collection, 'Contributor', $dccontributor); ?>
	<?php  endif; ?>

	<?php if (metadata('collection', array('Dublin Core', 'Publisher'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($collection, 'Publisher', $dcpublisher); ?>
  <?php  endif; ?>

	<?php if (metadata('collection', array('Dublin Core', 'Rights'))) :?> 
		<?php echo UiTemplatesPlugin::displayObjectDC($collection, 'Rights', $dcrights); ?>
	<?php  endif; ?>

		<p><span  class="dclabel"><strong>Date de cr&eacute;ation de la notice</strong></span> : <?php echo date('d/m/Y', strtotime(metadata('collection', 'added'))); ?></p> 
	</div>
<div>
<h2><?php echo $titrecitation; ?></h2>
		<?php echo $citation; ?>
</div>

<div>
	<h2 style='margin-top:0;'><?php echo $titresocial; ?></h2>
<?php       
	$url = record_url($collection, 'show', true);
	$title = strip_formatting(metadata($collection, array('Dublin Core', 'Title')));
	$description = strip_formatting(metadata($collection, array('Dublin Core', 'Description')));
	echo social_bookmarking_toolbar($url, $title, $description);
?>
</div>

<!-- export PDF et autre format -->
<div id="exports" class="element">
<div style='clear:both;margin-top:0;'><h2 style='margin-top:0;'><?php echo $titreexports; ?></h2></div>
<span id="jspdf" style="float:left;clear:left;margin-right:15px;"><img src="<?php echo WEB_ROOT; ?>/themes/eman/images/pdf.png" alt="pdf"/></span>
<?php echo $exports; ?>
</div>

<?php echo foot(); ?>

<!-- auparavant on avait l'affichage des documents de la collection avec cette manip (Ajax ?) mais je serai partisan d'enlever et d'en rester au lien "voir les docs de la collection" et on arrive sur une nouvelle page
<div id="collection-items">
<div class="collection-description">
 <h2>Documents de la collection</h2>
</div> 
<?php 
// Prepare list options
	$db = get_db();
	$itemtypes = $db->query("SELECT DISTINCT(text) nom FROM omeka_element_texts t LEFT JOIN omeka_items i ON i.id = t.record_id WHERE t.element_id = 51 AND i.collection_id = $collectionId")->fetchAll();
	$langues = $db->query("SELECT DISTINCT(text) nom FROM omeka_element_texts t LEFT JOIN omeka_items i ON i.id = t.record_id WHERE t.element_id = 44 AND i.collection_id = $collectionId")->fetchAll();
	
?>
<script type="text/javascript" src="<?php echo WEB_ROOT . '/plugins/Eman/javascripts/ajax.js'; ?>"></script>
<form>
				
			<div id="formetype" style="width:25%;float:left;">
			<label>Type</label>
			 <select id="item-type">
			 <option value="Tous">Tous</option>
			 <?php foreach ($itemtypes as $type) {
			 	echo "<option value='" . $type['nom'] . "'>" . $type['nom'] . "</option>";
			 }
			 ?>
  	   </select>	
			</div>

			<div id="formetype" style="width:25%;float:left;">
			<label>Langue</label>
			 <select id='item-language'>
			 <option value="Tous">Tous</option>
			 <?php foreach ($langues as $langue) {
			 	echo "<option value='" . $langue['nom'] . "'>" . $langue['nom'] . "</option>";
			 }
			 ?>
  	   </select>	
			</div>
	
    	<label>Trier par</label>
         <select id='item-sort'>
          <option value="titre">Titre</option>
          <option value="date">Date d'ajout</option>
         </select>
    	</div>
    	
			<input type="hidden" id="collectionId" value="<?php echo $collectionId; ?>">    	
    	<input type="hidden" id="phpWebRoot" value="<?php echo WEB_ROOT; ?>">    	
</form>
<div id="eman-ajax-results"></div>  [la sélection ne fonctionne pas sur les bons items]
-->
      <!-- The following prints a list of all tags associated with the item -->
      <!-- il n'y a pas de tages pour une collection !!! A enlever N-->
<!--
   <?php if (metadata('collection', 'has tags')): ?>
    <div id="item-tags" class="element">
        <h4><?php echo __('Tags').': '; ?></h4>
        <div class="element-text"><?php echo tag_string_collection('item'); ?></div>
    </div>
    <?php endif;?>  

-->
