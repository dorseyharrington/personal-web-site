<?php
/*******************************************************************************
 *	updateproject.php - AJAX server that does various things, uses echo to return
 *						operation status to caller.  This can be invoked with either
 *						POST or GET methods, but POST is preferred.
 *
 ******************************************************************************/
require_once( 'setup.php' );
//utils::logmsg( __FILE__.print_r( $_REQUEST, true ), 0 );
// Now we can do the server stuff
//	Use the mechanism below to return debug data to the client
//echo json_encode( array( 'status' => 'ERROR', 'message' => print_r( $_REQUEST, true ) ) );
//exit;
$id = $_REQUEST['id'];
// Change a project image's relative position
if( $id == 'move' ) {
//	echo json_encode( array( 'status' => 'OK', 'message' => null ) );
//	exit;
	$direction = isset( $_REQUEST['direction'] ) ? $_REQUEST['direction'] : null;	// direction: up, down, or delete
	try {
		if( $direction == 'delete' ) {
			$image = new image( $image_id );
			$image->fn_remove();
		} else {	// reset the diplay order and main status of all images in the set
			$display_order = 0;
			if( count( $_REQUEST['arrayorder'] ) > 0 )
				foreach( $_REQUEST['arrayorder'] as $image_id ) {
					$image = new image( $image_id );
					// Only update image if necessary to avoid flogging the DB as user re-arranges images
					if( $display_order == 0 ) {
						if( !$image->fn_IsMain() )
							$image->fn_setMain( true );
					} else {
						if( $image->fn_IsMain() )
							$image ->fn_setMain ( false );
					}
					if( $image->fn_getDisplayOrder() != $display_order )
						$image->fn_setDisplayOrder ( $display_order );
					++$display_order;
				}
		}
		echo json_encode( array( 'status' => 'OK', 'message' => null ) );
	} catch( Exception $e ) {
		echo json_encode( array( 'status' => 'ERROR', 'message' => $e->getMessage() ) );
	}
} else if( $id == 'rearrange' ) {
//	echo json_encode( array( 'status' => 'OK', 'message' => null ) );
//	exit;
	$direction = isset( $_REQUEST['direction'] ) ? $_REQUEST['direction'] : null;	// direction: up, down, or delete
	try {
		if( $direction == 'delete' ) {
			$project = new project( $project_id );
			$project->fn_remove();
		} else {	// reset the diplay order and main status of all projects in the set
			$display_order = 0;
			if( count( $_REQUEST['arrayorder'] ) > 0 )
				foreach( $_REQUEST['arrayorder'] as $project_id ) {
					$project = new project( $project_id );
					// Only update project if necessary to avoid flogging the DB as user re-arranges projects
					if( $project->fn_getDisplayOrder() != $display_order )
						$project->fn_setDisplayOrder ( $display_order );
					++$display_order;
				}
		}
		echo json_encode( array( 'status' => 'OK', 'message' => null ) );
	} catch( Exception $e ) {
		echo json_encode( array( 'status' => 'ERROR', 'message' => $e->getMessage() ) );
	}
} else if( $id == 'save' ) {
	// We're closely linking the request type and field name with the object class and setter method;
	//	i.e., field name = Title will invoke fn_setTitle().
//	echo json_encode( array( 'status' => 'OK', 'message' => 'Method save invoked' ) );
//	exit;
	$objectType = $_REQUEST['type'];
	if( $objectType == 'supply' || $objectType == 'project' || $objectType == 'image' ) {
		$target = new $objectType( $_REQUEST['key'] );
		$method = "fn_set{$_REQUEST['field']}";
		if( method_exists( $target, $method ) ) {
			try {
				$target->$method( $_REQUEST['value'] );
				echo json_encode( array( 'status' => 'OK', 'message' => null ) );
			} catch( Exception $e ) {
				echo json_encode( array( 'status' => 'ERROR', 'message' => $e->getMessage() ) );
			}
		} else
			echo json_encode( array( 'status' => 'ERROR', 'message' => "Unknown field for $objectType: {$_REQUEST['field']}" ) );
	} else
		echo json_encode( array( 'status' => 'ERROR', 'message' => "Invalid object type: $objectType" ) );
} else if( $id == 'upload' ) {	// => project image
//	utils::logmsg( 'Method upload invoked' );
	// Add the image to the given project, return the new images's data.  It would be cool to return the image object:
	//	json_encode( array( 'image' => serialize( $image ) );
	try {
		$project = new project( $_REQUEST['project_id'] );
		$image = $project->fn_addImage( $_FILES['newImage'] );
		// Return whatever we need from the image so it can be added to the upload page
		$caption = $image->fn_getCaption() == null ? 'Enter Short Description...' : $image->fn_getCaption ();
		$main = $image->fn_isMain() ? 'checked="checked"' : '';
		$imageURL = utils::getImageURL( 'thumbnail', $image );
		echo json_encode( array( 'status' => 'OK', 'display_name' => $image->fn_getFileName(),
								 'project_id' => $image->fn_getProjectID(),
								 'image_id' => $image->fn_getImageID(), 'caption' => $caption, 'main' => $main,
								 'imageURL' => $imageURL, 'orig_file_name' => $image->fn_getOriFileName() ) );
	} catch( Exception $e ) {
		echo json_encode( array( 'status' => 'ERROR', 'message' => $e->getMessage() ) );
	}
} else
	echo json_encode( array( 'status' => 'ERROR', 'message' => print_r( $_REQUEST, true ) ) );
?>