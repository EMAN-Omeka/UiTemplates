<?php
class UiTemplates_EmanController extends Omeka_Controller_AbstractActionController
{
public function buildContent($entity) {
  $type = get_class($entity);
  $column1 = $column2 = "";
  
	$db = get_db();
	$config = $db->query("SELECT * FROM `$db->UiTemplates` WHERE template_type = '" . $type . "s'")->fetchAll();
	$config = array_pop($config);
	$config = unserialize(base64_decode($config['text']));
	$elements = array();
	$elData = $db->query("SELECT o.id, o.name, s.name setName FROM `$db->Elements` o, `$db->ElementSets` s WHERE o.element_set_id = s.id AND o.id IN (SELECT DISTINCT element_id FROM `$db->ElementTexts`) ORDER BY o.name");
	$elData = $elData->fetchAll();
	foreach($elData as $id => $element) {
		$elements[$element['id']] = array("name" => $element['name'], "set" => $element['setName']);
	}
		foreach($config as $block => $fields) {				
			$blockContent = "";
			$content = array(); 
			// Skip collection link option
			if ($block == 'collection_link') {
    		if ($fields == 1) {
      		set_current_record('item', $entity); 		
    			$this->view->collection_link = '<div style="background: transparent; border:none;box-shadow:none;margin:0;padding:0;"><span class="dclabel">Collection : </span>' . link_to_collection_for_item();  
    			$collection = get_collection_for_item();
    			$link_to_collection_items = WEB_ROOT . '/items/browse?collection=' . $collection->id;
    			if ($collection) {
      			$link_to_collection_items = WEB_ROOT . '/items/browse?collection=' . $collection->id;
      			if ($collection->id) {
        			$this->view->collection_link .= "&nbsp;-&nbsp;<a href='$link_to_collection_items'>Voir les autres notices de cette collection</a>";      
      			}
      		}
          $this->view->collection_link .=  "</div>";      		
    		}	else {
    			$this->view->collection_link = '';
    		}  			
  			continue;
			}
			// We pop bloc properties from config
			$options = $fields['options'];
			// ... and unset the options value to loop through the fields
			unset($fields['options']); 
			$blockOrder = $options['order'];
			$column = $options['column'];
			
			$t = strtolower($type);  			
			// Plugins
			switch ($block) { 
				case 'plugin_files' : 						
					$blockContent = $this->displayFiles($entity);
					break;
				case 'plugin_gallery' : 
					$blockContent = $this->displayGallery($entity);
					break;
			  case 'plugin_social' : 
					$blockContent = $this->displaySocial($entity);
					break;
				case 'plugin_citation' :
					$blockContent = EmanCitationPlugin::citationTokens(metadata($t,'id'), $t);
					break;
				case 'plugin_tags' : 
					$blockContent = tag_string($entity);
					break;
				case 'plugin_relations' : 
						$blockContent = $this->displayRelations($entity->id);
					break;
				case 'plugin_geoloc' : 
						$blockContent = $this->displayMap($entity);
					break;
				case 'plugin_comment' : 
					$blockContent = $this->displayComments($entity);
					break;
			  case 'plugin_export' : 
					$blockContent = '';
					$blockContent .= $this->displayExports();
					break;					
				case 'plugin_children' : 
					$blockContent = $this->displayChildrenCollections($entity);
					break;						
				case 'plugin_items' : 
					$blockContent = $this->displayItems($entity);
					break;						
				case 'plugin_file' : 
					$blockContent = $this->displayFile($entity);
					break;						
				case 'plugin_fileinfo' : 
					$blockContent = $this->displayFileInfo($entity);
					break;						
/*
				case 'plugin_transcript' : 
					$blockContent = $this->displayTranscription($entity);
					break;		
*/				
		  }

			foreach($fields as $fieldName => $dataId) {				
				$fieldContent = "";				
				if (is_numeric($dataId) && $dataId <> 0) {
  				if (isset($fields['name_' . $fieldName])) {
						if ($fields['name_' . $fieldName] <> null) {
							$fieldTitle = $fields['name_' . $fieldName];
						} else {
							$fieldTitle = $elements[$dataId]['name'];
						}
				  }
				  // Exception pour bloc Item Relations
				  if (substr($fieldName, 0, 4) == 'bloc' || substr($fieldName, 0, 16) == 'plugin_relations' ) {				  
  					$fieldData = metadata($t, array($elements[$dataId]['set'], $elements[$dataId]['name']), array('no_filter' => true, 'all' => true));	
            $fieldContent = array();
  					foreach($fieldData as $i => $fieldInstance ) {
  						if ($fieldInstance) { 
  							// Field contains exactly an URL : link in a new window 
  							if (filter_var($fieldInstance, FILTER_VALIDATE_URL)) {
  								$fieldInstance = '<a target="_blank" href="$fieldInstance">$fieldInstance</a>';
  							}
  							// Type field : create a link to items/browse
  							if ($elements[$dataId]['name'] == 'Type') {
  								$fieldInstance = "<a href='" . WEB_ROOT . "/items/browse?type=" . urlencode($fieldInstance) . "'>$fieldInstance</a>";
  							}
  							$fieldContent[] = $fieldInstance;
  						}												
  					}
  					// If field contains something, display it
  					if (count($fieldContent) > 1) {
  						$fieldContent = "<ul class='eman-list'><li>" . implode("</li><li>", $fieldContent) . "</li></ul>";
  					} elseif ($fieldContent) {
  						$fieldContent = $fieldContent[0];
  					}

  					if ($fieldContent) {
  	  				$fieldTitle = "<span style='font-weight:bold;'>$fieldTitle</span> : ";		
  						$blockContent .= "<div class='field-uitemplates field-uitemplates-$dataId' id='$fieldName'>$fieldTitle $fieldContent</div>";
  					}
  				}
				}
			}
			
			if ($blockContent) {
				if ($config[$block]['options']['display']) {
					$blockTitle = "<h2>" . $config[$block]['options']['title'] . "</h2>";
				} else {
					$blockTitle = "";
				}
        if ($config[$block]['options']['private'] == 0 || ($config[$block]['options']['private'] == 1 && current_user())) {
 				  $content[$block] = "<div class='field-uitemplates field-uitemplates-$block' id='$block'>$blockTitle $blockContent</div>";            
				}
			}			
			// Channel output to column choosen in config
			if ($column == 1) {
				$column1 .= implode('', $content);
			} elseif ($column == 2) {
				$column2 .= implode('', $content);
			}					
		}
		if ($column2 && $column1) {
  		$content = "<div id='primary'>$column1</div><aside id='sidebar'>$column2</aside>";
		} else {
  		$content = "<div id='primary' style='width:99%;'>$column1$column2</div>";
		}
    return $content;
}

	public function filesShowAction()
	{
		$fileId = $this->view->filesId = $this->getParam('id');
		$file = get_record_by_id('file', $fileId);
		if (! $file) {
			throw new Zend_Controller_Action_Exception('This page does not exist', 404);
			return;
		}
		set_current_record('file', $file);		
    $this->view->content = $this->buildContent($file);    
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
    $this->view->content = $this->buildContent($collection);    
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
			$collectionId = $collection->id;
		}
    
    $this->view->content = $this->buildContent($item);
    
    // Lien vers la collection ?  
/*
		if ($collection_link && isset($collection)) {
			$this->view->collection_link = '<p id="linkHome"><span class="dclabel">Collection : </span>' . link_to_collection() . ' <br />';  
		}	else {
			$this->view->collection_link = '';
		}
*/
		// Edit link for Item
/*
		if ($currentUser = current_user()) {		
			if (in_array($currentUser->role, array('super', 'admin', 'editor'))) {
				$edit_link = "<a class='eman-edit-link' href='" . WEB_ROOT . "/admin/items/edit/$itemId'>Editer ce contenu</a>";
			}
		}
		isset($edit_link) ? $this->view->edit_link = $edit_link : $this->view->edit_link = "";
*/
// 		$this->view->content = "<div id='primary'>$column1</div><aside id='sidebar'>$column2</aside>"; 	
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
			if (in_array($file->getExtension(), array('jpg', 'JPG', 'jpeg', 'JPEG'))) {
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
	public function displaySocial($entity) {
  	$type = get_class($entity);
  	if ($type == 'Item') {
      $hook = 'public_items_show';
      $entity_type = 'item';    	
  	} else if ($type == 'Collection') {
      $hook = 'public_collections_show';
      $entity_type = 'collection';     	
  	} else if ($type == 'File') {
      $hook = 'public_files_show';
      $entity_type = 'file';     	
  	}
		$socialPlugins = get_specific_plugin_hook_output('SocialBookmarking', $hook, array('view' => $this, $entity_type => $entity));		
		return $socialPlugins;
	}
	
	public function displayGallery($item) {
		$FilesGallery = "";
		if (metadata($item, 'has files')) {
			$FilesGallery .= '<span>' . count($item->Files) . ' fichier(s) </span><div id="itemfiles" class="element">';
			$FilesGallery .= '<div id="files-carousel" style="width:450px;margin:0 auto;">';
// 			 . files_for_item(array('linkToMetadata' => true, 'linkAttributes' => array('class' => 'file-display'))) . '</div>';
			$FilesGallery .= file_markup($item->Files, array('linkToMetadata' => true));
			$FilesGallery .= '</div>';
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
		$output_formats = array('atom', 'dcmes-xml', 'json', 'omeka-xml');
		$content = get_view()->partial(
				'common/output-format-list.php',
				array('output_formats' => $output_formats, 'query' => $_GET,
						'list' => false, 'delimiter' => ', ')
		);
		$content .= "<a href='/pdf?url=" . $_SERVER["REQUEST_URI"] . "'>version PDF de la fiche</a>";
// 		$content .= "</div>";
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

	public function displayComments($item) {
		if (plugin_is_active("Commenting")) {
			ob_start(); // We need to capture plugin ouput
			CommentingPlugin::showComments(array('id' => 999 ));
			$comments = ob_get_contents();
			ob_end_clean();				
		} else {
			$comments = "";
		}
		return $comments;
	}	
	
  public function displayChildrenCollections($collection) {
		$enfants = get_db()->getTable('CollectionTree')->getDescendantTree(metadata('collection', 'id'));
		if ($enfants) {
  		$count = count($enfants);
  		if ($count == 1) {
        $count .= ' sous-collection';    		
  		} else {
        $count .= ' sous-collections';    		    		
  		}
      $content = '<div class="collection-enfants">' . $count . ' : <ul>';
  		foreach ($enfants as $id => $enfant) {
  			$content .= "<li><a href='" . WEB_ROOT . "/collections/show/" . $enfant['id'] . "'>" . $enfant['name'] . "</a></li>";			
  		} 
  		$content .= "</ul></div>";
		} else {
  		$content = "";
		}
    return $content;
  }
  
  public function displayItems($collection) { 
    $content = '<div id="collection-items" style="clear:both;display:block;overflow:auto;">';
    $nbItems = metadata('collection', 'total_items');    
    if ($nbItems > 0) {
      $nbItems == 1 ? $notice = 'notice' : $notice = 'notices'; 
      $content .= "<h4>$nbItems $notice dans cette collection.</h4>";
      $items = get_records('Item', array('collection_id' => metadata('collection', 'id')));
      foreach ($items as $id => $item) {    
        set_current_record('Item', $item);    
        $itemTitle = strip_formatting(metadata($item, array('Dublin Core', 'Title')));
        $content .= '<div class="item hentry" style="float:left;display:block;clear:none;width:84px;">';    
        if (metadata($item, 'has thumbnail')) {
          $thumbnail = item_image('square_thumbnail', array('alt' => $itemTitle));
        } else {
    		$thumbnail = "<img style='width:72px;height:72px;float:left;margin-bottom:0;' src='" . WEB_ROOT . "/themes/eman/images/eman-logo.png' />";          
        }
        $content .= link_to_item($thumbnail);
        $content .=  '</div>';
/*
        if ($text = metadata($item, array('Item Type Metadata', 'Text'), array('snippet'=>250))) {
          $content .= '<div class="item-description"><p>' . $text . '</p></div>';
        } elseif ($description = metadata($item, array('Dublin Core', 'Description'), array('snippet'=>250))) {
          $content .= '<div class="item-description">' . $description . '</div>';
        }
*/
      }
    } else {
      $content .=  '<p>' . __("There are currently no items within this collection.") . '</p>';
    }          
    $content .= "</div><br /><br />Tous les documents : " . link_to_items_browse(__('Consulter', metadata('collection', array('Dublin Core', 'Title'))), array('collection' => metadata('collection', 'id')));
    return $content;
  }
 
  public function displayFile($file) {
    $content = "<div id='primaryImage'>";
    if (isset($size) && $size[1] > 1000) {
      $content .= "<em>Passez le curseur sur l'image pour zoomer.</em>";
    } 
		$filename = str_replace(array('png', 'JPG', 'jpeg'), 'jpg' , $file->filename); 
		$content .= '<div class="panzoom">' . file_markup($file, array('imageSize'=>'fullsize', 'linkToFile' => false, 'imgAttributes'=>array('class'=>'zoomImage', 'data-zoom-image'=> WEB_ROOT . '/files/fullsize/' . $filename))) . '</div>'; 
    $content .= "</div>";
    return $content;
  }
  
  public function displayFileInfo($file) {
  	$filen = metadata($file, 'filename');
  	$filepath = BASE_DIR.'/files/original/'. $filen;
  	$fichier = pathinfo($filepath);
  	$ext = strtolower($fichier['extension']);
  	$fileSize =  round(filesize($filepath) / 1024 / 1024, 2);
  	$fileFormat =  mime_content_type($filepath);
  	$fileOriginal = metadata($file, 'original_filename');
  	if (in_array($ext, array('jpg', 'jpeg', 'png'))) {
  		$size = getimagesize($filepath);
  		$this->view->size = $size;
  	}
    $fileTitle = metadata($file, array('Dublin Core', 'Title')) ? strip_formatting(metadata($file, array('Dublin Core', 'Title'))) : metadata('file', 'original filename');  
    if ($fileTitle != '') {
        $fileTitle = ': &quot;' . $fileTitle . '&quot; ';
    } else {
        $fileTitle = '';
    }
  	$fileTitle = __('Fichier ') . $fileTitle;	   
    $content = "Nom original : <a href='" . WEB_ROOT . "/files/original/$filen'>$fileOriginal</a><br/>";
    $content .= "Extension : $fileFormat <br/>";
    $content .= "Poids : $fileSize Mo<br />";    
    if (in_array($ext, array('jpg', 'jpeg', 'png'))) {
      $content .= "Dimensions : " . round($size[0]) . " x " . round($size[1]) . " px<br/>";
    }
    return $content;
  }
  
	public function displayTranscription($file) {
//   	Zend_Debug::dump($file);
    $content = '<div id="textTranscription" type="text/template" style="display:block";>';
//     $content .= '<div id="titleTranscription" style="display:block;">' . metadata($file, array('Dublin Core', 'Title')) . '</div>';
//     $content .= metadata($file, array('Transcript', 'Transcription'), array('no_escape' => true));
  	$this->view->xmlFileName = $xmlFileName =  substr($file->filename, 0, strpos($file->filename, '.')) . '.xml'; 
  	$this->view->filen = metadata('file', 'filename');
  	if (file_exists(BASE_DIR . '/teibp/transcriptions/' . $xmlFileName)) :
//   		$content .= '<div id="teibpTranscription"><button id="display-overlay" style="width:100%;">Afficher la transcription</button></div>';
        $content .= "<a href='" . WEB_ROOT . "/transcription/" . metadata('file', 'id') . "'>Afficher la transcription</a>";
    endif;	
/*
    $content .= '<style>.show #content #primary #teibpTranscription { width: 100%;  margin:20px 0; padding: 0; overflow: hidden; }';
    $content .=  '#transcription { width: 1005px; height: 1500px; border: 0px; }';
    $content .= '#transcription {';
    $content .= 'zoom: 0.5;';
    $content .= '-moz-transform: scale(0.5)';
    $content .= '-moz-transform-origin: 0 0';
    $content .= '-o-transform: scale(0.5)';
    $content .= '-o-transform-origin: 0 0';
    $content .= '-webkit-transform: scale(0.5)';
    $content .= '-webkit-transform-origin: 0 0}';
    $content .= '#transcription-full {}';
    $content .= '@media screen and (-webkit-min-device-pixel-ratio:0) {#scaled-frame  { zoom: 1;}}</style>';
    $content .= "<div id='texte'><iframe id='transcription-full' src='" . WEB_ROOT . "/teibp/transcriptions/" . $xmlFileName . "' frameborder='0' scrolling='no' onload='resizeIframe(this)' ></iframe></div>";
*/
    $content .= "</div>";
		return $content;
	}
}