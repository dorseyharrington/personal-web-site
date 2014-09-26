<?php

/**
 *
 * A class to create a menu from a directory
 *
 */

//namespace sevenkevins;

class linkMaker {

	function __construct()
	{
	}

	/**
	*
	* Return a string representation of the HTML header links
	*
	* @access	public
	* @return	string
	*
	*/
	public function __toString()
	{
		$ret = null;
		$config = config::getInstance();
		// CSS stylesheets
		if( !empty( $config->config_values['css']['style']) )
			foreach( $config->config_values['css']['style'] as $fileName )
				$ret .= "<link rel='stylesheet' href='".__PUBLIC_PATH."/css/$fileName.css' />\n";
		// Javascript files
		if( !empty( $config->config_values['js']['filename'] ) )
			foreach( $config->config_values['js']['filename'] as $fileName )
				$ret .= "<script type='text/javascript' src='".__PUBLIC_PATH."/js/$fileName.js'></script>\n";
		return $ret;
	}
} // end of class

?>