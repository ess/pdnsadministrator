<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2007 Roger Libiez http://www.iguanadons.net
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

require_once $set['include_path'] . '/global.php';

/**
 * Front page view
 *
 * @author Roger Libiez [Samson]
 * @since 1.0
 **/
class main extends qsfglobal
{
	/**
	 * Construct the main page
	 *
	 **/
	function execute()
	{
		if ($this->perms->is_guest) {
			return eval($this->template('MAIN_SITE_GUEST'));
		} else {
			$content = $this->get_domain_list();

			return eval($this->template('MAIN_SITE_USER'));
		}
		return;
	}

	function get_domain_list()
	{
		$this->templater->add_templates('domains');
		$content = '';
		$id = $this->user['user_id'];

		$sql = "SELECT d.id, d.name, u.user_name, z.owner, COUNT(DISTINCT r.id) AS recs
		    FROM domains d
		    LEFT JOIN zones z ON d.id=z.domain_id
		    LEFT JOIN users u ON u.user_id=z.owner
		    LEFT JOIN records r ON r.domain_id=d.id";

		if ($this->user['user_group'] == USER_MEMBER) {
			$sql .= " WHERE z.owner=$id";
		}

		$sql .= " GROUP BY d.name, d.id
		    ORDER BY d.name";

		$result = $this->db->query($sql);
		while( $domain = $this->db->nqfetch($result) )
		{
			$content .= eval($this->template('DOMAIN_ITEM'));
		}

		return eval($this->template('DOMAIN_LIST'));
	}
}
?>