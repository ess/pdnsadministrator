<?php
/**
 * Quicksilver Forums
 * Copyright (c) 2005 The Quicksilver Forums Development Team
 *  http://www.quicksilverforums.com/
 * 
 * based off MercuryBoard
 * Copyright (c) 2001-2005 The Mercury Development Team
 *  http://www.mercuryboard.com/
 *
 * Uninstalls Quicksilver Forums
 **/

require '../settings.php';

$connection = mysql_connect($set['db_host'], $set['db_user'], $set['db_pass']);
mysql_select_db($set['db_name']);

$tables = implode(', ', array(
	$set['prefix'] . 'active',
	$set['prefix'] . 'attach',
	$set['prefix'] . 'forums',
	$set['prefix'] . 'groups',
	$set['prefix'] . 'help',
	$set['prefix'] . 'logs',
	$set['prefix'] . 'membertitles',
	$set['prefix'] . 'pmsystem',
	$set['prefix'] . 'posts',
	$set['prefix'] . 'replacements',
	$set['prefix'] . 'settings',
	$set['prefix'] . 'skins',
	$set['prefix'] . 'subscriptions',
	$set['prefix'] . 'templates',
	$set['prefix'] . 'topics',
	$set['prefix'] . 'users',
	$set['prefix'] . 'votes'
));

mysql_query('DROP TABLE ' . $tables);

echo "Quicksilver Forums uninstalled.<br><a href='../install.php'>Reinstall</a>";
?>