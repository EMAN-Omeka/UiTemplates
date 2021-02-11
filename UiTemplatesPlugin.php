<?php

/*
 * E-man Plugin
 *
 * Functions to customize Omeka for the E-man Project
 *
 */

class UiTemplatesPlugin extends Omeka_Plugin_AbstractPlugin
{

  protected $_hooks = array(
		'define_acl',
		'install',
		'uninstall',
		'define_routes',
		'public_head',
		'admin_head',
  );

  protected $_filters = array(
  	'admin_navigation_main',
  );

  function hookPublicHead()
  {
    queue_css_file('ui-templates');
  }

  function hookAdminHead()
  {
    queue_css_file('ui-templates');
  }

  function hookDefineRoutes($args)
  {

  		$router = $args['router'];

  		// Template configuration pages
  		$router->addRoute(
				'uitemplates_item_form',
				new Zend_Controller_Router_Route(
						'uitemplate/:type',
						array(
							'module' => 'ui-templates',
							'controller'   => 'page',
							'action'       => 'index',
							'type'				 => ''
						)
				)
  		);

  		$router->addRoute(
				'uitemplates_item_export',
				new Zend_Controller_Router_Route(
					'uitemplate/export',
					array(
						'module' => 'ui-templates',
						'controller'   => 'page',
						'action'       => 'export',
						'type'				 => ''
					)
				)
  		);

  		$router->addRoute(
  				'uitemplates_item_import',
  				new Zend_Controller_Router_Route(
  						'uitemplate/import',
  						array(
  								'module' => 'ui-templates',
  								'controller'   => 'page',
  								'action'       => 'import',
  								'type'				 => ''
  						)
  				)
  		);

  		if (! is_admin_theme()) {
	  		// If template is used, override Omeka items/show display
	  		$ui_template = get_option('use_ui_items_template');
	  		if ($ui_template && ! isset($_GET['output'])) {
		   		$router->addRoute(
		  				'uitemplates_show_item',
		  				new Zend_Controller_Router_Route(
		  						'items/show/:id',
		  						array(
		  								'module' => 'ui-templates',
		  								'controller'   => 'eman',
		  								'action'       => 'items-show',
		  								'id'					=> ''
		  						)
		  				)
		  		);
		   		// Exhibit Builder
		   		if (plugin_is_active('ExhibitBuilder')) {
			   		$router->addRoute(
			   				'uitemplates_exhibit_show_item',
			   				new Zend_Controller_Router_Route(
			   						'exhibits/show/:slug/item/:id',
			   						array(
			   								'module' => 'ui-templates',
			   								'controller'   => 'eman',
			   								'action'       => 'items-show',
			   								'id'					=> '',
			   								'slug' => '',
			   						)
			   				)
			   		);
		   		}
	  		}
	   		// If template is used, override Omeka  collections/show display
	  		$ui_template = get_option('use_ui_collections_template');
	  		if ($ui_template && ! isset($_GET['output'])) {
		   		$router->addRoute(
		  				'uitemplates_show_collection',
		  				new Zend_Controller_Router_Route(
		  						'collections/show/:id',
		  						array(
		  								'module' => 'ui-templates',
		  								'controller'   => 'eman',
		  								'action'       => 'collections-show',
		  								'id'					=> ''
		  						)
		  				)
		  		);
	  		}
	     	// If template is used, override Omeka collections/show display
	  		$ui_template = get_option('use_ui_files_template');
	  		if ($ui_template && ! isset($_GET['output'])) {
		   		$router->addRoute(
		  				'uitemplates_show_file',
		  				new Zend_Controller_Router_Route(
		  						'files/show/:id',
		  						array(
		  								'module' => 'ui-templates',
		  								'controller'   => 'eman',
		  								'action'       => 'files-show',
		  								'id'					=> ''
		  						)
		  				)
		  		);
	  		}
  		}
  }

  function hookDefineAcl($args)
  {
  	$acl = $args['acl'];
  	$uiTemplatesAdmin = new Zend_Acl_Resource('UiTemplates_Page');
  	$acl->add($uiTemplatesAdmin);
  }

  /**
   * Add the pages to the public main navigation options.
   *
   * @param array Navigation array.
   * @return array Filtered navigation array.
   */
  public function filterAdminNavigationMain($nav)
  {
    $nav[] = array(
                    'label' => __('UI Templates'),
                    'uri' => url('uitemplate/item'),
    								'resource' => 'UiTemplates_Page',
                  );
    return $nav;
  }

  /**
   * Install the plugin.
   */
  public function hookInstall()
  {
	  $db = $this->_db;
	  $sql = "CREATE TABLE IF NOT EXISTS `$db->UiTemplates` (
	  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `template_type` varchar(15) NOT NULL,
	  `text` mediumtext COLLATE utf8_unicode_ci,
	  PRIMARY KEY (`id`)
	  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
	  $db->query($sql);

	  $this->_installOptions();
  }


  /**
   * Uninstall the plugin.
   */
  public function hookUnInstall()
  {
	  $db = $this->_db;
	  $db->query("DROP TABLE `$db->UiTemplates`");
    delete_option('use_ui_collections_template');
    delete_option('use_ui_files_template');
    delete_option('use_ui_items_template');
    delete_option('uit_maxLength');
    delete_option('uit_nbBlocks');
    delete_option('uit_nbFields');
    delete_option('ir_intitule');
    delete_option('ir_intitule_obj');
    delete_option('cr_intitule');
    delete_option('cr_intitule_obj');
    delete_option('fr_intitule');
    delete_option('fr_intitule_obj');
  }

  static function displayObjectDC($object, $dc, $title, $itemset = 'Dublin Core') {
 		$metas = metadata($object, array($itemset, $dc), array('all' => true, 'no_filter' => true));
 		foreach ($metas as $i => $meta) {
 			if (strlen($meta) > 150) {
 				$metas[$i] = "<div style='text-align:justify;'>$meta</div>";
 			}
 		}
 		if ($title) : $title .= ' : '; endif;
		if (count($metas) > 1) {
			$metas = "<strong>$title</strong><ul class='eman-list'><li><!-- (DC.$dc) -->" . implode("</li><li>", $metas) . "</li></ul>";
		} else {
			$metas = "<strong>$title</strong><!-- (DC.$dc) -->" . $metas[0] . "<br />";
		}
  	return $metas;
  }
}