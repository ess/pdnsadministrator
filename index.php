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

define('PDNSADMIN', true);
define('PDNS_PUBLIC', true);

date_default_timezone_set('America/Los_Angeles');

$time_now   = explode(' ', microtime());
$time_start = $time_now[1] + $time_now[0];

srand((double)microtime() * 1234567);

require './settings.php';
$set['include_path'] = '.';
require_once $set['include_path'] . '/defaultutils.php';
require_once $set['include_path'] . '/global.php';

if (!$set['installed']) {
	header('Location: ./install/index.php');
}

set_error_handler('error');

error_reporting(E_ALL);

// Open connection to database
$db = new $modules['database']($set['db_name'], $set['db_user'], $set['db_pass'], $set['db_host'], $set['db_port'], $set['db_socket']);
if (!$db->connection) {
    error(PDNSADMIN_ERROR, 'A connection to the database could not be established and/or the specified database could not be found.', __FILE__, __LINE__);
}

/*
 * Logic here:
 * If 'a' is set, but the module doesn't exist, it's either a malformed URL or a bogus request.
 * Otherwise $missing remains false and no error is generated later.
 */
$missing = false;
if (!isset($_GET['a']) ) {
	$module = $modules['default_module'];
} elseif ( !file_exists( 'func/' . $_GET['a'] . '.php' ) ) {
	$module = $modules['default_module'];
	$missing = true;
} else {
	$module = $_GET['a'];
}

if ( strstr($module, '/') || strstr($module, '\\') ) {
	header('HTTP/1.0 403 Forbidden');
	exit( 'You have been banned from this site.' );
}

require './func/' . $module . '.php';

$pdns = new $module($db);

$pdns->get['a'] = $module;
$pdns->sets     = $pdns->get_settings($set);
$pdns->modules  = $modules;

ob_start('ob_gzhandler');

header( 'P3P: CP="CAO PSA OUR"' );
session_start();

$pdns->user_cl = new $modules['user']($pdns);
$pdns->user    = $pdns->user_cl->login();
$pdns->lang    = $pdns->get_lang($pdns->user['user_language'], $pdns->get['a']);

if( !isset($_SESSION['login']) && $pdns->user['user_id'] != USER_GUEST_UID ) {
	$_SESSION['login'] = true;
	$pdns->db->dbquery( "UPDATE users SET user_lastlogon=%d, user_lastlogonip='%s' WHERE user_id=%d", $pdns->time, $pdns->ip, $pdns->user['user_id'] );
}

if (!isset($pdns->get['skin'])) {
	$pdns->skin = $pdns->user['skin_dir'];
} else {
	$pdns->skin = $pdns->get['skin'];
}

$pdns->init();

$server_load = $pdns->get_load();

if( $missing ) {
	header( 'HTTP/1.0 404 Not Found' );
	$output = $pdns->message( $pdns->lang->error, $pdns->lang->error_404 );
} else {
	$output = $pdns->execute();
}

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