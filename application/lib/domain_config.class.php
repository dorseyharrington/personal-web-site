<?php

/**
 *
 * @Class domain
 *
 * @Purpose: Governs access to single of multiple configurations
 *
 * @Author: Kevin Waterson
 *
 * @copyright PHPRO.ORG (2009)
 *
 * @see config.class.php
 *
 */

//namespace sevenkevins;

class domain_config
{
	/*
	 * @var array $values; 
	 */
	public static $values = array();

	/*
	* @var object $instance
	*/
	private static $instance = null;

	/**
	 *
	 * @the constructor is set to private so
	 * @so nobody can create a new instance using new
	 *
	 */
	private function __construct()
	{
	}

	/**
	 *
	 * Return Config instance or create intitial instance
	 *
	 * @access public
	 *
	 * @return object
	 *
	 */
	public static function getInstance()
	{
		if(is_null(self::$instance))
		{
			self::$instance = new domain_config;
			self::setConfigOptions();
		}
		return self::$instance;
	}


	/**
	*
	* Get the config options from the db
	*
	* @access	private
	* @return	array
	*
	*/
	private function setConfigOptions()
	{
		$host = strtolower( $_SERVER['HTTP_HOST'] );
		// fetch config data from the db
		$db = db::getInstance();
		$sql = "SELECT * FROM sevenkevins_domain_config JOIN sevenkevins_domains 
			ON
				sevenkevins_domain_config.domain_id=sevenkevins_domains.domain_id
			WHERE
				domain_name=:host";
		$stmt=$db->prepare( $sql );
		$stmt->bindParam(':host', $host);
		$stmt->execute();
		self::$values = $stmt->fetchAll( \PDO::FETCH_ASSOC, \PDO::PARAM_STR );

	}

	/**
	*
	* Set to private so the class cannot be cloned
	*
	* @access	private
	*
	*/
	private function __clone()
	{
	}

} // end of domain class

?>
