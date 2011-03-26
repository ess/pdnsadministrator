<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2011 Roger Libiez http://www.iguanadons.net
 *
 * Based on Quicksilver Forums
 * Copyright (c) 2005-2011 The Quicksilver Forums Development Team
 *  http://code.google.com/p/quicksilverforums/
 * 
 * MercuryBoard
 * Copyright (c) 2001-2006 The Mercury Development Team
 * http://www.mercuryboard.com/
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
} else {
	require '../settings.php';
}

function get_backtrace()
{
	$backtrace = debug_backtrace();
	$out = "Backtrace:\n\n";

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

		$func = '';
		$arg_list = implode(", ", $args);
		if( $trace == 2 ) {
			$func = 'See above for details.';
		} else {
			$func = htmlspecialchars($frame['class'] . $frame['type'] . $frame['function']) . "(" . $arg_list . ")";
		}

		$out .= 'File: ' . $frame['file'] . "\n";
		$out .= 'Line: ' . $frame['line'] . "\n";
		$out .= 'Call: ' . $func . "\n\n";
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

	case E_STRICT:
		$type_str = 'Strict Standards';
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
				$backtrace = "";
				$trace = debug_backtrace();
				$file = $trace[2]['file'];
				$line = $trace[2]['line'];
			}

			if (file_exists($file)) {
				$lines = file($file);
			}

			if ($lines) {
				$details2 = "Code:\n" . error_getlines($lines, $line);
			}
		} else {
			$details2 = "MySQL Said:\n" . mysql_error() . "\n";
		}

		$details .= "$type_str [$type]:\n
		The error was reported on line $line of $file\n\n
		$details2";
	} else {
		$details .= "$type_str [$line]:\n
		This type of error is reported by SQL.\n\n
		Query:\n$file\n";
	}

	// IIS does not use $_SERVER['QUERY_STRING'] in the same way as Apache and might not set it
	if (isset($_SERVER['QUERY_STRING'])) {
		$querystring = str_replace( '&', '&amp;', $_SERVER['QUERY_STRING'] );
	} else {
		$querystring = '';
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
		$headers = "From: PDNS-Admin Error Module <{$set['admin_email']}>\r\n" . "X-Mailer: PHP/" . phpversion();

		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A';
		$ip    = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

		$error_report = "PDNS-Admin has exited with an error!\n";
		$error_report .= "The error details are as follows:\n\nURL: http://" . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . "?" . $querystring . "\n";
		$error_report .= 'Querying user agent: ' . $agent . "\n";
		$error_report .= 'Querying IP: ' . $ip . "\n\n";
		$error_report .= $message . "\n\n" . $details . "\n\n" . $backtrace;
		$error_report = str_replace( "&nbsp;", " ", html_entity_decode($error_report) );

		@mail( $set['admin_email'], 'PDNS-Admin Error Report', $error_report, $headers );
	}

	header('HTTP/1.0 500 Internal Server Error');
	exit( "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\">
 <head>
  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
  <meta name=\"robots\" content=\"noodp\" />
  <meta name=\"generator\" content=\"PDNS-Admin\" />
  <title>Fatal Error</title>
  <link rel=\"stylesheet\" type=\"text/css\" href=\"skins/default/styles.css\" />
 </head>
 <body>
 <div id=\"container\">
  <div id=\"header\">
   <div id=\"company\">
    <div class=\"logo\"></div>
   </div>
   <ul id=\"navigation\">
    <li><a href=\"index.php\">Home</a></li>
   </ul>
  </div>

  <div id=\"main\">
   <div class=\"article\">
    <div class=\"title\" style=\"color:yellow\">Fatal Error</div>
    The PDNS-Admin software has experienced a fatal error and is unable to process your request at this time. Unfortunately any data you may have sent has been lost, and we apologize for the inconvenience.<br /><br />
    A detailed report on exactly what went wrong has been sent to the site owner and will be investigated and resolved as quickly as possible.
   </div>
  </div>

  <div id=\"bottom\">&nbsp;</div>
 </div>
 <div id=\"footer\">Powered by PDNS-Admin &copy; 2006-2011 Roger Libiez</div>
</body>
</html>" );
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

		$code .= $i . $padding . $codeline . "\n";

		$previ = $i;
	}
	return $code;
}

function error_warning($message, $file, $line)
{
	return $message;
}

function error_notice($message)
{
	// Don't send it if this isn't available. Spamming mail servers is a bad bad thing.
	// This will also email the user agent string, in case errors are being generated by evil bots.
	if( isset($set['admin_email']) ) {
		$mailer = new mailer($set['admin_email'], $set['admin_email'], 'PDNS-Admin Error Module', false);
		$mailer->setSubject('PDNS-Admin Notice Report');

		$agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A';
		$ip    = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

		$error_report = "PDNS-Admin triggered the following notice:\n\n";
		$error_report .= 'URL: http://' . $_SERVER['SERVER_NAME'] . $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'] . "\n";
		$error_report .= 'Querying user agent: ' . $agent . "\n";
		$error_report .= 'Querying IP: ' . $ip . "\n\n";
		$error_report .= strip_tags($message) . "\n\n";
		$error_report = str_replace( '&nbsp;', ' ', html_entity_decode($error_report) );
		$mailer->setMessage($error_report);

		$mailer->setRecipient($set['admin_email']);
		$mailer->doSend();
	}
	return $message;
}
?>