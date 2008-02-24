<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2008 Roger Libiez http://www.iguanadons.net
 *
 * Based on Quicksilver Forums
 * Copyright (c) 2005 The Quicksilver Forums Development Team
 *  http://www.quicksilverforums.com/
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
 
$modules = array();

// The below libraries can be replaced with children that customise behavior
require_once $set['include_path'] . '/lib/' . $set['dbtype'] . '.php';
$modules['database'] = 'db_' . $set['dbtype'];
require_once $set['include_path'] .  '/lib/perms.php';
$modules['permissions'] = 'permissions';
require_once $set['include_path'] .  '/lib/user.php';
$modules['user'] = 'user';
require_once $set['include_path'] .  '/lib/mailer.php';
$modules['mailer'] = 'mailer';
require_once $set['include_path'] . '/lib/htmlwidgets.php';
$modules['widgets'] = 'htmlwidgets';
require_once $set['include_path'] . '/lib/templater.php';
$modules['templater'] = 'templater';
require_once $set['include_path'] . '/lib/tool.php';
$modules['validator'] = 'tool';

// Other variables that we can allow addons to change
$modules['default_module'] = 'main';
$modules['default_admin_module'] = 'home';
$modules['public_modules'] = array(
	'cp',
	'domains',
	'email',
	'login',
	'users');

$modules['admin_modules'] = array(
	'backup',
	'groups',
	'Admin',
	'logs',
	'user_control',
	'optimize',
	'perms',
	'php_info',
	'query',
	'settings',
	'supermaster',
	'templates');

// These are generic enough that you shouldn't need to customise them
require_once $set['include_path'] . '/lib/xmlparser.php';
require_once $set['include_path'] . '/func/constants.php';
require_once $set['include_path'] . '/lib/globalfunctions.php';
?>