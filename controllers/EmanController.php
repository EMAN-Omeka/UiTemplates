<?php
class UiTemplates_EmanController extends Omeka_Controller_AbstractActionController
{
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
			$blockContent = "";
			$content = array(); 
			// We pop bloc properties from config
			$options = $fields['options'];
			// ... and unset the options value to loop through the fields
			unset($fields['options']); 
			$blockOrder = $options['order'];
			$column = $options['column'];
				// Plugins
				switch ($block) { 
					case 'plugin_files' : 						
						$blockContent = $this->displayFiles($item);
						break;
					case 'plugin_gallery' : 
						$blockContent = $this->displayGallery($item);
						break;
						case 'plugin_social' : 
						$blockContent = $this->displaySocial($item);
						break;
					case 'plugin_citation' :
						$blockContent = EmanCitationPlugin::citationTokens(metadata('item','id'));
						break;
					case 'plugin_tags' : 
						$blockContent = tag_string($item);
						break;
					case 'plugin_relations' : 
 						$blockContent = $this->displayRelations($itemId);
						break;
					case 'plugin_geoloc' : 
 						$blockContent = $this->displayMap($item);
						break;
					case 'plugin_export' : 
						$blockContent = '<span id="jspdf" style="float:left;clear:left;margin-right:15px;"><img src="' . WEB_ROOT . '/themes/eman/images/pdf.png" alt="pdf"/></span>';
						$blockContent .= $this->displayExports();
						break;
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
						if ($elements[$dataId]['name'] == 'Type') {
								$fieldContent = "<a href='" . WEB_ROOT . "/items/browse?search=&advanced[1][element_id]=51&advanced[1][type]=is+exactly&advanced[1][terms]=$fieldContent'>$fieldContent</a>"; 
						}
						if (filter_var($fieldContent, FILTER_VALIDATE_URL)) {
							$fieldContent = "<a target='_blank' href='$fieldContent'>$fieldContent</a>";
						}
						
  					$fieldTitle = "<span style='font-weight:bold;'>$fieldTitle</span> : ";		
						$blockContent .= "<div class='field-uitemplates field-uitemplates-$dataId' id='$fieldName'>$fieldTitle $fieldContent</div>";
					}
				}
			}
			if ($blockContent) {
				if ($config[$block]['options']['display']) {
					$blockTitle = "<h2>" . $config[$block]['options']['title'] . "</h2>";
				} else {
					$blockTitle = "";
				}
				$content[$block] = "<div class='field-uitemplates field-uitemplates-$block' id='$block'>$blockTitle $blockContent</div>";
			}				
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
	public function displayMap($item) {
		$viewRenderer = Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
		if (null === $viewRenderer->view) {
		    $viewRenderer->initView();
		}
		$view = $viewRenderer->view;		
		$content = get_specific_plugin_hook_output('Geolocation', 'public_items_show', array('view' => $view, 'item' => $item));
		return $content;
	}	

}

