<?php
/*******************************************************************************
 * The sole purpose of this script is to set the session variable to enable admin
 *	privileges.  Because the directory this is in is password-protected at the server
 *	level, it should work pretty well as an alternative to using a DB.  The only
 *	downside is that there's no way to "log out" short of killing the session, and
 *	adding new admins requires cPanel.
*******************************************************************************/
session_start();
$info = pathinfo( $_SERVER['PHP_SELF'] );
$subdomain = !empty( $info['dirname'] ) && $info['dirname'] != '/' ? $info['dirname'] : null;
$root = str_replace( '/admin', '', "http://{$_SERVER['HTTP_HOST']}$subdomain" );
$_SESSION['member'] = new stdClass();	// Use this to show that an admin is logged-in
// All done - go to the home page
header( "location: $root" );
exit;
?>