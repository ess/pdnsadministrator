<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2011 Roger Libiez http://www.iguanadons.net
 *
 * Based on Quicksilver Forums
 * Copyright (c) 2005-2011 The Quicksilver Forums Development Team
 *  http://code.google.com/p/quicksilverforums/
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

class logs extends admin
{
	function execute()
	{
		$this->set_title($this->lang->logs_view);
		$this->tree($this->lang->logs_view);

		$data = $this->db->query('SELECT l.*, u.user_name FROM logs l, users u WHERE u.user_id=l.log_user ORDER BY l.log_time DESC');

		$this->lang->main();

		$this->get['min'] = isset($this->get['min']) ? intval($this->get['min']) : 0;
		$this->get['num'] = isset($this->get['num']) ? intval($this->get['num']) : 60;
		$pages = $this->htmlwidgets->get_pages( $data, 'a=logs', $this->get['min'], $this->get['num'] );

		$data = $this->db->query('SELECT l.*, u.user_name FROM logs l, users u WHERE u.user_id=l.log_user ORDER BY l.log_time DESC LIMIT %d, %d',
                       $this->get['min'], $this->get['num']);

		$out = null;
		while ($log = $this->db->nqfetch($data))
		{
			$date = $this->mbdate(DATE_LONG, $log['log_time']);
			$user = $log['user_name'];
			$action = '';
			$id = '';

			switch ($log['log_action'])
			{
			case 'change_owner':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_change_owner;
				$id = $dom_name['name'];
				break;

			case 'clone_domain':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_clone_domain;
				$id = $dom_name['name'];
				break;

			case 'new_domain':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_new_domain;
				$id = $dom_name['name'];
				break;

			case 'delete_domain_name':
				$action = $this->lang->logs_delete_domain;
				$id = $log['log_data1'];
				break;

			case 'new_reverse_domain':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_new_reverse_domain;
				$id = $dom_name['name'];
				break;

			case 'change_domain_type':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_change_type;
				$id = $dom_name['name'];
				break;

			case 'delete_A_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_delete_a_record;
				$id = $dom_name['name'];
				break;

			case 'delete_MX_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_delete_mx_record;
				$id = $dom_name['name'];
				break;

			case 'delete_NS_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_delete_ns_record;
				$id = $dom_name['name'];
				break;

			case 'delete_CNAME_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_delete_cname_record;
				$id = $dom_name['name'];
				break;

			case 'delete_TXT_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_delete_txt_record;
				$id = $dom_name['name'];
				break;

			case 'delete_PTR_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_delete_ptr_record;
				$id = $dom_name['name'];
				break;

			case 'delete_URL_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_delete_url_record;
				$id = $dom_name['name'];
				break;

			case 'delete_LOC_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_delete_loc_record;
				$id = $dom_name['name'];
				break;

			case 'delete_SRV_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_delete_srv_record;
				$id = $dom_name['name'];
				break;

			case 'delete_NAPTR_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_delete_naptr_record;
				$id = $dom_name['name'];
				break;

			case 'delete_SPF_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_delete_spf_record;
				$id = $dom_name['name'];
				break;

			case 'new_A_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_new_a_record;
				$id = $dom_name['name'];
				break;

			case 'new_MX_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_new_mx_record;
				$id = $dom_name['name'];
				break;

			case 'new_NS_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_new_ns_record;
				$id = $dom_name['name'];
				break;

			case 'new_CNAME_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_new_cname_record;
				$id = $dom_name['name'];
				break;

			case 'new_TXT_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_new_txt_record;
				$id = $dom_name['name'];
				break;

			case 'new_PTR_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_new_ptr_record;
				$id = $dom_name['name'];
				break;

			case 'new_URL_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_new_url_record;
				$id = $dom_name['name'];
				break;

			case 'new_LOC_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_new_loc_record;
				$id = $dom_name['name'];
				break;

			case 'new_SRV_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_new_srv_record;
				$id = $dom_name['name'];
				break;

			case 'new_NAPTR_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_new_naptr_record;
				$id = $dom_name['name'];
				break;

			case 'new_SPF_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_new_spf_record;
				$id = $dom_name['name'];
				break;

			case 'edit_A_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_edit_a_record;
				$id = $dom_name['name'];
				break;

			case 'edit_MX_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_edit_mx_record;
				$id = $dom_name['name'];
				break;

			case 'edit_NS_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_edit_ns_record;
				$id = $dom_name['name'];
				break;

			case 'edit_CNAME_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_edit_cname_record;
				$id = $dom_name['name'];
				break;

			case 'edit_TXT_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_edit_txt_record;
				$id = $dom_name['name'];
				break;

			case 'edit_PTR_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_edit_ptr_record;
				$id = $dom_name['name'];
				break;

			case 'edit_URL_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_edit_url_record;
				$id = $dom_name['name'];
				break;

			case 'edit_LOC_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_edit_loc_record;
				$id = $dom_name['name'];
				break;

			case 'edit_SRV_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_edit_srv_record;
				$id = $dom_name['name'];
				break;

			case 'edit_NAPTR_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_edit_naptr_record;
				$id = $dom_name['name'];
				break;

			case 'edit_SPF_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_edit_spf_record;
				$id = $dom_name['name'];
				break;

			case 'edit_SOA_record':
				$dom_name = $this->db->fetch( 'SELECT name FROM domains WHERE id=%d', $log['log_data1'] );
				$action = $this->lang->logs_edit_soa_record;
				$id = $dom_name['name'];
				break;

			default:
				$action = $log['log_action'];
				$id = $log['log_data1'];
			}

			$out .= eval($this->template('ADMIN_MOD_LOGS_ENTRY'));
		}
		return eval($this->template('ADMIN_MOD_LOGS'));
	}
}
?>