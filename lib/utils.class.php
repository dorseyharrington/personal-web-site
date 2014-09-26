<?php
class utils {

	public static function post_value( $key ) {
		return empty( $_REQUEST[$key] ) ? null : $_REQUEST[$key];
	}
	// Converts U.S.- format date string to MySQL-acceptable format:
	//	9/25/07 => 2007-09-25
	public static function fix_date( $date ) {
		if( !empty( $date ) ) {
			$temp = strtotime( $date );
			return date( 'Y-m-d', $temp );
		}
		return 0;
	}

	// Converts a MySQL-acceptable format to a U.S.-format date and time string
	//	 2007-09-25 => 9/25/07
	public static function unfix_date( $date ) {
		$temp = strtotime( $date );
		return date( 'n/j/y', $temp );
	}

	public static function get_facebook_cookie( $app_id, $application_secret ) {
		$args = array();
		parse_str( trim( $_COOKIE["fbs_$app_id"], '\\"' ), $args );
		ksort( $args );
		$payload = null;
		foreach( $args as $key => $value )
			if( $key != 'sig' )
				$payload .= "$key=$value";
		if( md5( $payload . $application_secret ) != $args['sig'] )
			return null;
		return $args;
	}

	public static function logmsg( $msg ) {
		error_log( $msg, 0 );
	}

	public static function sanitize( $var, $santype = 3 ) {
		switch ( $santype ) {
			case 1:
				return strip_tags( $var );
			case 2:
				return htmlentities(strip_tags($var),ENT_QUOTES,'UTF-8');
			case 3:
				if( !get_magic_quotes_gpc() )
					return addslashes( htmlentities( strip_tags( $var ), ENT_QUOTES, 'UTF-8' ) );
				return htmlentities(strip_tags($var),ENT_QUOTES,'UTF-8');
		}
		return $var;
	}

	public static function set_file_permissions( $path ) {
		$oldmask = umask( 0 );
		$success = @chmod( $path, 0666 );
		umask( $oldmask );
		return $success;
	}

	/**********************************************************************
	* Given the image's dimensions and original file name, we return a URL
	*	to an image of the correct dimensions.  This version (10/10/10) uses
	*	Image Magick rather than PHP's GD library because the latter was not
	*	able to process too many images.  Even one is too many.
	* Added by Dorsey 09/03/10
	* @params - $dim - Desired image dimensions WxH; ex. 305x230.  This is the format used by the legacy code
	* @params - $image - image object
	* $params - $qaulity - desired quality, affects compression.  Image Magick recommends 92 for jpg
	* @returns - URL of the image in the requested size
	***********************************************************************/
	public static function getImageURL( $sizename, $image, $quality = 92 ) {
		// A discrete set of image sizes is mapped to named values; anything else will be rejected so that a coding error somewhere won't
		//	create image widows that take up space.  Note that we're always creating JPEG images, and always from the original so as to
		//	minimize image loss when converting to jpg.
		//	We're defining standard sizes that match the 1.5::1 width::height ratio of 35mm film, and a set that matches the 1.33::1 ratio
		//	used by digital cameras that matches computer monitors.  We've also defined a "square" set in case that makes sense.
		//	For this work, I think that most of the images will originate with professional digital cameras using the 35mm ratio, but the
		//	aspect ratio chosen is defined in the configuration.
		//	Note that we've added one "square" exception to these rules.
		$imagesets = array( 'digital' => array( 'large' => '600x451', 'medium' => '200x150', 'square' => '120x120',
												'small' => '120x90', 'thumbnail' => '90x68', 'smallthumbnail' => '60x45' ),
							'35mm' => array( 'large' => '600x400', 'medium' => '200x133', 'square' => '120x120',
											 'small' => '120x80', 'thumbnail' => '90x60', 'smallthumbnail' => '60x40' ),
							'square' => array( 'large' => '600x600', 'medium' => '200x200', 'square' => '120x120',
											   'small' => '120x120', 'thumbnail' => '90x90', 'smallthumbnail' => '60x60' ) );
		$config = config::getInstance();
		$imageset = $imagesets[ $config->config_values['images']['aspect_ratio'] ];
		//die( "Looking for $sizename in <pre>".print_r( $imageset, true ).'</pre>' );
		if( isset( $imageset[$sizename] ) ) {
			$dim = $imageset[$sizename];
			$project_id = $image->fn_getProjectID();
			$config = config::getInstance();
			$PROJECT_URL = "{$config->config_values['images']['PROJECT_URL']}/$project_id";		// Image URL for browser
			$PROJECT_PATH = "{$config->config_values['images']['PROJECT_PATH']}/$project_id";	// Image path for local processing
			// Pick out the original file's base name and use that to see if we already have the requested version.
			//return __FUNCTION__ . ": Invoked with dim = $dim, filenm = $filenm<br/>";
			$info = pathinfo( $image->fn_getFileName() );
			// Isolate the file name minus the extension so we can build up the various names we need.
			$basename = basename( $info['basename'], ".{$info['extension']}" );	// Real basename doesn't include the extension
			$requested = "{$basename}_{$dim}.jpg";								// Requested version
			//echo __FUNCTION__ . ": Looking for $requested";
			// If the file we want doesn't exist, create the full image set
			if( !file_exists( "$PROJECT_PATH/$requested"  ) ) {
				$orig = $PROJECT_PATH.'/'.$image->fn_getOriFileName();	// Always start with the original image file in the original format
				foreach( $imageset as $label => $size ) {
					//echo __FNCTION__ . ": working on $ext";
					$filename = "$PROJECT_PATH/{$basename}_$size.jpg";	// This size
					$output = array();
					$retStat = null;
					// This is remarkably simple compared to PHP's GD library - IM does all the heavy lifting and
					//	produces really nice images (and it actually works on all image types!).  Invoking a separate
					//	process isn't great for performance, but this loop only needs to be executed the first time an image of a given size
					//	is requested.  After that, we'll return the pre-created image.
					$magick = $config->config_values['images']['imagemagickpath'];
					$command = "$magick '$orig' -resize $size -quality $quality '$filename' 2>&1";
					//echo "$command\n";
					exec( $command, $output, $retStat );
					if( !empty( $output ) )
						echo "$command\n" . print_r( $output, true ) . "\n";
					else
						utils::set_file_permissions( $filename );	// Changes from the default 644 to 666 so we can overwrite it later
				}
			}
			return "$PROJECT_URL/$requested";
		}
		return __PUBLIC_PATH.'/images/no_image.jpg';
	}

	public static function displayDescription( $desc ) {
		// Processes \r\n's first so they aren't converted twice.
		$order = array( "\r\n", "\n", "\r" );
		$replace = '<br>';
		$newstr = str_replace( $order, $replace, $desc );
		$order = array( "\t", "  " );
		$replace = '&nbsp;&nbsp;';
		return str_replace( $order, $replace, $newstr );
	}

	public static function displayImageSet( $collection ) {
		$cnt = count( $collection );	// Array of image objects
		$config = config::getInstance();
		$ret = <<<END
<br clear="all">
<br clear="all">
<div id="images">
	<div id="response"> </div>
	<ul id='details'>
END;
		if( $cnt > 0 ) {
			foreach( $collection as $image ) {
				$PROJECT_URL = "{$config->config_values['images']['PROJECT_URL']}/{$image->fn_getProjectID()}";
				$public_path = __PUBLIC_PATH;
				$caption = $image->fn_getCaption() == null ? 'Enter Short Description...' : $image->fn_getCaption ();
				$imageURL = self::getImageURL( 'thumbnail', $image );
				$mainImage = $image->fn_isMainImage() ? "checked='checked'" : null;
				$imageID = $image->fn_getImageID();
				$ret .= <<<END
		<li id="arrayorder_$imageID" style='background-color:rgb(239,247,198); margin-bottom: 10px; padding: 10px;'>
			<div style='width: 100%; margin-bottom: 5px;'>
				<a class='deleteImage' href="$imageID">
					<img style='float: right; vertical-align: top;' src='$public_path/images/delete.gif' border=0 alt='Delete this image'>
				</a>
				<img src='$public_path/images/up_down_arrow.png' height='30px' style='vertical-align: top; float: left;'>
				<span style='width: 150px; vertical-align: top; font-size: 12px; font-weight: bold; display: inline;'>{$image->fn_getFileName()}</span>
			</div><br>
			<div>
				<a class='enlarge' style='text-align: center;' href='$PROJECT_URL/{$image->fn_getOriFileName()}'>
					<img src='$imageURL' border='0' style='margin:0;padding:0' alt='{$image->fn_getCaption()}'>
				</a>
				<span title="$imageID" style='width: 150px; font-size: 12px; vertical-align: top'>
					<input class='save' name='Main' type='radio' value='1' $mainImage>&nbsp;Main Image
				</span>
			</div>
			<span class='bg220x70' title='$imageID'>
				<textarea class='save short_description' name='Caption' size=25>$caption</textarea>
			</span>
			<div class="clear"></div>
		</li>
END;
				++$n_count;
			}
		}
		$ret .= <<<END
	</ul>
</div>
END;
		return $ret;
	}

	public static function displayProjectSet( $collection ) {
		$cnt = count( $collection );	// Array of project objects
		$config = config::getInstance();
		$ret = <<<END
<br clear="all">
<br clear="all">
<div id="projects">
	<div id="response"> </div>
	<ul id='details'>
END;
		if( $cnt > 0 ) {
			foreach( $collection as $project ) {
				$PROJECT_URL = "{$config->config_values['images']['PROJECT_URL']}/".$project->fn_getProjectID();
				$public_path = __PUBLIC_PATH;
				$title = $project->fn_getTitle() == null ? 'Enter Short Description...' : $project->fn_getTitle ();
				$mainImage = $project->fn_getMainImage();
				$imageURL = self::getImageURL( 'thumbnail', $mainImage );
				$featured = $project->fn_isFeatured() ? "checked='checked'" : null;
				$projectID = $project->fn_getProjectID();
				$ret .= <<<END
		<li id="arrayorder_$projectID" style='background-color:rgb(239,247,198); margin-bottom: 10px; padding: 10px;'>
			<span style='width: 100%; margin-bottom: 5px; vertical-align: middle;'>
				<img src='$public_path/images/up_down_arrow.png' height='30px' style='vertical-align: top; float: left;' title='Drag to move' alt='Drag to move'>
				<a class='deleteProject' href="$projectID">
					<img style='float: right; vertical-align: top;' src='$public_path/images/delete.gif' border=0 title='Delete' alt='Delete this project'>
				</a>
				<span class='linklabelright'><a href='$public_path/account/edit/$projectID'>Edit</a></span>
			</span><br>
			<div>
				<h1 style='text-align: left'>$title</h1>
				<a class='enlarge' style='text-align: center;' href='$PROJECT_URL/{$mainImage->fn_getOriFileName()}'>
					<img src='$imageURL' border='0' style='margin:0;padding:0' alt='{$project->fn_getTitle()}'>
				</a>
				<span class="in520x24" style='vertical-align: top' title="$projectID">
					Featured Project: <input style='vertical-align: middle;' class="save" type="checkbox" name="Featured" id="featured" $featured value="1" >
				</span>
			<div>
			<div class="clear"></div>
		</li>
END;
				++$n_count;
			}
		}
		$ret .= <<<END
	</ul>
</div>
END;
		return $ret;
	}

	// Generate the project gallery page menu using the current page and total number of pages.
	//	The current page is highlighted and there is no link.
	public static function makePageNumberMenu( $curr_page = 1, $num_pages = 1 ) {
		$menu = null;
		if( $num_pages > 0 ) {
			$gallerylink = __PUBLIC_PATH.'/project/gallery/';
			$menu = "<div id='pagemenu'>\n";
			$menu .= "<ul>\n";
			if( $curr_page > 1 )
				$menu .= "<li><a href='$gallerylink' title='first page'><<</a></li>";
			for( $i = 1; $i <= $num_pages; ++$i ) {
				$link = "<a href='$gallerylink$i'>$i</a>";
				$current = null;
				if( $i == $curr_page ) {
					$current = "class='current_page'";
					$link = $i;
				}
				$menu .= "<li $current>$link</li>\n";
			}
			if( $curr_page < $num_pages )
				$menu .= "<li><a href='$gallerylink/$num_pages' title='last page'>>></a></li>";
			else
				$menu .= "<li>&nbsp;&nbsp;</li>";
			$menu .= "</ul>\n";
			$menu .= "</div>\n";
		}
		return $menu;
	}
}
?>