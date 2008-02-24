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
 * Domain Control Panel
 *
 * @author Roger Libiez
 * @since 1.0
 **/
class domains extends qsfglobal
{
	function execute()
	{
		if (!isset($this->get['s'])) {
			$this->get['s'] = null;
		}

		if ($this->perms->is_guest) {
			return $this->message($this->lang->domains, $this->lang->domains_login_first);
		}

		switch($this->get['s'])
		{
		case 'new':
			return $this->new_domain();

		case 'new_reverse':
			return $this->new_reverse_domain();

		case 'edit':
			return $this->edit_domain();

		case 'delete':
			return $this->delete_domain();

		case 'newrecord':
			return $this->new_record();

		case 'editrecord':
			return $this->edit_record();

		case 'deleterecord':
			return $this->delete_record();

		case 'changeowner':
			return $this->change_domain_owner();

		case 'changetype':
			return $this->change_domain_type();

		default:
			return $this->message($this->lang->domains, $this->lang->domains_unknown);
		}
	}

	function delete_record()
	{
		$this->set_title($this->lang->domains_record_delete);

		if (!isset($this->get['id'])) {
			return $this->message($this->lang->domains_record_delete, $this->lang->domains_id_invalid);
		}

		$id = $this->get['id'];
		if (!$this->is_owner($id, true)) {
			return $this->message($this->lang->domains_record_delete, $this->lang->domains_edit_not_permitted);
		}

		if (!isset($this->get['r'])) {
			return $this->message($this->lang->domains_record_delete, $this->lang->domains_record_required2);
		}

		$rec_id = $this->get['r'];
		$rec = $this->db->fetch( "SELECT * FROM records WHERE id=%d", $rec_id );
		if( $rec['domain_id'] != $id ) {
			return $this->message($this->lang->domains_record_delete, $this->lang->domains_record_delete_wrong);
		}

		$dom = $this->db->fetch( "SELECT name FROM domains WHERE id=%d", $id );
		if( $rec['type'] == 'SOA' )
			return $this->message($this->lang->domains_record_delete, $this->lang->domains_record_delete_soa);

		if( !isset($this->get['confirm']) ) {
			$confirm = sprintf( $this->lang->domains_record_delete_confirm, $rec['type'], $rec['name'], $rec['content']);

			return $this->message( $this->lang->domains_record_delete,
			$confirm . "<br /><br />
			<a href=\"{$this->self}?a=domains&amp;s=deleterecord&amp;id={$id}&amp;r={$rec_id}&amp;confirm=1\">{$this->lang->continue}</a>" );
		}

		$this->db->query( "DELETE FROM records WHERE id=%d", $rec_id );
		$this->update_soa_serial( $id );

		$this->log_action( "delete_" . $rec['type'] . "_record", $id );
		return $this->message($this->lang->domains_record_delete, $this->lang->domains_record_deleted, $this->lang->continue, "{$this->self}?a=domains&s=edit&id={$id}");
	}

	function edit_record()
	{
		$this->set_title($this->lang->domains_record_edit);

		if (!isset($this->get['id'])) {
			return $this->message($this->lang->domains_record_edit, $this->lang->domains_id_invalid);
		}

		$id = $this->get['id'];
		if (!$this->is_owner($id, true)) {
			return $this->message($this->lang->domains_record_edit, $this->lang->domains_edit_not_permitted);
		}

		if (!isset($this->get['r'])) {
			return $this->message($this->lang->domains_record_edit, $this->lang->domains_record_required2);
		}

		$rec_id = $this->get['r'];
		$rec = $this->db->fetch( "SELECT * FROM records WHERE id=%d", $rec_id );
		if( $rec['domain_id'] != $id ) {
			return $this->message($this->lang->domains_record_edit, $this->lang->domains_record_wrong);
		}

		$dom = $this->db->fetch( "SELECT name FROM domains WHERE id=%d", $id );
		$record_types = array( "A", "MX", "NS", "CNAME", "SOA", "TXT", "PTR", "URL" );

		if( !isset($this->post['submit']) ) {
			$rec_options = null;

			$rname = str_replace( $dom['name'], '', $rec['name'] );
			$rname = trim( $rname, '.' );

			foreach( $record_types as $rt )
			{
				if( $rt == $rec['type'] )
					$rec_options .= "<option value=\"{$rt}\" selected=\"selected\">{$rt}</option>";
				else
					$rec_options .= "<option value=\"{$rt}\">{$rt}</option>";
			}
			return eval($this->template('DOMAIN_RECORD_EDIT'));
		}

		if (!isset($this->post['record']) || empty($this->post['record'])) {
			return $this->message($this->lang->domains_record_add, $this->lang->domains_record_required);
		}

		$record = $this->post['record'];
		if (!in_array( $record, $record_types ) ) {
			return $this->message($this->lang->domains_record_edit, $this->lang->domains_record_invalid);
		}

		$name = isset($this->post['name']) ? $this->post['name'] : '';
		$content = isset($this->post['content']) ? $this->post['content'] : '';
		$ttl = isset($this->post['ttl']) ? intval($this->post['ttl']) : $this->sets['default_ttl'];
		$priority = isset($this->post['priority']) ? intval($this->post['priority']) : 0;

		// "A" record is stored in the form of name.domain.tld
		if( $record == 'A' ) {
			if( $name != '' )
				$name = $name . "." . $dom['name'];
			else
				$name = $dom['name'];

			if( !$this->is_valid_ip( $content ) ) {
				return $this->message($this->lang->domains_record_edit, $this->lang->domains_ip_invalid);
			}
		}

		// Doesn't matter what name someone puts in. 
		if( $record == 'TXT' || $record == 'URL' ) {
			$name = $dom['name'];
		}

		// Don't let it point to the same thing as CNAME
		if( $record == 'MX' ) {
			if( $name != '' )
				$name = $name . "." . $dom['name'];
			else
				$name = $dom['name'];

			if( $content == $name ) {
				return $this->message($this->lang->domains_record_edit, $this->lang->domains_invalid_mx2);
			}

			$rec = $this->db->fetch( "SELECT type FROM records WHERE type='CNAME' AND name='%s'", $content );
			if( $rec ) {
				return $this->message($this->lang->domains_record_edit, $this->lang->domains_invalid_mx);
			}
		}

		// Don't let it point to the same thing as CNAME
		if( $record == 'NS' ) {
			$name = $dom['name'];

			if( $content == $name ) {
				return $this->message($this->lang->domains_record_edit, $this->lang->domains_invalid_ns2);
			}

			$rec = $this->db->fetch( "SELECT type FROM records WHERE type='CNAME' AND name='%s'", $content );
			if( $rec ) {
				return $this->message($this->lang->domains_record_edit, $this->lang->domains_invalid_ns);
			}
		}

		// CNAME record should balk at an empty name field. And apparently can't use the same content as an NS or MX record.
		if( $record == 'CNAME' ) {
			if( $name != '' )
				$name = $name . "." . $dom['name'];
			else
				return $this->message($this->lang->domains_record_edit, $this->lang->domains_record_required3);

			$rec = $this->db->fetch( "SELECT type, content FROM records WHERE content='%s' AND (type='MX' OR type='NS')", $content );
			if( $rec ) {
				return $this->message($this->lang->domains_record_edit, $this->lang->domains_invalid_cname);
			}
		}

		// PTR record requires in-addr.arpa, so check for it.
		if( $record == 'PTR' ) {
			if( !stristr( $name, "." ) === FALSE )
				return $this->message($this->lang->domains_record_edit, $this->lang->domains_invalid_ptr);
		}

		if( $record == 'SOA' ) {
			$name = $dom['name'];
		}

		$this->db->query( "UPDATE records SET name='%s', type='%s', content='%s', ttl=%d, prio=%d, change_date=%d
		    WHERE id=%d", $name, $record, $content, $ttl, $priority, $this->time, $rec_id );

		if( $record != 'SOA' )
			$this->update_soa_serial( $id );

		$this->log_action( "edit_" . $record . "_record", $id );
		return $this->message($this->lang->domains_record_edit, $this->lang->domains_record_edited, $this->lang->continue, "{$this->self}?a=domains&s=edit&id={$id}");
	}

	function new_record()
	{
		$this->set_title($this->lang->domains_record_add);

		if (!isset($this->get['id'])) {
			return $this->message($this->lang->domains_record_add, $this->lang->domains_id_invalid);
		}

		$id = $this->get['id'];
		if (!$this->is_owner($id, true)) {
			return $this->message($this->lang->domains_record_add, $this->lang->domains_edit_not_permitted);
		}

		$domain = $this->db->fetch( "SELECT name FROM domains WHERE id=%d", $id );
		$record_types = array( "A", "MX", "NS", "CNAME", "TXT", "PTR", "URL" );

		if (!isset($this->post['submit'])) {
			$rec_options = null;

			foreach( $record_types as $rt )
			{
				$rec_options .= "<option value=\"{$rt}\">{$rt}</option>";
			}
			return eval($this->template('DOMAIN_RECORD_ADD'));
		}

		if (!isset($this->post['record']) || empty($this->post['record'])) {
			return $this->message($this->lang->domains_record_add, $this->lang->domains_record_required);
		}

		$record = $this->post['record'];
		if (!in_array( $record, $record_types ) ) {
			return $this->message($this->lang->domains_record_add, $this->lang->domains_record_invalid);
		}

		$name = isset($this->post['name']) ? $this->post['name'] : '';
		$content = isset($this->post['content']) ? $this->post['content'] : '';
		$ttl = isset($this->post['ttl']) ? intval($this->post['ttl']) : $this->sets['default_ttl'];
		$priority = isset($this->post['priority']) ? intval($this->post['priority']) : 0;

		// "A" record is stored in the form of name.domain.tld
		if( $record == 'A' ) {
			if( $name != '' )
				$name = $name . "." . $domain['name'];
			else
				$name = $domain['name'];

			if( !$this->is_valid_ip( $content ) ) {
				return $this->message($this->lang->domains_record_add, $this->lang->domains_ip_invalid);
			}
		}

		// Doesn't matter what name someone puts in. 
		if( $record == 'TXT' || $record == 'URL' ) {
			$name = $domain['name'];
		}

		// Don't let it point to the same thing as CNAME
		if( $record == 'MX' ) {
			if( $name != '' )
				$name = $name . "." . $domain['name'];
			else
				$name = $domain['name'];

			if( $content == $name ) {
				return $this->message($this->lang->domains_record_add, $this->lang->domains_invalid_mx2);
			}

			$rec = $this->db->fetch( "SELECT type FROM records WHERE type='CNAME' AND content='%s'", $content );
			if( $rec ) {
				return $this->message($this->lang->domains_record_add, $this->lang->domains_invalid_mx);
			}
		}

		// Don't let it point to the same thing as CNAME
		if( $record == 'NS' ) {
			$name = $domain['name'];

			if( $content == $name ) {
				return $this->message($this->lang->domains_record_add, $this->lang->domains_invalid_ns2);
			}

			$rec = $this->db->fetch( "SELECT type FROM records WHERE type='CNAME' AND name='%s'", $content );
			if( $rec ) {
				return $this->message($this->lang->domains_record_add, $this->lang->domains_invalid_ns);
			}
		}

		// CNAME record should balk at an empty name field. And apparently can't use the same content as an NS or MX record.
		if( $record == 'CNAME' ) {
			if( $name != '' )
				$name = $name . "." . $domain['name'];
			else
				return $this->message($this->lang->domains_record_add, $this->lang->domains_record_required3);

			$rec = $this->db->fetch( "SELECT type, content FROM records WHERE content='%s' AND (type='MX' OR type='NS')", $content );
			if( $rec ) {
				return $this->message($this->lang->domains_record_add, $this->lang->domains_invalid_cname);
			}
		}

		// PTR record requires in-addr.arpa, so check for it.
		if( $record == 'PTR' ) {
			if( !stristr( $name, "." ) === FALSE )
				return $this->message($this->lang->domains_record_add, $this->lang->domains_invalid_ptr);
		}

		$this->db->query( "INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
		    VALUES( %d, '%s', '%s', '%s', %d, %d, %d )",
		    $id, $name, $record, $content, $ttl, $priority, $this->time );

		$this->update_soa_serial( $id );

		$this->log_action( "new_" . $record . "_record", $id );
		return $this->message($this->lang->domains_record_add, $this->lang->domains_record_added, $this->lang->continue, "{$this->self}?a=domains&s=edit&id={$id}");
	}

	function new_reverse_domain()
	{
		$this->set_title($this->lang->domains_new_reverse);

		if (!isset($this->post['submit'])) {
			$users = $this->htmlwidgets->select_users($this->user['user_id']);
			$types = $this->htmlwidgets->select_domain_types("MASTER");

			return eval($this->template('DOMAINS_ADD_REVERSE'));
		}

		if (!isset($this->post['name']) || empty($this->post['name'])) {
			return $this->message($this->lang->domains_new_reverse, $this->lang->domains_required);
		}

		if (!isset($this->post['type']) || empty($this->post['type'])) {
			return $this->message($this->lang->domains_new_reverse, $this->lang->domains_type_required);
		}

		if (!isset($this->post['owner'])) {
			return $this->message($this->lang->domains_new_reverse, $this->lang->domains_user_invalid);
		}

		$dom_name = $this->post['name'];
		$dom_owner = $this->post['owner'];
		$dom_type = $this->post['type'];

		if (!$this->is_valid_domain($dom_name)) {
			return $this->message($this->lang->domains_new_reverse, $this->lang->domains_invalid);
		}

		if (!$this->db->fetch("SELECT user_id FROM users WHERE user_id=%d LIMIT 1", $dom_owner)) {
			return $this->message($this->lang->domains_new_reverse, $this->lang->domains_user_not_exist);
		}

		if ($this->db->fetch("SELECT name FROM domains WHERE name='%s' LIMIT 1", $dom_name)) {
			return $this->message($this->lang->domains_new_reverse, $this->lang->domains_exists);
		}

		$dom_master = '';
		if ($dom_type == 'SLAVE')
			$dom_master = $this->sets['domain_master_ip'];

		// Insert the domain into the primary domain table
		$this->db->query("INSERT INTO domains (name, type, master, notified_serial) VALUES( '%s', '%s', '%s', 1 )",
			$dom_name, $dom_type, $dom_master);
		$dom_id = $this->db->insert_id("domains");

		// Set the owner in the zones table
		$this->db->query("INSERT INTO zones (domain_id, owner, comment) VALUES( %d, %d, '%s' )", $dom_id, $dom_owner, "New Domain");

		// Insert the SOA record for the new domain.
		$new_serial = date('Ymd') . "00";
		$soa = $this->sets['primary_nameserver'] . " " . $this->sets['admin_incoming'] . " " . $new_serial . " 10800 3600 432000 " . $this->sets['default_ttl'];
		$this->db->query("INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
			VALUES( %d, '%s', 'SOA', '%s', %d, 0, %d )",
			$dom_id, $dom_name, $soa, $this->sets['default_ttl'], $this->time);

		// Insert the primary NS record for the new domain.
		$this->db->query("INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
			VALUES( %d, '%s', 'NS', '%s', %d, 0, %d )",
			$dom_id, $dom_name, $this->sets['primary_nameserver'], $this->sets['default_ttl'], $this->time);

		// Insert the secondary NS record for the new domain.
		$this->db->query("INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
			VALUES( %d, '%s', 'NS', '%s', %d, 0, %d )",
			$dom_id, $dom_name, $this->sets['secondary_nameserver'], $this->sets['default_ttl'], $this->time);

		$this->log_action( "new_reverse_domain", $dom_id );
		return $this->message($this->lang->domains_new_reverse, $this->lang->domains_new_created, $this->lang->continue, "{$this->self}?a=domains&s=edit&id={$dom_id}");
	}

	function new_domain()
	{
		$this->set_title($this->lang->domains_new);

		if (!isset($this->post['submit'])) {
			$users = $this->htmlwidgets->select_users($this->user['user_id']);
			$types = $this->htmlwidgets->select_domain_types("MASTER");

			return eval($this->template('DOMAINS_ADD'));
		}

		if (!isset($this->post['name']) || empty($this->post['name'])) {
			return $this->message($this->lang->domains_new, $this->lang->domains_required);
		}

		if (!isset($this->post['ip']) || empty($this->post['ip'])) {
			return $this->message($this->lang->domains_new, $this->lang->domains_ip_required);
		}

		if (!isset($this->post['type']) || empty($this->post['type'])) {
			return $this->message($this->lang->domains_new, $this->lang->domains_type_required);
		}

		if (!isset($this->post['owner'])) {
			return $this->message($this->lang->domains_new, $this->lang->domains_user_invalid);
		}

		$dom_name = $this->post['name'];
		$dom_ip = $this->post['ip'];
		$dom_owner = $this->post['owner'];
		$dom_type = $this->post['type'];

		if (!$this->is_valid_domain($dom_name)) {
			return $this->message($this->lang->domains_new, $this->lang->domains_invalid);
		}

		if (!$this->is_valid_ip($dom_ip)) {
			return $this->message($this->lang->domains_new, $this->lang->domains_ip_invalid);
		}

		if (!$this->db->fetch("SELECT user_id FROM users WHERE user_id=%d LIMIT 1", $dom_owner)) {
			return $this->message($this->lang->domains_new, $this->lang->domains_user_not_exist);
		}

		if ($this->db->fetch("SELECT name FROM domains WHERE name='%s' LIMIT 1", $dom_name)) {
			return $this->message($this->lang->domains_new, $this->lang->domains_exists);
		}

		$dom_master = '';
		if ($dom_type == 'SLAVE')
			$dom_master = $this->sets['domain_master_ip'];

		$dom_mail = "mail." . $dom_name;
		$dom_cname = "www." . $dom_name;

		// Insert the domain into the primary domain table
		$this->db->query("INSERT INTO domains (name, type, master, notified_serial) VALUES( '%s', '%s', '%s', 1 )",
			$dom_name, $dom_type, $dom_master);
		$dom_id = $this->db->insert_id("domains");

		// Set the owner in the zones table
		$this->db->query("INSERT INTO zones (domain_id, owner, comment) VALUES( %d, %d, '%s' )", $dom_id, $dom_owner, "New Domain");

		// Insert the SOA record for the new domain.
		$new_serial = date('Ymd') . "00";
		$soa_email = str_replace( '@', '.', $this->sets['admin_incoming'] );
		$soa = $this->sets['primary_nameserver'] . " " . $soa_email . " " . $new_serial . " 10800 3600 432000 " . $this->sets['default_ttl'];
		$this->db->query("INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
			VALUES( %d, '%s', 'SOA', '%s', %d, 0, %d )",
			$dom_id, $dom_name, $soa, $this->sets['default_ttl'], $this->time);

		// Insert the A record for the new domain.
		$this->db->query("INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
			VALUES( %d, '%s', 'A', '%s', %d, 0, %d )",
			$dom_id, $dom_name, $dom_ip, $this->sets['default_ttl'], $this->time);

		// Insert the primary NS record for the new domain.
		$this->db->query("INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
			VALUES( %d, '%s', 'NS', '%s', %d, 0, %d )",
			$dom_id, $dom_name, $this->sets['primary_nameserver'], $this->sets['default_ttl'], $this->time);

		// Insert the secondary NS record for the new domain.
		$this->db->query("INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
			VALUES( %d, '%s', 'NS', '%s', %d, 0, %d )",
			$dom_id, $dom_name, $this->sets['secondary_nameserver'], $this->sets['default_ttl'], $this->time);

		// Insert the MX record for the new domain. Priority defaults to 10.
		$this->db->query("INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
			VALUES( %d, '%s', 'MX', '%s', %d, 10, %d )",
			$dom_id, $dom_name, $dom_mail, $this->sets['default_ttl'], $this->time);

		// Insert the mail.domain.com A record for the new domain.
		$this->db->query("INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
			VALUES( %d, '%s', 'A', '%s', %d, 0, %d )",
			$dom_id, $dom_mail, $dom_ip, $this->sets['default_ttl'], $this->time);

		// Insert the CNAME record for the new domain.
		$this->db->query("INSERT INTO records (domain_id, name, type, content, ttl, prio, change_date)
			VALUES( %d, '%s', 'CNAME', '%s', %d, 0, %d )",
			$dom_id, $dom_cname, $dom_name, $this->sets['default_ttl'], $this->time);

		$this->log_action( "new_domain", $dom_id );
		return $this->message($this->lang->domains_new, $this->lang->domains_new_created, $this->lang->continue, "{$this->self}?a=domains&s=edit&id={$dom_id}");
	}

	function change_domain_owner()
	{
		if (!isset($this->get['id'])) {
			return $this->message($this->lang->domains_owner_change, $this->lang->domains_id_invalid);
		}

		if (!$this->perms->auth('edit_domains')) {
			return $this->message($this->lang->domains_owner_change, $this->lang->domains_owner_cant_change);
		}

		if (!isset($this->post['owner'])) {
			return $this->message($this->lang->domains_owner_change, $this->lang->domains_user_invalid);
		}

		$zone_exists = $this->db->fetch( "SELECT id FROM zones WHERE domain_id=%d", $this->get['id'] );
		$owner = intval($this->post['owner']);

		if( $zone_exists ) {
			$this->db->query( "UPDATE zones SET owner=%d, comment='%s' WHERE domain_id=%d",
				$owner, "Ownership Change", $this->get['id'] );
		} else {
			$this->db->query( "INSERT INTO zones (domain_id, owner, comment) VALUES(%d, %d, '%s')",
				$this->get['id'], $owner, "New Domain Owner" );
		}
		$this->log_action( "change_owner", $this->get['id'] );
		return $this->message($this->lang->domains_owner_change, $this->lang->domains_owner_changed, $this->lang->continue, "{$this->self}?a=domains&s=edit&id={$this->get['id']}");
	}

	function change_domain_type()
	{
		if (!isset($this->get['id'])) {
			return $this->message($this->lang->domains_type_change, $this->lang->domains_id_invalid);
		}

		if (!$this->is_owner($this->get['id'], true)) {
			return $this->message($this->lang->domains_type_change, $this->lang->domains_edit_not_permitted);
		}

		if ($this->post['type'] == 'SLAVE') {
			$this->db->query("UPDATE domains SET type='%s', master='%s' WHERE id=%d",
				$this->post['type'], $this->sets['domain_master_ip'], $this->get['id']);
		} else {
			$this->db->query("UPDATE domains SET type='%s', master='' WHERE id=%d",
				$this->post['type'], $this->get['id']);
		}

		$this->log_action( "change_domain_type", $this->get['id'] );
		return $this->message($this->lang->domains_type_change, $this->lang->domains_type_changed, $this->lang->continue, "{$this->self}?a=domains&s=edit&id={$this->get['id']}");
	}

	function edit_domain()
	{
		$this->set_title($this->lang->domains_edit);

		$records = '';

		if (!isset($this->get['id'])) {
			return $this->message($this->lang->domains_edit, $this->lang->domains_id_invalid);
		}

		$dom_id = $this->get['id'];

		// TRUE: Tells owner check to look at edit.
		if (!$this->is_owner($dom_id, true)) {
			return $this->message($this->lang->domains_edit, $this->lang->domains_edit_not_permitted);
		}

		$domain = $this->db->fetch("SELECT d.*, z.owner, u.user_name FROM domains d
		    LEFT JOIN zones z ON d.id=z.domain_id
		    LEFT JOIN users u ON u.user_id=z.owner
		    WHERE d.id=%d", $dom_id);

		$users = $this->htmlwidgets->select_users($domain['owner']);
		$types = $this->htmlwidgets->select_domain_types($domain['type']);

		$dom_records = $this->db->query("SELECT * FROM records WHERE domain_id=%d ORDER BY name ASC", $dom_id);
		while( $record = $this->db->nqfetch($dom_records) )
		{
			$rec_id = $record['id'];
			$records .= eval($this->template('DOMAINS_RECORD'));
		}
		return eval($this->template('DOMAINS_EDIT'));
	}

	function delete_domain()
	{
		$this->set_title($this->lang->domains_delete);

		if (!isset($this->get['id'])) {
			return $this->message($this->lang->domains_delete, $this->lang->domains_id_invalid);
		}

		$dom_id = $this->get['id'];

		// FALSE: Tells owner check to look at delete.
		if (!$this->is_owner($dom_id, false)) {
			return $this->message($this->lang->domains_delete, $this->lang->domains_delete_not_permitted);
		}

		$domain = $this->db->fetch("SELECT name FROM domains WHERE id=%d", $dom_id);

		if (!isset($this->get['confirm'])) {
			return $this->message($this->lang->domains_delete,
			$this->lang->domains_delete_confirm . " <b>{$domain['name']}</b> ?<br /><br />
			<a href='$this->self?a=domains&amp;s=delete&amp;id=$dom_id&amp;confirm=1'>{$this->lang->continue}</a>");
		}

		$this->db->query("DELETE FROM domains WHERE id=%d", $dom_id);
		$this->db->query("DELETE FROM records WHERE domain_id=%d", $dom_id);
		$this->db->query("DELETE FROM zones WHERE domain_id=%d", $dom_id);

		$this->log_action( "delete_domain_name", $dom_id );
		return $this->message($this->lang->domains_delete, $this->lang->domains_deleted, $this->lang->continue, "{$this->self}");
	}

	/**
	 * Checks if a domain name looks valid
	 *
	 * @param string $domain name to check
	 * @return true if the domain checks out
	 * @since 1.0
	 */
	function is_valid_domain($domain)
	{
		if( ( eregi( "^[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,4}$", $domain ) ) && ( strlen($domain) <= 64 ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Checks if an IP address looks valid
	 *
	 * @param string $ip address to check
	 * @return true if the address checks out
	 * @since 1.0
	 */
	function is_valid_ip($ip)
	{
		return( $ip == long2ip(ip2long($ip))) ? true : false;
	}

	function is_owner($dom_id, $edit)
	{
		$zone = $this->db->fetch("SELECT domain_id, owner FROM zones WHERE domain_id=%d", $dom_id);

		if ($zone['owner'] != $this->user['user_id'] && $edit) {
			if (!$this->perms->auth('edit_domains')) {
				return false;
			}
		}

		if ($zone['owner'] != $this->user['user_id'] && !$edit) {
			if (!$this->perms->auth('delete_domains')) {
				return false;
			}
		}
		return true;
	}

	// Lifted this from PowerAdmin
	function update_soa_serial( $domain_id )
	{
		$notify_serial = $this->db->fetch( "SELECT notified_serial FROM domains WHERE id=%d", $domain_id );
		$content = $this->db->fetch( "SELECT content FROM records WHERE type='SOA' AND domain_id=%d", $domain_id );

		$need_to_update = false;

		// Getting the serial field.
		$soa = explode( " ", $content['content'] );

		if( empty($notified_serial) ) {
			// Ok native replication, so we have to update.
			$need_to_update = true;
		} elseif( $notified_serial >= $soa[2] )	{
			$need_to_update = true;
		} elseif( strlen($soa[2]) != 10 ) {
			$need_to_update = true;
		} else {
			$need_to_update = false;
		}

   		if( $need_to_update ) {
			// Ok so we have to update it seems.
			$current_serial = $soa[2];

			// RFC1912 compliant date.
			$new_serial = date('Ymd'); // we will add revision number later
			if( strncmp( $new_serial, $current_serial, 8 ) === 0 ) {
				$revision_number = (int) substr( $current_serial, -2 );

				if( $revision_number == 99 )
					return false;
				++$revision_number;
				$new_serial .= str_pad( $revision_number, 2, "0", STR_PAD_LEFT );
			} else {
				/*
			         * Current serial is not RFC1912 compilant, so let's make a new one
			         */
				$new_serial .= '00';
			}
			$soa[2] = $new_serial; // change serial in SOA array
			$new_soa = "";
			// build new soa and update SQL after that
			for( $i = 0; $i < count($soa); $i++ )
			{
				$new_soa .= $soa[$i] . " ";
			}
			$this->db->query( "UPDATE records SET content='%s'
			    WHERE domain_id=%d AND type='SOA'", $new_soa, $domain_id );
		}
	}  
}
?>