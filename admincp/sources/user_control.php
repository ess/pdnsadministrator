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

class user_control extends admin
{
	function execute()
	{
		$this->set_title($this->lang->mc);

		if (!isset($this->get['s'])) {
			$this->get['s'] = null;
		}

		if ($this->get['s'] == 'new') {
			$this->tree($this->lang->mc_add);

			if (!isset($this->post['submit'])) {
				$selectGroups = $this->htmlwidgets->select_groups(USER_MEMBER);
				$selectLangs = $this->htmlwidgets->select_langs($this->sets['default_lang'], '..');

				$token = $this->generate_token();

				return eval($this->template('ADMIN_USER_ADD'));
			}

			if( !$this->is_valid_token() ) {
				return $this->message( $this->lang->mc_add, $this->lang->invalid_token );
			}

			if (!isset($this->post['name']) || empty($this->post['name'])) {
				return $this->message($this->lang->mc_add, $this->lang->mc_user_name_required);
			}

			if (!isset($this->post['email']) || empty($this->post['email'])) {
				return $this->message($this->lang->mc_add, $this->lang->mc_user_email_required);
			}

			if ($this->db->fetch("SELECT user_id FROM users WHERE user_name='%s' LIMIT 1", $this->post['name'])) {
				return $this->message($this->lang->mc_add, sprintf($this->lang->mc_user_name_exists, $this->post['name']));
			}

			$name = $this->post['name'];
			$email = $this->post['email'];
			$group = $this->post['group'];
			$lang = $this->post['lang'];
			$newpass = $this->generate_pass(8);

			$this->db->query("INSERT INTO users (user_name, user_email, user_password, user_group, user_language, user_created)
			  VALUES( '%s', '%s', '%s', %d, '%s', %d )", $name, $email, md5($newpass), $group, $lang, $this->time );

			$this->sets['users'] += 1;
			$this->write_sets();

			$mailer = new $this->modules['mailer']($this->sets['admin_incoming'], $this->sets['admin_outgoing'], 'PDNS-Admin', false);

			$message  = "A new PDNS-Admin account has been set up for you.\n\n";
			$message .= "Your password has been set to:\n$newpass\n\n";
			$message .= "You may log into your account here:\n\n{$this->sets['site_url']}{$this->mainfile}?a=login";

			$mailer->setSubject('PDNS-Admin - New account setup');
			$mailer->setMessage($message);
			$mailer->setRecipient($email);
			$mailer->setServer($this->sets['mailserver']);
			$mailer->doSend();

			return $this->message($this->lang->mc_add, $this->lang->mc_user_new);
		}

		$this->tree($this->lang->mc, "$this->self?a=user_control&amp;s=profile");

		if (!isset($this->get['id'])) {
			if (!isset($this->post['username'])) {
				return $this->message($this->lang->mc, "
				<form action='{$this->self}?a=user_control&amp;s={$this->get['s']}' method='post'>
				<div>
					{$this->lang->mc_find}:<br /><br />
					<input type='text' name='username' size='30' class='input' />
					<input type='submit' name='submit' value='{$this->lang->submit}' />
				</div>
				</form>");
			} else {
				$query = $this->db->query("SELECT user_id, user_name FROM users WHERE user_name LIKE '%%%s%%' LIMIT 250", $this->post['username']);

				if (!$this->db->num_rows($query)) {
					return $this->message($this->lang->mc, "{$this->lang->mc_not_found} \"{$this->post['username']}\"");
				}

				$ret = null;

				if ($this->get['s'] == 'profile') {
					$link = 'a=user_control&amp;s=profile';
				} elseif ($this->get['s'] == 'perms') {
					$link = 'a=perms&amp;s=user';
				} else {
					$link = 'a=user_control&amp;s=delete';
				}

				while ($user = $this->db->nqfetch($query))
				{
					$ret .= "<a href='{$this->self}?$link&amp;id=" . $user['user_id'] . "'>{$user['user_name']}</a><br />";
				}

				return $this->message($this->lang->mc, "{$this->lang->mc_found}<br /><br />$ret");
			}
		}

		$this->get['id'] = intval($this->get['id']);

		switch ($this->get['s'])
		{
		case 'delete':
			$this->tree($this->lang->mc_delete);

			$this->get['id'] = intval($this->get['id']);

			if ($this->get['id'] == USER_GUEST_UID) {
				return $this->message($this->lang->mc_delete, $this->lang->mc_guest_needed);
			}

			if (!isset($this->post['confirm'])) {
				$token = $this->generate_token();
				$user = $this->db->fetch('SELECT user_name FROM users WHERE user_id=%d', $this->get['id']);

				return eval($this->template('ADMIN_USER_DELETE'));
			} else {
				if( !$this->is_valid_token() ) {
					return $this->message( $this->lang->mc_delete, $this->lang->invalid_token );
				}

				$this->db->query('UPDATE logs SET log_user=%d WHERE log_user=%d', USER_GUEST_UID, $this->get['id']);
				$this->db->query('DELETE FROM users WHERE user_id=%d', $this->get['id']);

				$user = $this->db->fetch('SELECT user_id, user_name FROM users ORDER BY user_id DESC LIMIT 1');
				$counts = $this->db->fetch('SELECT COUNT(user_id) AS count FROM users');

				$this->sets['users'] = $counts['count']-1;
				$this->write_sets();

				return $this->message($this->lang->mc_delete, $this->lang->mc_deleted);
			}
			break;

		case 'profile':
			$this->tree($this->lang->mc_edit);

			$this->get['id'] = intval($this->get['id']);

			if (!isset($this->post['submit'])) {
				$token = $this->generate_token();

				$user = $this->db->fetch('SELECT * FROM users WHERE user_id=%d LIMIT 1', $this->get['id']);

				$out = '';

				define('U_IGNORE', 0);
				define('U_TEXT', 1);
				define('U_BOOL', 2);
				define('U_BLOB', 3);
				define('U_DATE', 4);
				define('U_TIME', 5);
				define('U_FLOAT', 6);
				define('U_INT', 7);
				define('U_CALLBACK', 8);

				$cols = array(
					'user_name'		=> array($this->lang->mc_user_name, U_TEXT, 20),
					'user_email'		=> array($this->lang->mc_user_email, U_TEXT, 100),
					'user_group'		=> array($this->lang->mc_user_group, U_CALLBACK, 'list_groups'),
					'user_language'		=> array($this->lang->mc_user_language, U_CALLBACK, 'list_langs'),
					'user_skin'		=> array($this->lang->mc_user_skin, U_CALLBACK, 'list_skins'),
					'user_id'		=> array($this->lang->mc_user_id, U_IGNORE),
					'user_created'		=> array($this->lang->mc_user_created, U_TIME),
					'user_lastlogon'	=> array($this->lang->mc_user_lastlogon, U_TIME)
				);

				foreach ($cols as $var => $data)
				{
					if (!isset($user[$var])) {
						continue;
					}

					$val = $user[$var];

					if (($var == 'user_signature') || ($var == 'user_email') || ($var == 'user_title')) {
						$val = $this->format($val, FORMAT_HTMLCHARS);
					} elseif (($var == 'user_icq') && !$val) {
						$val = null;
					}

					$line = '';

					switch ($data[1])
					{
					case U_IGNORE:
						if (!isset($cols[$var][2])) {
							$line = $val;
						} else {
							if ($val) {
								$line = $this->lang->yes;
							} else {
								$line = $this->lang->no;
							}
						}
						break;

					case U_TIME:
						$line = $val ? $this->mbdate( DATE_LONG, $val ) : '-';
						break;

					case U_DATE:
						$line = $val ? $this->mbdate( DATE_ONLY_LONG, $val ) : '-';
						break;

					case U_BOOL:
						$line = '<select name="' . $var . '"><option value="1"' . ($val ? ' selected="selected"' : '') . '>' . $this->lang->yes .'</option><option value="0"' . (!$val ? ' selected="selected"' : '') . '>' . $this->lang->no . '</option></select>';
						break;

					case U_FLOAT:
						$cols[$var][2] += 3;

					case U_TEXT:
					case U_INT:
						$line = '<input class="input" type="text" name="'. $var . '" value="' . $val . '" size="50" maxlength="' . $cols[$var][2] . '" />';
						break;

					case U_BLOB:
						$line = '<textarea class="input" name="' . $var . '" rows="5" cols="49">' . $val . '</textarea>';
						break;

					case U_CALLBACK:
						$line = $this->{$cols[$var][2]}($val);
						break;

					default:
						$line = $val;
					}

					$out .= eval($this->template('ADMIN_USER_EDIT'));
				}

				return eval($this->template('ADMIN_USER_PROFILE'));
			} else {
				if( !$this->is_valid_token() ) {
					return $this->message( $this->lang->mc_edit, $this->lang->invalid_token );
				}

				$user = $this->db->fetch('SELECT user_name FROM users WHERE user_id=%d LIMIT 1', $this->get['id']);

				$guest_email = $this->post['user_email'];
				if ($user['user_name'] != 'Guest' && !$this->validator->validate($guest_email, TYPE_EMAIL)) {
					return $this->message($this->lang->mc_err_updating, $this->lang->mc_email_invaid);
				}

				$user_name = $this->format($this->post['user_name'], FORMAT_HTMLCHARS);
				$user_group = intval($this->post['user_group']);
				$user_language = $this->post['user_language'];
				$user_skin = $this->post['user_skin'];

				$this->db->query( "UPDATE users SET user_name='%s', user_email='%s', user_group=%d,
				  user_language='%s', user_skin='%s' WHERE user_id=%d",
				  $user_name, $guest_email, $user_group, $user_language, $user_skin, $this->get['id'] );

				return $this->message($this->lang->mc_edit, $this->lang->mc_edited);
			}
			break;

		default:
			return $this->message($this->lang->mc, "<a href='{$this->self}?a=user_control&amp;s=profile'>{$this->lang->mc_edit}</a><br />");
		}
	}

	function list_groups($val)
	{
		$out = "<select name='user_group'>";
		$groups = $this->db->query('SELECT group_name, group_id FROM groups ORDER BY group_name');

		while ($group = $this->db->nqfetch($groups))
		{
			$out .= "<option value='{$group['group_id']}'" . (($val == $group['group_id']) ? ' selected=\'selected\'' : '') . ">{$group['group_name']}</option>";
		}

		return $out . '</select>';
	}

	function list_skins($val)
	{
		$out = "<select name='user_skin'>";
		$groups = $this->db->query('SELECT skin_name, skin_dir FROM skins ORDER BY skin_name');

		while ($group = $this->db->nqfetch($groups))
		{
			$out .= "<option value='{$group['skin_dir']}'" . (($val == $group['skin_dir']) ? ' selected=\'selected\'' : '') . ">{$group['skin_name']}</option>";
		}

		return $out . '</select>';
	}

	function list_user_avatar_types($val)
	{
		$out = "<select name='user_avatar_type'>";
		$types = array('local', 'url', 'uploaded', 'none');

		foreach ($types as $type)
		{
			$out .= "<option value='$type'" . (($val == $type) ? ' selected=\'selected\'' : '') . ">$type</option>";
		}

		return $out . '</select>';
	}

	function list_langs($current)
	{
		$out = "<select name='user_language'>";
		$dir = opendir('../languages');

		while (($file = readdir($dir)) !== false)
		{
			if (is_dir('../languages/' . $file)) {
				continue;
			}

			$code = substr($file, 0, -4);
			$ext  = substr($file, -4);
			if ($ext != '.php') {
				continue;
			}

			$out .= '<option value="' . $code . '"' . (($code == $current) ? ' selected=\'selected\'' : null) . '>' . $this->htmlwidgets->get_lang_name($code) . "</option>\n";
		}

		return $out . '</select>';
	}
}
?>