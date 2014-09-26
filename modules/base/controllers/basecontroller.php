<?php

//namespace sevenkevins;

class baseController
{
	protected	$breadcrumbs, $view, $content=null;
	protected	$layout = 'index.phtml';	// Default page layout.  Different than module view/template.

	public function __construct()
	{
		$this->view = new view();

		/*** create the bread crumbs in case we want to show the menu path to a visitor ***/
		$bc = new breadcrumbs();
		// $bc->setPointer('->');
		$bc->crumbs();
		$this->view->breadcrumbs = $bc->breadcrumbs;

		// Create common css and js links in header.  Add additional links to individual
		//	layouts as necessary.
		$links = new linkMaker();
		$this->view->links = $links;
		// Create the menu with current selection highlighted
		$menu = new menuMaker();
		$this->view->menu = $menu;
	}

	public function __destruct()
	{
		if( !is_null( $this->content ) )
		{
			$this->view->content = $this->content;
			$result = $this->view->fetch( __APP_PATH."/layouts/{$this->layout}" );
			$fc = FrontController::getInstance();
			$fc->setBody($result);
		}
	}
}