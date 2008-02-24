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

/**
 * Executes an array of queries
 *
 * @param array $queries Queries to execute
 * @param object $db Database connection
 * @return void
 **/
function execute_queries($queries, $db)
{
	foreach ($queries as $query)
	{
		$db->query($query);
	}
}

function check_writeable_files()
{
	// Need to check to see if the necessary directories are writeable.
	$writeable = true;
	$fixme = '';

	if(!is_writeable('../skins')) {
		$fixme .= "../skins/<br />";
		$writeable = false;
	}

	if(!is_writeable('../packages')) {
		$fixme .= "../packages/<br />";
		$writeable = false;
	}

	if( !$writeable ) {
		echo "<tr><td colspan='2'>The following directories are missing or not writeable. Some functions will be impaired unless these are changed to 0777 permission.</td></tr>";
                echo "<tr><td colspan='2'><span style='font-weight:bold; color:red'>" . $fixme . "</span></td></tr>";
	}
}

ob_start();
error_reporting(E_ALL);

require_once '../settings.php';
$set['include_path'] = '..';
require_once $set['include_path'] . '/defaultutils.php';
require_once $set['include_path'] . '/global.php';

define('INSTALLER', 1); // Used in query files
define('SKIN_FILE', 'skin_default.xml');

// Check for any addons available
include_addons($set['include_path'] . '/addons/');

$self   = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : 'index.php';
$failed = false;

if (!isset($_GET['mode'])) {
	$mode = null;
} else {
	$mode = $_GET['mode'];
}

if (!isset($_GET['step'])) {
	$step = 1;
} else {
	$step = $_GET['step'];
}

if ($mode) {
	require $set['include_path'] . '/install/' . $mode . '.php';
	$qsf = new $mode;
} else {
	$qsf = new qsfglobal;
}

include 'templates/header.php';

if (substr(PHP_VERSION, 0, 1) == '3') {
	echo 'Your PHP version is ' . PHP_VERSION . '.<br />Currently only PHP4 and PHP5 are supported.';
	$failed = true;
} else if (version_compare(PHP_VERSION, '4.3.0') == -1) {
	echo 'Your PHP version is ' . PHP_VERSION . '.<br />Currently only PHP 4.3.0 and higher are supported.';
	$failed = true;
}

if (!extension_loaded('mysql')) {
	if ($failed) { // If we have already shown a message, show the next one two lines down
		echo '<br /><br />';
	}

	echo 'Your PHP installation does not support MySQL.<br />Currently only MySQL is supported.';
	$failed = true;
}

if ($failed) {
	echo "<br /><br /><b>To run Quicksilver Forums and other advanced PHP software, the above error(s) must be fixed by your web host.</b>";
} else {
	$qsf->sets = $set;
	$qsf->modules = $modules;

	switch($mode) {
	case '':
		include 'templates/installtype.php';
		break;

	case 'new_install':
		$qsf->install_board($step);
		break;

	case 'upgrade':
		$qsf->upgrade_board($step);
		break;

	case 'convert':
		$qsf->convert_board($step);
		break;
	}
}

include 'templates/footer.php';
?>
