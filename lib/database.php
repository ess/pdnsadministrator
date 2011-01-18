<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2011 Roger Libiez http://www.iguanadons.net
 *
 * Based on Quicksilver Forums
 * Copyright (c) 2005-2011 The Quicksilver Forums Development Team
 *  http://code.google.com/p/quicksilverforums/
 * 
 * Based on MercuryBoard
 * Copyright (c) 2001-2005 The Mercury Development Team
 *  http://www.mercuryboard.com/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 **/

if (!defined('PDNSADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

/**
 * Generic Database Inteface
 *
 * @since 1.1.9
 **/
class database
{
	var $connection = false; // Connection link identifier @var resource
	var $querytime  = 0;     // Time spent executing queries @var int
	var $querycount = 0;     // Number of executed queries @var int
	var $get;                // Alias for $_GET @var array
	var $post;               // Alias for $_POST @var array
	var $host;               // Database Server @var string
	var $user;               // Database User Name @var string
	var $pass;               // Database Password @var string
	var $db;                 // Database Name @var string
	var $port = 3306;        // Database Port @var int

	/**
	 * Constructor; sets up variables and connection
	 *
	 * @param string $db_host Server
	 * @param string $db_user User Name
	 * @param string $db_pass Password
	 * @param string $db_name Database Name
	 * @param int $db_port Database Port
	 * @param string $db_socket Database Socket
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return void
	 **/
	function database($db_name, $db_user, $db_pass, $db_host, $db_port, $db_socket)
	{
		$this->get    = $_GET;
		$this->post   = $_POST;
		$this->db     = $db_name;
		$this->user   = $db_user;
		$this->pass   = $db_pass;
		$this->host   = $db_host;
		$this->port   = $db_port;
	}

	/**
	 * Retrieves debug information about a query
	 *
	 * @param string $query Query to debug
	 * @access protected
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return void
	 **/
	function debug($query)
	{
		$this->querylog[]   = $query;

		$mtime              = explode(' ', microtime());
		$starttime          = $mtime[1] + $mtime[0];

		$data = $this->get_debug_info($query);

		$mtime              = explode(' ', microtime());
		$querytime          = ($mtime[1] + $mtime[0]) - $starttime;
		$this->querytime   += $querytime;

		$data['querytime']  = $querytime;
		$data['query']      = $query;
		$this->querydebug[] = $data;
	}

	/**
	 * Runs an EXPLAIN or similar on a query
	 * Interface version
	 *
	 * @param string $query Query to debug
	 * @access protected
	 * @return void
	 **/
	function get_debug_info($query)
	{
		return array();
	}

	/**
	 * Retrieves the insert ID of the last executed query
	 * Interface version
	 *
	 * @param string $table Table name - unused
	 * @return int Insert ID
	 **/
	function insert_id($table)
	{
		return null;
	}

	/**
	 * Executes a query
	 * Interface version
	 *
	 * @param string $query SQL query
	 * @return resource Executed query
	 **/
	function dbquery($query)
	{
		return null;
	}

	/**
	 * Executes a query and fetches it into an array
	 *
	 * @param string $query SQL query
	 * @param string $args Data to pass into query as escaped strings
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return array Fetched rows
	 **/
	function fetch($query)
	{
		$args = array();
		if (is_array($query)) {
			$args = $query; // only use arg 1
		} else {
			$args  = func_get_args();
		}

		return $this->nqfetch($this->dbquery($args));
	}

	/**
	 * Fetches an executed query into an array
	 * Interface version
	 *
	 * @param resource $query Executed SQL query
	 * @return array Fetched rows
	 **/
	function nqfetch($query)
	{
		return array();
	}

	/**
	 * Gets the number of rows retrieved by a SELECT
	 * Interface version
	 *
	 * @param resource $query Executed SQL query
	 * @return int Number of retrieved rows
	 **/
	function num_rows($query)
	{
		return 0;
	}

	/**
	 * Gets the number of rows affected by the last executed UPDATE
	 * Interface version
	 *
	 * @return int Number of affected rows
	 **/
	function aff_rows()
	{
		return 0;
	}

	/**
	 * Returns a escaped string
	 *
	 * @since 1.3.0
	 * @return string A string with the quotes and other charaters escaped
	 * @param string $string The string to escape
	 **/
	function escape($string)
	{
		return addslashes($string);
	}

	/**
	 * Puts the data into the query using the escape function
	 *
	 * @param string $query SQL query
	 * @param string $args Data to pass into query as escaped strings
	 * @return string Formatted query
	 **/
	function _format_query($query)
	{
		// Format the query string
		$args = array();
		if (is_array($query)) {
			$args = $query; // only use arg 1
		} else {
			$args  = func_get_args();
		}

		$query = array_shift($args);

		for($i=0; $i<count($args); $i++) {
			$args[$i] = $this->escape($args[$i]);
		}
		array_unshift($args,$query);

		return call_user_func_array('sprintf',$args);
	}
}
?>