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

require_once $set['include_path'] . '/lib/tool.php';

/**
 * Templating system
 *
 * @author Geoffrey Dunn <geoff@warmage.com>
 * @since 1.2
 **/
class templater extends tool
{
	var $temps   = array();		// Loaded templates @var array
	var $skin = 'default';		// Skin to select from
	var $macro;                     // Array of code to execute for each template
	var $debug_mode = false;	// Set to true if we want to use start/end comments

	/**
	 * Constructor
	 *
	 * @param $pdns - PDNS-Admin module
	 **/
	function templater(&$pdns)
	{
		$this->db  = &$pdns->db;
		$this->sets = &$pdns->sets;

		// Need the template selection
		$this->skin = $pdns->skin;
		$this->debug_mode = $pdns->debug_mode;
	}

	/**
	 * Extends the existing templates array - see get_templates()
	 *
	 * @param string $section Template group
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return void
	 **/
	function add_templates($section)
	{
		$this->temps = array_merge($this->temps, $this->get_templates($section, 0));
	}

	/**
	 * Fetches templates and loads them into the temps property
	 *
	 * @param string $a Template group
	 * @param bool $admin Are we running an administration page
	 **/
	function init_templates($a, $admin)
	{
		$this->temps = $this->get_templates($a, !$admin, $admin);
	}

	/**
	 * Loads templates into an array, replacing {{var}} with $var
	 *
	 * @param string $a Template group
	 * @param bool $getMain Load the standard set of templates
	 * @param bool $getAdmin Load the standard set of admin cp templates
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return mixed Array of templates on success, error message on failure
	 **/
	function get_templates($a, $getMain = true, $getAdmin = false)
	{
		if ($getMain) {
			$temp_query = $this->db->dbquery("SELECT template_name, template_html FROM templates
				WHERE template_skin='%s' AND (template_set='Main' OR template_set='%s')",
				$this->skin, $a);
		} elseif ($getAdmin) {
			$temp_query = $this->db->dbquery("SELECT template_name, template_html FROM templates
				WHERE template_skin='%s' AND (template_set='Admin' OR template_set='%s')",
				$this->skin, $a);
		} else {
			$temp_query = $this->db->dbquery("SELECT template_name, template_html FROM templates
				WHERE template_skin='%s' AND template_set='%s'",
				$this->skin, $a);
		}

		while ($template = $this->db->nqfetch($temp_query))
		{
			// Check for IF statements
			$template['template_html'] = $this->parse_ifs($template['template_html'], $template['template_name']);
			$templates[$template['template_name']] = $template['template_html'];
		}

		if ($getAdmin)
			$dir = file_exists('../skins/' . $this->skin) && is_dir('../skins/' . $this->skin);
		else
			$dir = file_exists('./skins/' . $this->skin) && is_dir('./skins/' . $this->skin);

		if (isset($templates) && $dir) {
			// Set the templates!
			$templates = str_replace(array('\\', '"', '\\$'), array('\\\\', '\\"', '\\\\$'), $templates);
			return $templates;
		} else {
			if ($dir) {
				error(PDNSADMIN_ERROR, "Template set not found in database: $a", __FILE__, __LINE__);
			} else {
				error(PDNSADMIN_ERROR, "Template set not found in database: $a<br />Skin not found in the skins directory: $this->skin", __FILE__, __LINE__);
			}
		}
	}

	/**
	 * Quick check to see if the template exists
	 *
	 * @param mixed $piece Template name or an array of tempalte names
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.2
	 * @return bool true if it is loaded
	 **/
	function temp_set($piece)
	{
		if (is_array($piece)) {
			foreach ($piece as $p) {
				if (!isset($this->temps[$p])) return false;
			}
			return true;
		} else {
			return isset($this->temps[$piece]);
		}
	}

	/**
	 * Parse if statements in a template.
	 * @param string $string html template
	 * @param string $piece templates name
	 * @author Aaron Smith-Hayes (DavionKalhen@Gmail.com
	 * @since 1.4.3
	 * @return string with if's parsed.
	 **/
	function parse_ifs($string, $piece)
	{
		$stack = array();
		$last = 0;
		$pos = 0;
		$output = '';

		while( ($pos = strpos($string, '<', $pos) ) !== false )
		{
			$pos++;
			$close = $pos;
			while ( ($close = strpos($string, '>', $close )  ) !== false )
			{
				if($string{$close-1} == '-')
					$close++;
				else
					break;
			}

			if( $close  !== false )
			{
				if( (substr($string, $pos, 3) == 'IF ' ) ) {
					$condition = substr($string, $pos+3, $close-$pos-3);
					if($pos != 1)
						$stack[] = array('text', substr($string, $last, $pos-$last-1) );
					$stack[] = array('if', str_replace('"', '\\"', $condition) );
				} else if( (substr($string, $pos, 4) == 'ELSE' ) ) {
					$stack[] = array('text', substr($string, $last, $pos-$last-1) );
					$stack[] = array('else', 0);
				} else if( (substr($string, $pos, 3) == '/IF') ) {
					$stack[] = array('text', substr($string, $last, $pos-$last-1) );
					$stack[] = array('endif', 0);
				} else {
					continue;
				}
				$pos = $last = $close+1;
			}
		}

		if($last < strlen($string) )
			$stack[] = array('text', substr($string, $last, strlen($string)-$last ) );

		$nest = 0;
		$max = count($stack);

		for( $i = 0; $i < $max ; ++$i )
		{
			list($type, $value) = $stack[$i];
			if($type == 'text')
			{
				$output  .= $value;
				continue;
			}

			if($type == 'if')
			{
				list($else, $end) = $this->find_else_end($stack, $max, $i);
				$output .= $this->get_macro_replace($stack, $max, $piece, $i, $else, $end);
				$i = $end;
			}
		}
		return $output;
	}

	/**
	 * Replaces an ifstatement with a text representation and parses children.
	 *
	 * @param mixed $stack Stack queue of if's.
         * @param int $max size of the stack passed.
         * @param string $piece templates name
         * @param int $i location of this if on the stack
         * @param int $else location of the else -1 if no else.
         * @param int $end location of endif on the stack.
	 * @author Aaron Smith-Hayes (DavionKalhen@Gmail.com
	 * @since 1.4.3
	 * @return string representation of the if statement.
	 **/
	function get_macro_replace($stack, $max, $piece,  $i, $else, $end)
	{
		$if = $i++;
		$success_string = '';
		$fail_string = '';
		$init = 'success_string';

		for( ; $i < $max ; ++$i)
		{
			list($type, $value) = $stack[$i];
	
			if($type == 'text')
				$$init .= $value;
			if($type == 'else' && $i == $else)
				$init = 'fail_string';
			if($type == 'endif' && $i == $end)
				break;
			if($type == 'if')
			{
				list($nelse, $nend) = $this->find_else_end($stack, $max, $i);

				$$init .= $this->get_macro_replace($stack, $max, $piece, $i, $nelse, $nend);
				$i = $nend;
			}
		}
		$success_string = str_replace('"', '\\"', $success_string);
		$fail_string = str_replace('"', '\\"', $fail_string);
		return $this->_iftag_callback($stack[$if][1], $success_string, $piece, $fail_string );
	}

	/**
	 * Grab the else, and endif of an if statement
	 *
	 * @param mixed $stack Stack queue of if's.
         * @param int $size size of the stack passed.
         * @param int $if the if's location on the stack
	 * @author Aaron Smith-Hayes (DavionKalhen@Gmail.com
	 * @since 1.4.3
	 * @return array containing the locations of the else and if on the stack
	 **/
	function find_else_end($stack, $size, $if)
	{
		$i = 0;
		$nest = 0;
		$ifnest = -1;
		$ifelse = -1;
		$ifend = -1;

		for($i = 0 ; $i < $size ; ++$i )
		{
			list($type, $value) = $stack[$i];

			if($type == 'if')
			{
				$nest++;
				if($i == $if)
					$ifnest = $nest;
				continue;
			}

			if($type == 'else')
			{
				if($ifnest != -1 && $ifelse == -1 && $ifnest == $nest)
					$ifelse = $i;
				
				continue;
			}

			if($type == 'endif')
			{	
				if($ifnest != -1 && $ifend == -1 && $ifnest == $nest)
				{
					$ifend = $i;
					break;
				}
				$nest--;
			}
		}
		return array($ifelse, $ifend);
	}

	/**
	 * Stores if statements into an array (performance speed-up)
	 *
	 * PROTECTED
	 *
	 * @param string if statements
	 * @param string string to use if condition is true
	 * @param string template
	 * @param string string to use if contition is false (optional)
	 * @author Inverno
	 * @return string replace if statements with a var
	 **/
	function _iftag_callback($condition, $code, $piece, $falseCode = '')
	{	
		$macro_id = isset($this->macro[$piece]) ? count($this->macro[$piece]) : 0;
		$this->macro[$piece][$macro_id] = '$macro_replace[' . $macro_id . '] = ((' . $condition . ') ? "' . $code . '" : "' . $falseCode . '"); ';
		return '{' . chr(36) . 'macro_replace[' . $macro_id . ']}';
	}

	/**
	 * Returns a parsed template, for use in eval()
	 *
	 * @param string $piece Template name
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @author Inverno
	 * @since Beta 1.0
	 * @return string if statements to eval + Parsed HTML template
	 **/
	function template($piece)
	{
		if (!isset($this->temps[$piece])) {
			error(E_USER_ERROR, "Template not found: $piece", __FILE__, __LINE__);
		}

		$macro_output = "\$macro_replace = array(); ";

		if (isset($this->macro[$piece])) {
			foreach ($this->macro[$piece] as $macro_id => $macro_code) {
				$macro_output .= $macro_code;
			}
		}

		if ($this->debug_mode) {
			return "$macro_output return \"<!-- START: $piece -->\r\n{$this->temps[$piece]}\r\n<!-- END: $piece -->\r\n\";";
		}
		return "$macro_output return \"{$this->temps[$piece]}\r\n\";";
	}
}
?>