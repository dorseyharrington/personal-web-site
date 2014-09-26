<?php
/*******************************************************************************
 *	setup.php - unimaginatively named file that sets up various things for the
 *				AJAX servers in this directory.
 *
 *	Most of this is copied directly from the main index.php because it works.
 *
 ******************************************************************************/
//// Set up the AJAX server environment
// define the site path minus this file's parent directory
$site_path = str_replace( '/servers', '', realpath( dirname( __FILE__ ) ) );
define( '__SITE_PATH', $site_path );
// the application directory path
define( '__APP_PATH', __SITE_PATH.'/application' );
// Define a local log file for all errors ("system logger" in the PHP docs)
ini_set( "error_log", __SITE_PATH.'/logs/error.log' );
//ini_set( "log_errors", "1" );				// writes to the above log file
ini_set( "short_open_tag", "1" );			// allows <?= construct
// add the application to the include path
set_include_path( __APP_PATH );
set_include_path( __SITE_PATH );
// set the public web root path
$info = pathinfo( $_SERVER['PHP_SELF'] );
$subdomain = !empty( $info['dirname'] ) && $info['dirname'] != '/' ? $info['dirname'] : null;
$root = "http://{$_SERVER['HTTP_HOST']}$subdomain";
//$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', __SITE_PATH);
define( '__PUBLIC_PATH', $root );
spl_autoload_register( null, false );
spl_autoload_extensions( '.php,.class.php,.lang.php' );	// It's been reported that embedded blanks cause proglems
// create a model loader
function modelLoader( $class ) {
	//echo __FUNCTION__ . ": called for $class";
	//exit;
	$models = array('icontroller.php', 'frontcontroller.php', 'view.php');
	$class = strtolower( $class );
	$filename = $class . '.php';
	if( in_array( $filename, $models ) )
		$file = __APP_PATH . "/models/$filename";
	else
		$file = __APP_PATH . "/models/$class.class.php";
	//echo __FUNCTION__ . "Looking for $file";
	//exit;
	if (file_exists($file) == false)
		return false;
	include_once $file;
}

// autoload libs
function libLoader( $class ) {
	//echo __FUNCTION__ . ": called for $class<br/>";
	//$class = str_replace( 'sevenkevins\\', '', $class );
	$filename = strtolower( $class ) . '.class.php';
	// hack to remove namespace
	$file = __APP_PATH . '/lib/' . $filename;
	if (file_exists($file) == false)
	{
		return false;
	}
	include_once $file;
}

spl_autoload_register( 'libLoader' );
spl_autoload_register( 'modelLoader' );
?>