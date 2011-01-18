<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2011 Roger Libiez http://www.iguanadons.net
 *
 * Based on Quicksilver Forums
 * Copyright (c) 2005-2011 The Quicksilver Forums Development Team
 *  http://code.google.com/p/quicksilverforums/
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

require_once $set['include_path'] . '/lib/database.php';

/**
 * MySQLi Abstraction Class
 *
 * @author Jason Warner <jason@mercuryboard.com>
 * @author Roger Libiez [Samson] http://www.iguanadons.net
 * @since 1.2
 **/
class db_mysqli extends database
{
	var $socket;             // Database Socket @var string

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
	function __construct($db_name, $db_user, $db_pass, $db_host, $db_port = 3306, $db_socket = '')
	{
		parent::database($db_name, $db_user, $db_pass, $db_host, $db_port, $db_socket);
		$this->socket = $db_socket;

		$this->connection = new mysqli( $db_host, $db_user, $db_pass, $db_name /* , $db_port, $db_socket */ );

		if (!$this->connection->select_db( $db_name ))
			$this->connection = false;

		$this->db = $this->connection;
	}

	function close()
	{
		if( $this->connection )
			$this->db->close();
	}

	/**
	 * Runs an EXPLAIN or similar on a query
	 *
	 * @param string $query Query to debug
	 * @access protected
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return void
	 **/
	function get_debug_info($query)
	{
		$data = array();
		if (substr(trim(strtoupper($query)), 0, 6) == 'SELECT') {
			$result = $this->db->query("EXPLAIN $query") or error(PDNSADMIN_QUERY_ERROR, $this->db->error, $query, $this->db->errno);
			$data = $result->fetch_array(MYSQLI_ASSOC);
		}
		return $data;
	}

	/**
	 * Retrieves the insert ID of the last executed query
	 *
	 * @param string $table Table name - unused
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.1
	 * @return int Insert ID
	 **/
	function insert_id($table)
	{
		return $this->db->insert_id;
	}

	/**
	 * Executes a query
	 *
	 * @param string $query SQL query
	 * @param string $args Data to pass into query as escaped strings
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return resource Executed query
	 **/
	function dbquery($query)
	{
		$args = array();
		if (is_array($query)) {
			$args = $query; // only use arg 1
		} else {
			$args  = func_get_args();
		}

		$query = $this->_format_query($args);
		
		$this->querycount++;

		if (isset($this->get['debug'])) {
			$this->debug($query);
		}
		$result = $this->db->query($query) or error(PDNSADMIN_QUERY_ERROR, $this->db->error, $query, $this->db->errno);
		return $result;
	}

	/**
	 * Fetches an executed query into an array
	 *
	 * @param resource $query Executed SQL query
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return array Fetched rows
	 **/
	function nqfetch($query)
	{
		return $query->fetch_array(MYSQLI_ASSOC);
	}

	function nqfetch_row($query)
	{
		return $query->fetch_row();
	}

	/**
	 * Gets the number of rows retrieved by a SELECT
	 *
	 * @param resource $query Executed SQL query
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return int Number of retrieved rows
	 **/
	function num_rows($query)
	{
		return $query->num_rows;
	}

	/**
	 * Returns a escaped string
	 *
	 * @author Matthew Lawrence <matt@quicksilverforums.com>
	 * @since 1.3.0
	 * @return string A string with the quotes and other charaters escaped
	 * @param string $string The string to escape
	 **/
	function escape($string)
	{
		return $this->db->real_escape_string($string);
	}

	function invalid($errmsg)
	{
		if (stristr($errmsg, 'mysqli_fetch_array(): supplied argument is not a valid MySQLi result resource'))
			return true;
		
		return false;
	}

	function error_last()
	{
		return $this->db->error;
	}

	/**
	 * Returns an array containing the optimize results
	 *
	 * @author Roger Libiez [Samson] http://www.iguanadons.net
	 * @since 1.2
	 * @return array An array containing the results of the optimize operation
	 * @param string $tables The list of tables to optimize
	 **/
	function optimize($tables)
	{
		$result = $this->db->dbquery( 'OPTIMIZE TABLE ' . $tables );

		return $result;
	}

	/**
	 * Returns an array containing the repair results
	 *
	 * @author Roger Libiez [Samson] http://www.iguanadons.net
	 * @since 1.2
	 * @return array An array containing the results of the repair operation
	 * @param string $tables The list of tables to repair
	 **/
	function repair($tables)
	{
		$result = $this->db->dbquery( 'REPAIR TABLE ' . $tables );

		return $result;
	}
}
?>