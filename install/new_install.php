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

if (!defined('PDNSADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

require_once $set['include_path'] . '/lib/packageutil.php';

/**
 * New Console Installation
 *
 * @author Jason Warner <jason@mercuryboard.com>
 * @author Roger Libiez [Samson] http://www.iguanadons.net
 */
class new_install extends pdnsadmin
{
	function server_url()
	{
	   $proto = "http" .
		   ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "s" : "") . "://";
	   $server = isset($_SERVER['HTTP_HOST']) ?
		   $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
	   return $proto . $server;
	}

	function install_console( $step, $mysqli, $sqlite, $pgsql )
	{
		switch($step) {
		default:
			$url = preg_replace('/install\/?$/i', '', $this->server_url() . dirname($_SERVER['PHP_SELF']));

echo "<form action='{$this->self}?mode=new_install&amp;step=2' method='post'>
 <div class='article'>
  <div class='title' style='text-align:center'>New {$this->name} Installation</div>
  <div class='title'>Directory Permissions</div>";

			check_writeable_files();
			if(!is_writeable('../settings.php')) {
				echo "<br /><br />Settings file cannot be written to. The installer cannot continue until this problem is corrected.";
				break;
			}

echo "    <p></p>
  <div class='title' style='text-align:center'>New Database Configuration</div>

  <span class='field'>Host Server:</span>
  <span class='form'><input class='input' type='text' name='db_host' value='{$this->sets['db_host']}' /></span>
  <p class='line'></p>

  <span class='field'>Database Type:</span>
  <span class='form'>
   <select name='dbtype'>";

  if ($mysqli) {
    echo "<option value='mysqli'>MySQLi</option>";
  } else {
    echo "<option value='mysql'>MySQL</option>";
  }
  if( $sqlite )
    echo "<option value='sqlite3'>SQLite3</option>";
  if( $pgsql )
    echo "<option value='pgsql'>pgSQL</option>";

  echo "</select>
  </span>
  <p class='line'></p>

  <span class='field'>Database Name:</span> For SQLite, this is the database filename you will be using. Will be stored in the sqlite3db folder, which must be writeable.
  <span class='form'><input class='input' type='text' name='db_name' value='{$this->sets['db_name']}' /></span>
  <p class='line'></p>

  <span class='field'>Database Username:</span>
  <span class='form'><input class='input' type='text' name='db_user' value='{$this->sets['db_user']}' /></span>
  <p class='line'></p>

  <span class='field'>Database Password:</span>
  <span class='form'><input class='input' type='password' name='db_pass' value='' /></span>
  <p class='line'></p>

  <span class='field'>Database Port:</span>
  <span class='form'><input class='input' type='text' name='db_port' value='{$this->sets['db_port']}' /> Blank for none</span>
  <p class='line'></p>

  <span class='field'>Database Socket:</span>
  <span class='form'><input class='input' type='text' name='db_socket' value='{$this->sets['db_socket']}' /> Blank for none</span>
  <p></p>

  <div class='title' style='text-align:center'>New Site Settings</div>

  <span class='field'>Site Name:</span>
  <span class='form'><input class='input' type='text' name='site_name' value='{$this->name}' size='75' /></span>
  <p class='line'></p>

  <span class='field'>Site URL:</span>
  <span class='form'><input class='input' type='text' name='site_url' value='{$url}' size='75' /></span>
  <p class='line'></p>

  <span class='field'>Master IP:</span>
  <span class='form'><input class='input' type='text' name='master_ip' value='' size='50' />Used for SLAVE domains</span>
  <p class='line'></p>

  <span class='field'>Primary Nameserver:</span>
  <span class='form'><input class='input' type='text' name='primary_ns' value='' size='50' /></span>
  <p class='line'></p>

  <span class='field'>Secondary Nameserver:</span>
  <span class='form'><input class='input' type='text' name='secondary_ns' value='' size='50' /></span>
  <p></p>

  <div class='title' style='text-align:center'>Administrator Account Settings</div>

  <span class='field'>User Name:</span>
  <span class='form'><input class='input' type='text' name='admin_name' size='30' maxlength='30' /></span>
  <p class='line'></p>

  <span class='field'>User Password:</span>
  <span class='form'><input class='input' type='password' name='admin_pass' size='30' /></span>
  <p class='line'></p>

  <span class='field'>Password (confirmation):</span>
  <span class='form'><input class='input' type='password' name='admin_pass2' size='30' /></span>
  <p class='line'></p>

  <span class='field'>Contact Email:</span>
  <span class='form'>
   <input class='input' type='text' name='admin_email' size='50' maxlength='100' value='{$this->sets['admin_email']}' />
   This is where messages from the system will be sent. Needs to be a real address.
  </span>
  <p class='line'></p>

  <span class='field'>System Email:</span>
  <span class='form'>
   <input class='input' type='text' name='contact_email' size='50' maxlength='100' />
   Address the system sends mail as. Can be either real or fake.
  </span>
  <p class='line'></p>

  <div style='text-align:center'>
   <input type='submit' name='submit' value='Continue' />
  </div>
 </div>
</form>";
break;

		case 2:
  echo "<div class='article'>
  <div class='title'>New {$this->name} Installation</div>";
			if( $this->post['dbtype'] == 'sqlite3' ) {
				if(!is_writeable('../sqlite3db/')) {
					echo "The sqlite3db folder is not writeable, your SQLite3 database file cannot be created. Please correct this error and try again.";
					break 2;
				}
				@unlink( '../sqlite3db/' . $this->post['db_name'] );
			}

			$db = new $this->modules['database']($this->post['db_name'], $this->post['db_user'], $this->post['db_pass'], $this->post['db_host'], $this->post['db_port'], $this->post['db_socket']);

			if (!$db->connection) {
				echo 'Could not connect to a database using the specified information.';
				break;
			}
			$this->db = &$db;

			$this->sets['db_name']   = $this->post['db_name'];
			$this->sets['db_user']   = $this->post['db_user'];
			$this->sets['db_pass']   = $this->post['db_pass'];
			$this->sets['db_host']   = $this->post['db_host'];
			$this->sets['db_port']   = $this->post['db_port'];
			$this->sets['db_socket'] = $this->post['db_socket'];
			$this->sets['dbtype']    = $this->post['dbtype'];
			$this->sets['admin_email'] = $this->post['admin_email'];

			if (!$this->write_db_sets('../settings.php') && !isset($this->post['downloadsettings'])) {
				echo "The database connection was ok, but settings.php could not be updated.<br />\n";
				echo "You can CHMOD settings.php to 0666 and hit reload to try again<br/>\n";
				echo "Or you can force the install to continue and download the new settings.php file ";
				echo "so you can later place it on the website manually<br/>\n";
				echo "<form action=\"{$this->self}?mode=new_install&amp;step=2\" method=\"post\">\n
					<input type=\"hidden\" name=\"downloadsettings\" value=\"yes\" />\n
					<input type=\"hidden\" name=\"db_name\" value=\"" . htmlspecialchars($this->post['db_name']) . "\" />\n
					<input type=\"hidden\" name=\"db_user\" value=\"" . htmlspecialchars($this->post['db_user']) . "\" />\n
					<input type=\"hidden\" name=\"db_pass\" value=\"" . htmlspecialchars($this->post['db_pass']) . "\" />\n
					<input type=\"hidden\" name=\"db_host\" value=\"" . htmlspecialchars($this->post['db_host']) . "\" />\n
					<input type=\"hidden\" name=\"db_port\" value=\"" . htmlspecialchars($this->post['db_port']) . "\" />\n
					<input type=\"hidden\" name=\"db_socket\" value=\"" . htmlspecialchars($this->post['db_socket']) . "\" />\n
					<input type=\"hidden\" name=\"dbtype\" value=\"" . htmlspecialchars($this->post['dbtype']) . "\" />\n
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

			if (!is_readable('./' . $this->sets['dbtype'] . '_data_tables.php')) {
				echo 'Database connected, settings written, but no tables could be loaded from file: ./' . $this->sets['dbtype'] . '_data_tables.php';
				break;
			}

			if (!is_readable('skin_default.xml')) {
				echo 'Database connected, settings written, but no templates could be loaded from file: skin_default.xml';
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

			$queries = array();

			// Create tables
			include './' . $this->sets['dbtype'] . '_data_tables.php';

			execute_queries($queries, $db);
			$queries = null;

			// Create template
			$xmlInfo = new xmlparser();
			$xmlInfo->parse('skin_default.xml');
			$templatesNode = $xmlInfo->GetNodeByPath('QSFMOD/TEMPLATES');
			packageutil::insert_templates('default', $this->db, $templatesNode);
			unset($templatesNode);
			$xmlInfo = null;

			$this->sets = $this->get_settings($this->sets);
			$this->sets['loc_of_board'] = $this->post['site_url'];
			$this->sets['forum_name'] = $this->post['site_name'];

			$this->post['admin_pass'] = md5($this->post['admin_pass']);

			$this->post['admin_name'] = str_replace(
				array('&amp;#', '\''),
				array('&#', '&#39;'),
				htmlspecialchars($this->post['admin_name'])
			);

			$server = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
			$this->sets['cookie_domain'] = $server;

			$path = dirname($_SERVER['PHP_SELF']);
			$path = str_replace( 'install', '', $path );
			$this->sets['cookie_path'] = $path;

			$this->sets['cookie_prefix'] = 'pdnsadmin_';
			$this->sets['cookie_secure'] = 0;

			$this->sets['logintime'] = 31536000;
			$this->sets['default_skin'] = 'default';
			$this->sets['default_lang'] = 'en';
			$this->sets['default_group'] = 3;
			$this->sets['users'] = 0;
			$this->sets['debug_mode'] = 0;
			$this->sets['mailserver'] = 'localhost';

			$this->db->dbquery("INSERT INTO users (user_name, user_password, user_group, user_email, user_created)
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
			$this->sets['admin_outgoing'] = $this->post['contact_email'];
			$this->sets['servertime'] = 0;
			$this->sets['app_version'] = $this->version;

			$writeSetsWorked = $this->write_db_sets('../settings.php');
			$this->write_sets();

			setcookie($this->sets['cookie_prefix'] . 'user', $admin_uid, $this->time + $this->sets['logintime'], $this->sets['cookie_path'], $this->sets['cookie_domain'], $this->sets['cookie_secure'], true );
			setcookie($this->sets['cookie_prefix'] . 'pass', $this->post['admin_pass'], $this->time + $this->sets['logintime'], $this->sets['cookie_path'], $this->sets['cookie_domain'], $this->sets['cookie_secure'], true );

			if (!$writeSetsWorked) {
				echo "Congratulations! Your site has been installed.<br />
				An administrator account was registered.<br />";
				echo "Click here to download your settings.php file. You must put this file on the webhost before the board is ready to use<br/>\n";
				echo "<form action=\"{$this->self}?mode=new_install&amp;step=3\" method=\"post\">\n
					<input type=\"hidden\" name=\"db_name\" value=\"" . htmlspecialchars($this->post['db_name']) . "\" />\n
					<input type=\"hidden\" name=\"db_user\" value=\"" . htmlspecialchars($this->post['db_user']) . "\" />\n
					<input type=\"hidden\" name=\"db_pass\" value=\"" . htmlspecialchars($this->post['db_pass']) . "\" />\n
					<input type=\"hidden\" name=\"db_host\" value=\"" . htmlspecialchars($this->post['db_host']) . "\" />\n
					<input type=\"hidden\" name=\"db_port\" value=\"" . htmlspecialchars($this->post['db_port']) . "\" />\n
					<input type=\"hidden\" name=\"db_socket\" value=\"" . htmlspecialchars($this->post['db_socket']) . "\" />\n
					<input type=\"hidden\" name=\"dbtype\" value=\"" . htmlspecialchars($this->post['dbtype']) . "\" />\n
					<input type=\"hidden\" name=\"admin_email\" value=\"" . htmlspecialchars($this->post['admin_email']) . "\" />\n
					<input type=\"submit\" value=\"Download settings.php\" />
					</form>
					<br/>\n
					Once this is done: <span style='color:yellow; font-weight:bold;'>REMEMBER TO DELETE THE INSTALL DIRECTORY!</span><br /><br />
					<a href='../index.php'>Go to your site.</a>
					 ";
			} else {
				echo "Congratulations! The install completed successfully.<br />
				An administrator account was registered.<br /><br />
				<span style='color:yellow; font-weight:bold;'>REMEMBER TO DELETE THE INSTALL DIRECTORY!</span><br /><br />
				<a href='../index.php'>Go to your site.</a>";
			}
			echo '</div>';

			break;

		case 3:
			// Give them the settings.php file
			$this->sets['db_name']   = $this->post['db_name'];
			$this->sets['db_user']   = $this->post['db_user'];
			$this->sets['db_pass']   = $this->post['db_pass'];
			$this->sets['db_host']   = $this->post['db_host'];
			$this->sets['db_port']   = $this->post['db_port'];
			$this->sets['db_socket'] = $this->post['db_socket'];
			$this->sets['dbtype']    = $this->post['dbtype'];
			$this->sets['admin_email'] = $this->post['admin_email'];

			$settingsFile = $this->create_settings_file();
			ob_clean();
			header("Content-type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"settings.php\"");
			echo $settingsFile;
			exit;
			break;
		}
	}
}
?>