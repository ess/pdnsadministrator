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

/**
 * The PDNS-Admin Core
 *
 * @author Jason Warner <jason@mercuryboard.com>
 * @author Roger Libiez [Samson] http://www.iguanadons.net
 * @since 1.0
 **/
class pdnsadmin
{
	var $name    = 'PDNS-Admin';      // The name of the software @var string
	var $version = 1.21;              // PDNS-Admin version @var string
	var $server  = array();           // Alias for $_SERVER @var array
	var $get     = array();           // Alias for $_GET @var array
	var $post    = array();           // Alias for $_POST @var array
	var $cookie  = array();           // Alias for $_COOKIE @var array
	var $user    = array();           // Information about the user @var array
	var $sets    = array();           // Settings @var array
	var $modules = array();           // Module Settings @var array

	var $nohtml  = false;             // To display no board wrapper @var bool
	var $time;                        // The current Unix time @var int
	var $ip;                          // The user's IP address @var string
	var $agent;                       // The browser's user agent @var string
	var $self;                        // Alias for $PHP_SELF @var string
	var $db;                          // Database object @var object
	var $perms;                       // Permissions object @var object
	var $skin;                        // The user's selected skin @var string
	var $table;                       // Start to an HTML table @var string
	var $etable;                      // End to an HTML table @var string
	var $lang;                        // Loaded words @var object
	var $query;                       // The query string @var string
	var $mainfile = 'index.php';	  // Combined with set['loc_of_board'] to make full url

	var $htmlwidgets;		  // HTML widget handler @var object
	var $templater;			  // Template handler @var object
	var $validator;			  // Handler for checking usernames, passwords, etc
	
	var $debug_mode = false;	  // Switch to tell if debugging info is allowed

	/**
	 * Constructor; sets up variables
	 *
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 **/
	function pdnsadmin($db=null)
	{
		$this->db      = $db;
		$this->time    = time();
		$this->query   = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null;
		$this->ip      = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
		$this->agent   = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '-';
		$this->agent   = substr($this->agent, 0, 99); // Cut off after 100 characters.
		$this->self    = $_SERVER['PHP_SELF'];
		$this->server  = $_SERVER;
		$this->get     = $_GET;
		$this->post    = $_POST;
		$this->cookie  = $_COOKIE;
		$this->query   = htmlspecialchars($this->query);
	}

	/**
	 * Post constructor initaliser. By this time we have a user and a database
	 *
	 * Note: This is never run for special tools such as installs or upgrades
	 *
	 * @param bool $admin Set to true if we need to setup admin templates
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.2
	 **/
	function init($admin = false)
	{
		if ($this->sets['debug_mode']) {
			$this->debug_mode = true;
		}

		$this->perms = new $this->modules['permissions']($this);
		
		$this->htmlwidgets = new $this->modules['widgets']($this);
		$this->templater = new $this->modules['templater']($this);
		$this->validator = new $this->modules['validator']();

		$this->templater->init_templates($this->get['a'], $admin);
		
		$this->set_table();
	}

	/**
	 * Run actions that can be delayed until after output is sent
	 *
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.2.0
	 **/
	function cleanup()
	{
	}

	/**
	 * Set values for $this->table and $this->etable
	 *
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.2.0
	 **/
	function set_table()
	{
		$this->table  = eval($this->template('MAIN_TABLE'));
		$this->etable = eval($this->template('MAIN_ETABLE'));
	}

	/**
	 * Get the template for eval (templater interface)
	 *
	 * @param string $piece Name of the template to return
	 **/
	function template($piece)
	{
		return $this->templater->template($piece);
	}

	/**
	 * Formats a string
	 *
	 * @param string $in Input
	 * @param int $options Options
	 * @return string Formatted string
	 **/
	function format($in, $options = 0)
	{
		$maxwordsize = 40; // Maximum size a word can get before it's cut into a abbr tag

		if (!$options) {
			$this->options = FORMAT_BREAKS | FORMAT_HTMLCHARS;
		}

		$strtr = array();

		if( $options & FORMAT_HTMLCHARS ) {
			$in = htmlentities($in, ENT_COMPAT, 'UTF-8');
		}

		if ($options & FORMAT_BREAKS) {
			$strtr["\n"] = "<br />\n";
		}

		$in = strtr($in, $strtr);

		return $in;
	}

	/**
	 * Generates a random pronounceable password
	 *
	 * @param int $length Length of password
	 * @author http://www.zend.com/codex.php?id=215&single=1
	 * @since 1.1.0
	 */
	function generate_pass($length)
	{
		$vowels = array('a', 'e', 'i', 'o', 'u');
		$cons = array('b', 'c', 'd', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'u', 'v', 'w', 'tr',
		'cr', 'br', 'fr', 'th', 'dr', 'ch', 'ph', 'wr', 'st', 'sp', 'sw', 'pr', 'sl', 'cl');

		$num_vowels = count($vowels);
		$num_cons = count($cons);

		$password = '';

		for ($i = 0; $i < $length; $i++)
		{
			$password .= $cons[rand(0, $num_cons - 1)] . $vowels[rand(0, $num_vowels - 1)];
		}

		return substr($password, 0, $length);
	}

	/**
	 * Loads a user_language. Bet you couldn't figure that out...
	 *
	 * @param string $lang Language to load
	 * @param string $a Word set to load
	 * @param string $path Path to the user_languages directory
	 * @param bool $main Load main universal strings
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 3.0
	 * @return object Language
	 **/
	function get_lang($lang, $a = null, $path = './', $main = true)
	{
		if (isset($this->get['lang'])) {
			$lang = $this->get['lang'];
		}

		if (strstr($lang, '/') || strstr($lang, '\\') || !file_exists($path . 'languages/' . $lang . '.php')) {
			$lang = 'en';
		}

		include $path . 'languages/' . $lang . '.php';
		$obj = new $lang();

		// Check if language function is available before running it
		if ($a && is_callable(array($obj,$a))) {
			$obj->$a();
		}

		if ($main) {
			$obj->main();
		}
		$obj->universal();
		return $obj;
	}

	/**
	 * Retrieves the current server load
	 *
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return int Server load
	 **/
	function get_load()
	{
		if (get_cfg_var('safe_mode') || stristr(PHP_OS, 'WIN')) {
			return 0;
		}

		if (@file_exists('/proc/loadavg')) {
			$file = @fopen('/proc/loadavg', 'r');

			if (!$file) {
				return 0;
			}

			$load = explode(' ', fread($file, 6));
			fclose($file);
		} else {
			$load = @exec('uptime');

			if (!$load) {
				return 0;
			}

			$load = split('load averages?: ', $load);
			$load = explode(',', $load[1]);
		}

		return trim($load[0]);
	}

	/**
	 * Loads settings
	 *
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since 1.1.0
	 * @return array Settings
	 **/
	function get_settings($sets)
	{
		$settings = $this->db->fetch('SELECT settings_data FROM settings LIMIT 1');

		if( !is_array($settings) )
			return $sets;

		return array_merge($sets, unserialize($settings['settings_data']));
	}

	/**
	 * Used as a replacement for date() which deals with time zones
	 *
	 * @param mixed $format Date format using date() keywords. Either a date constant or a string.
	 * @param int $time Timestamp. If left out, uses current time
	 * @param bool $useToday true if dates should substitute date with 'today' or 'yesterday'
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.1
	 * @return string Human-readable, formatted Unix timestamp
	 **/
	function mbdate($format, $time = 0, $useToday = true)
	{
		if (!$time) {
			$time = $this->time;
		}

		$tz_adjust = $this->sets['servertime'] * 3600;
		$time += $tz_adjust;

		// DST adjustment if needed. Yes, it needs to know the current time so it can trick old posts into looking right.
		if( date( 'I', $this->time ) == 1 ) {
			$time += 3600;
		}

		if (is_int($format)) {
			switch($format)
			{
			case DATE_LONG:
				$date_format = $this->lang->date_long;
				$time_format = $this->lang->time_long;
				break;

			case DATE_SHORT:
				$date_format = $this->lang->date_short;
				$time_format = $this->lang->time_long;
				break;

			case DATE_ONLY_LONG:
				$date_format = $this->lang->date_long;
				$time_format = '';
				break;

			case DATE_TIME:
				$date_format = '';
				$time_format = $this->lang->time_only;
				break;

			case DATE_ISO822: // Standard, no localisation
				$date_format = 'D, j M Y';
				$time_format = ' H:i:s T';
				break;

			default: // DATE_ONLY_SHORT
				$date_format = $this->lang->date_short;
				$time_format = '';
				break;
			}

			if (!$useToday) {
				$date = gmdate($date_format, $time);
			} else if ($date_format) {
				$date = gmdate($date_format, $time);
				$today = gmdate($date_format, $this->time + $tz_adjust);
				$yesterday = gmdate($date_format, ($this->time - DAY_IN_SECONDS) + $tz_adjust);

				if ($today == $date) {
					$date = $this->lang->today;
				} elseif ($yesterday == $date) {
					$date = $this->lang->yesterday;
				}
			} else {
				$date = '';
			}

			return $date . gmdate($time_format, $time);
		} else {
			return gmdate($format, $time);
		}
	}

	/**
	 * Formats a message, error, or notice
	 *
	 * @param string $title Title of the message
	 * @param string $message Text of the message
	 * @param string $link_text Text for a link
	 * @param string $link Destination for a link
	 * @param string $redirect Target for an automated redirect
	 * @param int $delay Sets an optional delay for automated redirect
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return string HTML-formatted message
	 **/
	function message($title, $message, $link_text = null, $link = null, $redirect = null, $delay = 4)
	{
		if ($link_text) {
			$message .= '<br /><br /><a href="' . $link . '">' . $link_text . '</a>';
		}

		if ($redirect) {
			@header('Refresh: '.$delay.';url=' . $redirect);
		}

		return eval($this->template('MAIN_MESSAGE'));
	}

	/**
	 * Sets the title of the page
	 *
	 * @param string $title The title
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return void
	 **/
	function set_title($title)
	{
		$this->title = "PDNS-Admin - $title";
	}

	/**
	 * Handles debug information when $debug is set in the query string
	 *
	 * @param int $load Server load
	 * @param int $totaltime Time to execute the board
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.0
	 * @return void
	 **/
	function show_debug($load, $totaltime)
	{
		include './func/debug.php';

		return $out; // This is set in debug.php
	}

	/**
	 * Creates the contents of a settings file
	 *
	 * @since 1.3.0
	 * @return string Contents for settings file
	 **/
	function create_settings_file()
	{
		$settings = array(
			'db_name'   => $this->sets['db_name'],
			'db_pass'   => $this->sets['db_pass'],
			'db_user'   => $this->sets['db_user'],
			'db_host'   => $this->sets['db_host'],
			'db_port'   => $this->sets['db_port'],
			'db_socket' => $this->sets['db_socket'],
			'dbtype'    => $this->sets['dbtype'],
			'installed' => $this->sets['installed'],
			'admin_email' => $this->sets['admin_email']
			);
				
		$file = "<?php
if (!defined('PDNSADMIN')) {
       header('HTTP/1.0 403 Forbidden');
       die;
}

\$set = array();

";
		foreach ($settings as $set => $val)
		{
			$file .= "\$set['$set'] = '" . str_replace(array('\\', '\''), array('\\\\', '\\\''), $val) . "';\n";
		}

		$file .= '?' . '>';
		return $file;
	}

	/**
	 * Saves all data in the $this->sets array into a file
	 *
	 * @param string $sfile File to write settings into (default is settings.php)
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since 1.1.0
	 * @return bool True on success, false on failure
	 **/
	function write_db_sets($sfile = './settings.php')
	{
		$settings = $this->create_settings_file();

		$fp = @fopen($sfile, 'w');

		if (!$fp) {
			return false;
		}

		if (!@fwrite($fp, $settings)) {
			return false;
		}

		fclose($fp);

		return true;
	}

	/**
	 * Saves all data in the $this->sets array to the database
	 *
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since Beta 2.1
	 * @return void
	 **/
	function write_sets()
	{
		$db_settings = array(
			'db_host',
			'db_name',
			'db_pass',
			'db_port',
			'db_socket',
			'db_user',
			'dbtype',
			'installed',
			'include_path',
			'admin_email'
		);

		$sets = array();
		foreach ($this->sets as $set => $val)
		{
			if (!in_array($set, $db_settings)) {
				$sets[$set] = $val;
			}
		}

		$this->db->dbquery("UPDATE settings SET settings_data='%s'", serialize($sets));
	}

	/**
	 * Generates a random security token for forms.
	 *
	 * @author Roger Libiez
	 * @return string Generated security token.
	 * @since 1.1.9
	 */
	function generate_token()
	{
		$token = md5(uniqid(mt_rand(), true));
		$_SESSION['token'] = $token;
		$_SESSION['token_time'] = $this->time + 7200; // Token is valid for 2 hours.

		return $token;
	}

	/**
	 * Checks to be sure a submitted security token matches the one the form is expecting.
	 *
	 * @author Roger Libiez
	 * @return false if invalid, true if valid
	 * @since 1.1.9
	 */
	function is_valid_token()
	{
		if( !isset($_SESSION['token']) || !isset($_SESSION['token_time']) || !isset($this->post['token']) ) {
			return false;
		}

		if( $_SESSION['token'] != $this->post['token'] ) {
			return false;
		}

		$age = $this->time - $_SESSION['token_time'];

		if( $age > 7200 )
			return false;

		return true;
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
		if( ( preg_match( "/^([a-z0-9]([-a-z0-9]*[a-z0-9])?\\.)+((a[cdefgilmnoqrstuwxz]|aero|arpa)|(b[abdefghijmnorstvwyz]|biz)|(c[acdfghiklmnorsuvxyz]|cat|com|coop)|d[ejkmoz]|(e[ceghrstu]|edu)|f[ijkmor]|(g[abdefghilmnpqrstuwy]|gov)|h[kmnrtu]|(i[delmnoqrst]|info|int)|(j[emop]|jobs)|k[eghimnprwyz]|l[abcikrstuvy]|(m[acdghklmnopqrstuvwxyz]|mil|mobi|museum)|(n[acefgilopruz]|name|net)|(om|org)|(p[aefghklmnrstwy]|pro)|qa|r[eouw]|s[abcdeghijklmnortvyz]|(t[cdfghjklmnoprtvwz]|travel)|u[agkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw])$/i", $domain ) ) && ( strlen($domain) <= 64 ) ) {
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
	function is_valid_ip($ip, $type = 'A')
	{
		if( $type == 'A' ) {
			if( strpos( $ip, '.' ) === false )
				return false;

			return( $ip == @inet_ntop(@inet_pton($ip))) ? true : false;
		} else if( $type == 'AAAA' ) {
			if( strpos( $ip, ':' ) === false )
				return false;
			if( strpos( $ip, '.' ) !== false )
				return false;

			$newip = eregi_replace( "^0*([A-Fa-f0-9])", "\\1", $ip );
			$newip = eregi_replace( ":0*([A-Fa-f1-9])", ":\\1", $newip );
			$newip = eregi_replace( ":+0+(:0+)*:0+:+", "::", $newip );

			return( $newip == @inet_ntop(@inet_pton($newip))) ? true : false;
		}
		return false;
	}

	/**
	 * Adds a moderator log entry
	 *
	 * @param string $action The action that was taken
	 * @param int $data1 The data acted upon (post ID, forum ID, etc)
	 * @param int $data2 Additional data, if necessary
	 * @param int $data3 Additional data, if necessary
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since 1.1.0
	 * @return void
	 **/
	function log_action($action, $data1, $data2 = 0, $data3 = 0)
	{
		$this->db->dbquery("INSERT INTO logs (log_user, log_time, log_action, log_data1, log_data2, log_data3)
			VALUES (%d, %d, '%s', %d, %d, %d)",
			$this->user['user_id'], $this->time, $action, $data1, $data2, $data3);
	}
}
?>