<?php
class UiTemplates_EmanController extends Omeka_Controller_AbstractActionController
{
	public function filesShowAction()
	{
		$fileId = $this->view->collectionId = $this->getParam('id');
		$file = get_record_by_id('file', $fileId);
		if (! $file) {
			throw new Zend_Controller_Action_Exception('This page does not exist', 404);
			return;
		}
		set_current_record('file', $file);
		
		$db = get_db();
		$config = $db->query("SELECT * FROM `$db->UiTemplates` WHERE template_type = 'Files'")->fetchAll();
		$config = array_pop($config);
		$config = unserialize(base64_decode($config['text']));
		
		foreach ($config as $key => $val) {
			$config[$key] ? $this->view->{$key} = $config[$key] : $this->view->{$key} = '';				
		}
		$this->view->exports = $this->displayExports();
		$this->view->citation = '<div class="element-text field-uitemplates field-uitemplates-citation" id="citation">' . EmanCitationPlugin::citationTokens(metadata('file', 'id'), 'file') . '</div>';		
	}	
	public function collectionsShowAction()
	{
		$collectionId = $this->view->collectionId = $this->getParam('id');
		$collection = get_record_by_id('collection', $collectionId);
		if (! $collection) {
			throw new Zend_Controller_Action_Exception('This page does not exist', 404);
			return;
		}
		set_current_record('collection', $collection);
		
		$db = get_db();
		$config = $db->query("SELECT * FROM `$db->UiTemplates` WHERE template_type = 'Collections'")->fetchAll();
		$config = array_pop($config);
		$config = unserialize(base64_decode($config['text']));
				
		foreach ($config as $key => $val) {
			$config[$key] ? $this->view->{$key} = $config[$key] : $this->view->{$key} = '';				
		}
		
		$this->view->citation = '<div class="element-text field-uitemplates field-uitemplates-citation" id="citation">' . EmanCitationPlugin::citationTokens(metadata('collection', 'id'), 'collection') . '</div>';
		
		$this->view->exports = $this->displayExports();
	}	
	public function itemsShowAction()
	{
		
		$itemId = $this->view->itemId = $this->getParam('id');		
		$item = get_record_by_id('item', $itemId);		
		if (! $item) {
			throw new Zend_Controller_Action_Exception('This page does not exist', 404);
			return;
		} 
		set_current_record('item', $item);
		$collection = get_collection_for_item();
		if ($collection) {
			set_current_record('collection', $collection);
		}
		// Convert element's ids to text ids
		$db = get_db();
		$elData = $db->query("SELECT o.id, o.name, s.name setName FROM `$db->Elements` o, `$db->ElementSets` s WHERE o.element_set_id = s.id AND o.id IN (SELECT DISTINCT element_id FROM `$db->ElementTexts`) ORDER BY o.name");
		$elData = $elData->fetchAll();
		foreach($elData as $id => $element) {
			$elements[$element['id']] = array("name" => $element['name'], "set" => $element['setName']);
		}
				
		// Retrieve config from db for this Item type
		$itemTypeId = 1; // TODO : change for current item type
		$config = $db->query("SELECT * FROM `$db->UiTemplates` WHERE template_type = 'Items'")->fetchAll();
		$config = array_pop($config);
		$config = unserialize(base64_decode($config['text']));
		$collection_link = $config['collection_link'];
    unset($config['collection_link']);
    unset($config['use_ui_item_templates']);
		// Format each selected data from config for display
		$column1 = $column2 = "";
		foreach($config as $block => $fields) {				
			$content = array(); 
			// We pop bloc properties from config
			$options = $fields['options'];
			// ... and unset the options value to loop through the fields
			unset($fields['options']); 
			$blockOrder = $options['order'];
			$column = $options['column'];
			if (strpos($block, 'plugin_') === false) {			
				$title = $options['title'];
				if ($options['display']) {
					$blockTitle = "<h2>$title</h2>";
				} else {
					$blockTitle = "";
				}
			$content[] = "<div class='block-uitemplates uitemplates-order-$blockOrder' id='$block'>$blockTitle";
			} else {
				// Plugins
				$content[] = "<div class='block-uitemplates block-uitemplates-plugin uitemplates-order-$blockOrder' id='$block'>";
				if ($config[$block]['options']['display']) {
					$content[$block] = "<h2>" . $config[$block]['options']['title'] . "</h2>";						
				} else {
					$content[$block] = "";
				}
				switch ($block) { 
					case 'plugin_files' : 						
						$content[$block] .= "<div class='field-uitemplates field-uitemplates-$block' id='$block'>" . $this->displayFiles($item) . "</div>" ;
						break;
					case 'plugin_gallery' : 
						$content[$block] .= "<div class='field-uitemplates field-uitemplates-$block' id='$block'>" . $this->displayGallery($item) . "</div>" ;
						break;
						case 'plugin_social' : 
						$content[$block] .= "<div class='field-uitemplates field-uitemplates-$block' id='$block'>" . $this->displaySocial($item) . "</div>" ;
						break;
					case 'plugin_citation' :
						$content[$block] .= "<div class=\"element-text field-uitemplates field-uitemplates-$block\" id=\"$block\">" .  EmanCitationPlugin::citationTokens(metadata('item','id')) . '</div>';
						break;
					case 'plugin_tags' : 
						$content[$block] .= "<div class='element-text field-uitemplates field-uitemplates-$block' id='$block'>" . tag_string($item) . '</div>';
						break;
					case 'plugin_relations' : 
 						$content[$block] .= "<div class='element-text field-uitemplates field-uitemplates-$block' id='$block'>" . $this->displayRelations($itemId) . '</div>';
						break;
					case 'plugin_export' : 
						$content[$block] .= '<div class="element-text field-uitemplates field-uitemplates-$block" id="$block">';
						$content[$block] .= '<span id="jspdf" style="float:left;clear:left;margin-right:15px;"><img src="' . WEB_ROOT . '/themes/eman/images/pdf.png" alt="pdf"/></span>';
						$content[$block] .= $this->displayExports() . '</div>';
						break;
				}
			}				

			foreach($fields as $fieldName => $dataId) {				
				if (is_numeric($dataId)) {
						if ($fields['name_' . $fieldName] <> null) {
							$fieldTitle = $fields['name_' . $fieldName];
						} else {
							$fieldTitle = $elements[$dataId]['name'];
						}
					$fieldContent =  metadata('item', array($elements[$dataId]['set'], $elements[$dataId]['name']));
					if ($fieldContent) { 
						$fieldTitle = "<span style='font-weight:bold;'>$fieldTitle</span> : ";						
						$content[$fieldName] = "<div class='field-uitemplates field-uitemplates-$dataId' id='$fieldName'>$fieldTitle $fieldContent</div>";
					}
				}
			}

			$content[] = "</div>";	// Block end			
			// Channel output to column choosen in config
			if ($column == 1) {
				$column1 .= implode('', $content);
			} elseif ($column == 2) {
				$column2 .= implode('', $content);
			}					
		}
		// Lien vers la collection ?  
		if ($collection_link && isset($collection)) {
			$this->view->collection_link = '<p id="linkHome"><span class="dclabel">Collection : </span>' . link_to_collection() . ' <br />';  
		}	else {
			$this->view->collection_link = '';
		}
		// Edit link for Item
		if ($currentUser = current_user()) {		
			if (in_array($currentUser->role, array('super', 'admin', 'editor'))) {
				$edit_link = "<a class='eman-edit-link' href='" . WEB_ROOT . "/admin/items/edit/$itemId'>Editer ce contenu</a>";
			}
		}
		$this->view->edit_link = $edit_link;
		$this->view->content = "<div id='primary'>$column1</div><aside id='sidebar'>$column2</aside>"; 	
	}
	public function displayFiles($item) {
		$fileGallery = "";
		if (metadata('item', 'has files')) {	/*
			Affichage du visualiseur en fonction dy type de fichier
			jpg -> Bookreader
			Pdf -> DocViewer
			Autre -> Affichage classique */
				
			ob_start(); // We need to capture plugin ouput
			set_loop_records('files', $item->Files);
			foreach (loop('files') as $file):
			if ($file->getExtension() == 'jpg') {
				fire_plugin_hook('book_reader_item_show', array(
				'view' => $this,
				'item' => $item,
				'page' => '0',
				'embed_functions' => false,
				'mode_page' => 1,
				));
				break;
			} elseif ($file->getExtension() =='pdf') {
				echo  '<iframe width=100% height=800 src="' . WEB_ROOT . '/files/original/'.metadata($file,'filename').'"></iframe>';	break;
			} else {
				echo files_for_item();
				break;
			}
			endforeach;
		}
		$fileGallery = ob_get_contents();
		ob_end_clean();
		return $fileGallery;
	}	
	public function displaySocial($item) {
		$socialPlugins = get_specific_plugin_hook_output('SocialBookmarking', 'public_items_show', array('view' => $this, 'item' => $item));		
		return $socialPlugins;
	}
	
	public function displayGallery($item) {
		$FilesGallery = "";
		if (metadata($item, 'has files')) {
			$FilesGallery .= '<div id="itemfiles" class="element">';
			$FilesGallery .= '<div>' . files_for_item(array('linkToMetadata' => true, 'linkAttributes' => array('class' => 'file-display'))) . '</div>';
			$FilesGallery .= '<p id="btnItemfiles"> Voir plus</p>';
			$FilesGallery .= '</div>';
		}
		$FilesGallery = preg_replace('/<h3>[^>]+\<\/h3>/i', "", $FilesGallery);
		
		return $FilesGallery;
	}

	public function displayRelations($item) {
		$relations = get_specific_plugin_hook_output('ItemRelations', 'public_items_show', array('view' => $this, 'item' => $item));
		$relations .= "<br />";		
		return $relations;
	}	

	public function displayExports() {
		$output_formats = array('atom', 'dcmes-xml', 'json', 'omeka-json', 'omeka-xml');
		$content = get_view()->partial(
				'common/output-format-list.php',
				array('output_formats' => $output_formats, 'query' => $_GET,
						'list' => false, 'delimiter' => ', ')
		);
		return $content;
	}
	
// 	public function displayXMLExports($item) {
// 		$output_formats = array('atom', 'dcmes-xml', 'json', 'omeka-json', 'omeka-xml');
// 		return get_view()->partial(
// 				'common/output-format-list.php',
// 				array('output_formats' => $output_formats, 'query' => $_GET,
// 						'list' => false, 'delimiter' => ', ')
// 		);		
// 	}
}

