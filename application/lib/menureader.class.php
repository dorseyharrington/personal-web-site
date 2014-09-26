<?php

/**
 *
 * A class to create a menu from a directory
 *
 */

//namespace sevenkevins;

class menuReader extends DirectoryIterator {

	/**
	*
	* Constructor, passes the file path to the parent DirectoryIterator
	*
	* @access	public
	* @param	string	$path	The file path
	*
	*/
	function __construct($path)
	{
		 parent::__construct($path);
	}

	/**
	*
	* Settor
	*
	* @access	public
	* @param	string	$name	The name of the variable
	* @param	string	$value	The value to set
	*
	*/
	public function __set( $name, $value )
	{
		switch( $name )
		{
			case 'css_class':
			case 'active':
			$this->$name = $value;
			break;

			default:
			throw new InvalidArgumentException( "Unable to set $name" );
		}
	}

	/**
	*
	* Gettor, called when a called value is not defined
	*
	* @access	public
	* @param	string	$name	The name of the variable called
	* @return	mixed
	*
	*/
	public function __get( $name )
	{
		switch( $name )
		{
			case 'css_class':
			return 'menu';

			case 'active':
			return 'home';
			break;

			default:
			throw new InvalidArgumentException ( "Unable to get $name" );
		}
	}

	/**
	*
	* get the current file/dir name
	*
	* @access	public
	* @return	string
	*
	*/
	public function current()
	{
		$active = (parent::getFileName() == $this->active) ? ' class="active"' : '';
		return '<li'.$active.'><a href="'.__PUBLIC_PATH.'/'.parent::getFileName().'">' . parent::getFileName() . "</a></li>";
	}

	/**
	*
	* Define valid entries
	*
	* @access	public
	* @return	bool
	*
	*/
	public function valid()
	{
		// directory names to exclude from the menu
		$excluded = array( 'index', 'error404', 'base' );

		if ( parent::valid() )
		{
			if ( parent::isFile() || parent::isDot() || in_array( parent::current(), $excluded ) )
			{
				parent::next();
				return $this->valid();
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	*
	* Rewind the iterator
	*
	* @access	public
	*
	*/
	public function rewind()
	{
		parent::rewind();
	}

	/**
	*
	* Return a string representation of the menu
	*
	* @access	public
	* @return	string
	*
	*/
	public function __toString()
	{
		$ret = '<ul class="'.$this->css_class.'">'."\n";
//		$ret .= '<li><a href="'.__PUBLIC_PATH.'">'.lang::__HOME.'</a></li>'."\n";
		while( $this->valid() )
		{
			$ret .= $this->current() . "\n";
			$this->next();
		}
		$ret .= '</ul>'."\n";
		return $ret;
	}
} // end of class

?>
