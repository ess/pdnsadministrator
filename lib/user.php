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

if (!defined('PDNSADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

/**
 * User class
 *
 * @author Mark Elliot <mark.elliot@mercuryboard.com>
 * @since 1.0.1
 **/
class user
{
	/**
	 * Constructor
	 *
	 * @param $pdns - PDNS-Admin module
	 **/
	function user(&$pdns)
	{
		$this->db  = &$pdns->db;
		$this->pre = &$pdns->pre;
		$this->session = &$pdns->session;
		$this->server  = &$pdns->server;
		$this->cookie  = &$pdns->cookie;
		$this->sets    = &$pdns->sets;
		$this->time    = &$pdns->time;
		$this->ip      = &$pdns->ip;
	}

	/**
	 * Check for a session or cookie for a logged in user and return it or return
	 * the guest user using USER_GUEST_UID
	 *
	 * @return user record set
	 **/
	function login()
	{
		if(isset($this->cookie[$this->sets['cookie_prefix'] . 'user']) && isset($this->cookie[$this->sets['cookie_prefix'] . 'pass'])) {
			$cookie_user = intval($this->cookie[$this->sets['cookie_prefix'] . 'user']);
			$cookie_pass = $this->cookie[$this->sets['cookie_prefix'] . 'pass'];
			$user = $this->db->fetch("SELECT m.*, s.skin_dir, g.group_perms, g.group_name
				FROM users m, skins s, groups g
				WHERE m.user_id='%s' AND m.user_password='%s' AND s.skin_dir=m.user_skin AND g.group_id=m.user_group LIMIT 1",
				$cookie_user, $cookie_pass);

		}else if(isset($this->session['user']) && isset($this->session['pass'])) {
			$session_user = intval($this->session['user']);
			$session_pass = $this->session['pass'];
			$user = $this->db->fetch("SELECT m.*, s.skin_dir, g.group_perms, g.group_name
				FROM users m, skins s, groups g
				WHERE m.user_id='%s' AND MD5(CONCAT(m.user_password,'%s'))='%s' AND s.skin_dir=m.user_skin AND g.group_id=m.user_group LIMIT 1",
				$session_user, $this->ip, $session_pass);

		}else {
			$user = $this->db->fetch("SELECT m.*, s.skin_dir, g.group_perms, g.group_name
				FROM users m, skins s, groups g WHERE m.user_id=" . USER_GUEST_UID . " AND s.skin_dir=m.user_skin AND g.group_id=m.user_group LIMIT 1");
			$user['user_language'] = $this->get_browser_lang($this->sets['default_lang']);
		}

		if (!isset($user['user_id'])) {
			$user = $this->db->fetch("SELECT m.*, s.skin_dir, g.group_perms, g.group_name
			FROM users m, skins s, groups g
			WHERE m.user_id=%d AND s.skin_dir=m.user_skin AND g.group_id=m.user_group LIMIT 1",
			USER_GUEST_UID);
			if( version_compare( PHP_VERSION, '5.2.0', '<' ) ) {
				setcookie($this->sets['cookie_prefix'] . 'user', '', $this->time - 9000, $this->sets['cookie_path'], $this->sets['cookie_domain'].'; HttpOnly', $this->sets['cookie_secure']);
				setcookie($this->sets['cookie_prefix'] . 'pass', '', $this->time - 9000, $this->sets['cookie_path'], $this->sets['cookie_domain'].'; HttpOnly', $this->sets['cookie_secure']);
			} else {
				setcookie($this->sets['cookie_prefix'] . 'user', '', $this->time - 9000, $this->sets['cookie_path'], $this->sets['cookie_domain'], $this->sets['cookie_secure'], true );
				setcookie($this->sets['cookie_prefix'] . 'pass', '', $this->time - 9000, $this->sets['cookie_path'], $this->sets['cookie_domain'], $this->sets['cookie_secure'], true );
			}
			unset($_SESSION['user']);
			unset($_SESSION['pass']);
			$user['user_language'] = $this->get_browser_lang($this->sets['default_lang']);
		}
		return $user;
	}
    
   	/**
	 * Look at the information the browser has sent and try and find a language
	 *
	 * @param $deflang Fallback language to use
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.1.5
	 * @return character code for language to use
	 **/
	function get_browser_lang($deflang)
	{
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && strlen($_SERVER['HTTP_ACCEPT_LANGUAGE']) >= 2) {
			return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
		}
		return $deflang;
	}
}
?>