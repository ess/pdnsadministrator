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
 * Quick and (extremely) dirty script to sync and clean up the
 * language files
 *
 */

define('QUICKSILVERFORUMS', true);
ini_set( "memory_limit", "24M" ); // Temporary hackishly bad thing until the real bug gets fixed
error_reporting(E_ALL);
require '../settings.php';
$set['include_path'] = '..';
require_once $set['include_path'] . '/defaultutils.php';
require '../global.php';

$qsf = new qsfglobal;
$qsf->htmlwidgets = new $modules['widgets']($qsf);

/* Recursive ksort() */
function ksort_rec(&$array)
{
	ksort($array);

	foreach (array_keys($array) as $k)
	{
		if (is_array($array[$k])) {
			ksort_rec($array[$k]);
		}
	}
}

$words  = array();
$result = array();
$langs  = array();

$dp = opendir('../languages/');
while (($file = readdir($dp)) !== false)
{
	if (substr($file, -4) == '.php') {
		$langs[] = substr($file, 0, -4);
	}
}

foreach ($langs as $lang)
{
	if (class_exists($lang)) {
		continue;
	}

	include '../languages/' . $lang . '.php';
	$methods = get_class_methods($lang);

	foreach ($methods as $method)
	{
		unset($obj);

		$obj = new $lang(null, false);
		$obj->$method();

		$vars = get_object_vars($obj);

		foreach ($vars as $var => $val)
		{
			$words[$lang][$var] = array($val, $method);
		}
	}
}

foreach ($words as $lang_name => $lang_words)
{
	if ($lang_name == 'en') {
		foreach ($lang_words as $word => $info)
		{
			$result[$lang_name][$info[1]][$word] = $words[$lang_name][$word][0];
		}
		continue;
	}

	foreach ($words['en'] as $word => $info)
	{
		if (!isset($words[$lang_name][$word])) {
			$result[$lang_name][$info[1]][$word] = $info[0];
		} else {
			$result[$lang_name][$info[1]][$word] = $words[$lang_name][$word][0];
		}
	}
}

ksort_rec($result);

$out = array();

foreach ($result as $lang_name => $lang_words)
{
	$fp = fopen('../languages/' . $lang_name . '.php', 'r');
	$c  = fread($fp, filesize('../languages/' . $lang_name . '.php'));
	fclose($fp);

	if (preg_match('/@since (.+)/', $c, $since)) {
		$since = $since[1];
	} else {
		$since = substr($qsf->version, 1);
	}

	$authors_list = null;

	if (preg_match_all('/@author (.+)/', $c, $authors)) {
		foreach ($authors[1] as $author)
		{
			$author = trim($author);

			if (($author == '') || ($author == 'Unknown')) {
				$author = 'Anonymous';
			}

			$authors_list .= "\n * @author " . trim($author);
		}
	} else {
		$authors_list = ' @author Anonymous';
	}

	$out[$lang_name] = '<?php
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

/**
 * ' . $qsf->htmlwidgets->get_lang_name($lang_name) . ' language module
 *' . $authors_list . '
 * @since ' . trim($since) . '
 **/
class ' . $lang_name . '
{';

	foreach ($lang_words as $module_name => $module_words)
	{
		$out[$lang_name] .= "\n	function $module_name()\n	{";

		foreach ($module_words as $word_name => $word_value)
		{
			$translate = '';

			if ($word_name == 'charset') {
				//$word_value = 'utf-8';
			}

			$word_value = str_replace('<br>', '<br />', $word_value);

			if (($lang_name != 'en') && ($word_name != 'charset')) {
				// Determine if a translated word doesn't contain the same formatting as in English
				if ((substr_count($word_value, '%s') != substr_count($words['en'][$word_name][0], '%s'))
				|| (substr_count($word_value, '%d') != substr_count($words['en'][$word_name][0], '%d'))
				|| (substr_count($word_value, '<') != substr_count($words['en'][$word_name][0], '<'))
				|| (substr_count($word_value, '>') != substr_count($words['en'][$word_name][0], '>'))) {
					$word_value = $words['en'][$word_name][0];
				}

				// Determine if a word has been translated
				if ($word_value == $words['en'][$word_name][0]) {
					$translate = ' //Translate';
				}
			}

			$out[$lang_name] .= "\n		\$this->$word_name = '" . str_replace('\'', '\\\'', html_entity_decode($word_value, ENT_QUOTES)) . '\';' . $translate;
		}

		$out[$lang_name] .= "\n	}\n";
	}

	$out[$lang_name] = rtrim($out[$lang_name]) . "\n}\n?>";
}

foreach ($out as $filename => $contents)
{
	if (function_exists('mb_detect_encoding')) {
		$encoding = mb_detect_encoding($contents);
		if (!$encoding) {
			$encoding = 'unknown';
		}

		if ($encoding != 'UTF-8') {
			//$contents = utf8_encode($contents);
		}

		$encoding = ' - ' . $encoding;
	} else {
		$encoding = '';
	}

	$fp = fopen('../languages/' . $filename . '.php', 'r');
	$old = fread($fp, filesize('../languages/' . $filename . '.php'));
	fclose($fp);

	$contents = preg_replace("/(\r\n|\r)/", "\n", $contents);

    echo "<b>$filename - " . $qsf->htmlwidgets->get_lang_name($filename) . "$encoding</b><br />";

    $fp = fopen('../languages/' . $filename . '.php', 'w');
    fwrite($fp, $contents);
    fwrite($fp, "\n");
    fclose($fp);
}

echo '<br />Done';
?>
