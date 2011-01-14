<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2011 Roger Libiez http://www.iguanadons.net
 *
 * Based on Quicksilver Forums
 * Copyright (c) 2005-2011 The Quicksilver Forums Development Team
 *  http://code.google.com/p/quicksilverforums/
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

if (!defined('PDNSADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

$error_version = '2.0';

if( is_readable( './settings.php' ) ) {
	require './settings.php';
	require_once './lib/mailer.php';
} else {
	require '../settings.php';
	require_once '../lib/mailer.php';
}

function get_backtrace()
{
	$backtrace = debug_backtrace();
	$out = "<span class='header'>Backtrace:</span><br /><br />\n\n";

	foreach( $backtrace as $trace => $frame )
	{
		// 2 is the file that actually died. We don't need to list the error handlers in the trace.
		if( $trace < 2 ) {
			continue;
		}
		$args = array();

		if( $trace > 2 ) { // The call in the error handler is irrelevent anyway, so don't bother with the arg list
			if ( isset( $frame['args'] ) )
			{
				foreach( $frame['args'] as $arg )
				{
					if ( is_array( $arg ) && array_key_exists( 0, $arg ) && is_string( $arg[0] ) ) {
						$argument = htmlspecialchars( $arg[0] );
					} elseif( is_string( $arg ) ) {
						$argument = htmlspecialchars( $arg );
					} else {
						$argument = NULL;
					}
					$args[] = "'{$argument}'";
				}
			}
		}

		$frame['class'] = (isset($frame['class'])) ? $frame['class'] : '';
		$frame['type'] = (isset($frame['type'])) ? $frame['type'] : '';
		$frame['file'] = (isset($frame['file'])) ? $frame['file'] : '';
		$frame['line'] = (isset($frame['line'])) ? $frame['line'] : '';

		$func = "";
		$arg_list = implode(", ", $args);
		if( $trace == 2 ) {
			$func = "See above for details.";
		} else {
			$func = htmlspecialchars($frame['class'] . $frame['type'] . $frame['function']) . '(' . $arg_list . ')';
		}

		$out .= '<b>File:</b> ' . $frame['file'] . "<br />\n";
		$out .= '<b>Line:</b> ' . $frame['line'] . "<br />\n";
		$out .= '<b>Call:</b> ' . $func . "<br /><br />\n\n";
	}
	return $out;
}

function error_fatal($type, $message, $file, $line = 0)
{
	global $set;

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

	case PDNSADMIN_QUERY_ERROR:
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

	if ($type != PDNSADMIN_QUERY_ERROR) {
		if (strpos($message, 'mysql_fetch_array(): supplied argument') === false) {
			$lines = null;
			$details2 = null;

			if (strpos($message, 'Template not found') !== false) {
				$backtrace = '';
				$trace = debug_backtrace();
				$file = $trace[2]['file'];
				$line = $trace[2]['line'];
			}

			if (file_exists($file)) {
				$lines = file($file);
			}

			if ($lines) {
				$details2 = "
				<span class='header'>Code:</span><br />\n
				<span class='code'>" . error_getlines($lines, $line) . '</span>';
			}
		} else {
			$details2 = "
			<span class='header'>MySQL Said:</span><br />" . mysql_error() . '<br />\n';
		}

		$details .= "
		<span class='header'>$type_str [$type]:</span><br />\n
		The error was reported on line <b>$line</b> of <b>$file</b><br /><br />\n\n$details2";
	} else {
		$details .= "
		<span class='header'>$type_str [$line]:</span><br />\n
		This type of error is reported by MySQL.
		<br /><br /><span class='header'>Query:</span><br />$file<br />\n";
	}

	$checkbug = error_report($type, $message, $file, $line);

	// IIS does not use $_SERVER['QUERY_STRING'] in the same way as Apache and might not set it
	if (isset($_SERVER['QUERY_STRING'])) {
		$temp_querystring = str_replace("&","&amp;", $_SERVER['QUERY_STRING']);
	} else {
		$temp_querystring = '';
	}

	// DO NOT allow this information into the error reports!!!
	$details = str_replace( $set['db_name'], "****", $details );
	$details = str_replace( $set['db_pass'], "****", $details );
	$details = str_replace( $set['db_user'], "****", $details );
	$details = str_replace( $set['db_host'], "****", $details );
	$backtrace = str_replace( $set['db_name'], "****", $backtrace );
	$backtrace = str_replace( $set['db_pass'], "****", $backtrace );
	$backtrace = str_replace( $set['db_user'], "****", $backtrace );
	$backtrace = str_replace( $set['db_host'], "****", $backtrace );

	// Don't send it if this isn't available. Spamming mail servers is a bad bad thing.
	// This will also email the user agent string, in case errors are being generated by evil bots.
	if( isset($set['admin_email']) ) {
		$mailer = new mailer($set['admin_email'], $set['admin_email'], 'PDNS-Admin Error Module', false);
		$mailer->setSubject('PDNS-Admin Error Report');

		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A';
		$ip    = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

		$error_report = "PDNS-Admin has exited with an error!\n";
		$error_report .= "The error details are as follows:\n\nURL: http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . "\n";
		$error_report .= "Querying user agent: " . $agent . "\n";
		$error_report .= "Querying IP: " . $ip . "\n\n";
		$error_report .= strip_tags($message) . "\n\n" . strip_tags($details) . "\n\n" . strip_tags($backtrace);
		$error_report = str_replace( '&nbsp;', ' ', html_entity_decode($error_report) );
		$mailer->setMessage($error_report);

		$mailer->setRecipient($set['admin_email']);
		$mailer->doSend();
	}

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
	<span class='large'>PDNS-Admin has exited with an error!</span><br /><br />

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

	if (!isset($GLOBALS['pdnsadmin']) && class_exists('pdnsadmin')) {
		$pdns = new pdnsadmin;
	} elseif (isset($GLOBALS['pdnsadmin'])) {
		$pdns = $GLOBALS['pdnsadmin'];
	}

	$mysql_version   = mysql_result(mysql_query('SELECT VERSION() as version'), 0, 0);
	$server_software = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 0;
	$safe_mode       = get_cfg_var('safe_mode') ? 1 : 0;

	// $str = serialize(array($error_version, $pdns->version, PHP_VERSION, $mysql_version,$message, $server_software, PHP_OS, $safe_mode, $line));
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