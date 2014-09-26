<?php

/**
 * Manages an image that is either loaded or created by the constructor.
 *
 * @author Dorsey
 *
 */

class image {

	private	$image_id = null;
	private	$project_id = null;
	private	$caption = null;
	private	$display_order = null;
	private	$main = null;
	private	$ori_file_name = null;
	private	$file_name = null;
	private	$width = null;
	private	$height = null;

	function __construct( $image_id = null, $project = null, $file = null ) {
		//$this->logmsg( __METHOD__ );
		if( !empty( $image_id ) ) {
			$sql = "SELECT * FROM image WHERE image_id = $image_id";
			if( ( $resource = db::getInstance()->query( $sql ) ) && $resource->rowCount() == 1 ) {
				foreach( $resource->fetch( PDO::FETCH_ASSOC ) as $column => $value )
					if( property_exists( $this, $column ) )
						$this->{$column} = $value;
				if( empty( $this->ori_file_name ) )
					$this->ori_file_name = $this->file_name;
				$resource->closeCursor();
			} else {
				//$this->logmsg( __METHOD__ . ": Throwing an exception" );
				throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_IMAGE_NOT_FOUND_DESC, $image_id ) );
			}
		} else if( !empty( $project ) && !empty( $file['name'] ) ) {
			// Create a new image from an uploaded file
			if( !$this->fileExists( $file['name'], $project ) ) {
				if( $file['size'] <= 5242880 ) {
					// We're going to use PHP's getimagesize(), although it's not very reliable for the image size
					if( ( $imageInfo = @getimagesize( $file['tmp_name'] ) ) !== false ) {
						$this->project_id = $project->fn_getProjectID();
						$directory = __SITE_PATH."/projects/{$this->project_id}/";	// Target directory
						@mkdir( $directory, 0777, true );							// Makes the full tree and/or any missing component(s)
						$info = pathinfo( $file['name'] );
						// We're keeping two versions of the file name - the given name and one with '_ori' inserted
						//	We always store the original file using the second name so we can quickly and easily identify them.
						//	This is highly useful when clearing out old images.
						$this->file_name = $file['name'];											// Given file name
						$basename = basename( $file['name'], ".{$info['extension']}" );				// Given file name minus extension
						$this->ori_file_name = "{$basename}_ori.{$info['extension']}";				// Base name + suffix and extension
						$this->display_order = 0;
						$target = "$directory{$this->ori_file_name}";								// Final resting place
						if( move_uploaded_file( $file['tmp_name'], $target ) ) {
							utils::set_file_permissions( $target );	// Changes from the default 644 to 666 so we can overwrite it later
							$sql = "SELECT MAX( display_order ) AS display_order FROM image
									WHERE project_id = {$this->project_id}";
							// We're using prepare in case someone creates a file name with bad stuff in it.
							$statement = db::getInstance()->prepare( $sql );
							if( $statement->execute() && $statement->rowCount() == 1 ) {
								extract( $statement->fetch( PDO::FETCH_ASSOC ) );
								$this->display_order = $display_order + 1;	// Assign the next number
								$statement->closeCursor();
							}
							$sql = "INSERT INTO image( image_id, project_id, file_name, main, display_order, ori_file_name, width, height )
									VALUES( NULL, {$project->fn_getProjectID()}, '{$this->file_name}', 0, {$this->display_order},
											'{$this->ori_file_name}', {$imageInfo[0]}, {$imageInfo[1]} )";
							$statement = db::getInstance()->prepare( $sql );
							if( $statement->execute() && $statement->rowCount() == 1 )
								$this->image_id = db::getInstance()->lastInsertId();
							else
								throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_IMAGE_FILE_NOT_UPLOADED_DESC, $file['name'] ) );
						} else
							throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_IMAGE_FILE_NOT_UPLOADED_DESC, $file['name'] ) );
					} else
						throw new Exception( __METHOD__ .': ' . sprintf( ERR::ERR_IMAGE_NOT_VALID_DESC, $file['name'] ) );
				} else
					throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_IMAGE_TOO_LARGE_DESC, $file['name'], number_format( $file['size'], 0 ) ) );
			} else
				throw new Exception( sprintf( ERR::ERR_IMAGE_DUPLICATE_DESC, $file['name'] ) );
		} else
			throw new Exception( ERR::ERR_IMAGE_NO_DATA );
	}

	function __destruct() {
	}

	/**********************************************************************
	 *					PRIVATE METHODS
	 ***********************************************************************/

	private function dbSafeString( $value ) {
		return $value == null || strlen( $value ) == 0 ? 'NULL' : stripslashes( $value );
	}

	// Return true if the image has already been recorded
	private function fileExists( $name, $project ) {
		$sql = "SELECT COUNT(*) AS count FROM image
				WHERE file_name like '{$project->fn_getProjectID()}/%$name'";
		// We're using prepare in case someone creates a file name with bad stuff in it.
		$statement = db::getInstance()->prepare( $sql );
		if( $statement->execute() && $statement->rowCount() == 1 ) {
			extract( $statement->fetch( PDO::FETCH_ASSOC ) );
			$statement->closeCursor();
			return $count > 0;
		}
		return false;
	}

	/**********************************************************************
	 *					CLASS METHODS
	 ***********************************************************************/

	/**********************************************************************
	 *					PUBLIC METHODS
	 ***********************************************************************/

	public function fn_IsMain() {
		return $this->main == 1;
	}

	public function fn_setCaption( $value ) {
		if( !empty( $this->image_id ) ) {
			$this->caption = $value;
			$sql = "UPDATE image SET caption = :caption WHERE image_id = {$this->image_id}";
			$statement = db::getInstance()->prepare( $sql );
			if( $statement->execute( array( ':caption' => $this->dbSafeString( $this->caption ) ) ) )
				return;
			throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_PROJECT_NOT_UPDATED_DESC, 'caption', $this->image_id ) );
		}
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_PROJECT_NOT_INITIALIZED_DESC );
	}

	public function fn_setOriFileName( $name ) {
		$this->ori_file_name = $name;
	}

	public function fn_getCaption() {
		return $this->caption;
	}

	public function fn_getDisplayOrder() {
		return $this->display_order;
	}

	public function fn_getPosition() {
		return $this->display_order;
	}

	public function fn_getFileName() {
		return $this->file_name;
	}

	public function fn_getHeight() {
		return $this->height;
	}

	public function fn_getImageID() {
		return $this->image_id;
	}

	public function fn_getOriFileName() {
		return $this->ori_file_name;
	}

	public function fn_getProjectID() {
		return $this->project_id;
	}

	public function fn_getWidth() {
		return $this->width;
	}

	public function fn_isMainImage() {
		return $this->main == 1;
	}

	public function fn_setDisplayOrder( $order ) {
		if( !empty( $this->image_id ) ) {
			$this->display_order = $order;
			$sql = "UPDATE image SET display_order = {$this->display_order} WHERE image_id = {$this->image_id}";
			if( db::getInstance()->exec( $sql ) == 1 )
				return;
			throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_IMAGE_NOT_UPDATED_DESC, $this->image_id ) );
		}
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_IMAGE_NOT_INITIALIZED_DESC );
	}

	public function fn_setMain( $yesno ) {
		if( !empty( $this->image_id ) ) {
			// We can only have one main image, so if this one is declared to be it, we have to first
			//	un-declare any previous one(s).  The smart move here is to assume that there might be more than
			//	one, and clear them all.
			if( $yesno ) {
				$sql = "UPDATE image SET main = 0 WHERE project_id = {$this->project_id} AND main = 1";
				db::getInstance()->exec( $sql );
			}
			$this->main = $yesno ? 1 : 0;
			$sql = "UPDATE image SET main = {$this->main} WHERE image_id = {$this->image_id}";
			if( db::getInstance()->exec( $sql ) == 1 )
				return;
			throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_IMAGE_NOT_UPDATED_DESC, $this->image_id ) );
		}
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_IMAGE_NOT_INITIALIZED_DESC );
	}

	// Remove this image from the DB and file system
	public function fn_remove() {
		if( !empty( $this->image_id ) ) {
			$sql = "DELETE FROM image WHERE image_id = {$this->image_id}";
			if( db::getInstance()->exec( $sql ) == 1 ) {
				// Remove the entire image set, which is derived from the base file name
				$info = pathinfo( $this->file_name );
				$config = config::getInstance();
				$filemask = "{$config->config_values['images']['PROJECT_PATH']}/$this->project_id/{$info['basename']}";
				$filemask = str_replace( ".{$info['extension']}", '*', $filemask );
				//echo "filemask = $filemask\n";
				foreach( glob( $filemask ) as $filename ) {
					//echo "unlinking $filename\n";
					if( !@unlink( $filename ) )
						throw new Exception(  __METHOD__ . ': ' . sprintf( ERR::ERR_IMAGE_FILE_NOT_REMOVED_DESC, $this->filename ) );
				}
				return;
			} else
				throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_IMAGE_NOT_REMOVED_DESC, $this->image_id ) );
		}
		throw new Exception( __METHOD__ . ERR::ERR_IMAGE_NOT_INITIALIZED_DESC );
	}
}
?>
