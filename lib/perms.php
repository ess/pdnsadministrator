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

if (!defined('QUICKSILVERFORUMS')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

/**
 * Permissions class
 *
 * @author Jason Warner <jason@mercuryboard.com>
 * @since Beta 4.0
 **/
class permissions
{
	var $cube = array();
	var $group;
	var $user;
	var $db;
	var $pre;
	var $is_guest;
	var $standard = array(
		'site_view' => false,
		'do_anything' => false,
		'create_domains' => false,
		'edit_domains' => false,
		'delete_domains' => false,
		'is_admin' => false
	);

	var $globals = array(
		'site_view' => true,
		'do_anything' => true,
		'create_domains' => true,
		'edit_domains' => true,
		'delete_domains' => true,
		'is_admin' => true,
	);
	
	/**
	 * Constructor; sets up variables
	 *
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.2
	 **/
	function permissions(&$qsf)
	{
		$this->db  = &$qsf->db;
		$this->pre = &$qsf->pre;
		if (!empty($qsf->user)) {
			$this->get_perms($qsf->user['user_group'], $qsf->user['user_id'],
				($qsf->user['user_perms'] ? $qsf->user['user_perms'] : $qsf->user['group_perms']));
		}
	}

	/**
	 * Initialise the permissions object cube
	 *
	 * @param int $group Group id to load perms from. Set to -1 if using user perms
	 * @param int $user User id. Checked against USER_GUEST_UID as loaded as perms if group is set to -1
	 * @param mixed $perms Optional array of permissions to use instead of group or user perms from the database
	 **/
	function get_perms($group, $user, $perms = false)
	{
		if (!$perms) {
			if ($group != -1) {
				$data  = $this->db->fetch('SELECT group_perms FROM groups WHERE group_id=%d', $group);
				$perms = $data['group_perms'];
			} else {
				$data  = $this->db->fetch('SELECT user_perms, user_group FROM users WHERE user_id=%d', $user);
				$perms = $data['user_perms'];
				$group = $data['user_group'];
			}
		}

		$this->cube = unserialize($perms);
		if (!$this->cube) {
			$this->cube = $this->standard;
		}

		$this->is_guest = (($user == USER_GUEST_UID) || ($group == USER_GUEST));
		$this->group = $group;
		$this->user  = $user;
	}

	/**
	 * Query if a permission is turned on or not
	 *
	 * @param string $y Indentifier of the permission being queried
	 * @param mixed $z Forum to check the permission against
	 *
	 * @return true if found the permission and it is on
	 **/
	function auth($y, $z = false)
	{
		if (!isset($this->cube[$y])) {
			return false;
		}

		if ($z === false) {
			return !is_array($this->cube[$y]) ? $this->cube[$y] : !in_array(false, $this->cube[$y]);
		} else {
			return is_array($this->cube[$y]) ? (isset($this->cube[$y][$z]) && $this->cube[$y][$z]) : $this->cube[$y];
		}
	}

	/**
	 * Run through the cube and rebuild all permissions to on or off
	 *
	 * @param bool $bool What value to assign to all permissions
	 **/
	function reset_cube($bool)
	{
		$cube = $this->standard;

		foreach ($cube as $y => $z)
		{
			$cube[$y] = $bool;
		}

		$this->cube = $cube;
	}

	/**
	 * Turn on or off a specific permission. Also turn on or off for all forums
	 * that permission applies to
	 *
	 * @param string $y Indentifier of the permission being queried
	 * @param bool $bool What value to assign to all permissions
	 **/
	function set_xy($y, $bool)
	{
		if (!isset($this->cube[$y])) {
			$this->cube[$y] = $bool;
		} else {
			$this->cube[$y] = $bool;
		}
	}

	/**
	 * This will load a new group for each while iteration
	 *
	 * while ($perms->get_group())
	 * {
	 *     $perms->set_xy();
	 *     $perms->update();
	 * }
	 *
	 * @param bool $users If true load user permissions instead of group permissions
	 **/
	function get_group($users = false)
	{
		static $start = true;
		static $groups = array();
		static $p = 0;

		if ($start) {
			$start = false;

			if ($users) {
				$query = $this->db->query("SELECT user_id, user_perms FROM users WHERE user_perms != ''");
			} else {
				$query = $this->db->query('SELECT group_id, group_perms FROM groups');
			}

			while ($group = $this->db->nqfetch($query))
			{
				$groups[] = $group;
			}
		}

		if ($p < count($groups)) {
			if ($users) {
				$this->get_perms(-1, $groups[$p]['user_id'], $groups[$p]['user_perms']);
			} else {
				$this->get_perms($groups[$p]['group_id'], -1, $groups[$p]['group_perms']);
			}

			$p++;

			return true;
		} else {
			$start = true;
			$groups = array();
			$p = 0;

			return false;
		}
	}
	
	/**
	 * Save the permissions back to the database
	 **/
	function update()
	{
		if ($this->cube) {
			ksort($this->cube);
			$serialized = serialize($this->cube);
		} else {
			$serialized = '';
		}

		if ($this->user == -1) {
			$this->db->query("UPDATE groups SET group_perms='%s' WHERE group_id=%d",
				$serialized, $this->group);
		} else {
			$this->db->query("UPDATE users SET user_perms='%s' WHERE user_id=%d",
				$serialized, $this->user);
		}
	}
}
?>