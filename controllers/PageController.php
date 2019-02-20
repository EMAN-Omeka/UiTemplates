<?php
class UiTemplates_PageController extends Omeka_Controller_AbstractActionController
{

	public function getForm($type = "Items")
	{        
        $form = new Zend_Form();
        $form->setName('UITemplates' . $type . 'Form');        
        // Retrieve list of item's metadata
        $db = get_db();
      	$elements = $db->query("SELECT id, name FROM `$db->Elements` WHERE id IN (SELECT DISTINCT element_id FROM `$db->ElementTexts`) ORDER BY name");
       	$elements = $elements->fetchAll();
        $elements = array_column($elements, 'name', 'id');
        $elements['none'] = "None"; // Aucun champ
                
        // Retrieve config for this type from DB
      	$config = $db->query("SELECT * FROM `$db->UiTemplates` WHERE template_type = '$type'")->fetchAll();
      	$config = array_pop($config);
      	$config = unserialize(base64_decode($config['text']));        	

       	// Available blocks for template
       	$blocks = array('bloc1' => "Bloc 1", 'bloc2' => "Bloc 2", 'bloc3' => "Bloc 3", 'bloc4' => "Bloc 4", 'bloc5' => "Bloc 5");
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
           	}
           	plugin_is_active('SocialBookmarking') ? $blocks['plugin_social'] = "Social Networks" : null; 
         	case 'Files' :
            $blocks['plugin_export'] = "Export de la fiche";
           	plugin_is_active('EmanCitation') ? $blocks['plugin_citation'] = "Citation" : null; 
           	plugin_is_active('Commenting') ? $blocks['plugin_comment'] = "Commentaires" : null;  
           	if ($type == 'Files') {           	
             	$blocks['plugin_file'] = "Affichage du fichier";           	         	         	
             	$blocks['plugin_fileinfo'] = "Informations sur le fichier";           	         	         	         	         	         	
//               plugin_is_active('Transcript') ? $blocks['plugin_transcript'] = "Transcription" : null;  
            }
           break;
       	}
       	foreach ($blocks as $id => $block) {
       			$blockSelects = array();

       			$blockTitle = new Zend_Form_Element_Note('blockTitle_' . $id);
       			$blockTitle->setValue("<h2>$block</h2>");       								
      			$blockTitle->setBelongsTo($id);
       			$form->addElement($blockTitle);
       			       			
	       		// Titre du block 
       			$titleField = new Zend_Form_Element_Text('title_' . $id);
       			$titleField->setLabel('Titre du bloc');
       			$titleField->setValue($config[$id]['options']['title']);
       			$titleField->setBelongsTo($id);
       			$form->addElement($titleField);
       			// Checkbox afficher titre oui/non
       			$titleDisplay = new Zend_Form_Element_Checkbox('display_' . $id);
       			$titleDisplay->setLabel('Afficher le titre ?');
       			$titleDisplay->setValue($config[$id]['options']['display']);
       			$titleDisplay->setBelongsTo($id);
       			$form->addElement($titleDisplay);     

       			// Checkbox afficher oui/non
       			$private = new Zend_Form_Element_Checkbox('private_' . $id);
       			$private->setLabel('Bloc privé (masqué aux visiteurs) ?');
       			$private->setValue($config[$id]['options']['private']);
       			$private->setBelongsTo($id);
       			$form->addElement($private);     
       			
       			$blockSelects[] = $blockTitle;
       			$blockSelects[] = $titleField;
       			$blockSelects[] = $titleDisplay;       		
       				
       			// Poids pour ordre d'affichage
       			$blockOrder = new Zend_Form_Element_Text('order_' . $id);
       			$blockOrder->setLabel('Ordre d\'apparition du bloc dans la colonne');
       			$blockOrder->setValue($config[$id]['options']['order']);
       			$blockOrder->setBelongsTo($id);
       			$form->addElement($blockOrder);
       			
       			// Colonne ?
       			$column = new Zend_Form_Element_Radio('column_' . $id);
       			$column->setLabel('Colonne d\'affichage (Aucune = bloc masqué)');
       			$column->setMultiOptions(array(1 => '1', 2 => '2', 0 => 'Aucune'));
       			$column->setValue($config[$id]['options']['column']);
       			$column->setSeparator(' ');
       			$column->setAttrib('label_class', 'eman-radio-label');
       			$column->setBelongsTo($id);
       			$form->addElement($column);

       			$blockSelects[] = $blockOrder;
       			$blockSelects[] = $column;
       			if ($id == 'plugin_relations' || substr($id, 0, 7) <> 'plugin_') {   			
	       			// Fields
	       			$nbFields = 7;
	       			for ($i = 1; $i <= $nbFields ; $i++) {       				
	       				$selectName = $id . '_' . $i;       				 
	       				$selectField = new Zend_Form_Element_Select($selectName);
	       				$selectField->setLabel("Champ " . $i)
	       				->setMultiOptions($elements);
	       				
	       				if (isset($config[$id][$selectName])) {
	       					$defaultValue = $config[$id][$selectName];
	       				} else {
	       					$defaultValue = 'none';
	       				}
	       				$selectField->setValue($defaultValue); // Set field value fetched from DB
	       				$selectField->setBelongsTo($id);
	       				$form->addElement($selectField);
								
	       				$titleName = 'name_' . $id . '_' . $i;
	       				$fieldName = new Zend_Form_Element_Text($titleName);
	       				$fieldName->setValue($config[$id][$titleName]);
	       				$fieldName->setBelongsTo($id);
	       				$fieldName->setLabel("Titre du champ : Champ " . $i);       				 
	       				$form->addElement($fieldName);
	       							
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
       	
       	// Checkbox utiliser le template oui/non
       	$t = strtolower($type);       	
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
		$type = $this->getParam('type');
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
		}
		if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				$blocs = $form->getValues();
				$optionVariable = "use_ui_" . $type . "s_template";
				set_option($optionVariable, $blocs[$optionVariable]);
 				// We don't save this variable in config == it's system wide
				unset($blocs[$optionVariable]);
				
				$config = array();
				if ($type == 'item') {
						$config['collection_link'] = $blocs['collection_link'];
						unset($blocs['collection_link']);		
				}

				// Tri des blocs avant sauvegarde		
				$blocs = $this->triBlocs($blocs);		
				foreach($blocs as $bloc => $values) {
				// Reorganize form values to fit config array format  				
					$config[$bloc]['options']['title'] = $values['title_' . $bloc];
					$config[$bloc]['options']['private'] = $values['private_' . $bloc];
					$config[$bloc]['options']['display'] = $values['display_' . $bloc];
					$config[$bloc]['options']['order'] = $values['order_' . $bloc];
					$config[$bloc]['options']['column'] = $values['column_' . $bloc];					
					// We get rid of the blockTitle, just used for admin display purposes
					unset($config[$bloc]['blockTitle_' . $bloc]);
					if ($values['title_' . $bloc]) {
  					unset($values['title_' . $bloc], $values['display_' . $bloc], $values['order_' . $bloc], $values['column_' . $bloc]);  					
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
		$form->setDecorators(array(
				'FormElements',
				array('HtmlTag', array('tag' => 'table')),
				'Form'
		));
		$form->setElementDecorators(array(
				'ViewHelper',
				'Errors',
				array(array('data' => 'HtmlTag'), array('tag' => 'td')),
				array('Label', array('tag' => 'td', 'style' => 'text-align:right;float:right;')),
				array(array('row' => 'HtmlTag'), array('tag' => 'tr'))
		));		
		return $form;	
	}
	private function getDC() {
		return array(
				'dccontributor',
				'dccoverage',
				'dccreator',
				'dcdate',
				'dcdescription',
				'dcformat',
				'dcidentifier',
				'dclanguage',
				'dcpublisher',
				'dcrelation',
				'dcrights',
				'dcsource',
				'dcsubject',
				'dctitle',
				'dctype',
		);		
	}
}