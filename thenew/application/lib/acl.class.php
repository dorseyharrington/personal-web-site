<?php

error_reporting( E_ALL );

class Acl
{
	/**
	* @var	array	$roles
	* @access	protected
	*/
	protected $roles = array();

	/**
	* @var	array	$resources
	* @access	protected
	*/
	protected $resources = array();

	/**
	*
	* Add a role
	*
	* @access	public
	* @param	string	$name
	* @return	object	Instance of Acl_Role
	*
	*/
	public function addRole( $name )
	{
		$role = new Acl_Role;
		$role->name = $name;
		$this->roles[] = $role;
			
		// allow for chaining
		return $role;
	}

	/**
	*
	* Add a resource
	*
	* @access	public
	* @param	string	$name
	* @param	array	$allowed
	* @return	object	Instance of Acl_Resource
	*
	*/
	public function addResource( $name, array $allowed )
	{
		$resource = new Acl_Resource;
		$resource->name = $name;
		$resource->allowed = $allowed;
		$this->resources[] = $resource;
		// allow chaining
		return $resource;
	}

	/**
	*
	* Allowed
	*
	* @access	public
	* @param	object	$role
	* @param	string	$resource
	* @return	bool
	*
	*/
	public function isAllowed( $role, $resource )
	{
		return in_array( $role->name, $resource->allowed );
	}


	/**
	*
	* Get a resource
	*
	* @access	public
	* @param	string	$name
	* @return	resource
	*
	*/
	public function getResource( $name )
	{
		$resource = null;
		
		foreach ($this->resources as $r)
		{
			if ( $r->getName() == $name )
			{
				$resource = $r;
				break;
			}
		}
		return $resource;
	}

	/**
	*
	* @get a role
	*
	* @access	public
	* @param	string	$name
	* @return	role
	*
	*/
	public function getRole( $name )
	{
		foreach ($this->roles as $r)
		{
			if ($r->getName() == $name)
			{
				$role = $r;
				break;
			}
		}
		var_dump($role); die();
		return $role;
	}
	
	/* add more methods here */
}

class Acl_Role
{
	/**
	* Constructor, duh
	*
	* @access	public
	* @param	string	$name
	*
	*/
	public function __construct( )
	{
	}

	/**
	* @settor
	*
	* @access	public
	* @param	string	$name
	* @param	string	$value
	*
	*/
	public function __set( $name, $value )
	{
		switch( $name )
		{
			case 'name':
			case 'permissions':
			$this->$name = $value;
			break;

			default:
			throw new Exception( "Unable to set $name." );
		}
	}
	

	/**
	* Gettor
	*
	* @access	public
	* @param	string	$name
	* @return	string
	*
	*/	
	public function __get( $name )
	{
		switch( $name )
		{
			case 'name':
			case 'permissions':
			return $this->name;

			default:
			throw new Exception( "Unable to get $name" );
		}
	}
} // end of class



class Acl_Resource
{
	/**
	*
	* Constructor
	*
	*/
	public function __construct( )
	{
	}

	/**
	*
	* @settor
	*
	* @access	public
	* @param	string	$name
	* @param	string	$value
	*
	*/
	public function __set( $name, $value )
	{
		switch( $name )
		{
			case 'name':
			case 'allowed':
			$this->$name = $value;
			break;

			default:
			throw new Exception( "Unable to set $name" );
		}
	}

	/**
	*
	* Gettor
	*
	* @access	public
	* @param	string 	$name
	*
	*/
	public function __get( $name )
	{
		switch( $name )
		{
			case 'name':
			case 'allowed':
			return $this->$name;

			default:
			throw new Exception( "Unable to get $name" );
		}
	}
} // end of class

?>
