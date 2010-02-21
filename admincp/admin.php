<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2010 Roger Libiez http://www.iguanadons.net
 *
 * Based on Quicksilver Forums
 * Copyright (c) 2005 The Quicksilver Forums Development Team
 *  http://www.quicksilverforums.com/
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

if (!defined('PDNSADMIN') || !defined('PDNS_ADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

require_once $set['include_path'] . '/global.php';

/**
 * Miscellaneous functions specific to the admin center
 *
 * @author Jason Warner <jason@mercuryboard.com>
 * @since Beta 2.1
 */
class admin extends pdnsadmin
{
	/**
	 * Post constructor initaliser. Take care of admin specific stuff
	 *
	 * @param bool $admin Set to true if we need to setup admin templates
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.2
	 **/
	function init($admin = true)
	{
		if (@file_exists('../install/index.php') ) {
			exit('<h1>' . $this->lang->admin_cp_warning . '</h1>');
		}

		parent::init($admin);
		
		if (!$this->perms->auth('is_admin')) {
			exit('<h1>' . $this->lang->admin_cp_denied . '</h1>');
		}
	}

	/**
	 * Set to admin tables
	 *
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.2
	 **/
	function set_table()
	{
		$this->table  = eval($this->template('ADMIN_TABLE'));
		$this->etable = eval($this->template('ADMIN_ETABLE'));
	}
	
	/**
	 * Formats a message (admin cp version)
	 *
	 * @param string $title Title of the message
	 * @param string $message Text of the message
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.1
	 * @return string HTML
	 */
	function message($title, $message, $link_text = null, $link = null, $redirect = null)
	{
		if ($link_text) {
			$message .= "<br /><br /><a href=\"$link\">$link_text</a>";
		}

		if ($redirect) {
			@header('Refresh: 4;url=' . $redirect);
		}

		return eval($this->template('ADMIN_MESSAGE'));
	}

	/**
	 * Copies a directory and its files, recursively
	 *
	 * @param $from_path Source directory
	 * @param $to_path Destination directory
	 * @author See http://www.php.net/copy
	 * @since Beta 3.0
	 * @return bool True on success, false on failure
	 */
	function dir_copy($from_path, $to_path)
	{
		if (!file_exists($to_path)) {
			$ret = @mkdir($to_path, 0777);

			if (!$ret) {
				return false;
			}
		}

		if (file_exists($from_path) && is_dir($from_path)) {
			$handle = opendir($from_path);

			while (($file = readdir($handle)) !== false)
			{
				if (($file != '.') && ($file != '..') && ($file != 'CVS')) {
					if (is_dir($from_path . $file)) {
						$this->dir_copy($from_path . $file . '/', $to_path . $file . '/');
					}

					if (is_file($from_path . $file)) {
						copy($from_path . $file, $to_path . $file);
					}
				}
			}
			closedir($handle);
		}

		return true;
	}

	/**
	 * Loads a user_language. Bet you couldn't figure that out...
	 *
	 * @param string $lang Language to load
	 * @param string $a Word set to load
	 * @param string $path Path to the user_languages directory
	 * @param bool $main Load main universal strings
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 3.0
	 * @return object Language
	 **/
	function get_lang($lang, $a = null, $path = '../', $main = true)
	{
		if (isset($this->get['lang'])) {
			$lang = $this->get['lang'];
		}

		if (strstr($lang, '/') || !file_exists($path . 'languages/' . $lang . '.php')) {
			$lang = 'en';
		}

		include $path . 'languages/' . $lang . '.php';
		$obj = new $lang();

		// Check if language function is available before running it
		if ($a && is_callable(array($obj,$a))) {
			$obj->$a();
		}

		if ($main) {
			$obj->admin();
		}
		$obj->universal();
		return $obj;
	}

	function list_groups($val, $select = 'user_group', $custom_only = false)
	{
		$out = "<select name=\"$select\">";

		if ($custom_only) {
			$groups = $this->db->query('SELECT group_name, group_id FROM groups WHERE group_type="" ORDER BY group_name');
		} else {
			$groups = $this->db->query('SELECT group_name, group_id FROM groups ORDER BY group_name');
		}

		while ($group = $this->db->nqfetch($groups))
		{
			$out .= "<option value=\"{$group['group_id']}\"" . (($val == $group['group_id']) ? ' selected=\"selected\"' : '') . ">" . htmlspecialchars($group['group_name']) . "</option>";
		}

		return $out . '</select>';
	}

	/**
	 * Adds an entry to the navigation tree (interface to html widgets)
	 *
	 * @param string $label Label for the tree entry
	 * @param string $link URL to link to
	 **/
	function tree($label, $link = null)
	{
		$this->htmlwidgets->tree($label, $link);
	}

	/**
	 * Grabs the current list of table names in the database
	 *
	 * @author Roger Libiez [Samson]
	 * @since 1.3.3
	 * @return array
	 **/
	function get_db_tables()
	{
		$tarray = array();

		// This looks a bit strange, but it will pull all of the proper prefixed tables.
		$tb = $this->db->query( 'SHOW TABLES' );
		while( $tb1 = $this->db->nqfetch($tb) )
		{
			foreach( $tb1 as $col => $data )
				$tarray[] = $data;
		}

		return $tarray;
	}

	/**
	 * Attempts to CHMOD a directory or file
	 *
	 * @param string $path Path to CHMOD
	 * @param int $mode New CHMOD value
	 * @param bool $recursive True for recursive
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since 1.1.5
	 * @return void
	 **/
	function chmod($path, $mode, $recursive = false)
	{
		if (!$recursive || !is_dir($path)) {
			@chmod($path, $mode);
			return;
		}

		$dir = opendir($path);
		while (($file = readdir($dir)) !== false)
		{
			if(($file == '.') || ($file == '..')) {
				continue;
			}

			$fullpath = $path . '/' . $file;
			if(!is_dir($fullpath)) {
				@chmod($fullpath, $mode);
			} else {
				$this->chmod($fullpath, $mode, true);
			}
		}

		closedir($dir);
		@chmod($path, $mode);
	}
}
?>