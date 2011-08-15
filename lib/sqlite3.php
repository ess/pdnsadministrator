<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2011 Roger Libiez http://www.iguanadons.net
 *
 * SQLite3 adaptation by Marco Wessel <marco@mediamatic.nl>
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

/**
 * SQLite3 Abstraction Class
 *
 * @author Marco Wessel <marco@mediamatic.nl>
 * @since 1.2
 **/
class db_sqlite3 extends SQLite3
{
	var $connection = false;
	var $db = null;

	/**
	 * Constructor; sets up variables and connection
	 *
	 * @param string $db_file File name
	 * @param string $db_user User Name (not used)
	 * @param string $db_pass Password (not used)
	 * @param string $db_name Database Name (not used)
	 * @param int $db_port Database Port (not used)
	 * @param string $db_socket Database Socket (not used)
	 * @author Marco Wessel <marco@mediamatic.nl>
	 * @since Beta 2.0
	 * @return void
	 */
	public function __construct( $db_file )
	{
		global $set;

		$db_file = $set['include_path'] . '/sqlite3db/' . $db_file;

		try {
			$this->connection = $this->open($db_file);
			$this->connection = true;
		} catch(Exception $e) {}
	}

	public function close()
	{
		if( $this->connection ) {
			$this->close();
		}
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
	private function get_debug_info($query)
	{
		$data = array();
		if (substr(trim(strtoupper($query)), 0, 6) == 'SELECT') {
			$result = $this->query("EXPLAIN $query") or error(PDNSADMIN_QUERY_ERROR, $this->lastErrorMsg(), $query, $this->error_last());
			$data = $result->fetchArray(SQLITE_ASSOC);
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
	public function insert_id($table)
	{
		return $this->lastInsertRowid();
	}

	/**
	 * Puts the data into the query using the escape function
	 *
	 * @param string $query SQL query
	 * @param string $args Data to pass into query as escaped strings
	 * @return string Formatted query
	 **/
	private function format_query($query)
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

	/**
	 * Executes a query
	 *
	 * @param string $query SQL query
	 * @param string $args Data to pass into query as escaped strings
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return resource Executed query
	 **/
	public function dbquery($query)
	{
		$args = array();
		if (is_array($query)) {
			$args = $query; // only use arg 1
		} else {
			$args  = func_get_args();
		}

		$query = $this->format_query($args);
		
		$this->querycount++;

		if (isset($this->get['debug'])) {
			$this->debug($query);
		}
		$result = $this->query($query) or error(PDNSADMIN_QUERY_ERROR, $this->lastErrorMsg(), $query, $this->error_last());
		return $result;
	}

	/**
	 * Executes a query on a single row
	 *
	 * @param string $query SQL query
	 * @param string $args Data to pass into query as escaped strings
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return resource Executed query
	 **/
	public function fetch($query)
	{
		$args = array();
		if (is_array($query)) {
			$args = $query; // only use arg 1
		} else {
			$args  = func_get_args();
		}

		$query = $this->format_query($args);
		
		$this->querycount++;

		if (isset($this->get['debug'])) {
			$this->debug($query);
		}
		$result = $this->querySingle($query) or error(PDNSADMIN_QUERY_ERROR, $this->lastErrorMsg(), $query, $this->error_last());
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
	public function nqfetch($query)
	{
		return $query->fetchArray(SQLITE_ASSOC);
	}

	public function nqfetch_row($query)
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
	public function num_rows($query)
	{
		return $query->numRows();
	}

	/**
	 * Returns a escaped string
	 *
	 * @author Matthew Lawrence <matt@quicksilverforums.com>
	 * @since 1.3.0
	 * @return string A string with the quotes and other charaters escaped
	 * @param string $string The string to escape
	 **/
	public function escape($string)
	{
		return $this->escapeString($string);
	}

	public function invalid($errmsg)
	{
		if (stristr($errmsg, 'sqlite_fetch_array(): supplied argument is not a valid SQLite3 result resource'))
			return true;
		
		return false;
	}

	public function error_last()
	{
		return $this->lastErrorCode();
	}

	/**
	 * Returns an array containing the optimize results
	 *
	 * @author Roger Libiez [Samson] http://www.iguanadons.net
	 * @since 1.2
	 * @return array An array containing the results of the optimize operation
	 * @param string $tables The list of tables to optimize
	 **/
	public function optimize($tables)
	{
		$result = $this->dbquery( 'ANALYZE ' . $tables );

		return $result;
	}

	/**
	 * Returns an array containing the repair results
	 *
	 * @author Roger Libiez [Samson] http://www.iguanadons.net
	 * @since 1.2
	 * @return array An array containing the results of the repair operation
	 * @param string $tables The list of tables to repair, ignored as SQLite does not accept tables in the argument
	 **/
	public function repair($tables)
	{
		$result = $this->dbquery( 'VACUUM' );

		return $result;
	}
}
?>