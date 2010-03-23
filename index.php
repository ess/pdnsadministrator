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
 
define('PDNSADMIN', true);
define('PDNS_PUBLIC', true);

date_default_timezone_set('America/Los_Angeles');

$time_now   = explode(' ', microtime());
$time_start = $time_now[1] + $time_now[0];

srand((double)microtime() * 1234567);

require './settings.php';
$set['include_path'] = '.';
require_once $set['include_path'] . '/defaultutils.php';

if (!$set['installed']) {
	header('Location: ./install/index.php');
}

set_error_handler('error');

error_reporting(E_ALL);

// Open connection to database
$db = new $modules['database']($set['db_host'], $set['db_user'], $set['db_pass'], $set['db_name'], $set['db_port'], $set['db_socket']);
if (!$db->connection) {
    error(PDNSADMIN_ERROR, 'A connection to the database could not be established and/or the specified database could not be found.', __FILE__, __LINE__);
}
$settings = $db->fetch('SELECT settings_data FROM settings LIMIT 1');
$set = array_merge($set, unserialize($settings['settings_data']));

if (!isset($_GET['a']) || !in_array($_GET['a'], $modules['public_modules'])) {
	$module = $modules['default_module'];
} else {
	$module = $_GET['a'];
}

require './func/' . $module . '.php';

$pdns = new $module($db);

$pdns->get['a'] = $module;
$pdns->sets     = $set;
$pdns->modules  = $modules;

// If zlib isn't available, then trying to use it doesn't make much sense.
if (extension_loaded('zlib')) {
	if ($pdns->sets['output_buffer'] && isset($pdns->server['HTTP_ACCEPT_ENCODING']) && stristr($pdns->server['HTTP_ACCEPT_ENCODING'], 'gzip')) {
		if( !@ob_start('ob_gzhandler') ) {
			ob_start();
		}
	} else {
		ob_start();
	}
} else {
	ob_start();
}

header( 'P3P: CP="CAO PSA OUR"' );
session_start();

$pdns->user_cl = new $modules['user']($pdns);
$pdns->user    = $pdns->user_cl->login();
$pdns->lang    = $pdns->get_lang($pdns->user['user_language'], $pdns->get['a']);

if( !isset($_SESSION['login']) && $pdns->user['user_id'] != USER_GUEST_UID ) {
	$_SESSION['login'] = true;
	$pdns->db->query( "UPDATE users SET user_lastlogon=%d, user_lastlogonip='%s' WHERE user_id=%d", $pdns->time, $pdns->ip, $pdns->user['user_id'] );
}

if (!isset($pdns->get['skin'])) {
	$pdns->skin = $pdns->user['skin_dir'];
} else {
	$pdns->skin = $pdns->get['skin'];
}

$pdns->init();

$server_load = $pdns->get_load();

$output = $pdns->execute();
$users = $pdns->db->fetch( 'SELECT COUNT(user_id) count FROM users' );
$domains = $pdns->db->fetch( 'SELECT COUNT(id) count FROM domains' );

$users['count'] -= 1;

$userheader = eval($pdns->template('MAIN_HEADER_' . ($pdns->perms->is_guest ? 'GUEST' : 'MEMBER')));

$title = isset($pdns->title) ? $pdns->title : $pdns->sets['site_name'];

$time_now  = explode(' ', microtime());
$time_exec = round($time_now[1] + $time_now[0] - $time_start, 4);

if (isset($pdns->get['debug'])) {
	$output = $pdns->show_debug($server_load, $time_exec);
}

if (!$pdns->nohtml) {
	$servertime = $pdns->mbdate( DATE_LONG, $pdns->time, false );
	$copyright = eval($pdns->template('MAIN_COPYRIGHT'));
	$pdnspage = $output;
	echo eval($pdns->template('MAIN'));
} else {
	echo $output;
}

@ob_end_flush();
@flush();

// Do post output stuff
$pdns->cleanup();

// Close the DB connection.
$pdns->db->close();
?>