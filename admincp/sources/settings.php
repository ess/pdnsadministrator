<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2008 Roger Libiez http://www.iguanadons.net
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

if (!defined('QUICKSILVERFORUMS') || !defined('QSF_ADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

require_once $set['include_path'] . '/admincp/admin.php';

class settings extends admin
{
	function execute()
	{
		if (!isset($this->get['s'])) {
			$this->get['s'] = null;
		}

		switch($this->get['s'])
		{
		case 'add':
			$this->set_title($this->lang->settings_new_add);
			$this->tree($this->lang->settings_new_add);

			if(!isset($this->post['submit'])) {
				return $this->message($this->lang->settings_new, "
				<form action='{$this->self}?a=settings&amp;s=add' method='post'>
				<div>
				{$this->lang->settings_new_name}:  <input class='input' name='new_setting' type='text' value='' /><br /><br />
				{$this->lang->settings_new_value}: <input class='input' name='new_value' type='text' value='' /><br />
				<input type='submit' name='submit' value='{$this->lang->submit}' />
				</div>
				</form>" );
			}
			else {
				if(empty($this->post['new_setting'])) {
					return $this->message($this->lang->settings_new, $this->lang->settings_new_required);
				}

				$new_setting = $this->post['new_setting'];
				$new_value = $this->post['new_value'];

				if( isset($this->sets[$new_setting]) ) {
					return $this->message($this->lang->settings_new, $this->lang->settings_new_exists);
				}

				$this->sets[$new_setting] = $new_value;
				$this->write_sets();

				return $this->message($this->lang->settings_new, $this->lang->settings_new_added);
			}
			break;

		case 'db':
			$this->set_title($this->lang->settings_db);
			$this->tree($this->lang->settings_db);

			return eval($this->template('ADMIN_EDIT_DB_SETTINGS'));
			break;

		case 'basic':
			$this->set_title($this->lang->settings_basic);
			$this->tree($this->lang->settings_basic);

			$defaultlang = $this->htmlwidgets->select_langs($this->sets['default_lang'], '..');
			$timezones = $this->htmlwidgets->select_timezones($this->sets['servertime']);

			// Set data for use in skin
			$selectSkins = $this->htmlwidgets->select_skins($this->sets['default_skin']);

			return eval($this->template('ADMIN_EDIT_BOARD_SETTINGS'));
			break;

		case 'update':
			if (!$this->post) {
				return $this->message($this->lang->settings, $this->lang->settings_nodata);
				break;
			}

			$vartypes = array(
				'db_host' => 'string',
				'db_name' => 'string',
				'db_user' => 'string',
				'db_pass' => 'string',
				'db_port' => 'string',
				'db_socket' => 'string',

				'admin_incoming' => 'string',
				'admin_outgoing' => 'string',
				'mailserver' => 'string',
				'debug_mode' => 'bool',
				'default_skin' => 'string',
				'default_lang' => 'string',
				'logintime' => 'int',
				'cookie_prefix' => 'string',
				'cookie_path' => 'string',
				'cookie_domain' => 'string',
				'cookie_secure' => 'bool',
				'output_buffer' => 'bool',
				'domain_master_ip' => 'string',
				'default_ttl' => 'int',
				'primary_nameserver' => 'string',
				'secondary_nameserver' => 'string',
				'tertiary_nameserver' => 'string',
				'quaternary_nameserver' => 'string',
				'quinary_nameserver' => 'string',
				'senary_nameserver' => 'string',
				'septenary_nameserver' => 'string',
				'octonary_nameserver' => 'string',
				'site_name' => 'string',
				'site_url' => 'string',
				'servertime' => 'float'
			);

			foreach ($this->post as $var => $val)
			{
				if ($var == 'tos')
					continue;
				if (($vartypes[$var] == 'int') || ($vartypes[$var] == 'bool')) {
					$val = intval($val);
				} elseif ($vartypes[$var] == 'float') {
					$val = (float)$val;
				} elseif ($vartypes[$var] == 'kilobytes') {
					$val = intval($val) * 1024;
				} elseif ($vartypes[$var] == 'array') {
					$val = explode("\n", $val);
					$count = count($val);

					for ($i = 0; $i < $count; $i++)
					{
						$val[$i] = trim($val[$i]);
					}
				} elseif ($vartypes[$var] == 'string') {
					$val = $val;
				}

				if ($var == 'cookie_path' && $val != '/') {
					$newval = '';
					if ($val{0} != '/')
						$newval .= '/';
					$newval .= $val;
					if ($val{strlen($val)-1} != '/')
						$newval .= '/';
					$val = $newval;
				}

				$this->sets[$var] = $val;
			}

			if (isset($this->get['db'])) {
				$this->write_db_sets('../settings.php');
			} else {
				$this->db->query("UPDATE users SET user_language='%s', user_skin='%s' WHERE user_id=%d",
					$this->post['default_lang'], $this->post['default_skin'], USER_GUEST_UID);
				$this->write_sets();
			}

			return $this->message($this->lang->settings, $this->lang->settings_updated);
			break;
		}
	}
}
?>