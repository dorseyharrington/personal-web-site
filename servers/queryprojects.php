<?php
require_once( 'setup.php' );
// Now we can do the server stuff - Using AJAX's UI library for autocomplete, we know
//	that this script will be sent a search string in $_REQUEST['q'], and that we simply echo
//	back the resutls, one newline-terminated line at a time.
	// Debug - return the words on either side of the matching string
//echo json_encode( array( 'window', 'remodel' ) );
//exit;
$matches = array();
if( !empty( $_REQUEST['term'] ) ) {
	$q = $_REQUEST['term'];
	// Get matching projects
	$query = "SELECT title AS match1, description AS match2
			  FROM project
			  WHERE published_on IS NOT NULL AND
					MATCH( title, description ) AGAINST( :query IN BOOLEAN MODE )
			  UNION
			  SELECT caption AS match1, NULL as match2
			  FROM image AS i LEFT JOIN project AS p USING( project_id )
			  WHERE p.published_on IS NOT NULL AND
					MATCH( caption ) AGAINST( :query IN BOOLEAN MODE )";
	// Use prepare to ward off SQL injection attacks
	$statement = db::getInstance()->prepare( $query );
	if( $statement->execute( array( ':query' => "'$q*'" ) ) ) {
		while( $row = $statement->fetch( PDO::FETCH_ASSOC ) ) {
			extract( $row );
			$matches[] = $match1;
			if( !empty( $match2 ) )
				$matches[] = $match2;
		}
		$statement->closeCursor();
	}
	if( count( $matches ) > 0 ) {
		sort( $matches );
		$results = array();
		$qLen = strlen( $q );
		foreach( $matches as $match ) {
			$mLen = strlen( $match );
			$mid = floor( $mLen / 2 );
			// Pick out the text surrounding the matching string
			$start = strpos( $match, $q );
			$results[] = substr( $match, $start, 20 );
//			$results[] = $match;
		}
		sort( $results );
		echo json_encode( $results );
		exit;
	}
}
echo json_encode( $matches );
?>