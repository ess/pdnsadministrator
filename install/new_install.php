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
require_once $set['include_path'] . '/lib/xmlparser.php';
require_once $set['include_path'] . '/lib/packageutil.php';

/**
 * New Board Installation
 *
 * @author Jason Warner <jason@mercuryboard.com>
 */
class new_install extends pdnsadmin
{
	function install_console( $step )
	{
		switch($step) {
		default:
			$url = preg_replace('/install\/?$/i', '', $this->server_url() . dirname($_SERVER['PHP_SELF']));

			echo "<form action='{$this->self}?mode=new_install&amp;step=2' method='post'>
                              <table border='0' cellpadding='4' cellspacing='0'>\n";

			check_writeable_files();

			echo "    <tr>
        <td class='subheader' colspan='2'>New Database Configuration</td>
    </tr>
    <tr>
        <td><b>Host Server</b></td>
        <td><input class='input' type='text' name='db_host' value='{$this->sets['db_host']}' /></td>
    </tr>
    <tr>
        <td><b>Database Name</b></td>
        <td><input class='input' type='text' name='db_name' value='{$this->sets['db_name']}' /></td>
    </tr>
    <tr>
        <td><b>Database Username</b><br /><span class='tiny'>Username used by PowerDNS to access the database.</span></td>
        <td><input class='input' type='text' name='db_user' value='{$this->sets['db_user']}' /></td>
    </tr>
    <tr>
        <td><b>Database Password</b><br /><span class='tiny'>Password used by PowerDNS to access the database.</span></td>
        <td><input class='input' type='password' name='db_pass' value='' /></td>
    </tr>
    <tr>
        <td><b>Database Port</b><br /><span class='tiny'>Blank for none</span></td>
        <td><input class='input' type='text' name='db_port' value='{$this->sets['db_port']}' /></td>
    </tr>
    <tr>
        <td><b>Database Socket</b><br /><span class='tiny'>Blank for none</span></td>
        <td><input class='input' type='text' name='db_socket' value='{$this->sets['db_socket']}' /></td>
    </tr>
    <tr>
        <td class='subheader' colspan='2'>New Site Settings</td>
    </tr>
    <tr>
        <td><input class='input' type='text' name='site_name' value='PDNS-Admin' size='50' /></td>
        <td><b>Site Name</b></td>
    </tr>
    <tr>
        <td><input class='input' type='text' name='site_url' value='{$url}' size='50' /></td>
        <td><b>Site URL</b></td>
    </tr>
    <tr>
        <td><input class='input' type='text' name='master_ip' value='' size='50' /></td>
        <td><b>Master IP</b><br /><span class='tiny'>Used for SLAVE domains</span></td>
    </tr>
    <tr>
        <td><input class='input' type='text' name='primary_ns' value='' size='50' /></td>
        <td><b>Primary Nameserver</b></td>
    </tr>
    <tr>
        <td><input class='input' type='text' name='secondary_ns' value='' size='50' /></td>
        <td><b>Secondary Nameserver</b></td>
    </tr>
    <tr>
        <td class='subheader' colspan='2'>Administrator Account Settings</td>
    </tr>
    <tr>
        <td><input class='input' type='text' name='admin_name' /></td>
        <td><b>User Name</b></td>
    </tr>
    <tr>
        <td><input class='input' type='password' name='admin_pass' /></td>
        <td><b>Password</b></td>
    </tr>
    <tr>
        <td><input class='input' type='password' name='admin_pass2' /></td>
        <td><b>Password (confirmation)</b></td>
    </tr>
    <tr>
        <td><input class='input' type='text' name='admin_email' /></td>
        <td><b>Email</b></td>
    </tr>
	<tr>
                         <td class='subheader' colspan='2' align='center'><input type='submit' value='Continue' /></td>
                         </tr>
                         </table>
                         </form>";
			break;

		case 2:
			$db = new $this->modules['database']($this->post['db_host'], $this->post['db_user'], $this->post['db_pass'], $this->post['db_name'], $this->post['db_port'], $this->post['db_socket']);

			if (!$db->connection) {
				echo 'Could not connect to a database using the specified information: <br />' . mysql_error();
				break;
			}
			$this->db = &$db;

			$this->sets['db_host']   = $this->post['db_host'];
			$this->sets['db_user']   = $this->post['db_user'];
			$this->sets['db_pass']   = $this->post['db_pass'];
			$this->sets['db_name']   = $this->post['db_name'];
			$this->sets['db_port']   = $this->post['db_port'];
			$this->sets['db_socket'] = $this->post['db_socket'];

			if (!$this->write_db_sets('../settings.php') && !isset($this->post['downloadsettings'])) {
				echo "The database connection was ok, but settings.php could not be updated.<br />\n";
				echo "You can CHMOD settings.php to 0666 and hit reload to try again<br/>\n";
				echo "Or you can force the install to continue and download the new settings.php file ";
				echo "so you can later place it on the website manually<br/>\n";
				echo "<form action=\"{$this->self}?mode=new_install&amp;step=2\" method=\"post\">\n
					<input type=\"hidden\" name=\"downloadsettings\" value=\"yes\" />\n
					<input type=\"hidden\" name=\"db_host\" value=\"" . htmlspecialchars($this->post['db_host']) . "\" />\n
					<input type=\"hidden\" name=\"db_name\" value=\"" . htmlspecialchars($this->post['db_name']) . "\" />\n
					<input type=\"hidden\" name=\"db_user\" value=\"" . htmlspecialchars($this->post['db_user']) . "\" />\n
					<input type=\"hidden\" name=\"db_pass\" value=\"" . htmlspecialchars($this->post['db_pass']) . "\" />\n
					<input type=\"hidden\" name=\"db_port\" value=\"" . htmlspecialchars($this->post['db_port']) . "\" />\n
					<input type=\"hidden\" name=\"db_socket\" value=\"" . htmlspecialchars($this->post['db_socket']) . "\" />\n
					<input type=\"hidden\" name=\"site_name\" value=\"" . htmlspecialchars($this->post['site_name']) . "\" />\n
					<input type=\"hidden\" name=\"site_url\" value=\"" . htmlspecialchars($this->post['site_url']) . "\" />\n
					<input type=\"hidden\" name=\"admin_name\" value=\"" . htmlspecialchars($this->post['admin_name']) . "\" />\n
					<input type=\"hidden\" name=\"admin_pass\" value=\"" . htmlspecialchars($this->post['admin_pass']) . "\" />\n
					<input type=\"hidden\" name=\"admin_pass2\" value=\"" . htmlspecialchars($this->post['admin_pass2']) . "\" />\n
					<input type=\"hidden\" name=\"admin_email\" value=\"" . htmlspecialchars($this->post['admin_email']) . "\" />\n
					";
				echo "<input type=\"submit\" value=\"Force Install\" />
					</form>
					 ";
				break;
			}

			$filename = './' . $this->sets['dbtype'] . '_data_tables.php';
			if (!is_readable($filename)) {
				echo 'Database connected, settings written, but no tables could be loaded from file: ' . $filename;
				break;
			}

			if (!is_readable(SKIN_FILE)) {
				echo 'Database connected, settings written, but no templates could be loaded from file: ' . SKIN_FILE;
				break;
			}

			if ((trim($this->post['admin_name']) == '')
			|| (trim($this->post['admin_pass']) == '')
			|| (trim($this->post['admin_email']) == '')) {
				echo 'You have not specified an admistrator account. Please go back and correct this error.';
				break;
			}

			if ($this->post['admin_pass'] != $this->post['admin_pass2']) {
				echo 'Your administrator passwords do not match. Please go back and correct this error.';
				break;
			}

			// Build settings step by step
			$this->sets['cookie_path'] = '/';
			$this->sets['cookie_prefix'] = 'pdns_';
			$this->sets['cookie_domain'] = '';
			$this->sets['cookie_secure'] = 0;
			$this->sets['logintime'] = 31536000;
			$this->sets['output_buffer'] = (extension_loaded('zlib') ? 1 : 0);
			$this->sets['default_skin'] = 'default';
			$this->sets['default_lang'] = 'en';
			$this->sets['default_group'] = 3;
			$this->sets['users'] = 0;
			$this->sets['debug_mode'] = 0;
			$this->sets['mailserver'] = 'localhost';

			$settings = serialize($this->sets);

			$queries = array();

			// Create tables
			include './' . $this->sets['dbtype'] . '_data_tables.php';

			execute_queries($queries, $db);
			$queries = null;
			
			// Create template
			$xmlInfo = new xmlparser();
			$xmlInfo->parse(SKIN_FILE);
			$templatesNode = $xmlInfo->GetNodeByPath('QSFMOD/TEMPLATES');
			packageutil::insert_templates('default', $this->db, $templatesNode);
			unset($templatesNode);
			$xmlInfo = null;

			$this->sets = $this->get_settings($this->sets);

			$this->post['admin_pass'] = md5($this->post['admin_pass']);

			if (get_magic_quotes_gpc()) {
				$this->unset_magic_quotes_gpc($this->get);
				$this->unset_magic_quotes_gpc($this->post);
				$this->unset_magic_quotes_gpc($this->cookie);
			}

			$this->post['admin_name'] = str_replace(
				array('&amp;#', '\''),
				array('&#', '&#39;'),
				htmlspecialchars($this->post['admin_name'])
			);

			$this->db->query("INSERT INTO users (user_name, user_password, user_group, user_email, user_created)
				VALUES ('%s', '%s', %d, '%s', %d)",
				$this->post['admin_name'], $this->post['admin_pass'], USER_ADMIN, $this->post['admin_email'], $this->time);
			$admin_uid = $this->db->insert_id("users");

			$this->sets['users']++;
			$this->sets['installed'] = 1;
			$this->sets['site_url'] = $this->post['site_url'];
			$this->sets['site_name'] = $this->post['site_name'];
			$this->sets['domain_master_ip'] = $this->post['master_ip'];
			$this->sets['primary_nameserver'] = $this->post['primary_ns'];
			$this->sets['secondary_nameserver'] = $this->post['secondary_ns'];
			$this->sets['tertiary_nameserver'] = '';
			$this->sets['quaternary_nameserver'] = '';
			$this->sets['quinary_nameserver'] = '';
			$this->sets['senary_nameserver'] = '';
			$this->sets['septenary_nameserver'] = '';
			$this->sets['octonary_nameserver'] = '';
			$this->sets['default_ttl'] = 7200;
			$this->sets['soa_retry'] = 3600;
			$this->sets['soa_refresh'] = 10800;
			$this->sets['soa_expire'] = 1814400;
			$this->sets['domains_per_page'] = 50;
			$this->sets['records_per_page'] = 50;
			$this->sets['admin_incoming'] = $this->post['admin_email'];
			$this->sets['admin_outgoing'] = $this->post['admin_email'];
			$this->sets['servertime'] = 0;

			$writeSetsWorked = $this->write_db_sets('../settings.php');
			$this->write_sets();

			if( version_compare( PHP_VERSION, '5.2.0', '<' ) ) {
				setcookie($this->sets['cookie_prefix'] . 'user', $admin_uid, $this->time + $this->sets['logintime'], $this->sets['cookie_path'], $this->sets['cookie_domain'].'; HttpOnly', $this->sets['cookie_secure']);
				setcookie($this->sets['cookie_prefix'] . 'pass', $this->post['admin_pass'], $this->time + $this->sets['logintime'], $this->sets['cookie_path'], $this->sets['cookie_domain'].'; HttpOnly', $this->sets['cookie_secure']);
			} else {
				setcookie($this->sets['cookie_prefix'] . 'user', $admin_uid, $this->time + $this->sets['logintime'], $this->sets['cookie_path'], $this->sets['cookie_domain'], $this->sets['cookie_secure'], true );
				setcookie($this->sets['cookie_prefix'] . 'pass', $this->post['admin_pass'], $this->time + $this->sets['logintime'], $this->sets['cookie_path'], $this->sets['cookie_domain'], $this->sets['cookie_secure'], true );
			}

			if (!$writeSetsWorked) {
				echo "Congratulations! The install completed successfully.<br />
				An administrator account was registered.<br />";
				echo "Click here to download your settings.php file. You must put this file on the webhost before the admin panel is ready to use<br/>\n";
				echo "<form action=\"{$this->self}?mode=new_install&amp;step=3\" method=\"post\">\n
					<input type=\"hidden\" name=\"db_host\" value=\"" . htmlspecialchars($this->post['db_host']) . "\" />\n
					<input type=\"hidden\" name=\"db_name\" value=\"" . htmlspecialchars($this->post['db_name']) . "\" />\n
					<input type=\"hidden\" name=\"db_user\" value=\"" . htmlspecialchars($this->post['db_user']) . "\" />\n
					<input type=\"hidden\" name=\"db_pass\" value=\"" . htmlspecialchars($this->post['db_pass']) . "\" />\n
					<input type=\"hidden\" name=\"db_port\" value=\"" . htmlspecialchars($this->post['db_port']) . "\" />\n
					<input type=\"hidden\" name=\"db_socket\" value=\"" . htmlspecialchars($this->post['db_socket']) . "\" />\n
					<input type=\"submit\" value=\"Download settings.php\" />
					</form>
					<br/>\n
					Once this is done: REMEMBER TO DELETE THE INSTALL DIRECTORY!<br /><br />
					<a href='../index.php'>Go to main page.</a>
					 ";
			} else {
				echo "Congratulations! The install completed successfully.<br />
				An administrator account was registered.<br />
				REMEMBER TO DELETE THE INSTALL DIRECTORY!<br /><br />
				<a href='../index.php'>Go to main page.</a>";
			}
			break;
		case 3:
			// Give them the settings.php file
			$this->sets['db_host']   = $this->post['db_host'];
			$this->sets['db_user']   = $this->post['db_user'];
			$this->sets['db_pass']   = $this->post['db_pass'];
			$this->sets['db_name']   = $this->post['db_name'];
			$this->sets['db_port']   = $this->post['db_port'];
			$this->sets['db_socket'] = $this->post['db_socket'];

			$settingsFile = $this->create_settings_file();
			ob_clean();
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"settings.php\"");
			echo $settingsFile;
			exit;
			break;
		}
	}

	function server_url()
	{ 
	   $proto = "http" .
		   ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "s" : "") . "://";
	   $server = isset($_SERVER['HTTP_HOST']) ?
		   $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
	   return $proto . $server;
	}
}
?>