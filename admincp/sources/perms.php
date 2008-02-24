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

if (!defined('PDNSADMIN') || !defined('PDNS_ADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

require_once $set['include_path'] . '/admincp/admin.php';

class perms extends admin
{
	function execute()
	{
		$perms_obj = new $this->modules['permissions']($this);

		if (isset($this->get['s']) && ($this->get['s'] == 'user')) {
			if (!isset($this->get['id'])) {
				header('Location: $this->self?a=member&amp;s=perms');
			}

			$this->post['group'] = intval($this->get['id']);

			$mode  = 'user';
			$title = 'User Control';
			$link  = '&amp;s=user&amp;id=' . $this->post['group'];

			$perms_obj->get_perms(-1, $this->post['group']);
		} else {
			if (!isset($this->post['group'])) {
				return $this->message('User Groups', "
				<form action='$this->self?a=perms' method='post'><div>
					{$this->lang->perms_edit_for}
					<select name='group'>
					" . $this->htmlwidgets->select_groups(-1) . "
					</select>
					<input type='submit' value='{$this->lang->submit}' /></div>
				</form>");
			}

			$this->post['group'] = intval($this->post['group']);

			$mode  = 'group';
			$title = $this->lang->perms_title;
			$link  = null;

			$perms_obj->get_perms($this->post['group'], -1);
		}

		$this->set_title($title);
		$this->tree($title);

		$perms = array(
				'site_view'		=> $this->lang->perms_site_view,
				'do_anything'		=> $this->lang->perms_do_anything,
				'create_domains'	=> $this->lang->perms_create_domains,
				'edit_domains'		=> $this->lang->perms_edit_domains,
				'delete_domains'	=> $this->lang->perms_delete_domains,
				'is_admin'		=> $this->lang->perms_is_admin
		);

		if (!isset($this->post['submit'])) {
			if ($mode == 'user') {
				$query = $this->db->fetch('SELECT user_name, user_perms FROM users WHERE user_id=%d', $this->post['group']);
				$label = "{$this->lang->perms_user} '{$query['user_name']}'";
			} else {
				$query = $this->db->fetch('SELECT group_name FROM groups WHERE group_id=%d', $this->post['group']);
				$label = "{$this->lang->perms_group} '{$query['group_name']}'";
			}

			$out = "
			<script type='text/javascript' src='../javascript/permissions.js'></script>

			<form id='form' action='$this->self?a=perms$link' method='post'>
			<div align='center'><span style='font-size:14px;'><b>" . $this->lang->perms_for . " $label</b></span>";

			if ($mode == 'user') {
				$out .= "<br />{$this->lang->perms_override_user}<br /><br />
				<div style='border:1px dashed #ff0000; width:25%; padding:5px'><input type='checkbox' name='usegroup' id='usegroup' style='vertical-align:middle'" . (!$query['user_perms'] ? ' checked' : '') . " /> <label for='usegroup' style='vertical-align:middle'>{$this->lang->perms_only_user}</label></div>";
			}

			$out .= "</div>" .
			$this->table . "
			<tr>
				<td colspan='2' class='header'>$label</td>
			</tr>";

			$i = 0;
			foreach ($perms as $perm => $label)
			{
				$out .= "
				<tr>
					<td class='tabledark'>$label</td>
					<td class='tabledark' align='center'>
						<input type='checkbox' name='perms[$perm][-1]' id='perms_{$perm}' onclick='checkrow(\"$perm\", this.checked)'" . ($perms_obj->auth($perm) ? ' checked=\'checked\'' : '') . " />All
					</td>";

				$out .= "
				</tr>";

				$i++;
			}

			return $out . "
			<tr>
				<td colspan='2' class='tabledark' align='center'><input type='hidden' name='group' value='{$this->post['group']}' /><input type='submit' name='submit' value='Update Permissions' /></td>
			</tr>" . $this->etable . "</form>";
		} else {
			if (($mode == 'user') && isset($this->post['usegroup'])) {
				$perms_obj->cube = '';
				$perms_obj->update();
				return $this->message($this->lang->perms, $this->lang->perms_user_inherit);
			}

			$perms_obj->reset_cube(false);

			if (!isset($this->post['perms'])) {
				$this->post['perms'] = array();
			}

			if ($mode == 'user') {
				if ((!isset($this->post['perms']['do_anything'])) && ($this->post['group'] == USER_GUEST_UID)) {
					return $this->message($this->lang->perms, $this->lang->perms_guest1);
				}
			} else {
				if ((!isset($this->post['perms']['do_anything'])) && ($this->post['group'] == USER_GUEST)) {
					return $this->message($this->lang->perms, $this->lang->perms_guest2);
				}
			}

			foreach ($this->post['perms'] as $name => $data)
			{
				if (isset($data[-1]) || isset($data['-1'])) {
					$perms_obj->set_xy($name, true);
				}
			}

			$perms_obj->update();

			return $this->message($this->lang->perms, $this->lang->perms_updated);
		}
	}
}
?>