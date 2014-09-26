<?php

//namespace sevenkevins;

class error404Controller extends baseController implements IController
{
	/**
	*
	* Constructor, duh
	*
	*/
	public function __construct()
	{
		parent::__construct();
		$this->layout = 'index.phtml';
	}

	/**
	*
	* The index function
	*
	* @access	public
	*
	* The way to use this for displaying an error that occurs in a controller action method
	*  so that the error appears within the full 404 page:
	* 	$ec = new error404Controller();
	*	return $ec->index( $e );
	*/
	public function index( $e = null )
	{
		/*** a new view instance ***/
		$tpl = new view;

		/*** turn caching on for this page ***/
		// $view->setCaching(true);

		/*** set the template dir ***/
		$tpl->setTemplateDir(__APP_PATH.'/modules/error404/views');

		/*** a view variable ***/
		if( is_a( $e, "Exception" ) ) {
			$this->view->title = 'An error occured while processing your request';
			$this->view->heading = 'Error Message:';
			$tpl->__set( 'content', $e->getMessage() );
		} else {
			$this->view->title = '404 File Not Found';
			$this->view->heading = '404 File Not Found';
			$tpl->__set( 'content', 'The page you are looking for was not found on the system.' );
		}

		/*** the cache id is based on the file name ***/
		$cache_id = md5( '404/index.php' );

		/*** fetch the template ***/
		$this->content = $tpl->fetch( 'index.phtml', $cache_id);
	}

}
