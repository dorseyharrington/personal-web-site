<?php
/**
 * File containing the index controller
 *
 * @package Seven Kevins
 * @copyright Copyright (C) 2009 PHPRO.ORG. All rights reserved.
 *
 */

//namespace sevenkevins;

class shoppingController extends baseController implements IController
{

	public function __construct()
	{
		parent::__construct();
		// Set the layout here, if not layouts/index.phtml
	}

	public function index( $uri = null )
	{
		/*** a new view instance ***/
		$tpl = new view;

		/*** turn caching on for this page ***/
//		$tpl->setCaching(true);

		/*** set the template dir ***/
		$tpl->setTemplateDir(__APP_PATH . '/modules/shopping/views');

		/*** the include template ***/
		$tpl->include_tpl = __APP_PATH . '/views/shopping/index.phtml';

		// a new config
		$config = config::getInstance();
		$this->view->version = $config->config_values['application']['version'];

		/*** a view variable ***/
		$this->view->title = $config->config_values['application']['site_name']." :: Shopping List";
		$this->view->heading = 'Lindex CC';
		$this->view->sidebarimage = '<img class="sidebar" src="images/LCC_SideBar_Shop_216x540.jpg" />';

		// view variables
		$tpl->__set( 'image', 'Image goes here' );
		/*** the cache id is based on the file name ***/
		$cache_id = md5( 'admin/index.phtml' );

		/*** fetch the template ***/
		$this->content = $tpl->fetch( 'index.phtml', $cache_id);
	}

	public function test()
	{
		$view = new view;
		$view->text = 'this is a test';
		$result = $view->fetch( __APP_PATH.'/views/index.php' );
		$fc = FrontController::getInstance();
		$fc->setBody($result);
	}
}
