<?php
class UiTemplates_EmanController extends Omeka_Controller_AbstractActionController
{
  public function init() {
    $this->lang = null;
    $this->default_language = get_option('locale_lang_code');
    if (plugin_is_active('Babel')) {
      $this->lang = substr(getLanguageForOmekaSwitch(), 0, 2);
      $this->view->lang = $this->lang;
      $this->traductions = unserialize(base64_decode(get_option('ui_templates_translations')));
    }
    $this->view->controller = $this;
  }

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
      			$this->view->collection_link = '<div style="background: transparent; border:none;box-shadow:none;margin:0;padding:0;"><span class="dclabel">' . $this->t('Collection') . ' : </span>' . link_to_collection_for_item();
      			$collection = get_collection_for_item();
      			if ($collection) {
        			if ($collection->id && count(get_records('Item', array('collection_id' => $collection->id), 10)) > 1) {
          			$link_to_collection_items = WEB_ROOT . '/items/browse?collection=' . $collection->id;
          			$this->view->collection_link .= "&nbsp;-&nbsp;<a href='$link_to_collection_items'>" . $this->t('Voir les autres notices de cette collection') . "</a>";
        			}
        		}
            $this->view->collection_link .=  "</div>";
      		}	else {
      			$this->view->collection_link = '';
      		}
    			continue;
  			}
  			$t = strtolower($type) . 's';
  			if (in_array($block, array('author_name_' . $t, 'author_size_' . $t, 'title_size_' . $t))) {
    			$this->view->$block = $fields;
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
  					$blockContent = $this->tag_string_eman($entity);
  					break;
  				case 'plugin_relations' :
  						$blockContent = $this->displayRelations($entity->id);
  					break;
  				case 'plugin_collection_relations' :
  						$blockContent = $this->displayCollectionRelations($entity->id);
  					break;
  				case 'plugin_file_relations' :
  						$blockContent = $this->displayFileRelations($entity->id);
  					break;
  				case 'plugin_geoloc' :
  						$blockContent = $this->displayMap($entity);
  					break;
  				case 'plugin_comment' :
  					$blockContent = $this->displayComments($entity);
  					break;
  			  case 'plugin_export' :
  					$blockContent = '';
  					$blockContent .= $this->displayExports($entity);
  					break;
  				case 'plugin_children' :
  					$blockContent = $this->displayChildrenCollections($entity);
  					break;
  				case 'plugin_items' :
    				$nbItems = 10;
    				if (isset($fields['nb_items'])) {
      				$nbItems = $fields['nb_items'];
    				}
  					$blockContent = $this->displayItems($entity, $nbItems);
  					break;
  				case 'plugin_file' :
  					$blockContent = $this->displayFile($entity);
  					break;
  				case 'plugin_fileinfo' :
  					$blockContent = $this->displayFileInfo($entity);
  					break;
  		  }
        $nbFields = 0;
  			foreach ($fields as $fieldName => $dataId) {
  				$fieldContent = $fieldTitle = '';

  				if (is_numeric($dataId) && $dataId <> 0) {
    				if ($this->lang) {
      				if (isset($fields['name_' . $fieldName . '_' . $this->lang])) {
    						if ($fields['name_' . $fieldName . '_' . $this->lang] <> null) {
    							$fieldTitle = $fields['name_' . $fieldName . '_' . $this->lang];
    						} else {
    							$fieldTitle = __($elements[$dataId]['name']);
    						}
    				  }
    				} else {
      				if (isset($fields['name_' . $fieldName])) {
    						if ($fields['name_' . $fieldName] <> null) {
    							$fieldTitle = $fields['name_' . $fieldName];
    						} else {
    							$fieldTitle = __($elements[$dataId]['name']);
    						}
    				  }
    				}
  				  // Exception pour blocs Relations
  				  //TODO : revoir ce test
  				  if (substr($fieldName, 0, 4) == 'bloc' || in_array(substr($fieldName, 0, 16), array('plugin_relations', 'plugin_collection_relations', 'plugin_file_relations', 'plugin_tags')) || in_array(substr($fieldName, 0, 11), array('plugin_tags'))) {
      				isset($config[$block]['bold_' . $fieldName]) && $config[$block]['bold_' . $fieldName] == 1 || get_option('uit_boldTitles') ? $bold = 'bold' : $bold = '';
      				isset($config[$block]['retour_' . $fieldName]) && $config[$block]['retour_' . $fieldName] == 1 || get_option('uit_retTitles') == 1 ? $retour = 'retour' : $retour = '';
    					$fieldData = metadata($t, array($elements[$dataId]['set'], $elements[$dataId]['name']), array('no_filter' => true, 'all' => true));
              $fieldContent = array();
    					foreach($fieldData as $i => $fieldInstance ) {
    						if ($fieldInstance) {
        					if (strlen($fieldInstance < 200 && $config[$block]['link_' . $fieldName])) {
          					$fieldInstance = "<a target='_blank' href='" . WEB_ROOT . "/items/browse?field=$dataId&val=$fieldInstance'>$fieldInstance</a>";
        					}
                  // Field contains exactly an URL : link in a new window
                  if (filter_var($fieldInstance, FILTER_VALIDATE_URL)) {
                    $fieldInstance = "<a target='_blank' href='$fieldInstance'>$fieldInstance</a>";
                  }
    							$fieldContent[] = $fieldInstance;
    						}
    					}
    					// If field is an array ...
              if (count($fieldContent) > 1) {
                setlocale(LC_COLLATE, 'fr_FR.UTF-8');
                if ($config[$block]['ordre_' . $fieldName] == 'asc') {
                  usort($fieldContent, function($a, $b) {return strcoll($a, $b);});
                } else {
                  usort($fieldContent, function($a, $b) {return strcoll($b, $a);});
                }
                if ($config[$block]['pres_' . $fieldName] == 'liste') {
                  $fieldContent = "<ul class='uit-list'><li>" . implode("</li><li>", $fieldContent) . "</li></ul>";
                } else {
                  $fieldContent = "<span class='uit-field $retour'>" . implode(", ", $fieldContent) . "</span>";
                  if (strrpos($fieldContent, ',') == strlen($fieldContent) && strrpos($fieldContent, 'uit-field')) {
                   $fieldContent = substr_replace($fieldContent, '', strrpos($fieldContent, ','), 1);
                  }
                }
              } elseif ($fieldContent) {
                $fieldContent = $fieldContent[0];
                if (strrpos($fieldContent, ',') <> 0 && strrpos($fieldContent, 'uit-field')) {
                 $fieldContent = substr_replace($fieldContent, '', strrpos($fieldContent, ','), 1);
                }
              }
              // Remember fieldTitle in case we must replace blockTitle with it later
              $blockTitle = $fieldTitle;
    					if ($fieldContent) {
                $nbFields++;
      					$maxLength = get_option('uit_maxLength');
                $maxLength ? null : $maxLength = 500;
      					// If generating PDF or text too short, display as it is ...
      					if (isset($_GET['context']) && $_GET['context'] == 'pdf' || strlen($fieldContent) < $maxLength || $config[$block]['more_' . $fieldName] != 1) {
      						$blockContent .= "<div class='field-uitemplates field-uitemplates-$dataId' id='$fieldName'><div class='uit-field-wrapper'><span class='field-uitemplates-title $bold $retour'>$fieldTitle</span><span class='uit-field $retour'>$fieldContent</span></div></div>";
      					} else {
        					// ... else hide text above $maxlength
                  $fieldContentShort = $this->truncateHtml($fieldContent, $maxLength, ' ...', false, true);
                  $fieldContentShort = tidy_repair_string($fieldContentShort, array('show-body-only' => true));
                  $fieldContentShort .= "<span class='suite'> ... ". $this->t('Lire la suite') . "</span>";
                  $fieldContentComplet = $fieldContent . '<span class="replier"><br />' . $this->t('Retour à la version réduite') . '</span></span>';
      	  				$fieldTitle = "<span class='field-uitemplates-title $bold $retour'>$fieldTitle</span>";
      						$blockContent .= "<div class='field-uitemplates field-uitemplates-$dataId' id='$fieldName'>$fieldTitle<div class='uit-field-wrapper'><span class='uit-field $retour'><div class='fieldcontentshort'>$fieldContentShort</div><div class='fieldcontentcomplet' style='display:none;'>$fieldContentComplet</div></span></div></div>";
                }
    					}
    				}
  				}
  			}
  			if ($blockContent) {
  				if ($config[$block]['options']['display']) {
    				if (! ($options['field_as_title'] == 1 && $nbFields == 1)) {
              if ($this->lang) {
                $config[$block]['options']['title'][$this->lang] <> "" ? $blockTitle = $config[$block]['options']['title'][$this->lang] : $blockTitle = $config[$block]['options']['title'][$this->default_language];
              } else {
                $blockTitle = $config[$block]['options']['title'];
              }
            } else {
          		$blockContent = str_replace("field-uitemplates-title", "field-uitemplates-title hidden", $blockContent);
      		  }
      		} else {
        		$blockTitle = '';
      		}
  				isset($blockTitle) ? $blockTitle = "<h2>" . $blockTitle . "</h2>" : $blockTitle = '';
  				// Affichage du bloc ?
  				if (! isset($config[$block]['options']['mask_it'])) : $config[$block]['options']['mask_it'] = 0; endif;
          if (($config[$block]['options']['private'] == 0 || ($config[$block]['options']['private'] == 1 && current_user())) && ! in_array($entity->collection_id, (array) $config[$block]['options']['mask_col']) && ! in_array($entity->item_type_id, (array) $config[$block]['options']['mask_it'])) {
   				  $content[$block] = "<div class='field-uitemplates field-uitemplates-$block block-uitemplates' id='$block'>$blockTitle $blockContent</div>";
  				}
  			}
  			$blockTitle = '';
  			// Channel output to column choosen in config
  			if ($column == 1) {
  				$column1 .= implode('', $content);
  			} elseif ($column == 2) {
  				$column2 .= implode('', $content);
  			}
  		}
  		if ($column2 && $column1) {
    		$content = "<div id='primary'>$column1</div><aside id='sidebar'>$column2</aside>";
    		$this->view->bodyColumns = 'two-columns';
  		} else {
    		$content = "<div id='primary' style='width:99%;'>$column1$column2</div>";
    		$this->view->bodyColumns = 'one-column';
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

	}

	public function displayFiles($item) {
		$fileGallery = "";
		if (metadata('item', 'has files')) {
			ob_start(); // We need to capture the UniversalViwer plugin ouput
			if (plugin_is_active('UniversalViewer')) {
    	  echo get_specific_plugin_hook_output('UniversalViewer', 'public_items_show', array(
          'record' => $item,
          'view' => $this->view,
        ));
      } else {
        /*
  			Affichage du visualiseur en fonction dy type de fichier
  			jpg -> Bookreader
  			Pdf -> DocViewer
  			Autre -> Affichage classique
        */
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
    				echo  '<iframe width=100% height=800 src="' . WEB_ROOT . '/files/original/'. metadata($file,'filename').'"></iframe>';	break;
    			} else {
    				echo files_for_item();
    				break;
    			}
  			endforeach;
  		}
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
  	// Désactivé cause erreur "String could not be parsed as XML"
		$socialPlugins = get_specific_plugin_hook_output('SocialBookmarking', $hook, array('view' => $this->view, $entity_type => $entity));
		return $socialPlugins;
	}

	public function displayGallery($item) {
		$FilesGallery = "";
		$db = get_db();
		$transcriptTagId = $db->query("SELECT id FROM `$db->Elements` WHERE `name` = 'Transcription'")->fetchObject();
		if (metadata($item, 'has files')) {
			$FilesGallery .= $this->t("En passant la souris sur une vignette, le titre de l'image apparaît.") . "<br /><br />";
			$FilesGallery .= '<span>' . count($item->Files) . ' ' . $this->t('Fichier(s)') . '</span><div id="itemfiles" class="element">';
			$fileIds = [];
			$FilesGallery .= '<div id="files-carousel" style="width:450px;margin:0 auto;">';
			foreach ($item->Files as $file) {
  			if ($transcriptTagId) {
    			if ($db->query("SELECT 1 FROM `$db->ElementTexts` WHERE record_type='File' AND record_id = " . $file->id . " AND element_id = " . $transcriptTagId->id)->fetchAll()) {
      			$fileIds[] = "<span>" . $file->id . "</span>";
    			}
  			}
        $FilesGallery .= '<div id="' . $file->id . '" class="item-file image-jpeg">';
        $FilesGallery .= '<a href="' . WEB_ROOT . '/files/show/' . $file->id . '">';
        $FilesGallery .= '<img class="thumb" data-lazy="' . WEB_ROOT .'/files/square_thumbnails/' . str_replace(['jpeg', 'png', 'gif'], 'jpg', strtolower($file->filename)) . '" alt="' . $file->title . '" title="' . $file->title . '" />';
        $FilesGallery .= '</a></div>';
			}
			$this->view->markTranscripted = implode('', $fileIds);
      $FilesGallery .= '</div></div>';
		}
		$FilesGallery = preg_replace('/<h3>[^>]+\<\/h3>/i', "", $FilesGallery);
		return $FilesGallery;
	}

	public function displayRelations($item) {
		$relations = get_specific_plugin_hook_output('ItemRelations', 'public_items_show', array('view' => $this->view, 'item' => $item));
		if (strstr($relations, 'Ce document n\'a pas de relation indiquée avec un autre document du projet.')) {
  		$relations = str_replace('Ce document n\'a pas de relation indiquée avec un autre document du projet.', $this->t('Ce document n\'a pas de relation indiquée avec un autre document du projet.'), $relations);
		} elseif (plugin_is_active('Graph'))   {
  		$relations .= "<br /><a target='_blank' href='" . WEB_ROOT . "/graphitem/" . $item . "'>Afficher la visualisation des relations de la notice</a>.<br /><br />";
		}
		$relations = "<br />" . $relations;
		return $relations;
	}

	public function displayCollectionRelations($collection) {
		$relations = get_specific_plugin_hook_output('CollectionRelations', 'public_collections_show', array('view' => $this->view, 'collection' => $collection));
		if (strstr($relations, 'Ce document n\'a pas de relation indiquée avec un autre document du projet.')) {
  		$relations = str_replace('Ce document n\'a pas de relation indiquée avec un autre document du projet.', $this->t('Ce document n\'a pas de relation indiquée avec un autre document du projet.'), $relations);
		} elseif (plugin_is_active('Graph'))   {
  		$relations .= "<br /><a target='_blank' href='" . WEB_ROOT . "/graphcollection/" . $collection . "'>Afficher la visualisation des relations de la collection</a>.<br /><br />";
		}
		$relations .= "<br />";
		return $relations;
	}

	public function displayFileRelations($file) {
		$relations = get_specific_plugin_hook_output('FileRelations', 'public_files_show', array('view' => $this->view, 'file' => $file));
		if (strstr($relations, 'Ce document n\'a pas de relation indiquée avec un autre document du projet.')) {
  		$relations = str_replace('Ce document n\'a pas de relation indiquée avec un autre document du projet.', $this->t('Ce document n\'a pas de relation indiquée avec un autre document du projet.'), $relations);
		} elseif (plugin_is_active('Graph'))   {
  		$relations .= "<br /><a target='_blank' href='" . WEB_ROOT . "/graphfile/" . $file . "'>Afficher la visualisation des relations de ce fichier</a>.<br /><br />";
		}
		$relations .= "<br />";
		return $relations;
	}

	public function displayExports($entity) {
  	$db = get_db();
		$output_formats = array('atom', 'dcmes-xml', 'json', 'omeka-xml');
		$content = get_view()->partial(
				'common/output-format-list.php',
				array('output_formats' => $output_formats, 'query' => $_GET,
						'list' => false, 'delimiter' => ', ')
		);
		$content .= "<a href='/export/pdf.php?url=" . $_SERVER["REQUEST_URI"] . "' target='_blank' >" . $this->t('Exporter en PDF les métadonnnées') . "</a>";
		// Transcriptions et images
		if ($entity->public == 1) {
      $element_id = $db->query("SELECT id FROM `$db->Elements` WHERE name ='Transcription' AND description = 'A TEI tagged representation of the document.'")->fetchObject();
      if ($element_id) {
    		$transcriptions = $db->query("SELECT COUNT(id) nb FROM `$db->ElementTexts` WHERE record_id IN (SELECT id FROM `$db->Files` WHERE item_id = $entity->id) AND record_type = 'File' AND element_id = $element_id->id")->fetchObject();
    		if ($transcriptions->nb > 0) {
	    		if ($transcriptions->nb == 1) {
  	    		$t = "la transcription";
          } else {
  	    		$t = "les $transcriptions->nb transcriptions";
          }
          $content .= "<br /><a target='_blank' href='/export/compilation-transcriptions.php?url=" . $_SERVER["REQUEST_URI"] . "'>Exporter $t dans un fichier XML</a>";
    		}
      }
      if (get_class($entity) == 'Item') {
    		$fichiers = $db->query("SELECT COUNT(id) nb FROM `$db->Files` WHERE item_id = $entity->id")->fetchObject();
    		if ($fichiers->nb > 0) {
      		if ($fichiers->nb == 1) {
  	    		$t = "l'image";
          } else {
  	    		$t = "les $fichiers->nb images";
          }
          $content .= "<br /><a target='_blank' href='/export/compilation-fichiers.php?url=" . $_SERVER["REQUEST_URI"] . "'>" . $this->t("Exporter en PDF les métadonnées et $t") ."</a>";
    		}
      }
    }
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
        $count .= ' ' . $this->t('sous-collection');
  		} else {
        $count .= ' ' . $this->t('sous-collections');
  		}
      $content = '<div class="collection-enfants">' . $count . ' : <ul>';

      $enfants = $this->orderCollections($enfants);
  		foreach ($enfants as $id => $enfant) {
  			$content .= "<li><a href='" . WEB_ROOT . "/collections/show/" . $enfant['id'] . "'>" . $enfant['name'] . "</a></li>";
  		}
  		$content .= "</ul></div>";
		} else {
  		$content = "";
		}
    return $content;
  }

  public function displayItems($collection, $nbNotices = 10) {
    $content = '<div id="collection-items" style="clear:both;display:block;overflow:visible;">';
    $nbItems = metadata('collection', 'total_items');
    $nbItems > $nbNotices ? $message = "Les " .  $nbNotices. " premiers documents de la collection" : $message = "Les documents de la collection";
    $nbItems == 1 ? $message = "Le seul document de la collection" : null;
    if ($nbItems > 0) {
      $nbItems == 1 ? $notice = $this->t('notice') : $notice = $this->t('notices');
      $content .= "<h4>$nbItems $notice " . $this->t('dans cette collection') . "</h4>";
      $content .= $this->t("En passant la souris sur une vignette, le titre de la notice apparaît.") . "<br /><br />" . $this->t($message) . " :<br /><br />";
      $items = get_records('Item', array('collection_id' => metadata('collection', 'id')), $nbNotices);

      usort($items, function($a ,$b) {return $a->id > $b->id ? 1 : -1;});

      foreach ($items as $id => $item) {
        set_current_record('Item', $item);
        $itemTitle = strip_formatting(metadata($item, array('Dublin Core', 'Title')));
        $content .= '<div class="item hentry collection-item">';
        if (metadata($item, 'has thumbnail')) {
          $thumbnail = item_image('square_thumbnail', array('alt' => $itemTitle));
        } else {
    		  $thumbnail = theme_logo();
        }
        $content .= "<div class='collection-item-image'>" . link_to_item($thumbnail) . "</div>";
        $content .= "<div class='collection-item-wrap'>";
        $content .= "<div class='collection-item-title'>" . metadata($item, array('Dublin Core', 'Title')) . "</div>";
        if ($creator = metadata($item, array('Dublin Core', 'Creator'))) {
          $content .= "<div class='collection-item-author'>&nbsp;$creator</div>";
        }
        if ($this->tag_string_eman($item)) {
          $content .= "<div class='collection-item-tags'>Mots Clefs : " . $this->tag_string_eman($item) . "</div>";
        }
        $content .=  '</div></div>';
      }
    } else {
      $content .=  '<p>' . __("There are currently no items within this collection.") . '</p>';
    }
    $content .= "</div><br /><br /><div style='clear:both';>" . $this->t("Tous les documents") . " : " . link_to_items_browse(__('Consulter', metadata('collection', array('Dublin Core', 'Title'))), array('collection' => metadata('collection', 'id'))) . "</div>";
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
    $content = $this->t("Nom original") . " : $fileOriginal<br/>";
    $content .= $this->t("Lien vers le") . " <a href='" . WEB_ROOT . "/files/original/$filen'>" . $this->t('fichier') . "</a><br/>";
    $content .= $this->t("Extension") . " : $fileFormat <br/>";
    $content .= $this->t("Poids") . " : $fileSize Mo<br />";
    if (in_array($ext, array('jpg', 'jpeg', 'png'))) {
      $content .= $this->t("Dimensions") . " : " . round($size[0]) . " x " . round($size[1]) . " px<br/>";
    }
    return $content;
  }

	public function displayTranscription($file) {
    $content = '<div id="textTranscription" type="text/template" style="display:block";>';
  	$this->view->xmlFileName = $xmlFileName =  substr($file->filename, 0, strpos($file->filename, '.')) . '.xml';
  	$this->view->filen = metadata('file', 'filename');
  	if (file_exists(BASE_DIR . '/teibp/transcriptions/' . $xmlFileName)) :
        $content .= "<a href='" . WEB_ROOT . "/transcription/" . metadata('file', 'id') . "'>Afficher la transcription</a>";
    endif;
    $content .= "</div>";
		return $content;
	}

   public function orderCollections($cols) {
    $order = unserialize(get_option('sortcol_preferences'));
    if ($order) {
      foreach ($cols as $id => $col) {
        if (isset($order[$col['id']])) {
          if ($order[$col['id']] <> "") {
            $cols[$id]['ordre'] = $order[$col['id']];
          } else {
            $cols[$id]['ordre'] = 0;
          }
        } else {
          $cols[$id]['ordre'] = 0;
        }
      }
    }

    $sansnumero = array_filter($cols, function($a) { if ($a['ordre'] == 0) {return true;} else {return false;} });
    $avecnumero = array_filter($cols, function($a) { if ($a['ordre'] <> 0) {return true;} else {return false;} });

    setlocale(LC_COLLATE, 'fr_FR.utf8');
    usort($avecnumero, function($a, $b) {if (!isset($a['ordre']) || !isset($b['ordre'])) {return 1;}; return ($a['ordre'] < $b['ordre']) ? -1 : 1;});
    usort($sansnumero, function($a, $b) {return strnatcmp($a['name'], $b['name']);} );

    $cols = $sansnumero;
    foreach ($avecnumero as $i => $val) {
      $cols[] = $val;
    }
    return $cols;
  }

  public function tag_string_eman($recordOrTags = null, $link = 'items/browse', $delimiter = null) {
    // Set the tag_delimiter option if no delimiter was passed.
    if (is_null($delimiter)) {
        $delimiter = get_option('tag_delimiter') . ' ';
    }
    if (!$recordOrTags) {
        $tags = array();
    } else if (is_string($recordOrTags)) {
        $tags = get_current_record($recordOrTags)->Tags;
    } else if ($recordOrTags instanceof Omeka_Record_AbstractRecord) {
        $tags = $recordOrTags->Tags;
    } else {
        $tags = $recordOrTags;
    }

    if (empty($tags)) {
        return '';
    }

    $tagStrings = array();
    foreach ($tags as $tag) {
        $name = $tag['name'];
        if (!$link) {
            $tagStrings[] = html_escape($name);
        } else {
            $tagStrings[] = '<a href="' . html_escape(url($link, array('tags' => strip_tags($name)))) . '" rel="tag">' . $name . '</a>';
        }
    }
    return join(html_escape($delimiter), $tagStrings);
  }

 /**
 * truncateHtml can truncate a string up to a number of characters while preserving whole words and HTML tags
 *
 * @param string $text String to truncate.
 * @param integer $length Length of returned string, including ellipsis.
 * @param string $ending Ending to be appended to the trimmed string.
 * @param boolean $exact If false, $text will not be cut mid-word
 * @param boolean $considerHtml If true, HTML tags would be handled correctly
 *
 * @return string Trimmed string.
 */
	public function truncateHtml($text, $length = 100, $ending = '...', $exact = false, $considerHtml = true) {
  	if ($considerHtml) {
  		// if the plain text is shorter than the maximum length, return the whole text
  		if (strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
  			return $text;
  		}
  		// splits all html-tags to scanable lines
  		preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
  		$total_length = strlen($ending);
  		$open_tags = array();
  		$truncate = '';
  		foreach ($lines as $line_matchings) {
  			// if there is any html-tag in this line, handle it and add it (uncounted) to the output
  			if (!empty($line_matchings[1])) {
  				// if it's an "empty element" with or without xhtml-conform closing slash
  				if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
  					// do nothing
  				// if tag is a closing tag
  				} else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
  					// delete tag from $open_tags list
  					$pos = array_search($tag_matchings[1], $open_tags);
  					if ($pos !== false) {
  					unset($open_tags[$pos]);
  					}
  				// if tag is an opening tag
  				} else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
  					// add tag to the beginning of $open_tags list
  					array_unshift($open_tags, strtolower($tag_matchings[1]));
  				}
  				// add html-tag to $truncate'd text
  				$truncate .= $line_matchings[1];
  			}
  			// calculate the length of the plain text part of the line; handle entities as one character
  			$content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
  			if ($total_length+$content_length> $length) {
  				// the number of characters which are left
  				$left = $length - $total_length;
  				$entities_length = 0;
  				// search for html entities
  				if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
  					// calculate the real length of all entities in the legal range
  					foreach ($entities[0] as $entity) {
  						if ($entity[1]+1-$entities_length <= $left) {
  							$left--;
  							$entities_length += strlen($entity[0]);
  						} else {
  							// no more characters left
  							break;
  						}
  					}
  				}
  				$truncate .= substr($line_matchings[2], 0, $left+$entities_length);
  				// maximum lenght is reached, so get off the loop
  				break;
  			} else {
  				$truncate .= $line_matchings[2];
  				$total_length += $content_length;
  			}
  			// if the maximum length is reached, get off the loop
  			if($total_length>= $length) {
  				break;
  			}
  		}
  	} else {
  		if (strlen($text) <= $length) {
  			return $text;
  		} else {
  			$truncate = substr($text, 0, $length - strlen($ending));
  		}
  	}
  	// if the words shouldn't be cut in the middle...
  	if (!$exact) {
  		// ...search the last occurance of a space...
  		$spacepos = strrpos($truncate, ' ');
  		if (isset($spacepos)) {
  			// ...and cut the text in this position
  			$truncate = substr($truncate, 0, $spacepos);
  		}
  	}
  	// add the defined ending to the text
  	$truncate .= $ending;
  	if($considerHtml) {
  		// close all unclosed html-tags
  		foreach ($open_tags as $tag) {
  			$truncate .= '</' . $tag . '>';
  		}
  	}
  	return $truncate;
  }

  public function t($string) {
    if (plugin_is_active('Babel')) {
      return BabelPlugin::translate($string);
    } else {
      return $string;
    }
  }
}