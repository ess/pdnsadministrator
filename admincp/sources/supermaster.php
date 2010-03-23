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

require_once $set['include_path'] . '/admincp/admin.php';

/**
 * Supermaster support
 *
 * Supermasters are used to add newly created domains to the slave servers for a given master.
 * http://docs.powerdns.com/slave.html#SUPERMASTER
 *
 * @author Roger Libiez [Samson] http://www.iguanadons.net
 * @since 1.1.5
 **/
class supermaster extends admin
{
	function execute()
	{
		if (!isset($this->get['s'])) {
			$this->get['s'] = null;
		}

		switch($this->get['s'])
		{
			default:
				$content = $this->list_supermasters();
				break;

			case 'add':
				return $this->add_supermaster();
			case 'delete':
				return $this->delete_supermaster();
		}

		return eval($this->template('ADMIN_SUPERMASTERS'));
	}

	/**
	 * Display a listing of existing supermaster records
	 *
	 * @author Roger Libiez [Samson] http://www.iguanadons.net
	 * @since 1.1.5
	 **/
	function list_supermasters()
	{
		$count = 0;
		$content = '';

		$masters = $this->db->query( 'SELECT * FROM supermasters' );

		while( $master = $this->db->nqfetch( $masters ) )
		{
			$count++;

			$ip = $this->format( $master['ip'], FORMAT_HTMLCHARS );
			$ns = $this->format( $master['nameserver'], FORMAT_HTMLCHARS );
			$account = $this->format( $master['account'], FORMAT_HTMLCHARS );

			$content .= eval($this->template('ADMIN_SUPERMASTER_ENTRY'));
		}

		if( $count == 0 )
			$content = $this->lang->supermasters_none;

		return $content;
	}

	/**
	 * Create a supermaster record for PDNS
	 *
	 * IP address should always be the PDNS server this code is running on.
	 * Nameserver is a domain name to look for in the notification from the master server.
	 * The account value is not used for anything that I can see, so it's set to "Internal"
	 *
	 * @author Roger Libiez [Samson] http://www.iguanadons.net
	 * @since 1.1.5
	 **/
	function add_supermaster()
	{
		if(!isset($this->post['submit'])) {
			$token = $this->generate_token();

			return eval($this->template('ADMIN_SUPERMASTER_ADD'));
		}

		if( !$this->is_valid_token() ) {
			return $this->message( $this->lang->supermaster_add, $this->lang->invalid_token );
		}

		$ip = $this->post['ip'];
		$ns = $this->post['ns'];

		// If the 2 pieces of useful input match, then we can't insert the data.
		$exists = $this->db->fetch( "SELECT * FROM supermasters WHERE ip='%s' AND nameserver='%s'", $ip, $ns );
		if( $exists )
			return $this->message( $this->lang->supermaster_add, $this->lang->supermaster_exists );

		$type = 'A';
		if( strpos( $ip, '.' ) === false )
			$type = 'AAAA';

		if( !$this->is_valid_ip($ip, $type) )
			return $this->message( $this->lang->supermaster_add, $this->lang->supermaster_ip_invalid );

		if( !$this->is_valid_domain($ns) )
			return $this->message( $this->lang->supermaster_add, $this->lang->supermaster_ns_invalid );

		$this->db->query( "INSERT INTO supermasters (ip,nameserver,account) VALUES( '%s', '%s', 'Internal' )", $ip, $ns );
		return $this->message( $this->lang->supermaster_add, $this->lang->supermaster_added );
	}

	/**
	 * Delete an existing supermaster record
	 *
	 * @author Roger Libiez [Samson] http://www.iguanadons.net
	 * @since 1.1.5
	 **/
	function delete_supermaster()
	{
		$ip = isset($this->get['ip']) ? $this->get['ip'] : '*BOGUS*';
		$ns = isset($this->get['ns']) ? $this->get['ns'] : '*BOGUS*';

		$exists = $this->db->fetch( "SELECT ip,nameserver FROM supermasters WHERE ip='%s' AND nameserver='%s'", $ip, $ns );
		if( !$exists )
			return $this->message( $this->lang->supermaster_delete, $this->lang->supermaster_ns_unknown );

		if( !isset($this->post['confirm'])) {
			$token = $this->generate_token();

			return eval($this->template('ADMIN_SUPERMASTER_DELETE'));
		}

		if( !$this->is_valid_token() ) {
			return $this->message( $this->lang->supermaster_delete, $this->lang->invalid_token );
		}

		$this->db->query( "DELETE FROM supermasters WHERE ip='%s' AND nameserver='%s'", $ip, $ns );
		return $this->message( $this->lang->supermaster_delete, $this->lang->supermaster_deleted );
	}
}
?>