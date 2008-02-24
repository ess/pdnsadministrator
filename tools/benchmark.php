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
 * Benchmarks MercuryBoard
 *
 */

if (!isset($_GET['loops'])) {
	exit('Usage:<br>benchmark.php?loops=<i>INT</i><br><br>The query string will also be applicable for index.php, so you can use any parameters you like.');
}

if ($_GET['loops'] <= 0) {
	exit('Done.');
}

ob_start('ob_gzhandler');

chdir('..');
require './index.php';

ob_end_clean();

$fp = fopen('./tools/benchmark_results.txt', 'a');
fwrite($fp, (($time_now[1] + $time_now[0]) - $time_start) . "\n");
fclose($fp);

echo '<script>window.location.href="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . str_replace('loops=' . $_GET['loops'], 'loops=' . ($_GET['loops']-1), $_SERVER['QUERY_STRING']) . '"</script>';
?>