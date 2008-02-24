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
 * Searches and replaces in Quicksilver Forums templates
 **/

$search  = array();
$replace = array();

@set_time_limit(0);
set_magic_quotes_runtime(0);

$mtime     = explode(' ', microtime());
$starttime = $mtime[1] + $mtime[0];

require '../settings.php';

$connection = mysql_connect($set['db_host'], $set['db_user'], $set['db_pass']);
mysql_select_db($set['db_name']);

$x = 0;
$items = array();

$query = mysql_query('SELECT * FROM '.$set['prefix'].'templates');
while ($temp = mysql_fetch_array($query))
{
	$q = mysql_query('UPDATE '.$set['prefix'].'templates SET template_html="' . addslashes(str_replace($search, $replace, $temp['template_html'])) . '"
	WHERE
	    template_skin="' . $temp['template_skin'] . '" AND
		template_set="' . $temp['template_set'] . '" AND
		template_name="' . $temp['template_name'] . '"');


	if (mysql_affected_rows()) {
		$x++;
		$items[] = $temp['template_name'];
	}
}

$mtime     = explode(' ', microtime());
$totaltime = round(($mtime[1] + $mtime[0]) - $starttime, 2);

echo "Replacements made in $x templates<br>Time: $totaltime seconds<br><br>";

foreach ($items as $item)
{
	echo "$item<br>";
}
?>