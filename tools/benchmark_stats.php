<?php
/**
 * Interprets benchmark results from benchmark_results.txt
 * $Id: benchmark_stats.php,v 1.2 2003/05/18 01:25:37 jason Exp $
 */

$times = file('benchmark_results.txt');
$total = 0;

foreach ($times as $time) $total += $time;

echo 'Average execution time was: ' . $total / (count($times)-1);
?>