<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2008 Roger Libiez http://www.iguanadons.net
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

require_once $set['include_path'] . '/lib/tool.php';

/**
 * Contains all the functions for generating small pieces of
 * HTML that can not be easily done in a template
 *
 * @author Geoffrey Dunn <geoff@warmage.com>
 * @since 1.2
 **/
class htmlwidgets extends tool
{
	var $tree    = null;              // The navigational tree @var string

	/**
	 * Constructor
	 *
	 * @param $pdns - PDNS-Admin module
	 **/
	function htmlwidgets(&$pdns)
	{
		$this->modules = &$pdns->modules;
		$this->lang = &$pdns->lang;
		$this->self = $pdns->self;
		$this->skin = $pdns->skin;
		$this->db  = &$pdns->db;
		$this->sets = &$pdns->sets;
		$this->perms = &$pdns->perms;
		$this->user = &$pdns->user;
	}

	/**
	 * Creates HTML-formatted page numbers
	 *
	 * @param mixed $rows Can be either a resource, query, or number; number of total entries for pagination
	 * @param string $link Query string to attach to link
	 * @param int $min First entry to display
	 * @param int $num Number of entries per page
	 * @author Mark Elliot <mark.elliot@mercuryboard.com>
	 * @since Beta 1.0
	 * @return string HTML-formatted page numbers
	 **/
	function get_pages($rows, $link, $min = 0, $num = 10)
	{
		// No stupid values!
		if ($num < 1) {
			$num = 10;
		}

		if( $link && $link != '' )
			$link = "$this->self?$link&amp;";
		else
			$link = "$this->self?";

		// preliminary row handling
		if (!is_resource($rows)) {
			if (!is_numeric($rows)) {
				$rows = $this->db->num_rows($this->db->query($rows));
			}
		} else {
			$rows = $this->db->num_rows($rows);
		}

		// some base variables
		$current = ceil($min / $num);
		$string  = null;
		$pages   = ceil($rows / $num);
		$end     = ($pages - 1) * $num;

		// check if there's previous articles
		if ($min == 0) {
			$startlink = '&lt;&lt;';
			$previouslink = $this->lang->main_prev;
		} else {
			$startlink = "<a href=\"{$link}min=0&amp;num=$num\" class=\"pagelinks\">&lt;&lt;</a>";
			$prev = $min - $num;
			$previouslink = "<a href=\"{$link}min=$prev&amp;num=$num\" class=\"pagelinks\">{$this->lang->main_prev}</a> ";
		}

		// check for next/end
		if (($min + $num) < $rows) {
			$next = $min + $num;
  			$nextlink = "<a href=\"{$link}min=$next&amp;num=$num\" class=\"pagelinks\">{$this->lang->main_next}</a>";
  			$endlink = "<a href=\"{$link}min=$end&amp;num=$num\" class=\"pagelinks\">&gt;&gt;</a>";
		} else {
  			$nextlink = $this->lang->main_next;
  			$endlink = '&gt;&gt;';
		}

		// setup references
		$b = $current - 2;
		$e = $current + 2;

		// set end and beginning of loop
		if ($b < 0) {
  			$e = $e - $b;
  			$b = 0;
		}

		// check that end coheres to the issues
		if ($e > $pages - 1) {
  			$b = $b - ($e - $pages + 1);
  			$e = ($pages - 1 < $current) ? $pages : $pages - 1;
  			// b may need adjusting again
  			if ($b < 0) {
				$b = 0;
			}
		}

 		// ellipses
		if ($b != 0) {
			$badd = '...';
		} else {
			$badd = '';
		}

		if (($e != $pages - 1) && $rows) {
			$eadd = '...';
		} else {
			$eadd = '';
		}

		// run loop for numbers to the page
		for ($i = $b; $i < $current; $i++)
		{
			$where = $num * $i;
			$string .= ", <a href=\"{$link}min=$where&amp;num=$num\" class=\"bodylinktype\">" . ($i + 1) . '</a>';
		}

		// add in page
		$string .= ', <strong>' . ($current + 1) . '</strong>';

		// run to the end
		for ($i = $current + 1; $i <= $e; $i++)
		{
			$where = $num * $i;
			$string .= ", <a href=\"{$link}min=$where&amp;num=$num\" class=\"bodylinktype\">" . ($i + 1) . '</a>';
		}

		// get rid of preliminary comma. (optimized by jason: mark uses preg_replace() like candy)
		if (substr($string, 0, 1) == ',') {
			$string = substr($string, 1);
		}

		return "<span class=\"pagelinks\">$startlink $previouslink $badd $string $eadd $nextlink $endlink</span>";
	}

	function select_domain_types($type)
	{
		$dom_types = array( 'MASTER', 'SLAVE', 'NATIVE' );

		$out = null;
		foreach( $dom_types as $x )
		{
			if ($x == $type)
				$selected = " selected='selected'";
			else
				$selected = '';
			$out .= "<option value='{$x}'{$selected}>{$x}</option>";
		}
		return $out;
	}

	/**
	 * Create options of user names
	 *
	 * @param int $val user_id of the person using the form
	 * @return string HTML
	 **/
	function select_users($val)
	{
		$users = $this->db->query('SELECT user_name, user_id FROM users ORDER BY user_name');

		$out = null;

		while ($user = $this->db->nqfetch($users))
		{
			if ($user['user_id'] == USER_GUEST_UID)
				continue;

			if ($this->user['user_group'] == USER_MEMBER && $user['user_id'] != $this->user['user_id'])
				continue;

			$out .= "<option value=\"{$user['user_id']}\"" . (($val == $user['user_id']) ? ' selected="selected"' : '') . ">{$user['user_name']}</option>";
		}

		return $out;
	}

	/**
	 * Create options of group names
	 *
	 * @param int $var group_id of the selected group 
	 * @param bool $custom_only Show only groups that are not part of the built in groups
	 * @return string HTML
	 **/
	function select_groups($val, $custom_only = false)
	{
		if ($custom_only) {
			$groups = $this->db->query('SELECT group_name, group_id FROM groups WHERE group_type="" ORDER BY group_name');
		} else {
			$groups = $this->db->query('SELECT group_name, group_id FROM groups ORDER BY group_name');
		}

		$out = null;

		while ($group = $this->db->nqfetch($groups))
		{
			$out .= "<option value=\"{$group['group_id']}\"" . (($val == $group['group_id']) ? ' selected="selected"' : '') . ">{$group['group_name']}</option>";
		}

		return $out;
	}

	/**
	 * Generates a select box of skins
	 *
	 * @param string $skin user_skin to select
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 4.0
	 * @return string HTML
	 **/
	function select_skins($skin)
	{
		$out = null;

		$query = $this->db->query('SELECT * FROM skins');
		while ($s = $this->db->nqfetch($query))
		{
			if ($s['skin_dir'] == 'default') {
				$s['skin_name'] .= ' (default)';
			}
			$out .= "<option value=\"{$s['skin_dir']}\"" . (($s['skin_dir'] == $skin) ? ' selected="selected"' : null) . ">{$s['skin_name']}</option>\n";
		}

		return $out;
	}

	function select_timezones($zone)
	{
		$out = null;

		$zones = array(
			'-12'   => $this->lang->gmt_nev12,
			'-11'   => $this->lang->gmt_nev11,
			'-10'   => $this->lang->gmt_nev10,
			'-9'    => $this->lang->gmt_nev9,
			'-8'    => $this->lang->gmt_nev8,
			'-7'    => $this->lang->gmt_nev7,
			'-6'    => $this->lang->gmt_nev6,
			'-5'    => $this->lang->gmt_nev5,
			'-4'    => $this->lang->gmt_nev4,
			'-3.5'  => $this->lang->gmt_nev35,
			'-3'    => $this->lang->gmt_nev3,
			'-2'    => $this->lang->gmt_nev2,
			'-1'    => $this->lang->gmt_nev1,
			'0'     => $this->lang->gmt,
			'1'     => $this->lang->gmt_pos1,
			'2'     => $this->lang->gmt_pos2,
			'3'     => $this->lang->gmt_pos3,
			'3.5'   => $this->lang->gmt_pos35,
			'4'     => $this->lang->gmt_pos4,
			'4.5'   => $this->lang->gmt_pos45,
			'5'     => $this->lang->gmt_pos5,
			'5.5'   => $this->lang->gmt_pos55,
			'6'     => $this->lang->gmt_pos6,
			'7'     => $this->lang->gmt_pos7,
			'8'     => $this->lang->gmt_pos8,
			'9'     => $this->lang->gmt_pos9,
			'9.5'   => $this->lang->gmt_pos95,
			'10'    => $this->lang->gmt_pos10,
			'11'    => $this->lang->gmt_pos11,
			'12'    => $this->lang->gmt_pos12
		);

		foreach ($zones as $offset => $zone_name)
		{
			$out .= "<option value='$offset'" . (($offset == $zone) ? ' selected=\'selected\'' : null) . ">$zone_name</option>\n";
		}

		return $out;
	}

	/**
	 * Create options of selectable languages
	 *
	 * @param string $current The current language being used
	 * @param string $relative Path to look for avatars in (optional)
	 * @return string HTML
	 **/
	function select_langs($current, $relative = '.')
	{
		$out   = null;
		$langs = array();
		$dir   = opendir($relative . '/languages');

		while (($file = readdir($dir)) !== false)
		{
			if (is_dir($relative . '/languages/' . $file)) {
				continue;
			}

			$code = substr($file, 0, -4);
			$ext  = substr($file, -4);
			if ($ext != '.php') {
				continue;
			}

			$langs[$code] = $this->get_lang_name($code);
		}

		asort($langs);

		foreach ($langs as $code => $name)
		{
			$out .= "<option value='$code'" . (($code == $current) ? ' selected=\'selected\'' : null) . ">$name</option>\n";
		}

		return $out;
	}

	/**
	 * Fetch the language name for the language code
	 *
	 * @param string $code Two character country code
	 * @return string Language name (in English)
	 **/
	function get_lang_name($code)
	{
		$code = strtolower($code);

		switch($code)
		{
		case 'bg': return 'Bulgarian'; break;
		case 'zh': return 'Chinese'; break;
		case 'cs': return 'Czech'; break;
		case 'nl': return 'Dutch'; break;
		case 'en': return 'English'; break;
		case 'fi': return 'Finnish'; break;
		case 'fr': return 'French'; break;
		case 'de': return 'German'; break;
		case 'he': return 'Hebrew'; break;
		case 'hu': return 'Hungarian'; break;
		case 'id': return 'Indonesian'; break;
		case 'it': return 'Italian'; break;
		case 'no': return 'Norwegian'; break;
		case 'pt': return 'Portuguese'; break;
		case 'ru': return 'Russian'; break;
		case 'sk': return 'Slovak'; break;
		case 'es': return 'Spanish'; break;
		case 'sv': return 'Swedish'; break;
		default: return $code; break;
		}
	}

	/**
	 * Adds an entry to the navigation tree
	 *
	 * @param string $label Label for the tree entry
	 * @param string $link URL to link to
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.1
	 * @return void
	 **/
	function tree($label, $link = null)
	{
		$this->tree .= ' <b>&raquo;</b> ' . ($link ? "<a href='$link'>$label</a>" : $label);
	}
}
?>