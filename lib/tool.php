<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2007 Roger Libiez http://www.iguanadons.net
 *
 * Based on Quicksilver Forums
 * Copyright (c) 2005 The Quicksilver Forums Development Team
 *  http://www.quicksilverforums.com/
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

/**
 * Generic functions. Mostly validator stuff
 *
 * @author Geoffrey Dunn <geoff@warmage.com>
 * @since 1.2
 **/
class tool
{
	/**
	 * Checks if parameter is valid according to the rules passed
	 *
	 * Typical uses are:
	 * validate($this->get['f'], TYPE_UINT)
	 * validate($this->get['order'], TYPE_STRING, array('title', 'starter', 'replies', 'views'), '')
	 * validate($this->post['adminemail'], TYPE_EMAIL, null, 'root@localhost')
	 *
	 *
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.1.5
	 * @param mixed $var Variable from the user, typically a get or post
	 * @param int $type Type to check against. See TYPE defaults in constants.php
	 * @param array $range optional A range of values of $type the variable must match
	 * @param mixed $default optional A default value if the check fails
	 * @return true if $var is valid and unchanged, false if a match failed
	 */
	function validate(&$var, $type, $range=null, $default=null)
	{
		$unchanged = true;
		switch($type) {
		case TYPE_BOOLEAN:
			// $range and $default are unused
			if (!is_bool($var)) {
				$unchanged = false;
			}
			$var = (true && $var); // Convert to a proper boolean
			break;
		case TYPE_UINT:
			$newvar = intval($var);
			if ($newvar != $var || $newvar < 0 || ($range != null && !in_array($var, $range))) {
				$unchanged = false;
				if ($default != null) $newvar = $default;
			}
			$var = $newvar;
			break;
		case TYPE_INT:
			$newvar = intval($var);
			if ($newvar != $var || ($range != null && !in_array($var, $range))) {
				$unchanged = false;
				if ($default != null) $newvar = $default;
			}
			$var = $newvar;
			break;
		case TYPE_FLOAT:
			if (!is_numeric($var) || ($range != null && !in_array($var, $range))) {
				$unchanged = false;
				if ($default != null) $var = $default;
			}
			break;
		case TYPE_STRING:
			if (!is_string($var) || ($range != null && !in_array($var, $range))) {
				$unchanged = false;
				if ($default != null) $var = $default;
			}
			break;
		case TYPE_ARRAY:
			// $range is unused
			if (!is_array($var)) {
				$unchanged = false;
				if ($default != null) $var = $default;
			}
			break;
		case TYPE_OBJECT:
			// $range is seen as a class name
			if (!is_object($var) || ($range != null && !is_subclass_of($var, $range))) {
				$unchanged = false;
				if ($default != null) $var = $default;
			}
			break;
		case TYPE_USERNAME:
			// $range is unused
			if (strlen($username) > 20) {
				$unchanged = false;
				if ($default != null) $var = $default;
			}
			break;
		case TYPE_PASSWORD:
			// $range is unused
			if (!preg_match("/^[a-z0-9_\- ]{5,}$/i", $pass)) {
				$unchanged = false;
				if ($default != null) $var = $default;
			}
			break;
		case TYPE_EMAIL:
			// $range is unused
			if (!$this->_is_valid_email_address($var)) {
				$unchanged = false;
				if ($default != null) $var = $default;
			}
			break;
		default:
			// Invalid type! Only developers should ever see this error
			error(QUICKSILVER_ERROR, "Invalid type sent to validate()", __FILE__, __LINE__);
		}
		return $unchanged;
	}

	/**
	 * Checks if an email address looks valid
	 *
	 * PRIVATE
	 *
	 * @param string $email Address to check
	 * @return true if the email checks out
	 * @author http://iamcal.com/publish/articles/php/parsing_email
	 * @since 1.1.5
	 */
	function _is_valid_email_address($email)
	{
		$qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
		$dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
		$atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'.
			'\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
		$quoted_pair = '\\x5c\\x00-\\x7f';
		$domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
		$quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
		$domain_ref = $atom;
		$sub_domain = "($domain_ref|$domain_literal)";
		$word = "($atom|$quoted_string)";
		$domain = "$sub_domain(\\x2e$sub_domain)*";
		$local_part = "$word(\\x2e$word)*";
		$addr_spec = "$local_part\\x40$domain";
		return preg_match("!^$addr_spec$!", $email) ? 1 : 0;
	}
}
?>
