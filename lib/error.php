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

if (!defined('QUICKSILVERFORUMS')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

$error_version = '2.0';

function get_backtrace()
{
	$backtrace = debug_backtrace();
	$out = "<span class='header'>Backtrace:</span><br /><br />";

	foreach( $backtrace as $trace => $frame )
	{
		// 2 is the file that actually died. We don't need to list the error handlers in the trace.
		if( $trace < 2 ) {
			continue;
		}
		$args = array();

		if( $trace > 2 ) { // The call in the error handler is irrelevent anyway, so don't bother with the arg list
			foreach( $frame['args'] as $arg ) {
				$argument = htmlspecialchars($arg[0]);
				$args[] = "'{$argument}'";
			}
		}

		$frame['class'] = (isset($frame['class'])) ? $frame['class'] : "";
		$frame['type'] = (isset($frame['type'])) ? $frame['type'] : "";
		$frame['file'] = (isset($frame['file'])) ? $frame['file'] : "";
		$frame['line'] = (isset($frame['line'])) ? $frame['line'] : "";

		$func = "";
		$arg_list = implode(", ", $args);
		if( $trace == 2 ) {
			$func = "See above for details.";
		} else {
			$func = htmlspecialchars($frame['class'] . $frame['type'] . $frame['function']) . "(" . $arg_list . ")";
		}

		$out .= "<b>File:</b> " . $frame['file'] . "<br />";
		$out .= "<b>Line:</b> " . $frame['line'] . "<br />";
		$out .= "<b>Call:</b> " . $func . "<br /><br />";
	}
	return $out;
}

function error_fatal($type, $message, $file, $line = 0)
{
	switch($type)
	{
	case E_USER_ERROR:
		$type_str = 'Error';
		break;

	case E_WARNING:
	case E_USER_WARNING:
		$type_str = 'Warning';
		break;

	case E_NOTICE:
	case E_USER_NOTICE:
		$type_str = 'Notice';
		break;

	case QUICKSILVER_QUERY_ERROR:
		$type_str = 'Query Error';
		break;

	default:
		$type_str = 'Unknown Error';
	}

	if (strstr($file, 'eval()')) {
		$split    = preg_split('/[\(\)]/', $file);
		$file     = $split[0];
		$line     = $split[1];
		$message .= ' (in evaluated code)';
	}

	$details = null;
	$backtrace = null;

	if (strpos($message, 'Template not found') === false) {
		$backtrace = get_backtrace();
	}

	if ($type != QUICKSILVER_QUERY_ERROR) {
		if (strpos($message, 'mysql_fetch_array(): supplied argument') === false) {
			$lines = null;
			$details2 = null;

			if (strpos($message, 'Template not found') !== false) {
				$backtrace = "";
				$trace = debug_backtrace();
				$file = $trace[2]['file'];
				$line = $trace[2]['line'];
			}

			if (file_exists($file)) {
				$lines = file($file);
			}

			if ($lines) {
				$details2 = "
				<span class='header'>Code:</span><br />
				<span class='code'>" . error_getlines($lines, $line) . '</span>';
			}
		} else {
			$details2 = "
			<span class='header'>MySQL Said:</span><br />" . mysql_error() . '<br />';
		}

		$details .= "
		<span class='header'>$type_str [$type]:</span><br />
		The error was reported on line <b>$line</b> of <b>$file</b><br /><br />$details2";
	} else {
		$details .= "
		<span class='header'>$type_str [$line]:</span><br />
		This type of error is reported by MySQL.
		<br /><br /><span class='header'>Query:</span><br />$file<br />";
	}

	$checkbug = error_report($type, $message, $file, $line);

	$temp_querystring = str_replace("&","&amp;", $_SERVER['QUERY_STRING']);

	return "
	<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
	<html>
	<head>
	<title>PDNS-Admin Error</title>
	<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">

	<style type='text/css'>
	body {font-size:12px; font-family: verdana, arial, helvetica, sans-serif; color:#000000; background-color:#ffffff}
	hr {height:1px}
	.large  {font-weight:bold; font-size:18px; color:#660000; background-color:transparent}
	.header {font-weight:bold; font-size:12px; color:#660000; background-color:transparent}
	.error  {font-weight:bold; font-size:12px; color:#ff0000; background-color:transparent}
	.small  {font-weight:bold; font-size:10px; color:#000000; background-color:transparent}
	.code   {font-weight:normal; font-size:12px; font-family:courier new, fixedsys, serif}
	</style>
	</head>

	<body>
	<span class='large'>PDNS-Admin has exited with an error</span><br /><br />

	<hr>
	<span class='error'>$message</span>
	<hr><br />

	$details

	<br /><hr><br />

	$backtrace

	<br /><hr><br />
	<a href='{$_SERVER['PHP_SELF']}?{$temp_querystring}&amp;debug=1' class='small'>View debug information (advanced)</a><br />
	<a href='{$_SERVER['PHP_SELF']}' class='small'>Return to the console</a>
	</body>
	</html>";
}

function error_getlines($lines, $line)
{
	$code    = null;
	$padding = ' ';
	$previ   = $line-3;
	$total_lines = count($lines);

	for ($i = $line - 3; $i <= $line + 3; $i++)
	{
		if ((strlen($previ) < strlen($i)) && ($padding == ' ')) {
			$padding = null;
		}

		if (($i < 1) || ($i > $total_lines)) {
			continue;
		}

		$codeline = rtrim(htmlentities($lines[$i-1]));
		$codeline = str_replace("\t", '&nbsp;&nbsp;&nbsp;&nbsp;', $codeline);
		$codeline = str_replace(' ', '&nbsp;', $codeline);

		if ($i != $line) {
			$code .= $i . $padding . $codeline . "<br />\n";
		} else {
			$code .= '<font color="#FF0000">' . $i . $padding . $codeline . "</font><br />\n";
		}

		$previ = $i;
	}
	return $code;
}

function error_report($type, $message, $file, $line)
{
	global $error_version;

	if (stristr($message, 'mysql_fetch_array(): supplied argument is not a valid MySQL result resource')) {
		$message .= '; ' . mysql_error();
	}

	if (!isset($GLOBALS['qsfglobal']) && class_exists('qsfglobal')) {
		$qsf = new qsfglobal;
	} elseif (isset($GLOBALS['qsfglobal'])) {
		$qsf = $GLOBALS['qsfglobal'];
	}

	$mysql_version   = mysql_result(mysql_query('SELECT VERSION() as version'), 0, 0);
	$server_software = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 0;
	$safe_mode       = get_cfg_var('safe_mode') ? 1 : 0;

	// $str = serialize(array($error_version, $qsf->version, PHP_VERSION, $mysql_version,$message, $server_software, PHP_OS, $safe_mode, $line));
	$str = '';
	return urlencode(base64_encode(md5($str) . $str));
}

function error_warning($message, $file, $line)
{
	return $message;
}

function error_notice($message)
{
	return $message;
}
?>
