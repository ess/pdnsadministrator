<?php
/**
 * Locates use of deprecated and problematic code
 *
 * $Id: deprecated.php,v 1.8 2004/03/19 16:16:00 jason Exp $
 */

/**
 * deprecated: adj. Said of a program or feature that is considered obsolescent
 * and in the process of being phased out, usually in favor of a specified
 * replacement.
 */

ob_start('ob_gzhandler');
error_reporting(E_ALL);

set_magic_quotes_runtime(0);
set_time_limit(0);

$problems = array();

$checks = array(
	array(
		'REGEX'    => '/\$(this|qsf|admin)->output/',
		'PROBLEM'  => '$output was removed in Beta 3',
		'SOLUTION' => 'Return values from execute()'
	),

	array(
		'REGEX'    => '/\$(this|qsf|admin)->wrap\((.*?)\)/is',
		'PROBLEM'  => 'wrap() was removed in Beta 3',
		'SOLUTION' => 'May be supported at a later time by format()'
	),

	array(
		'REGEX'    => '/\$(this|qsf|admin)->checkTags\((.*?)\)/is',
		'PROBLEM'  => 'checkTags() was removed in Beta 3',
		'SOLUTION' => 'Done automatically by format()'
	),

	array(
		'REGEX'    => '/\$(this|qsf|admin)->mbcode\((.*?)\)/is',
		'PROBLEM'  => 'mbcode() was removed in Beta 4',
		'SOLUTION' => 'Use format() with the flag FORMAT_MBCODE'
	),

	array(
		'REGEX'    => '/\$(this|qsf|admin)->emoticons\((.*?)\)/is',
		'PROBLEM'  => 'emoticons() was removed in Beta 4',
		'SOLUTION' => 'Use format() with the flag FORMAT_EMOTICONS'
	),

	array(
		'REGEX'    => '/\$(this|qsf|admin)->censor\((.*?)\)/is',
		'PROBLEM'  => 'censor() was removed in Beta 4',
		'SOLUTION' => 'Use format() with the flag FORMAT_CENSOR'
	),

	array(
		'REGEX'    => '/\$(this|qsf|admin)->strip_mbcode\((.*?)\)/is',
		'PROBLEM'  => 'strip_mbcode() was removed in Beta 4',
		'SOLUTION' => 'Use format() with the flag FORMAT_STRIP'
	),

	array(
		'REGEX'    => '/htmlspecialchars\((.*?)\)/is',
		'PROBLEM'  => 'htmlspecialchars() was discouraged in Beta 3',
		'SOLUTION' => 'Use format() with the flag FORMAT_HTMLCHARS'
	),

	array(
		'REGEX'    => '/htmlentities\((.*?)\)/is',
		'PROBLEM'  => 'htmlentities() was discouraged in Beta 3',
		'SOLUTION' => 'Use format() with the flag FORMAT_HTMLCHARS'
	),

	array(
		'REGEX'    => '/extract\((.*?)\)/is',
		'PROBLEM'  => 'extract() was discouraged in Beta 4',
		'SOLUTION' => 'Arrays are your friend. Don\'t use extract()'
	),

	array(
		'REGEX'    => '/\$(this|qsf|admin)->startNav\((.*?)\)/is',
		'PROBLEM'  => 'startNav() was removed in Beta 4',
		'SOLUTION' => 'Use tree()'
	),

	array(
		'REGEX'    => '/\$(this|qsf|admin)->addNav\((.*?)\)/is',
		'PROBLEM'  => 'addNav() was removed in Beta 4',
		'SOLUTION' => 'Use tree()'
	)
);

function traverse($dir)
{
	$dp = opendir($dir);
	while (($file = readdir($dp)) !== false)
	{
		if (($file == 'CVS') || ($file == 'tools') || ($file == '.') || ($file == '..')) {
			continue;
		}

		$name = "$dir/$file";
		if (is_dir($name)) {
			traverse($name);
		} else {
			$ext = substr($file, -4);
			if ($ext == '.php') {
				$fp = fopen($name, 'r');
				$contents = fread($fp, filesize($name));
				fclose($fp);

				foreach ($GLOBALS['checks'] as $check) {
					preg_match_all($check['REGEX'], $contents, $matches);

					foreach ($matches[0] as $match)
					{
						$i = count($GLOBALS['problems']);
						$GLOBALS['problems'][$i] = array(
							'MATCH'    => $match,
							'FILE'     => $name,
							'PROBLEM'  => $check['PROBLEM'],
							'SOLUTION' => $check['SOLUTION']
						);
					}
				}
			}
		}
	}
}

traverse('..');

if (!count($problems)) {
	exit('<h2>No Problems Found</h2>');
}

echo '
<h2>' . count($problems) . ' Problems Found</h2>

<table border="1" cellpadding="3" cellspacing="0">
<tr>
	<td align="center"><b>Match</b></td>
	<td align="center"><b>File</b></td>
	<td align="center"><b>Problem</b></td>
	<td align="center"><b>Solution</b></td>
</tr>';

foreach ($problems as $problem)
{
	echo '
	<tr>
		<td>' . htmlspecialchars($problem['MATCH']) . '</td>
		<td>' . $problem['FILE'] . '</td>
		<td>' . $problem['PROBLEM'] . '</td>
		<td>' . $problem['SOLUTION'] . '</td>
	</tr>';
}

echo '</table>';
?>