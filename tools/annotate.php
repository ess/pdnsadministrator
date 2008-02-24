<?php
/**
 * Parses an annotated file from CVS
 * $Id: annotate.php,v 1.2 2003/10/11 18:29:29 jason Exp $
 **/

error_reporting(E_ALL);

$filename = './cvs.txt';

if (!file_exists($filename)) {
	exit ("Can't find <b>$filename</b>. This is the name of the annotated file to analyze.");
}

$lines = file($filename);

$data = array(
	'jason' => array(0, 0, 0),
	'melliot' => array(0, 0, 0),
	'mezz' => array(0, 0, 0)
);

$data2 = $data;
$total_lines = 0;

foreach ($lines as $line)
{
	preg_match('/^([0-9]\.[0-9]+)\s+\(([a-z0-9_]+)\s+([a-z0-9\-]+)\)/i', $line, $match);

	list($matched, $rev, $name, $date) = $match;

	if (isset($data[$name])) {
		$data[$name][0]++;

		if (($rev === '1.1') && (($name == 'jason') || ($name == 'melliot'))) {
			$data2['jason'][0] += 0.5;
			$data2['melliot'][0] += 0.5;
		} else {
			$data2[$name][0]++;
		}

		if (end(explode('.', $rev)) > end(explode('.', $data[$name][1]))) {
			$data[$name][1] = $rev;
			$data[$name][2] = $date;

			$data2[$name][1] = $rev;
			$data2[$name][2] = $date;
		}
	}

	$total_lines++;
}

echo '
<b><font size=5>As Annotated:</b></font><br>
<table border=1 cellpadding=5 cellspacing=0>
<tr>
	<td><b>Author</b></td>
	<td><b>Lines</b></td>
	<td><b>Last Version</b></td>
	<td><b>Last Date</b></td>
	<td><b>Percent Of Lines</b></td>
</tr>';

foreach ($data as $author => $commits)
{
	echo "
	<tr>
		<td>$author</td>
		<td>$commits[0]</td>
		<td>$commits[1]</td>
		<td>$commits[2]</td>
		<td>" . round($commits[0] / $total_lines * 100, 2) . "%</td>
	</tr>";
}

echo '</table>';

//---------

echo '
<br><br><b><font size=5>If jason and melliot each did 50% of version 1.1:</b></font><br>
<table border=1 cellpadding=5 cellspacing=0>
<tr>
	<td><b>Author</b></td>
	<td><b>Lines</b></td>
	<td><b>Last Version</b></td>
	<td><b>Last Date</b></td>
	<td><b>Percent Of Lines</b></td>
</tr>';

foreach ($data2 as $author => $commits)
{
	echo "
	<tr>
		<td>$author</td>
		<td>$commits[0]</td>
		<td>$commits[1]</td>
		<td>$commits[2]</td>
		<td>" . round($commits[0] / $total_lines * 100, 2) . "%</td>
	</tr>";
}

echo '</table>';
?>