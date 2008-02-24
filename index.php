<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2007 Roger Libiez http://www.iguanadons.net
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
 
define('QUICKSILVERFORUMS', true);
define('QSF_PUBLIC', true);

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
set_magic_quotes_runtime(0);

// Open connection to database
$db = new $modules['database']($set['db_host'], $set['db_user'], $set['db_pass'], $set['db_name'], $set['db_port'], $set['db_socket']);
if (!$db->connection) {
    error(QUICKSILVER_ERROR, 'A connection to the database could not be established and/or the specified database could not be found.', __FILE__, __LINE__);
}
$settings = $db->fetch("SELECT settings_data FROM settings LIMIT 1");
$set = array_merge($set, unserialize($settings['settings_data']));

if (!isset($_GET['a']) || !in_array($_GET['a'], $modules['public_modules'])) {
	$module = $modules['default_module'];
} else {
	$module = $_GET['a'];
}

require './func/' . $module . '.php';

$qsf = new $module($db);

$qsf->get['a'] = $module;
$qsf->sets     = $set;
$qsf->modules  = $modules;

// If zlib isn't available, then trying to use it doesn't make much sense.
if (extension_loaded('zlib')) {
	if ($qsf->sets['output_buffer'] && isset($qsf->server['HTTP_ACCEPT_ENCODING']) && stristr($qsf->server['HTTP_ACCEPT_ENCODING'], 'gzip')) {
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

$qsf->user_cl = new $modules['user']($qsf);
$qsf->user    = $qsf->user_cl->login();
$qsf->lang    = $qsf->get_lang($qsf->user['user_language'], $qsf->get['a']);
$qsf->session = &$_SESSION;
$qsf->session['id'] = session_id();

if( !isset($qsf->session['login']) && $qsf->user['user_id'] != USER_GUEST_UID ) {
	$qsf->session['login'] = true;
	$qsf->db->query( "UPDATE users SET user_lastlogon=%d WHERE user_id=%d", $qsf->time, $qsf->user['user_id'] );
}

if (!isset($qsf->get['skin'])) {
	$qsf->skin = $qsf->user['skin_dir'];
} else {
	$qsf->skin = $qsf->get['skin'];
}

$qsf->init();

$server_load = $qsf->get_load();

$reminder = null;
$reminder_text = null;

if ($reminder_text) {
	$reminder = eval($qsf->template('MAIN_REMINDER'));
}

$output = $qsf->execute();

$userheader = eval($qsf->template('MAIN_HEADER_' . ($qsf->perms->is_guest ? 'GUEST' : 'MEMBER')));

$title = isset($qsf->title) ? $qsf->title : $qsf->sets['site_name'];

$time_now  = explode(' ', microtime());
$time_exec = round($time_now[1] + $time_now[0] - $time_start, 4);

if (isset($qsf->get['debug'])) {
	$output = $qsf->show_debug($server_load, $time_exec);
}

if (!$qsf->nohtml) {
	$servertime = $qsf->mbdate( DATE_LONG, $qsf->time, false );
	$copyright = eval($qsf->template('MAIN_COPYRIGHT'));
	$quicksilverforums = $output;
	echo eval($qsf->template('MAIN'));
} else {
	echo $output;
}

@ob_end_flush();
@flush();

// Do post output stuff
$qsf->cleanup();
?>