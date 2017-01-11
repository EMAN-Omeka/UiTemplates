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
  		'install',
  		'define_routes',  		
  );
  
  protected $_filters = array(
  	'admin_navigation_main',
  );  

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
  		if (! is_admin_theme()) {
	  		// If template is used, override Omeka items/show display
	  		$ui_template = get_option('use_ui_item_template');  		
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
	  		}
  		}
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
                    'uri' => url('uitemplate/item')
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
	  `text` text COLLATE utf8_unicode_ci,
	  PRIMARY KEY (`id`)
	  ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
	  $db->query($sql);
	  
	  $this->_installOptions();
  }  
  static function displayCollectionDC($collection, $dc, $title) {
		$meta = metadata($collection, array('Dublin Core', $dc), array('delimiter'=>'<br />'));
		if (strlen($meta) > 150) {
			$meta = "<div style='padding-left:2em;text-align:justify;'>$meta</div>";
		}
		$meta = "<span class='dclabel'><strong>$title</strong></span><!-- (DC.$dc) --> : $meta<br /><br />";
  	return $meta;  	  	
  }
}
