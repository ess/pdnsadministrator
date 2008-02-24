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

 /**
 * Generates query files for Quicksilver Forums
 **/

@set_time_limit(0);
error_reporting(E_ALL);

$mtime     = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];

require '../settings.php';
require './dump_functions.php';

if ($_SERVER['QUERY_STRING'] == '') {
	$action = 'templates';
} else {
	$action = 'tables';
}

$showcolumns = 'yes';

$db     = $set['db_name'];
$prefix = $set['prefix'];
$asfile = 'sendit';
$drop   = 1;

$sql1   = '../install/data_tables.php';
$sql2   = '../install/data_templates.php';

$connection = mysql_connect($set['db_host'], $set['db_user'], $set['db_pass']);
mysql_select_db($db);

// Structure
$table_select1 = array(
	"{$prefix}active"        => 1,
	"{$prefix}attach"        => 1,
	"{$prefix}forums"        => 1,
	"{$prefix}groups"        => 1,
	"{$prefix}help"          => 1,
	"{$prefix}logs"          => 1,
	"{$prefix}membertitles"  => 1,
	"{$prefix}pmsystem"      => 1,
	"{$prefix}posts"         => 1,
	"{$prefix}replacements"  => 1,
	"{$prefix}settings"      => 1,
	"{$prefix}skins"         => 1,
	"{$prefix}subscriptions" => 1,
	"{$prefix}templates"     => 1,
	"{$prefix}timezones"     => 1,
	"{$prefix}topics"        => 1,
	"{$prefix}users"         => 1,
	"{$prefix}votes"         => 1
);

// Data
$table_select2 = array(
	"{$prefix}groups"       => 1,
	"{$prefix}help"         => 1,
	"{$prefix}membertitles" => 1,
	"{$prefix}replacements" => 1,
	"{$prefix}settings"     => 1,
	"{$prefix}skins"        => 1,
	"{$prefix}timezones"    => 1,
	"{$prefix}users"        => 1
);

$dump1  = makedump($table_select1, 'structure', $db);
$dump1 .= makedump($table_select2, 'dataonly', $db);

$dump2  = makedump(array("{$prefix}templates" => 1), 'dataonly', $db);

define_mysql_version();

$queries1 = array();
$queries2 = array();
PMA_splitSqlFile($queries1, $dump1, PMA_MYSQL_INT_VERSION);
PMA_splitSqlFile($queries2, $dump2, PMA_MYSQL_INT_VERSION);

$out1 = '<?php
/**
 * Quicksilver Forums
 * Copyright (c) 2005 The Quicksilver Forums Development Team
 *  http://www.quicksilverforums.com/
 * 
 * based off MercuryBoard
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

if (!defined(\'QUICKSILVERFORUMS\')) {
	header(\'HTTP/1.0 403 Forbidden\');
	die;
}

if (!defined(\'INSTALLER\')) {
	exit(\'Use index.php to install.\');
}' . "\n\n";

$out2 = $out1;

foreach ($queries1 as $query)
{
	$query = str_replace('"', '\\"', $query);
	$query = str_replace('$', '\\$', $query);
	$query = preg_replace('/ ENGINE(.+)/', ' TYPE=MyISAM', $query);
	$query = str_replace("DROP TABLE IF EXISTS $prefix", 'INSERT INTO {$pre}', $query);
	$query = str_replace("CREATE TABLE $prefix", 'INSERT INTO {$pre}', $query);
	$query = str_replace("INSERT INTO $prefix", 'INSERT INTO {$pre}', $query);

	$out1 .= '$queries[] = "' . $query . "\";\n";

	if (strpos($query, "\n") !== false) {
		$out1 .= "\n";
	}
}

$templates = array();

foreach ($queries2 as $query)
{
	$query = str_replace('"', '\\"', $query);
	$query = str_replace('$', '\\$', $query);

	preg_match("/'([A-Z_]+?)',/", $query, $template_name);

	$templates[] = '$queries[\'' . $template_name[1] . '\'] = "' . $query . "\";\n";
}

sort($templates);

foreach ($templates as $template)
{
	$out2 .= str_replace(array('\r\n', '\n'), array("\n", "\n"), $template);
}

$out1 = preg_replace('/(TABLE|EXISTS|INTO) {$prefix}/', '\\1 {$pre}', $out1);
$out2 = preg_replace("/INTO $prefix/", 'INTO {$pre}', $out2);

$out1 .= '?>';
$out2 .= '?>';

if ($action == 'tables') {
	$fp = fopen($sql1, 'w');
	fwrite($fp, $out1);
	fwrite($fp, "\n");
	fclose($fp);
} else {
	$fp = fopen($sql2, 'w');
	fwrite($fp, $out2);
	fwrite($fp, "\n");
	fclose($fp);
}

$mtime     = explode(' ', microtime());
$totaltime = round(($mtime[1] + $mtime[0]) - $starttime, 3);

echo "Done!<br />$totaltime seconds<br /><br />Without a query string, templates are generated. ($sql2)<br />With a query string, install data is generated. ($sql1)";

?>
