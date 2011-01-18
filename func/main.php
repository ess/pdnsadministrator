<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2011 Roger Libiez http://www.iguanadons.net
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
 * Front page view
 *
 * @author Roger Libiez [Samson]
 * @since 1.0
 **/
class main extends pdnsadmin
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
		$check = false;

		if( isset($this->post['search']) ) {
			$check = true;
			$search = $this->post['search'];
		}

		$sql = 'SELECT d.id, d.name, d.type, d.master, u.user_name, u.user_id, z.owner, COUNT(DISTINCT r.id) AS recs
		    FROM domains d
		    LEFT JOIN zones z ON d.id=z.domain_id
		    LEFT JOIN users u ON u.user_id=z.owner
		    LEFT JOIN records r ON r.domain_id=d.id';

		if ($check)
			$sql .= " WHERE d.name LIKE '%%$search%%'";

		if (!$this->perms->auth('edit_domains')) {
			if ($check)
				$sql .= " AND z.owner=$id";
			else
				$sql .= " WHERE z.owner=$id";
		}

		$sql .= ' GROUP BY d.name, d.id
		    ORDER BY d.name';

		$result = $this->db->dbquery($sql);
		$num = $this->db->num_rows( $result );

		// Need to pick a default in case the setting doesn't exist for some reason.
		$domains_per_page = isset($this->sets['domains_per_page']) ? $this->sets['domains_per_page'] : 50;

		$this->get['min'] = isset($this->get['min']) ? intval($this->get['min']) : 0;
		$this->get['num'] = isset($this->get['num']) ? intval($this->get['num']) : $domains_per_page;

		$pages = $this->htmlwidgets->get_pages( $num, '', $this->get['min'], $this->get['num'] );

		$sql .= sprintf( ' LIMIT %d, %d', $this->get['min'], $this->get['num'] );
		$result = $this->db->dbquery( $sql );

		while( $domain = $this->db->nqfetch($result) )
		{
			$content .= eval($this->template('DOMAIN_ITEM'));
		}

		$token = $this->generate_token();
		return eval($this->template('DOMAIN_LIST'));
	}
}
?>