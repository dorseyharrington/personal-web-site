<?php

/**
 * File containing the index for system.
 *
 * @package Seven Kevins
 * @copyright Copyright (C) 2009 PHPRO.ORG. All rights reserved.
 * @filesource
 *
 */

//namespace sevenkevins;

session_start();
// In production, this will be set by first accessing a special password-protected script
//$_SESSION['member'] = new stdClass();	// Use this to show that an admin is logged-in

try {
	// define the site path
	$site_path = realpath(dirname(__FILE__));
	define ('__SITE_PATH', $site_path);

	// the application directory path 
	define ('__APP_PATH', __SITE_PATH.'/application');

	// add the application to the include path
	set_include_path( __APP_PATH );
	set_include_path( __SITE_PATH );

	// set the public web root path
	$info = pathinfo( $_SERVER['PHP_SELF'] );
	// We may have sub-domains using either sub.lindex.com or www.lindex.com/sub forms during development and testing
	$subdomain = !empty( $info['dirname'] ) && $info['dirname'] != '/' ? $info['dirname'] : null;
	$root = "http://{$_SERVER['HTTP_HOST']}$subdomain";
	//$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', __SITE_PATH);
	define( '__PUBLIC_PATH', $root );
	// Define a local log file for all errors ("system logger" in the PHP docs), also the file that error_log() writes to
	ini_set( "error_log", __SITE_PATH.'/logs/error.log' );
//	ini_set( "log_errors", "1" );				// writes to the above log file
	ini_set( "short_open_tag", "1" );			// allows <?= construct

	spl_autoload_register(null, false);

	spl_autoload_extensions('.php,.class.php,.lang.php');	// Embedded blanks have been reported as causing problems

	// model loader
	function modelLoader($class) {
		$class = strtolower( str_replace( 'sevenkevins\\', '', $class ) );
		// Special models; all others use <class>.class.php convention
		$models = array( 'icontroller.php', 'frontcontroller.php', 'view.php' );
		$class = strtolower( $class );
		$filename = $class . '.php';
		$file = in_array( $filename, $models ) ? __APP_PATH . "/models/$filename" : __APP_PATH . "/models/$class.class.php";
		if (file_exists($file) == false) {
//			echo "<p>".__METHOD__.": Didn't find $file</p>";
			return false;
		}

		include_once $file;
	}


	// autoload controllers
	function controllerLoader($class) {
		$class = str_replace( 'sevenkevins\\', '', $class );
		$module = str_replace( 'Controller', '', $class );
		$filename = $class . '.php';
		$file = strtolower( __APP_PATH . "/modules/$module/controllers/$filename" );
		if (file_exists($file) == false) {
//			echo "<p>".__METHOD__.": Didn't find $file</p>";
			return false;
		}
		include_once $file;
	}

	// autoload libs
	function libLoader( $class ) {
		$class = str_replace( 'sevenkevins\\', '', $class );
		$filename = strtolower( $class ) . '.class.php';
		// hack to remove //namespace 
		$file = __APP_PATH . '/lib/' . $filename;
		if (file_exists($file) == false) {
//			echo "<p>".__METHOD__.": Didn't find $file</p>";
			return false;
		}
		include_once $file;
	}

	spl_autoload_register( 'libLoader' );
	spl_autoload_register( 'modelLoader' );
	spl_autoload_register( 'controllerLoader' );

	$config = config::getInstance();
	$lang = $config->config_values['application']['language'];
	$filename = strtolower($lang) . '.lang.php';
	$file = __APP_PATH . '/lang/' . $filename;
	include $file;

	// alias the lang class
	//class_alias( '\\' . $lang, '\lang' );

	// set the domain status
//	$domain_config = domain_config::getInstance();
	// var_dump( $domain_config );

	// set the timezone
	date_default_timezone_set( $config->config_values['application']['timezone'] );

	/**
	 *
	 * @custom error function to throw exception
	 *
	 * @param int $errno The error number
	 *
	 * @param string $errmsg The error message
	 *
	 */
	function sevenkevinsErrorHandler( $errno, $errmsg ) {
		throw new sevenkevinsException( $errmsg, $errno );
	}
	/*** set error handler level to E_WARNING ***/
	// set_error_handler('sevenkevinsErrorHandler', $config->config_values['application']['error_reporting']);

	// Initialize the FrontController
	$front = FrontController::getInstance();
	$front->route();

	echo $front->getBody();
}
catch( sevenkevinsException $e ) {
	//show a 404 page here
	echo 'FATAL:<br />';
	echo $e->getMessage();
	echo $e->getLine();
}
// catch exceptions from the php exception class
catch( Exception $e ) {
	echo $e->getMessage();
}