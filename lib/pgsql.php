<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2010 Roger Libiez http://www.iguanadons.net
 *
 * Based on Quicksilver Forums
 * Copyright (c) 2005 The Quicksilver Forums Development Team
 *  http://www.quicksilverforums.com/
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
 * PostgreSQL Abstraction Class
 *
 * @author Matthew Lawrence <matt@quicksilverforums.co.uk>
 * @since 1.1.9
 **/
class db_pgsql extends database
{
	/**
	 * Constructor; sets up variables and connection
	 *
	 * @param string $db_host Server
	 * @param string $db_user User Name
	 * @param string $db_pass Password
	 * @param string $db_name Database Name
	 * @param int $db_port Database Port
	 * @param string $db_socket unused
	 * @author Matthew Lawrence <matt@quicksilverforums.co.uk>
	 * @since 1.1.9
	 * @return void
	 **/
	function db_pgsql($db_host, $db_user, $db_pass, $db_name, $db_port = 5432, $db_socket)
	{
		parent::database($db_host, $db_user, $db_pass, $db_name, $db_port, $db_socket);
		$pg_connstr = "host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_pass";

		$this->connection = @pg_connect($pg_connstr);
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
			if (!pg_send_query($this->connection, "EXPLAIN $query"))
			{
				$err = pg_get_result($this->connection);
				error(PDNSADMIN_QUERY_ERROR, pg_result_error($err), $query, 0);
			} else {
				$result = pg_get_result($this->connection);
	
				if (false === $this->last)
				{
					error(PDNSADMIN_QUERY_ERROR, pg_result_error($err), $query, 0);
				}
			}
			$data = pg_fetch_array($result);
		}
		return $data;
	}


	/**
	 * Retrieves the insert ID of the last executed query
	 *
	 * @param string $table Table name
	 * @author Geoffrey Dunn <geoff@wwarmage.com>
	 * @since 1.1.9
	 * @return int Insert ID
	 **/
	function insert_id($table)
	{
		$results = $this->fetch("select currval('{$table}_seq') last_id");
		return $results['last_id'];
	}

	/**
	 * Executes a query
	 *
	 * @param string $query SQL query
	 * @param string $args Data to pass into query as escaped strings
	 * @author Matthew Lawrence <matt@quicksilverforums.co.uk>
	 * @since 1.1.9
	 * @return resource Executed query
	 **/
	function query($query)
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

		if (!pg_send_query($this->connection, $query))
		{
			$err = pg_get_result($this->connection);
			error(PDNSADMIN_QUERY_ERROR, pg_result_error($err), $query, 0);
		} else {
			$this->last = pg_get_result($this->connection);

			if (false === $this->last)
			{
				error(PDNSADMIN_QUERY_ERROR, pg_result_error($err), $query, 0);
			}
		}
		return $this->last;
	}


	/**
	 * Fetches an executed query into an array
	 *
	 * @param resource $query Executed SQL query
	 * @author Matthew Lawrence <matt@quicksilverforums.co.uk>
	 * @since 1.1.9
	 * @return array Fetched rows
	 **/
	function nqfetch($query)
	{
		return pg_fetch_array($query);
	}

	/**
	 * Gets the number of rows retrieved by a SELECT
	 *
	 * @param resource $query Executed SQL query
	 * @author Matthew Lawrence <matt@quicksilverforums.co.uk>
	 * @since 1.1.9
	 * @return int Number of retrieved rows
	 **/
	function num_rows($query)
	{
		return pg_num_rows($query);
	}


	/**
	 * Gets the number of rows affected by the last executed UPDATE
	 *
	 * @author Matthew Lawrence <matt@quicksilverforums.co.uk>
	 * @since 1.1.9
	 * @return int Number of affected rows
	 **/
	function aff_rows()
	{
		return pg_affected_rows($this->last);
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
		return pg_escape_string($string);
	}
}
?>