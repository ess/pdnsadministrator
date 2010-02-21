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

if (!defined('PDNSADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

require_once $set['include_path'] . '/global.php';

class users extends pdnsadmin
{
	function execute()
	{
		if( !$this->perms->auth('is_admin') ) {
			return $this->message( $this->lang->users_action_forbidden, $this->lang->users_not_allowed );
		}

		if( isset($this->get['w']) ) {
			$w = intval( $this->get['w'] );
			return $this->user_display( $w );
		}

		$this->set_title($this->lang->users_list);

		$this->get['min'] = isset($this->get['min']) ? intval($this->get['min']) : 0;
		$this->get['num'] = isset($this->get['num']) ? intval($this->get['num']) : 50;
		$asc = 0;

		if (isset($this->get['order'], $this->get['asc'])) {
			$order = $this->get['order'];

			switch($this->get['order'])
			{
			case 'domains':
				$sortby = 'm.user_domains';
				break;

			case 'group':
				$sortby = 'm.user_group';
				break;

			case 'created':
				$sortby = 'm.user_created';
				break;

			default:
				$order = 'user';
				$sortby = 'm.user_name';
				break;
			}

			if (!$this->get['asc']) {
				$sortby .= ' DESC';
			}

			$asc  = ($this->get['asc'] == 0) ? 1 : 0;
			$lasc = ($this->get['asc'] == 0) ? 0 : 1;

		} else {
			$lasc = 1;
			$order = 'user';
			$sortby = 'm.user_name ASC';
		}

		if (!isset($this->get['l'])) {
			$l = null;
		} else {
			$l = strtoupper(preg_replace('/[^A-Za-z]/', '', $this->get['l']));
		}

		if ($l) {
		$PageNums = $this->htmlwidgets->get_pages(
			array("SELECT user_id FROM users m, groups g
			WHERE m.user_group = g.group_id AND m.user_id != %d AND UPPER(LEFT(LTRIM(m.user_name), 1)) = '%s'",
			USER_GUEST_UID, $l),
			"a=users&amp;l={$l}&amp;order=$order&amp;asc=$lasc", $this->get['min'], $this->get['num']);
		} else {
		$PageNums = $this->htmlwidgets->get_pages(
			array("SELECT user_id FROM users m, groups g WHERE m.user_group = g.group_id AND m.user_id != %d", USER_GUEST_UID),
			"a=users&amp;l={$l}&amp;order=$order&amp;asc=$lasc", $this->get['min'], $this->get['num']);
		}

		$result = $this->db->query("
			SELECT
				m.user_created, m.user_email, m.user_name, m.user_id, m.user_domains, g.group_name
			FROM
				users m,
				groups g
			WHERE
				m.user_group = g.group_id AND
				m.user_id != %d" .
				($l ? " AND UPPER(LEFT(LTRIM(m.user_name), 1)) = '{$l}'" : '') . "
			ORDER BY
				{$sortby}
			LIMIT
				%d, %d",
			USER_GUEST_UID, $this->get['min'], $this->get['num']);

		$Users = null;

		while ($user = $this->db->nqfetch($result))
		{
			$user['user_created'] = $this->mbdate(DATE_ONLY_LONG, $user['user_created']);

			$Users .= eval($this->template('USERS_USER'));
		}

		return eval($this->template('USERS_MAIN'));
	}

	function user_display( $w )
	{
		$user = $this->db->fetch( 'SELECT * FROM users WHERE user_id=%d', $w );

		if( !$user ) {
			return $this->message( $this->lang->users_not_found, $this->users_no_user );
		}

		$domain_list = '';
		$domain_count = 0;
		if( $user['user_domains'] > 0 ) {
			$domains = $this->db->query( 'SELECT z.domain_id, d.name
			   FROM zones z
			   LEFT JOIN domains d ON d.id=z.domain_id
			   WHERE z.owner=%d
			   ORDER BY d.name ASC', $user['user_id'] );

			while( $domain = $this->db->nqfetch($domains) )
			{
				$domain_count++;
				$domain_list .= "<a href=\"{$this->self}?a=domains&amp;s=edit&amp;id={$domain['domain_id']}\">{$domain['name']}</a><br />";
			}
		}

		if( $domain_count < 1 )
			$domain_list = $this->lang->users_no_domains;
		else
			$domain_list .= '<br />' . sprintf( $this->lang->users_owns_domains, $domain_count );

		return eval($this->template('USERS_PROFILE'));
	}
}
?>