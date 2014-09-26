<?php/* namespace makeMania; */class accountController extends baseController implements IController{	public function __construct() {		parent::__construct();		$this->layout = 'account.phtml';		/* set specific view-layout vars to render */	}	/***************************************************************************	 * PRIVATE METHODS	 **************************************************************************/	/***************************************************************************	 * PUBLIC METHODS - Action mapping	 ***************************************************************************/	// Summary of this member's account viewable by the member only, because this	//	will include unpublished projects.	public function account( $uri = null ) {		// This user must be logged-in to prevent unauthorized viewing		if( !empty( $_SESSION['member'] ) ) {			$tpl = new view();			/*** turn caching on for this page ***/			//$tpl->setCaching( true );			$tpl->setTemplateDir( __APP_PATH.'/modules/account/views' );			// Create the detailed project content			$config = config::getInstance();			$this->view->config = $config = config::getInstance();			$this->view->version = $config->config_values['application']['version'];			try {//				echo "<pre>".print_r( project::fn_getFullProjectCollection( 1, 10, $num_pages ), true )."</pre>\n";				$collection = utils::displayProjectSet( project::fn_getFullProjectCollection( 1, 10, $num_pages ) );				$tpl->__set( 'collection', $collection );			} catch( Exception $e ) {				$ec = new error404Controller();				return $ec->index( $e );			}			/*** the cache id is based on the file name ***/			$cache_id = md5( 'account/account.php' );			/*** fetch the template (becomes $content in the layout template) ***/			$this->content = $tpl->fetch( 'account.phtml', $cache_id );		} else {			header( 'Location: '.__PUBLIC_PATH.'/admin');			exit;		}	}	// Cancel an edit - resets published status (value of published_on field)	public function cancel( $uri = null ) {		if( !empty( $_SESSION['member'] ) ) {			try {				$project = new project( $uri->fragment( 2 ) ); 				if( isset( $_SESSION['editing'][$project->fn_getProjectID()] ) && !empty( $_SESSION['editing'][$project->fn_getProjectID()] ) ) {					$project->fn_publish( $_SESSION['editing'][$project->fn_getProjectID()] );					unset( $_SESSION['editing'][$project->fn_getProjectID()] );				}				if( isset( $_SESSION['creating'] ) ) {					$project = new project( $_SESSION['creating'] );					$project->fn_remove();					unset( $_SESSION['creating'] );				}				$this->account( $uri );			} catch( Exception $e ) {				$ec = new error404Controller();				return $ec->index( $e );			}		} else {			header( 'Location: '.__PUBLIC_PATH.'/admin');			exit;		}	}	// Create a new project - can't use the word 'new' as the function name/action because that's reserved by PHP	public function create( $uri = null ) {		// The admin must be logged-in to prevent unauthorized editing		if( !empty( $_SESSION['member'] ) ) {			$tpl = new view();			/*** turn caching on for this page ***/			//$tpl->setCaching( true );			$tpl->setTemplateDir( __APP_PATH.'/modules/account/views' );			// Create the detailed project content for edit by member			$config = config::getInstance();			$this->view->version = $config->config_values['application']['version'];			try {				// We must have a logged-in member, if not, re-direct to login page				//die( '<pre>'.print_r( $_SESSION['member'], true ).'</pre>' );				$project = new project();				$_SESSION['creating'] = $project->fn_getProjectID();				$this->view->title = $this->view->heading = $config->config_values['application']['site_name']." :: New Project";				//echo '<pre>'.print_r( $project, true ).'</pre>';				$tpl->__set( 'which', 'Upload Your Project' );				$tpl->__set( 'project', $project );				$tpl->__set( 'new', true );				// The next call is necessary to create the empty image list structure				$tpl->__set( 'images', utils::displayImageSet( $project->fn_getImages() ) );			} catch( Exception $e ) {				$ec = new error404Controller();				return $ec->index( $e );			}			/*** the cache id is based on the file name ***/			$cache_id = md5( 'account/edit.php' );			/*** fetch the template (becomes $content in the layout template) ***/			$this->content = $tpl->fetch( 'edit.phtml', $cache_id );		} else {			header( 'Location: '.__PUBLIC_PATH.'/admin');			exit;		}	}	// Edit a member's project	public function edit( $uri = null ) {		// The admin must be logged-in to prevent unauthorized editing		if( !empty( $_SESSION['member'] ) ) {			$tpl = new view();			/*** turn caching on for this page ***/			//$tpl->setCaching( true );			$tpl->setTemplateDir( __APP_PATH.'/modules/account/views' );			// Create the detailed project content for edit by member			$config = config::getInstance();			$this->view->version = $config->config_values['application']['version'];			try {				$project = new project( $uri->fragment( 2 ) );				// Change project status to editing, save current status in case member cancels				$_SESSION['editing'][$project->fn_getProjectID()] = $project->fn_edit();				$tpl->__set( 'which', 'Edit Your Project' );				$tpl->__set( 'project', $project );				$tpl->__set( 'images', utils::displayImageSet( $project->fn_getImages() ) );			} catch( Exception $e ) {				$ec = new error404Controller();				return $ec->index( $e );			}			/*** the cache id is based on the file name ***/			$cache_id = md5( 'account/edit.php' );			/*** fetch the template (becomes $content in the layout template) ***/			$this->content = $tpl->fetch( 'edit.phtml', $cache_id );		} else {			header( 'Location: '.__PUBLIC_PATH.'/admin');			exit;		}	}	// Completely and irreversably remove a project and all images.	public function delete( $uri = null ) {		// This user must be logged-in to prevent unauthorized editing		if( !empty( $_SESSION['member'] ) ) {			$tpl = new view();			/*** turn caching on for this page ***/			//$tpl->setCaching( true );			$tpl->setTemplateDir( __APP_PATH.'/modules/account/views' );			// Create the detailed project content for edit by member			$config = config::getInstance();			$this->view->version = $config->config_values['application']['version'];			try {				$project = new project( $uri->fragment( 2 ) );				$project->fn_remove();				//echo '<pre>'.print_r( $project, true ).'</pre>';				header( 'Location: '.__PUBLIC_PATH.'/account/account' );				exit;			} catch( Exception $e ) {				$ec = new error404Controller();				return $ec->index( $e );			}		} else {			header( 'Location: '.__PUBLIC_PATH.'/admin');			exit;		}	}		public function logout( $uri = null ) {		// Simply remove the appropriate session variable so that the admin menu becomes inaccessible		unset( $_SESSION['member'] );		header( 'Location: '.__PUBLIC_PATH );		exit;	}	// Publish a new project, or re-publish an older one.	public function publish( $uri = null ) {		// This user must be logged-in to prevent unauthorized editing		if( !empty( $_SESSION['member'] ) ) {			$tpl = new view();			/*** turn caching on for this page ***/			//$tpl->setCaching( true );			$tpl->setTemplateDir( __APP_PATH.'/modules/account/views' );			// Create the detailed project content for edit by member			$config = config::getInstance();			$this->view->version = $config->config_values['application']['version'];			try {				$project = new project( $uri->fragment( 2 ) );				$project->fn_publish();				//echo '<pre>'.print_r( $project, true ).'</pre>';				header( 'Location: '.__PUBLIC_PATH.'/account/account' );				exit;			} catch( Exception $e ) {				$ec = new error404Controller();				return $ec->index( $e );			}		} else {			header( 'Location: '.__PUBLIC_PATH.'/admin');			exit;		}	}}