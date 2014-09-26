<?php
/**
 *
 * A class to create a menu from a directory
 *
 */
//namespace sevenkevins;

class menuMaker {

	private $menulist = array( 'home' => null, 'about me' => 'about', 'project gallery' => 'project/fullgallery' );
//							   'contact me' => 'contact' );

	function __construct() {
		// Don't know how we'll fit this in to the nav menu images.  Maybe have to create a special link on the page somewhere.
		if( isset( $_SESSION['member'] ) )
			$this->menulist['Admin'] = 'account/account';
	}

	/**
	*
	* Return a string representation of the menu
	*
	* @access	public
	* @return	string
	*
	*/
	public function __toString() {
		$uri = uri::getInstance();
		$current = $uri->fragment( 0 );	// Use this component to highlight the current menu selection
		if( empty( $current ) )
			$current = null;
		$ret = "<div id='nav'><ul id='navlist'>\n";
		foreach( $this->menulist as $display => $action ) {
			$class = ( $action === $current ) ? 'menu_highlight' : 'menu_normal';
			$ret .= "<li class='$class'><a href='".__PUBLIC_PATH."/$action'>$display</a></li>\n";
		}
		$ret .= "</ul></div>\n";
		return $ret;
	}
} // end of class
?>