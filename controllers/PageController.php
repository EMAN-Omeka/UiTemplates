<?php
class UiTemplates_PageController extends Omeka_Controller_AbstractActionController
{
  public function init() {
    $this->languages = null;
    if (plugin_is_active('Babel')) {
      $this->languages = explode("#", get_option('languages_options'));
      $this->view->lang = substr(getLanguageForOmekaSwitch(), 0, 2);
    }
  }

public function getBrowseForm($type = "Items")
	{
    $db = get_db();
    // Retrieve config for this type from DB
  	$config = $db->query("SELECT * FROM `$db->UiTemplates` WHERE template_type = '$type'")->fetchAll();
  	$config = array_pop($config);
  	$config = unserialize(base64_decode($config['text']));
   	// Count currently used blocks
   	if (is_array($config)) {
      $nbBlocks = count(array_filter(array_keys($config), function($e) {return strstr($e, 'bloc');}));
   	}
		$lang = get_html_lang();
    $form = new Zend_Form();
    // le nombre de bloc est limité à (20 ou 99), le nombre de champs à 9 et la longueur maximale à 9999.
		$optionsTitle = new Zend_Form_Element_Note('OptionsTitle');
		$optionsTitle->setValue("<strong>Le nombre de bloc est limité à 99, le nombre de champs à 9 et la longueur maximale à 9999.</strong><br/><br/>");
		$form->addElement($optionsTitle);

		$nbBlocks = new Zend_Form_Element_Text('nbBlocks');
		$nbBlocks->setLabel('Nombre de blocs : ');
		$nb = get_option('uit_nbBlocks');
		isset($nb) ? null : $nb = 7;
		$nbBlocks->setValue($nb);
		$nbBlocks->setAttrib('size', 1);
		$nbBlocks->setAttrib('maxlength', 2);
		$form->addElement($nbBlocks);

		$nbFields = new Zend_Form_Element_Text('nbFields');
		$nbFields->setLabel('Nombre de champs par bloc : ');
		$nb = get_option('uit_nbFields');
		isset($nb) ? null : $nb = 5;
		$nbFields->setValue($nb);
		$nbFields->setAttrib('size', 1);
		$nbFields->setAttrib('maxlength', 1);
		$form->addElement($nbFields);

		$maxLength = new Zend_Form_Element_Text('maxLength');
		$maxLength->setLabel('Longueur maximale des champs tronqués : ');
		$nb = get_option('uit_maxLength');
		isset($nb) ? null : $nb = 500;
		$maxLength->setValue($nb);
		$maxLength->setAttrib('size', 4);
		$maxLength->setAttrib('maxlength', 4);
		$form->addElement($maxLength);

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel('Sauvegarder');
    $form->addElement($submit);

		return $this->prettifyForm($form);
  }

	public function getForm($type = "Items")
	{
    $db = get_db();
		$lang = get_html_lang();
  	$collectionIds = $db->query("SELECT id FROM `$db->Collections` ORDER BY id")->fetchAll();
  	$collections = [0 => 'Ne pas masquer'];
		foreach ($collectionIds as $i => $data) {
  		$collection = get_record_by_id('collection', $data['id']);
  		$collections[$data['id']] = substr(metadata($collection, array('Dublin Core', 'Title')), 0, 50);
    }
    setlocale(LC_COLLATE, 'fr_FR.UTF-8');
		$c = new Collator('fr_FR');
		$c->setAttribute(Collator::NUMERIC_COLLATION, Collator::ON);
		$c->setAttribute(Collator::FRENCH_COLLATION, Collator::ON);
    uasort($collections, function($a, $b) use ($c) {
  		$x = $c->compare($a, $b);
  		return ($x);
    });
  	$itemTypesIds = $db->query("SELECT id, name FROM `$db->ItemTypes` ORDER BY name")->fetchAll();
  	$itemTypes = [0 => 'Ne pas masquer'];
		foreach ($itemTypesIds as $i => $data) {
  		$itemTypes[$data['id']] = $data['name'];
    }
    $form = new Zend_Form();
    $form->setName('UITemplates' . $type . 'Form');
    $form->setAttrib('class', 'uit-form');
    // Retrieve list of item's metadata
  	$elements = $db->query("SELECT id, name FROM `$db->Elements` WHERE id IN (SELECT DISTINCT element_id FROM `$db->ElementTexts`) ORDER BY name");
   	$elements = $elements->fetchAll();
		foreach ($elements as $i => $element) {
  		$elements[$i]['name'] = __($elements[$i]['name']);
    }
    setlocale(LC_COLLATE, 'fr_FR.utf8');
    usort($elements, function($a, $b) {return strcoll($a['name'], $b['name']); } );
    $elements = array_column($elements, 'name', 'id');
    $elements['none'] = "None"; // Aucun champ

    // Retrieve config for this type from DB
  	$config = $db->query("SELECT * FROM `$db->UiTemplates` WHERE template_type = '$type'")->fetchAll();
  	$config = array_pop($config);
  	$config = unserialize(base64_decode($config['text']));

   	$blocks = [];
   	// Count currently used blocks
   	$nb = get_option('uit_nbBlocks');
   	$nb ? null : $nb = 7;
    foreach (range(1, $nb) as $number) {
      $blocks['bloc' . $number] = 'Bloc ' . $number;
    }
   	switch ($type) {
     	case 'Items' :
       	// Plugins
       	$blocks['plugin_files'] = "File Viewer";
       	$blocks['plugin_gallery'] = "File Gallery";
       	$blocks['plugin_tags'] = "Tags / Mots-clefs";
       	// Conditional plugins
       	plugin_is_active('ItemRelations') ? $blocks['plugin_relations'] = "Relations" : null;
       	plugin_is_active('Geolocation') ? $blocks['plugin_geoloc'] = "G&eacute;olocalisation" : null;
     	case 'Collections' :
       	if ($type == 'Collections') {
         	$blocks['plugin_children'] = "Sous Collections";
         	$blocks['plugin_items'] = "Notices";
          plugin_is_active('CollectionRelations') ? $blocks['plugin_collection_relations'] = "Relations de la collection" : null;
       	}
       	plugin_is_active('SocialBookmarking') ? $blocks['plugin_social'] = "Social Networks" : null;
     	case 'Files' :
        $blocks['plugin_export'] = "Export de la fiche";
       	plugin_is_active('EmanCitation') ? $blocks['plugin_citation'] = "Citation" : null;
       	plugin_is_active('Commenting') ? $blocks['plugin_comment'] = "Commentaires" : null;
       	if ($type == 'Files') {
         	$blocks['plugin_file'] = "Affichage du fichier";
         	$blocks['plugin_fileinfo'] = "Informations sur le fichier";
          plugin_is_active('FileRelations') ? $blocks['plugin_file_relations'] = "Relations du fichier" : null;
        }
        break;
   	}
   	foreach ($blocks as $id => $block) {
 			$blockSelects = array();

 			$blockTitle = new Zend_Form_Element_Note('blockTitle_' . $id);
			$blockTitle->setBelongsTo($id);

   		// Titre du block
   		$blockTitleValue = "";
   		$titleLang = "";
   		if ($this->languages) {
     		$titlesFields = [];
     		foreach ($this->languages as $i => $lang) {
     			$titleField = new Zend_Form_Element_Text('title_' . $lang . '_' . $id);
     			$titleField->setLabel('Titre du bloc : ' . $lang);
     			isset($config[$id]['options']['title'][$lang]) ? $titleValue = $config[$id]['options']['title'][$lang] : $titleValue = "";
          $titleField->setValue($titleValue);
     			$titleField->setBelongsTo($id);
     			$titlesFields[] = $titleField;
     		}
     		$titleLang = " (" . $this->view->lang . ")";
   			isset($config[$id]['options']['title'][$this->view->lang]) &&  $config[$id]['options']['title'][$this->view->lang] ? $blockTitleValue = $config[$id]['options']['title'][$this->view->lang] . $titleLang : $blockTitleValue = null;
     } else {
   			$titleField = new Zend_Form_Element_Text('title_' . $id);
   			$titleField->setLabel('Titre du bloc : ');
   			isset($config[$id]['options']['title']) ? $titleValue = $config[$id]['options']['title'] : $titleValue = $id;
        $titleField->setValue($titleValue);
   			$blockTitleValue = $titleValue;
   			$titleField->setBelongsTo($id);
   			$titlesFields[] = $titleField;
     }
       if ($blockTitleValue) {
 			$blockTitle->setValue("<h2>$block : $blockTitleValue</h2>");
     } else {
 			$blockTitle->setValue("<h2><em>$block : Sans titre $titleLang</em></h2>");
     }
 			$form->addElement($blockTitle);
 			foreach($titlesFields as $i => $titleField) {
        $form->addElement($titleField);
      }

 			// Checkbox afficher titre oui/non
 			$titleDisplay = new Zend_Form_Element_Checkbox('display_' . $id);
 			$titleDisplay->setLabel('Afficher le titre ?');
      isset($config[$id]['options']['title']) ? $titleDisplay->setValue($config[$id]['options']['display']) : $titleDisplay->setValue(0);
 			$titleDisplay->setBelongsTo($id);
 			$form->addElement($titleDisplay);

 			// Checkbox si titre masqué, remplacer par titre du champ oui/non
      if ($id <> 'plugin_citation') {
   			$fieldAsTitle = new Zend_Form_Element_Checkbox('field_as_title_' . $id);
   			$fieldAsTitle->setLabel('Remplacer titre du bloc par titre du champ unique ?');
   			isset($config[$id]['options']['field_as_title']) ? $fieldAsTitle->setValue($config[$id]['options']['field_as_title']) : $fieldAsTitle->setValue(0);
   			$fieldAsTitle->setBelongsTo($id);
   			$form->addElement($fieldAsTitle);

   			// Checkbox afficher oui/non
   			$private = new Zend_Form_Element_Checkbox('private_' . $id);
   			$private->setLabel('Bloc privé (masqué aux visiteurs) ?');
   			isset($config[$id]['options']['private']) ? $private->setValue($config[$id]['options']['private']) : $private->setValue(0);
   			$private->setBelongsTo($id);
   			$form->addElement($private);
   	  }

      if ($type == 'Items' && $id <> 'plugin_citation') {
 				$maskParalCollection = new Zend_Form_Element_Multiselect('mask_col_' . $id);
 				$maskParalCollection->setLabel("Masquer le bloc quand l'item appartient à une des collections : ")
 				->setMultiOptions($collections);
   			isset($config[$id]['options']['mask_col']) ? $maskParalCollection->setValue($config[$id]['options']['mask_col']) : $maskParalCollection->setValue(0);
   			$maskParalCollection->setBelongsTo($id);
   			$maskParalCollection->setAttrib('class', 'multiselect');
       	$form->addElement($maskParalCollection);

 				$maskItemType = new Zend_Form_Element_Multiselect('mask_it_' . $id);
 				$maskItemType->setLabel("Masquer le bloc quand l'item est du type : ")
 				->setMultiOptions($itemTypes);
   			isset($config[$id]['options']['mask_it']) ? $maskItemType->setValue($config[$id]['options']['mask_it']) : $maskItemType->setValue(0);
   			$maskItemType->setBelongsTo($id);
   			$maskItemType  ->setAttrib('class', 'multiselect');
       	$form->addElement($maskItemType);
      }

 			$blockSelects[] = $blockTitle;
 			$blockSelects[] = $titleField;
 			$blockSelects[] = $titleDisplay;

 			// Poids pour ordre d'affichage
 			$blockOrder = new Zend_Form_Element_Text('order_' . $id);
 			$blockOrder->setLabel('Ordre d\'apparition du bloc dans la colonne');
 			isset($config[$id]['options']['order']) ? $blockOrder->setValue($config[$id]['options']['order']) : $blockOrder->setValue(0);
 			$blockOrder->setAttrib('size', 2);
 			$blockOrder->setAttrib('maxlength', 2);
 			$blockOrder->setBelongsTo($id);
 			$form->addElement($blockOrder);

 			// Colonne gauche ou droite
 			$column = new Zend_Form_Element_Radio('column_' . $id);
 			$column->setLabel('Colonne d\'affichage (Aucune = bloc masqué)');
      $colonnes = [1 => '1', 2 => '2'];
      if ($id <> 'plugin_citation') {
        $colonnes[0] = 'Aucune';
      }
 			$column->setMultiOptions($colonnes);
 			isset($config[$id]['options']['column']) ? $column->setValue($config[$id]['options']['column']) : $column->setValue(0);
 			$column->setSeparator(' ');
 			$column->setAttrib('label_class', 'eman-radio-label');
 			$column->setBelongsTo($id);
 			$form->addElement($column);

 			$blockSelects[] = $blockOrder;
 			$blockSelects[] = $column;
 			if (in_array($id, array('plugin_relations', 'plugin_collection_relations', 'plugin_file_relations'))) {
   			switch ($id) {
     			case 'plugin_relations' :
     			 $ir_prefix = 'ir';
     			break;
     			case 'plugin_collection_relations' :
     			 $ir_prefix = 'cr';
     			break;
     			case 'plugin_file_relations' :
     			 $ir_prefix = 'fr';
     			break;
   			}
 				$irIntitule = $ir_prefix . '_intitule_' . $id;
 				$irIntitule = new Zend_Form_Element_Text($ir_prefix . '_intitule');
 				if (isset($config[$id][$ir_prefix . '_intitule'])) {
   				$irIntitule->setValue($config[$id][$ir_prefix . '_intitule']);
 				} else {
   				$irIntitule->setValue('Ce document');
 				}
 				$irIntitule->setBelongsTo($id);
 				$irIntitule->setLabel("Intitulé sujet");
 				$form->addElement($irIntitule);
 				$irIntituleObj = $ir_prefix . '_intitule_obj' . $id;
 				$irIntituleObj = new Zend_Form_Element_Text($ir_prefix . '_intitule_obj');
 				if (isset($config[$id][$ir_prefix . '_intitule_obj'])) {
   				$irIntituleObj->setValue($config[$id][$ir_prefix . '_intitule_obj']);
 				} else {
   				$irIntituleObj->setValue('ce document');
 				}
 				$irIntituleObj->setBelongsTo($id);
 				$irIntituleObj->setLabel("Intitulé objet");
 				$form->addElement($irIntituleObj);
 			}

 			// Nombre d'items par collection
 			if ($id == 'plugin_items') {
 				$nbItems = new Zend_Form_Element_Text('nb_items');
 				if (isset($config[$id]['nb_items'])) {
   				$nbItems->setValue($config[$id]['nb_items']);
 				} else {
   				$nbItems->setValue(10);
 				}
 				$nbItems->setBelongsTo($id);
 				$nbItems->setLabel("Nombre d'items à afficher");
 				$form->addElement($nbItems);
 			}

 			if (in_array($id, array('plugin_relations', 'plugin_collection_relations', 'plugin_file_relations', 'plugin_tags', 'plugin_geoloc')) || substr($id, 0, 7) <> 'plugin_') {
   			// Fields
       	$nb = get_option('uit_nbFields');
       	$nb ? null : $nb = 5;
   			for ($i = 1; $i <= $nb ; $i++) {
          $fieldTitle = new Zend_Form_Element_Note('fieldTitle_' . $id . '_' . $i);
     			$fieldTitle->setValue("<h4> => Champ $i</h4>");
    			$fieldTitle->setBelongsTo($id);
     			$form->addElement($fieldTitle);

   				$selectName = $id . '_' . $i;
   				$selectField = new Zend_Form_Element_Select($selectName);
   				$selectField->setAttrib('class', "field-" . $i);
   				$selectField->setLabel("Champ " . $i)
   				->setMultiOptions($elements);

   				isset($config[$id][$selectName]) ? $defaultValue = $config[$id][$selectName] : $defaultValue = 'none';
   				$selectField->setValue($defaultValue); // Set field value fetched from DB
   				$selectField->setBelongsTo($id);
   				$form->addElement($selectField);

       		if ($this->languages) {
	       		foreach ($this->languages as $x => $lang) {
       				$titleName = 'name_' . $id . '_' . $i . '_' . $lang;
       				$fieldName = new Zend_Form_Element_Text($titleName);
       				if (isset($config[$id][$titleName])) {
	       				$fieldName->setValue($config[$id][$titleName]);
       				} else {
	       				isset($config[$id]['name_' . $id . '_' . $i]) ? $fieldName->setValue($config[$id]['name_' . $id . '_' . $i]) : "";
       				}
       				$fieldName->setBelongsTo($id);
       				$fieldName->setLabel("Titre du champ : Champ " . $i . " (" . $lang . ") ");
       				$form->addElement($fieldName);
	       		}
          } else {
     				$titleName = 'name_' . $id . '_' . $i;
     				$fieldName = new Zend_Form_Element_Text($titleName);
     				isset($config[$id][$titleName]) ? $fieldName->setValue($config[$id][$titleName]) : $fieldName->setValue('');
     				$fieldName->setBelongsTo($id);
     				$fieldName->setLabel("Titre du champ : Champ " . $i . " ");
     				$form->addElement($fieldName);
          }

         	$bold = new Zend_Form_Element_Checkbox('bold_' . $id . '_' . $i);
         	$bold->setLabel('Afficher en gras ');
         	isset($config[$id]['bold_' . $id . '_' . $i]) ? $bold->setValue($config[$id]['bold_' . $id . '_' . $i]) : $bold->setValue(0);
  				$bold->setBelongsTo($id);
         	$form->addElement($bold);

         	$retour = new Zend_Form_Element_Checkbox('retour_' . $id . '_' . $i);
         	$retour->setLabel('Retour à la ligne ');
         	isset($config[$id]['retour_' . $id . '_' . $i]) ? $retour->setValue($config[$id]['retour_' . $id . '_' . $i]) : $retour->setValue(0);
  				$retour->setBelongsTo($id);
         	$form->addElement($retour);

     			$presField = new Zend_Form_Element_Radio('pres_' . $id . '_' . $i);
     			$presField->setLabel('Afficher avec ');
     			$presField->setMultiOptions(array('liste' => 'Liste', 'virgule' => 'Virgule'));
     			isset($config[$id]['pres_' . $id . '_' . $i]) ? $presField->setValue($config[$id]['pres_' . $id . '_' . $i]) : $presField->setValue('liste');
     			$presField->setSeparator(' ');
     			$presField->setAttrib('label_class', 'eman-radio-label');
     			$presField->setBelongsTo($id);
     			$form->addElement($presField);

     			$triField = new Zend_Form_Element_Radio('tri_' . $id . '_' . $i);
     			$triField->setLabel('Tri ');
     			$triField->setMultiOptions(array('alpha' => 'Alphanumérique', 'date' => 'Ordre de saisie'));
     			isset($config[$id]['tri_' . $id . '_' . $i]) ? $triField->setValue($config[$id]['tri_' . $id . '_' . $i]) : $triField->setValue('alpha');
     			$triField->setSeparator(' ');
     			$triField->setAttrib('label_class', 'eman-radio-label');
     			$triField->setBelongsTo($id);
     			$form->addElement($triField);

     			$ordreField = new Zend_Form_Element_Radio('ordre_' . $id . '_' . $i);
     			$ordreField->setLabel('Ordre ');
     			$ordreField->setMultiOptions(array('asc' => 'Ascendant', 'desc' => 'Descendant'));
     			isset($config[$id]['ordre_' . $id . '_' . $i]) ? $ordreField->setValue($config[$id]['ordre_' . $id . '_' . $i]) : $ordreField->setValue('asc');
     			$ordreField->setSeparator(' ');
     			$ordreField->setAttrib('label_class', 'eman-radio-label');
     			$ordreField->setBelongsTo($id);
     			$form->addElement($ordreField);

         	$more = new Zend_Form_Element_Checkbox('more_' . $id . '_' . $i);
         	$more->setLabel('Lire la suite ');
         	isset($config[$id]['more_' . $id . '_' . $i]) ? $more->setValue($config[$id]['more_' . $id . '_' . $i]) : $more->setValue(0);
  				$more->setBelongsTo($id);
         	$form->addElement($more);

         	$link = new Zend_Form_Element_Checkbox('link_' . $id . '_' . $i);
         	$link->setLabel('Lier les valeurs du champ ');
         	isset($config[$id]['link_' . $id . '_' . $i]) ? $link->setValue($config[$id]['link_' . $id . '_' . $i]) : $link->setValue(0);
  				$link->setBelongsTo($id);
         	$form->addElement($link);

   				$blockSelects[] = $selectField;
   				$blockSelects[] = $fieldName;
   			}
 			}
   	}

   	// Checkbox lien collection
    if ($type == 'Items') {
     	$collection_link = new Zend_Form_Element_Checkbox('collection_link');
     	$collection_link->setLabel('Afficher le lien collection ?');
     	$collection_link->setValue($config['collection_link']);
     	$form->addElement($collection_link);
    }
   	$t = strtolower($type);
   	$titleSize = new Zend_Form_Element_Select('title_size_' . $t);
		$titleSize->setMultiOptions(array('16px' => '16px', '20px' => '20px', '24px' => '24px'));
		isset($config['title_size_' . $t]) ? $titleSize->setValue($config['title_size_' . $t]) : $titleSize->setValue('16px');
		$titleSize->setLabel("Taille du champ Titre(s) :");
		$form->addElement($titleSize);

   	$authorName = new Zend_Form_Element_Text('author_name_' . $t);
		isset($config['author_name_' . $t]) ? $authorName->setValue($config['author_name_' . $t]) : $authorName->setValue('Auteurs');
		$authorName->setLabel("Titre du champ Auteur(s) :");
		$form->addElement($authorName);

   	$authorSize = new Zend_Form_Element_Select('author_size_' . $t);
		$authorSize->setMultiOptions(array('16px' => '16px', '20px' => '20px', '24px' => '24px'));
		isset($config['author_size_' . $t]) ? $authorSize->setValue($config['author_size_' . $t]) : $authorSize->setValue('16px');
		$authorSize->setLabel("Taille du champ Auteur(s) :");
		$form->addElement($authorSize);

   	// Checkbox utiliser le template oui/non
   	$use_ui_templates = new Zend_Form_Element_Checkbox('use_ui_' . $t . '_template');
   	$use_ui_templates->setLabel("Remplacer $t/show ?");
   	$use_ui_templates->setValue(get_option('use_ui_' . $t . '_template'));
   	$form->addElement($use_ui_templates);

    $submit = new Zend_Form_Element_Submit('submit');
    $submit->setLabel('Save Template');
    $form->addElement($submit);

    return $this->prettifyForm($form);
	}

	public function indexAction()
	{
		$lang = get_html_lang();
		$type = $this->getParam('type');
		$type ? null : $type = 'item';
		$this->view->content = "<h3>UI Templates $type admin page</h3>";

		switch ($type) {
			case 'item' :
				$form = $this->getForm();
				$this->view->type = "Items";
				break;
			case 'collection' :
				$form = $this->getForm("Collections");
				$this->view->type = "Collections";
				break;
  		case 'file' :
				$form = $this->getForm("Files");
				$this->view->type = "Files";
				break;
  		case 'options' :
				$form = $this->getBrowseForm();
				$this->view->type = "Options Générales";
				break;
		}
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				$blocs = $form->getValues();
        if ($type == 'options') {
          set_option('uit_nbBlocks', $blocs['nbBlocks']);
          set_option('uit_nbFields', $blocs['nbFields']);
          set_option('uit_maxLength', $blocs['maxLength']);
          $this->view->form = $form;
          $this->_helper->flashMessenger('UI Templates general options saved.');
          return;
        }
				$optionVariable = "use_ui_" . $type . "s_template";
				set_option($optionVariable, $blocs[$optionVariable]);
 				// We don't save this variable in config == it's system wide
				unset($blocs[$optionVariable]);

				$config = array();
				if ($type == 'item') {
					$config['collection_link'] = $blocs['collection_link'];
				}
				$t = $type . 's';
				$config['author_name_' . $t] = $blocs['author_name_' . $t];
				$config['author_size_' . $t] = $blocs['author_size_' . $t];
				$config['title_size_' . $t] = $blocs['title_size_' . $t];

				unset($blocs['collection_link']);
				unset($blocs['author_name_' . $t]);
				unset($blocs['author_size_' . $t]);
				unset($blocs['title_size_' . $t]);
				// Tri des blocs avant sauvegarde
				$blocs = $this->triBlocs($blocs);
				foreach($blocs as $bloc => $values) {
  				if ($bloc == 'plugin_item_relations') {
    				set_option('ir_intitule', $values['ir_intitule']);
    				set_option('ir_intitule_obj', $values['ir_intitule_obj']);
  				}
  				if ($bloc == 'plugin_collection_relations') {
    				set_option('cr_intitule', $values['cr_intitule']);
    				set_option('cr_intitule_obj', $values['cr_intitule_obj']);
  				}
  				if ($bloc == 'plugin_file_relations') {
    				set_option('fr_intitule', $values['fr_intitule']);
    				set_option('fr_intitule_obj', $values['fr_intitule_obj']);
  				}
				  // Reorganize form values to fit config array format
				  if ($this->languages) {
  				  foreach($this->languages as $x => $lang) {
    					$config[$bloc]['options']['title'][$lang] = $values['title_' . $lang . '_' . $bloc];
  				  }
				  } else {
  					isset($values['title_' . $bloc]) ? $config[$bloc]['options']['title'] = $values['title_' . $bloc] : $config[$bloc]['options']['title'] = '';
  					isset($values['more_' . $bloc]) ? $config[$bloc]['options']['more'] = $values['more_' . $bloc] : $config[$bloc]['options']['more'] = 0;
  					isset($values['pres_' . $bloc]) ? $config[$bloc]['options']['pres'] = $values['pres_' . $bloc] : $config[$bloc]['options']['pres'] = 'liste';
  					isset($values['tri_' . $bloc]) ? $config[$bloc]['options']['tri'] = $values['tri_' . $bloc] : $config[$bloc]['options']['tri'] = 'alpha';
  					isset($values['ordre_' . $bloc]) ? $config[$bloc]['options']['ordre'] = $values['ordre_' . $bloc] : $config[$bloc]['options']['ordre'] = 'asc';
  					isset($values['link_' . $bloc]) ? $config[$bloc]['options']['link'] = $values['link_' . $bloc] : $config[$bloc]['options']['link'] = 0;
  					isset($values['bold_' . $bloc]) ? $config[$bloc]['options']['bold'] = $values['bold_' . $bloc] : $config[$bloc]['options']['bold'] = 0;
  					isset($values['retour_' . $bloc]) ? $config[$bloc]['options']['retour'] = $values['retour_' . $bloc] : $config[$bloc]['options']['retour'] = 0;
				  }
					$config[$bloc]['options']['private'] = isset($values['private_' . $bloc]) ? $values['private_' . $bloc] : 1;
					$config[$bloc]['options']['mask_col'] = isset($values['mask_col_' . $bloc]) ? $values['mask_col_' . $bloc] : 1;
					$config[$bloc]['options']['mask_it'] = isset($values['mask_it_' . $bloc]) ? $values['mask_it_' . $bloc] : 1;
					$config[$bloc]['options']['display'] = $values['display_' . $bloc];
					$config[$bloc]['options']['field_as_title'] = isset($values['field_as_title_' . $bloc]) ? $values['field_as_title_' . $bloc] : 1;
					$config[$bloc]['options']['order'] = $values['order_' . $bloc];
					$config[$bloc]['options']['column'] = $values['column_' . $bloc];
					// We get rid of the blockTitle, just used for admin display purposes
					unset($config[$bloc]['blockTitle_' . $bloc]);
					if (isset($values['title_' . $bloc])) {
  					unset($values['title_' . $bloc], $values['display_' . $bloc], $values['order_' . $bloc], $values['column_' . $bloc], $values['mask_col_' . $bloc], $values['mask_it_' . $bloc]);
					}
					if (isset($values['blockTitle_' . $bloc])) {
  					unset($values['blockTitle_' . $bloc]);
					}
					foreach($values as $key => $value) {
            $config[$bloc][$key] = $value;
					}
				}
				// Sauvegarde form dans DB
				$db = get_db();
				$config = base64_encode(serialize($config));
				$db->query("DELETE FROM `$db->UiTemplates` WHERE template_type = '" . $this->view->type . "'");
				$db->query("INSERT INTO `$db->UiTemplates` VALUES (null, '" . $this->view->type . "', '$config')");
				$this->_helper->flashMessenger('UI Templates options saved for ' . $type . ' display.');
			}
		}
		$this->view->form = $form;
	}

	private function triBlocs($blocs) {
		// Prepare array : copy order column for sorting
		foreach ($blocs as $key => $bloc) {
			$blocs[$key]['ordre'] = $blocs[$key]['order_' . $key];
		}
		foreach($blocs as $key => $values) {
			$order[$key] = $values['ordre'];
		}
		array_multisort($order, SORT_NUMERIC, $blocs);
		foreach($blocs as $key => $values) {
  		if ($blocs[$key]['ordre']) {
  			unset($blocs[$key]['ordre']);
  		}
		}
		return $blocs;
	}

	private function prettifyForm($form) {
		// Prettify form
		$blocks = $form->getElements();

    foreach ($blocks as $elem) {
      if ($elem instanceof Zend_Form_Element_Hidden) {
        $elem->removeDecorator('label')->removeDecorator('HtmlTag');
      }
    }

    // Fieldset pour les blocs
    $displayGroups = [];
    $currentDisplayGroup = '';
 		foreach ($form->getElements() as $name => $block) {
      $displayGroup = $block->getBelongsTo();
   		if ($displayGroup <> $currentDisplayGroup) {
     		$currentDisplayGroup = $displayGroup;
   		}
      $displayGroups[$currentDisplayGroup][] = $name;
   	}
   	foreach ($displayGroups as $block => $displayGroup) {
     	if ($block) {
       	$form->addDisplayGroup($displayGroup, $block);
       	$form->getDisplayGroup($block)->removeDecorator('DtDdWrapper');
     	} else {
       	$form->addDisplayGroup($displayGroup, 'general');
     	}
   	}
    $form->setDisplayGroupDecorators(array(
      'FormElements',
      'Fieldset',
      array('Fieldset', array('class' => 'uitemplates-fieldset'))
    ));
		$form->setDecorators(array(
			'FormElements',
			 array('HtmlTag', array('tag' => 'div', 'class' => 'uitemplates-form')),
			'Form'
		));
    $form->setElementDecorators(array(
        'ViewHelper',
        'Errors',
        array('Description', array('tag' => 'p', 'class' => 'description')),
        array('HtmlTag',     array('class' => 'form-div')),
        array('Label',       array('class' => 'form-label'))
      )
    );
    $form->setElementDecorators(array(
        'ViewHelper',
        'Label',
        new Zend_Form_Decorator_HtmlTag(array('tag' => 'div','class'=>'elem-wrapper'))
      )
    );
		return $form;
	}
}