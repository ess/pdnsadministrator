<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2007 Roger Libiez http://www.iguanadons.net
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

if (!defined('QUICKSILVERFORUMS')) {
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
	var $qsf = null;		// Pointer to quicksilverforums object for modlets

	var $macro;                     // Array of code to execute for each template
	var $modlets = array();         // Array of modlet objects for running in templates
	
	var $debug_mode = false;	// Set to true if we want to use start/end comments
    
	/**
	 * Constructor
	 *
	 * @param $qsf - Quicksilver Forums module
	 **/
	function templater(&$qsf)
	{
		$this->db  = &$qsf->db;
		$this->sets = &$qsf->sets;

		// Need the template selection
		$this->skin = $qsf->skin;
		$this->debug_mode = $qsf->debug_mode;

		// Needed for modlets
		$this->qsf = &$qsf;
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
			$temp_query = $this->db->query("SELECT template_name, template_html FROM templates
				WHERE template_skin='%s' AND (template_set='Main' OR template_set='%s')",
				$this->skin, $a);
		} elseif ($getAdmin) {
			$temp_query = $this->db->query("SELECT template_name, template_html FROM templates
				WHERE template_skin='%s' AND (template_set='Admin' OR template_set='%s')",
				$this->skin, $a);
		} else {
			$temp_query = $this->db->query("SELECT template_name, template_html FROM templates
				WHERE template_skin='%s' AND template_set='%s'",
				$this->skin, $a);
		}

		while ($template = $this->db->nqfetch($temp_query))
		{
			// Check for MODLET with optional parameter
			$template['template_html'] = preg_replace('/<MODLET\s+(.*?)\((.*?)\)\s*>/se', '$this->_modlets_callback(\'\\1\', \'\\2\', $template[\'template_name\'])', $template['template_html']);
			// Check for IF statements
			$template['template_html'] = preg_replace('~<IF (.*?)(?<!\-)>(.*?)(<ELSE>(.*?))?</IF>~se', '$this->_iftag_callback(\'\\1\', \'\\2\', $template[\'template_name\'], \'\\3\')', $template['template_html']);
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
				error(QUICKSILVER_ERROR, "Template set not found in database: $a", __FILE__, __LINE__);
			} else {
				error(QUICKSILVER_ERROR, "Template set not found in database: $a<br />Skin not found in the skins directory: $this->skin", __FILE__, __LINE__);
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
	 * Run a modlet and return it's output
	 *
	 * @param string $modlet Name of the modlet
	 * @param string $parameter Parameter string to pass
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.2
	 * @return html formatted text
	 **/
	function modlet_exec($modlet, $parameter)
	{
		return $this->modlets[$modlet]->run($parameter);
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
		if ($falseCode) {
			// Strip off the <ELSE>
			$falseCode = substr($falseCode, 6);
		}
		$this->macro[$piece][$macro_id] = '$macro_replace[' . $macro_id . '] = ((' . $condition . ') ? "' . $code . '" : "' . $falseCode . '"); ';
		return '{' . chr(36) . 'macro_replace[' . $macro_id . ']}';
	}

   	/**
	 * Creates the modlet and stores modlet run statements into an array
	 *
	 * PROTECTED
	 *
	 * @param string $modlet modlet to run
	 * @param string $parameter String parameter to pass to the modlet
	 * @param string $piece template
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @return string replace modlet statements with a var
	 **/
	function _modlets_callback($modlet, $parameter, $piece)
	{
		$macro_id = isset($this->macro[$piece]) ? count($this->macro[$piece]) : 0;
        
		// Check the modlet uses valid characters
		if (preg_match('/[^a-zA-Z0-9_\-]/', $modlet)) {
			return '<!-- ERROR: Modlet ' . htmlspecialchars($modlet) . ' is not a valid modlet name -->';
		}
		if (!isset($this->modlets[$modlet])) {
			if (!is_readable($this->sets['include_path'] .  '/modlets/' . $modlet . '.php')) {
				return '<!-- ERROR: Modlet ' . htmlspecialchars($modlet) . ' does not exist -->';
			} else {
				require_once($this->sets['include_path'] .  '/modlets/' . $modlet . '.php');
			}
			$this->modlets[$modlet] =& new $modlet($this->qsf);
			if ($this->validate($modlet, TYPE_OBJECT, 'modlet')) {
				return '<!-- ERROR: Modlet ' . htmlspecialchars($modlet) . ' is not a type of modlet -->';
			}
		}
        
		$this->macro[$piece][$macro_id] = '$macro_replace[' . $macro_id . '] = (isset($this)) ? $this->templater->modlet_exec("'. $modlet . '", "' . $parameter . '") : $qsf->templater->modlet_exec("'. $modlet . '", "' . $parameter . '"); ';
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
