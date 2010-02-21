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
define('PDNS_ADMIN', true);

$time_now   = explode(' ', microtime());
$time_start = $time_now[1] + $time_now[0];

require '../settings.php';
$set['include_path'] = '..';
require_once $set['include_path'] . '/defaultutils.php';

if (!$set['installed']) {
	header('Location: ../install/index.php');
}

ob_start();
session_start();

set_error_handler('error');

error_reporting(E_ALL);
set_magic_quotes_runtime(0);

if (!isset($_GET['a']) || !in_array($_GET['a'], $modules['admin_modules'])) {
	$module = $modules['default_admin_module'];
} else {
	$module = $_GET['a'];
}

require './sources/' . $module . '.php';

$db = new $modules['database']($set['db_host'], $set['db_user'], $set['db_pass'], $set['db_name'], $set['db_port'], $set['db_socket']);

if (!$db->connection) {
	exit('<center><font face="verdana" size="4" color="#000000"><b>A connection to the database could not be established and/or the specified database could not be found.</font></center>');
}

$admin = new $module($db);

$admin->get['a'] = $module;
$admin->sets     = $admin->get_settings($set);
$admin->modules  = $modules;
$admin->user_cl  = new $admin->modules['user']($admin);
$admin->user     = $admin->user_cl->login();
$admin->lang     = $admin->get_lang($admin->user['user_language'], $admin->get['a']);
$server_load     = $admin->get_load();

if (!isset($admin->get['skin'])) {
	$admin->skin = $admin->user['skin_dir'];
} else {
	$admin->skin = $admin->get['skin'];
}

$admin->init();

$output = $admin->execute();

$title = isset($pdns->title) ? $pdns->title : $admin->name .' Admin CP';

$time_now  = explode(' ', microtime());
$time_exec = round(($time_now[1] + $time_now[0]) - $time_start, 4);

if (!$admin->nohtml) {
	$admin_main = $output . eval($admin->template('ADMIN_COPYRIGHT'));
	echo eval($admin->template('ADMIN_INDEX'));
} else {
	echo $output;
}
?>