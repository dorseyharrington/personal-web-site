<?php
/*******************************************************************************
 * Manages project items.  Note that projects and items are synonymous.
 *
 * @author Dorsey
********************************************************************************/

class project {
	// DB columns
	private	$project_id = null;
	private	$title = null;
	private	$description = null;
	private	$create_time = null;
	private	$display_order = null;
	private	$featured = null;
	private	$published_on = null;

	// Derived values
	private	$mainImage = null;
	private $mainImageIndex = null;

	function __construct( $item_id = null ) {
		//echo __METHOD__ . ": Invoked with item_id = $item_id<br />";
		if( !empty( $item_id ) ) {
			$sql = "SELECT *
					FROM project AS p
					WHERE p.project_id = $item_id";
			if( ( $resource = db::getInstance()->query( $sql ) ) && $resource->rowCount() == 1 ) {
				foreach( $resource->fetch( PDO::FETCH_ASSOC ) as $column => $value )
					if( property_exists( $this, $column ) )
						$this->{$column} = $value;
				$resource->closeCursor();
				$this->getImages();
			} else {
				//$this->logmsg( __METHOD__ . ": Throwing an exception" );
				throw new Exception( sprintf( ERR::ERR_ITEM_NOT_FOUND_DESC, $item_id ) );
			}
		} else {
			$this->display_order = 0;
			$sql = "SELECT MAX( display_order ) AS display_order FROM project WHERE project_id > 1";
			// We're using prepare in case someone creates a file name with bad stuff in it.
			$statement = db::getInstance()->prepare( $sql );
			if( $statement->execute() && $statement->rowCount() == 1 ) {
				extract( $statement->fetch( PDO::FETCH_ASSOC ) );
				$this->display_order = $display_order + 1;	// Assign the next number
				$statement->closeCursor();
			}
			$sql = "INSERT into project( project_id, create_time, display_order )
					VALUES( NULL, NOW(), {$this->display_order} )";
			$statement = db::getInstance()->prepare( $sql );
			if( $statement->execute() && $statement->rowCount() == 1 )
				$this->project_id = db::getInstance()->lastInsertId();
			else
				throw new Exception( __METHOD__ . ': ' . ERR::ERR_ITEM_NOT_CREATED );
		}
	}

	function __destruct() {
	}

	/**********************************************************************
	 *					PRIVATE METHODS
	 ***********************************************************************/

	private function getImages() {
		$imgQ = "SELECT image_id FROM image WHERE project_id = {$this->project_id} ORDER BY display_order ASC";
		if( ( $resource = db::getInstance()->query( $imgQ ) ) && $resource->rowCount() > 0 ) {
			$mainImageIndex = 0;
			foreach( $resource as $row ) {
				extract( $row );
				$image = new image( $image_id );
				$this->images[$image_id] = $image;
				if( empty( $this->mainImage ) || $image->fn_IsMain() ) {	// First project image or designated main image
					$this->mainImage = $image;
					$this->mainImageIndex = $mainImageIndex;
				}
				++$mainImageIndex;
			}
			$resource->closeCursor();
		} else {
			$config = config::getInstance();
			$this->mainImage = new image( 1 );	// Special "no image yet" image
			$this->mainImageIndex = 0;
		}
	}

	private function dbSafeString( $value ) {
		return $value == null || strlen( $value ) == 0 ? 'NULL' : stripslashes( $value );
	}

	/**********************************************************************
	 *					CLASS METHODS
	 ***********************************************************************/

	/**********************************************************************
	 *					PUBLIC METHODS
	 ***********************************************************************/

	public function fn_addImage( $upload ) {
		if( !empty( $this->project_id ) ) {
			$image = new image( null, $this, $upload );
			$this->images[$image->fn_getImageID()] = $image;
			return $image;
		}
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_ITEM_NOT_INITIALIZED_DESC );
	}

	// fn_edit - sets project status to editing, returns published_on date.  If date is non-null 
	//			 project is currently published.
	public function fn_edit() {
		if( !empty( $this->project_id ) ) {
			$published_on = null;
			$sql = "SELECT published_on FROM project where project_id = {$this->project_id}";
			// We're using prepare in case someone creates a file name with bad stuff in it.
			$statement = db::getInstance()->prepare( $sql );
			if( $statement->execute() && $statement->rowCount() == 1 ) {
				extract( $statement->fetch( PDO::FETCH_ASSOC ) );
				$statement->closeCursor();
			}
			$sql = "UPDATE project SET published_on = NULL WHERE project_id = {$this->project_id}";
			$statement = db::getInstance()->prepare( $sql );
			if( $statement->execute() && strncmp( $statement->errorCode(), '00', 2 ) == 0 )
				return $published_on;
			throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_PROJECT_NOT_UPDATED_DESC, 'status', "{$this->status}" ) );
		}
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_PROJECT_NOT_INITIALIZED_DESC );
	}

	// This returns a collection of projects objects, published or otherwise.
	//	Invoke this to get a set of projects for editing.
	public static function fn_getFullProjectCollection( $curr_page, $items_per_page, &$num_pages ) {
		$items = array();
		$offset = ( $curr_page - 1 ) * $items_per_page;
		$limit= " limit $offset, $items_per_page";
		$query = "SELECT *
				  FROM project WHERE project_id > 1
				  ORDER BY display_order ASC
				  LIMIT $offset, $items_per_page";
		if( ( $resource = db::getInstance()->query( $query ) ) && $resource->rowCount() > 0 ) {
			foreach( $resource as $row )
				$items[$row['project_id']] = new project( $row['project_id'] );
			$resource->closeCursor();
		}
		return $items;
	}

	public function fn_getImages() {
		if( !empty( $this->project_id ) )
			return $this->images;
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_ITEM_NOT_INITIALIZED_DESC );
	}

	public function fn_getDescription() {
		if( !empty( $this->project_id ) )
			return $this->description;
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_ITEM_NOT_INITIALIZED_DESC );
	}

	public function fn_getDisplayOrder() {
		return $this->display_order;
	}

	public function fn_isFeatured() {
		if( !empty( $this->project_id ) )
			return $this->featured == 1;
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_ITEM_NOT_INITIALIZED_DESC );
	}

	public function fn_getMainImage() {
		if( !empty( $this->project_id ) )
			return $this->mainImage;
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_ITEM_NOT_INITIALIZED_DESC );
	}

	public function fn_getMainImageIndex() {
		if( !empty( $this->project_id ) )
			return $this->mainImageIndex;
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_ITEM_NOT_INITIALIZED_DESC );
	}

	// This returns a collection of published projects with featured projects
	//	earlier in the set.
	//	Use this to get a set of projects to show visitors.
	public static function fn_getProjectCollection( $curr_page, $items_per_page, &$num_pages ) {
		$items = array();
		$offset = ( $curr_page - 1 ) * $items_per_page;
//		$query = "SELECT SQL_CALC_FOUND_ROWS COUNT(*) AS rows
		$query = "SELECT COUNT(*) AS rows
				  FROM project
				  WHERE published_on IS NOT NULL AND project_id > 1";
		$statement = db::getInstance()->prepare( $query );
		if( $statement->execute() && $statement->rowCount() == 1 ) {
			extract( $statement->fetch( PDO::FETCH_ASSOC ) );
			$num_pages = ceil( $rows / $items_per_page );
			$statement->closeCursor();
		} else
			$num_pages = 0;
		$query = "SELECT project_id
				  FROM project
				  WHERE published_on IS NOT NULL AND project_id > 1
				  ORDER BY featured DESC, display_order ASC
				  LIMIT $offset, $items_per_page";
		if( ( $resource = db::getInstance()->query( $query ) ) && $resource->rowCount() > 0 ) {
			foreach( $resource as $row )
				$items[$row['project_id']] = new project( $row['project_id'] );
			$resource->closeCursor();
//			$query = "SELECT FOUND_ROWS() AS rows";
//			$statement = db::getInstance()->prepare( $query );
//			if( $statement->execute() && $statement->rowCount() == 1 ) {
//				extract( $statement->fetch( PDO::FETCH_ASSOC ) );
//				$num_pages = ceil( $rows / $items_per_page );
//				$statement->closeCursor();
//			} else
//				$num_pages = 0;
		}
		return $items;
	}

	public function fn_getProjectID() {
		if( !empty( $this->project_id ) )
			return $this->project_id;
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_ITEM_NOT_INITIALIZED_DESC );
	}

	public function fn_getPublishedOn() {
		if( !empty( $this->project_id ) )
			return $this->published_on;
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_ITEM_NOT_INITIALIZED_DESC );
	}

	// This returns a collection of published projects with featured projects
	//	earlier in the set.
	//	Use this to get a set of projects to show visitors.
	public static function fn_getSearchProjectCollection( $search, $curr_page, $items_per_page, &$num_pages ) {
		$items = array();
		$offset = ( $curr_page - 1 ) * $items_per_page;
//		$query = "SELECT SQL_CALC_FOUND_ROWS COUNT(*) AS rows
		$query = "SELECT COUNT(*) AS rows
				  FROM project
				  WHERE published_on IS NOT NULL AND project_id > 1 AND
						MATCH( title, description ) AGAINST( :query IN BOOLEAN MODE )
				  UNION
				  SELECT COUNT(*) AS rows
				  FROM image AS i LEFT JOIN project AS p USING( project_id )
				  WHERE p.published_on IS NOT NULL AND p.project_id > 1 AND
						MATCH( caption ) AGAINST( :query IN BOOLEAN MODE )";
		$statement = db::getInstance()->prepare( $query );
		if( $statement->execute( array( ':query' => "'$search*'" ) ) ) {
			$rows = 0;
			while( $row = $statement->fetch( PDO::FETCH_ASSOC ) )
				$rows += $row['rows'];
			$num_pages = ceil( $rows / $items_per_page );
			$statement->closeCursor();
		} else
			$num_pages = 0;
		$query = "SELECT project_id, featured, display_order
				  FROM project
				  WHERE published_on IS NOT NULL AND project_id > 1 AND
						MATCH( title, description ) AGAINST( :query IN BOOLEAN MODE )
				  UNION
				  SELECT p.project_id, p.featured, p.display_order
				  FROM image AS i LEFT JOIN project AS p USING( project_id )
				  WHERE p.published_on IS NOT NULL AND p.project_id > 1 AND
						MATCH( caption ) AGAINST( :query IN BOOLEAN MODE )
				  ORDER BY featured DESC, display_order ASC
				  LIMIT $offset, $items_per_page";
		//die( "<pre>$query</pre>\n" );
		try {
			// Using PDO prepare mechanism/execute to protect query from SQL injection attacks.
			//	This throws an exception on SQL errors, not data-related errors.
			$statement = db::getInstance()->prepare( $query );
			if( $statement->execute( array( ':query' => "'$search*'" ) ) ) {
				while( $row = $statement->fetch( PDO::FETCH_ASSOC ) )
					if( empty( $items[$row['project_id']] ) )
						$items[$row['project_id']] = new project( $row['project_id'] );
				$statement->closeCursor();
//				$query = "SELECT FOUND_ROWS() AS rows";
//				$statement = db::getInstance()->prepare( $query );
//				if( $statement->execute() && $statement->rowCount() == 1 ) {
//					extract( $statement->fetch( PDO::FETCH_ASSOC ) );
//					$num_pages = ceil( $rows / $items_per_page );
//					$statement->closeCursor();
//				} else
//					$num_pages = 0;
			}
		} catch( PDOException $e) {
			throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_PROJECT_SEARCH_ERROR_DESC, $e->getMessage() ) );
		}
		return $items;
	}

	public function fn_getTitle() {
		if( !empty( $this->project_id ) )
			return $this->title;
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_ITEM_NOT_INITIALIZED_DESC );
	}

	public function fn_publish( $date = null ) {
		if( !empty( $this->project_id ) ) {
			$date = empty( $date ) ? 'NOW()' : "'$date'";
			$sql = "UPDATE project SET published_on = $date WHERE project_id = {$this->project_id}";
			$statement = db::getInstance()->prepare( $sql );
			if( $statement->execute() && strncmp( $statement->errorCode(), '00', 2 ) == 0 )
				return;
			throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_PROJECT_NOT_UPDATED_DESC, 'published_on', "NOW()" ) );
		}
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_PROJECT_NOT_INITIALIZED_DESC );
	}

	// Removes a project and all images.  This may throw an exception if there is a problem removing any of the
	//	image files
	public function fn_remove() {
		if( !empty( $this->project_id ) ) {
			if( count( $this->images ) > 0 )
				foreach( $this->images as $image )
					$image->fn_remove();
			$sql = "DELETE from project WHERE project_id = {$this->project_id}";
			$statement = db::getInstance()->prepare( $sql );
			if( $statement->execute() && strncmp( $statement->errorCode(), '00', 2 ) == 0 )
				return;
			throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_PROJECT_NOT_REMOVED_DESC, $this->project_id ) );
		}
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_PROJECT_NOT_INITIALIZED_DESC );
	}

	public function fn_setDescription( $value ) {
		if( !empty( $this->project_id ) ) {
			$this->description = $value;
			$sql = "UPDATE project SET description = :description WHERE project_id = {$this->project_id}";
			$statement = db::getInstance()->prepare( $sql );
			if( $statement->execute( array( ':description' => $this->dbSafeString( $this->description ) ) ) && strncmp( $statement->errorCode(), '00', 2 ) == 0 )
				return;
			throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_PROJECT_NOT_UPDATED_DESC, 'description', $this->description ) );
		}
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_PROJECT_NOT_INITIALIZED_DESC );
	}

	public function fn_setDisplayOrder( $order ) {
		if( !empty( $this->project_id ) ) {
			$this->display_order = $order;
			$sql = "UPDATE project SET display_order = {$this->display_order} WHERE project_id = {$this->project_id}";
			if( db::getInstance()->exec( $sql ) == 1 )
				return;
			throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_PROJECT_NOT_UPDATED_DESC, $this->project_id ) );
		}
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_PROJECT_NOT_INITIALIZED_DESC );
	}

	public function fn_setFeatured( $value ) {
		if( !empty( $this->project_id ) ) {
			$this->featured = $value ? 1 : 0;
			$sql = "UPDATE project SET featured = {$this->dbSafeString( $this->featured )} WHERE project_id = {$this->project_id}";
			$statement = db::getInstance()->prepare( $sql );
			if( $statement->execute() && strncmp( $statement->errorCode(), '00', 2 ) == 0 )
				return;
			throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_PROJECT_NOT_UPDATED_DESC, 'description', $this->description ) );
		}
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_PROJECT_NOT_INITIALIZED_DESC );
	}

	public function fn_setDesplayOrder( $value ) {
		if( !empty( $this->project_id ) ) {
			$this->display_order = $value;
			$sql = "UPDATE project SET display_order = {$this->dbSafeString( $this->display_order )} WHERE project_id = {$this->project_id}";
			$statement = db::getInstance()->prepare( $sql );
			if( $statement->execute() && strncmp( $statement->errorCode(), '00', 2 ) == 0 )
				return;
			throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_PROJECT_NOT_UPDATED_DESC, 'description', $this->description ) );
		}
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_PROJECT_NOT_INITIALIZED_DESC );
	}

	public function fn_setTitle( $value ) {
		if( !empty( $this->project_id ) ) {
			$this->title = $value;
			$sql = "UPDATE project SET title = :title WHERE project_id = {$this->project_id}";
			$statement = db::getInstance()->prepare( $sql );
			if( $statement->execute( array( ':title' => $this->dbSafeString( $this->title ) ) ) && strncmp( $statement->errorCode(), '00', 2 ) == 0 )
				return;
			throw new Exception( __METHOD__ . ': ' . sprintf( ERR::ERR_PROJECT_NOT_UPDATED_DESC, 'title', $this->title ) );
		}
		throw new Exception( __METHOD__ . ': ' . ERR::ERR_PROJECT_NOT_INITIALIZED_DESC );
	}
}
?>