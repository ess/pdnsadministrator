<?php
/**
 * Identifies inconsistencies between words used in the code and the words
 * available in en.php
 *
 * $Id: lang_match.php,v 1.7 2003/10/11 18:22:23 jason Exp $
 */

ob_start('ob_gzhandler');
error_reporting(E_ALL);

set_magic_quotes_runtime(0);
set_time_limit(0);

$pieces_set  = array();

$pieces_used = array();
$pieces_used_files = array();

$untranslated = array();

$dp = opendir('../languages');
while (($file = readdir($dp)) !== false)
{
	if (($file == 'CVS') || ($file == '.') || ($file == '..')) {
		continue;
	}

	$fp = fopen('../languages/' . $file, 'r');
	$contents = fread($fp, filesize('../languages/' . $file));
	fclose($fp);

	preg_match_all('!\$this->(.+?) = .+? //Translate!', $contents, $to_translate);

	foreach ($to_translate[1] as $trans_phrase)
	{
		if (!isset($untranslated[$file])) {
			$untranslated[$file] = array();
		}

		$untranslated[$file][] = $trans_phrase;
	}
}
closedir($dp);

function traverse($dir)
{
	$dp = opendir($dir);
	while (($file = readdir($dp)) !== false)
	{
		if (($file == 'CVS') || ($file == 'languages') || ($file == 'tools') || ($file == '.') || ($file == '..')) {
			continue;
		}

		$name = "$dir/$file";
		if (is_dir($name)) {
			traverse($name);
		} else {
			$ext = substr($file, -4);
			if (($ext == '.php') || ($ext == '.sql')) {
				$fp = fopen($name, 'r');
				$contents = fread($fp, filesize($name));
				fclose($fp);

				preg_match_all('/\$(this|qsf|admin)->lang->([a-zA-Z0-9_]+)/', $contents, $matches);

				foreach ($matches[2] as $match)
				{
					$GLOBALS['pieces_used'][] = $match;
					$GLOBALS['pieces_used_files'][$match] = $file;
				}
			}
		}
	}
}

traverse('..');

$pieces_used = array_unique($pieces_used);

$fp = fopen('../languages/en.php', 'r');
$contents = fread($fp, filesize('../languages/en.php'));
fclose($fp);

preg_match_all('/\$this->([a-zA-Z0-9_]+)/', $contents, $matches);

foreach ($matches[1] as $match)
{
	$pieces_set[] = $match;
}

/**
 * Written by arjanz@intermax.nl as a suggested replacement for array_diff()
 * http://www.php.net/array_diff
 */
function array_minus_array($a, $b)
{
	$c = array();
	foreach ($a as $key => $val)
	{
		$posb = array_search($val, $b);
		if (is_int($posb)) {
			unset($b[$posb]);
		} else {
			$c[] = $val;
		}
	}
	return $c;
}

$duplicates  = array_minus_array($pieces_set, array_unique($pieces_set));
$pieces_set  = array_unique($pieces_set);
$pieces_diff = array_merge(array_diff($pieces_used, $pieces_set), array_diff($pieces_set, $pieces_used));

if (!$pieces_diff && !$duplicates && !$untranslated) {
	exit('<h2>No Problems Found</h2>');
}

if (count($pieces_diff)) {
	echo '
	<h2>Problems Found</h2>
	<h3>These words were not found everywhere they should.</h4>
	<h4>Note: false positives may result when a method of $lang is called in the code.</h4>

	<table border="1" cellpadding="3" cellspacing="0">
	<tr>
		<td align="center"><b>Word</b></td>
		<td align="center"><b>Found in en.php</b></td>
		<td align="center"><b>Found elsewhere</b></td>
	</tr>';

	foreach ($pieces_diff as $difference)
	{
		if (in_array($difference, $pieces_set)) {
			echo "
			<tr>
				<td>$difference</td>
				<td align='center'>yes</td>
				<td align='center'>&nbsp;</td>
			</tr>";
		} else {
			echo "
			<tr>
				<td>$difference</td>
				<td align='center'>&nbsp;</td>
				<td align='center'>" . $pieces_used_files[$difference] . "</td>
			</tr>";
		}
	}
	echo '</table><br><br>';
}

if ($duplicates) {
	echo '
	<h2>Duplicates Found</h2>
	<h3>These words appeared multiple times in en.php.</h4>';

	foreach ($duplicates as $difference)
	{
		echo $difference . '<br>';
	}

	echo '<br>';
}


if ($untranslated) {
	echo '
	<h2>Translations Needed</h2>
	<h3>These words need to be translated.</h4>';

	foreach ($untranslated as $name => $lang)
	{
		echo "<b>$name (" . count($lang) . ' words)</b><br>';

		foreach ($lang as $phrase)
		{
			echo $phrase . '<br>';
		}

		echo '<br>';
	}
}
?>