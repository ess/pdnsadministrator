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

/* Users */
define('USER_GUEST_UID', 1);

/* Error Reporting */
define('QUICKSILVER_NOTICE', 3);
define('QUICKSILVER_ERROR', 5);
define('QUICKSILVER_QUERY_ERROR', 6);

/* Password Changing */
define('PASS_NOT_VERIFIED', 1);
define('PASS_NO_MATCH', 2);
define('PASS_INVALID', 3);
define('PASS_SUCCESS', 4);

/* Time Formatting */
define('DATE_LONG', 1);
define('DATE_SHORT', 2);
define('DATE_ONLY_LONG', 3);
define('DATE_ONLY_SHORT', 4);
define('DATE_TIME', 5);
define('DATE_ISO822', 6); // For RSS feeds

/* Text Formatting */
define('FORMAT_BREAKS', 1);
define('FORMAT_HTMLCHARS', 2);

/* User Groups */
define('USER_ADMIN', 1);
define('USER_DOM_ADMIN', 2);
define('USER_MEMBER', 3);
define('USER_GUEST', 4);

/* Types for validation */
define('TYPE_BOOLEAN', 1); // Variable should be 1 or 0 and will be changed to true or false
define('TYPE_UINT', 2); // Variable should be an integer that is also >= 0
define('TYPE_INT', 3); // Variable should be an integer
define('TYPE_FLOAT', 4); // Variable is a floating point number
define('TYPE_STRING', 5); // Variable is a string (essentially anything except array or object)
define('TYPE_ARRAY', 6); // Variable is an array. Probably better to use is_array()
define('TYPE_OBJECT', 7); // Variable is an object. Can put in a class in the range to check if object's ancestry
define('TYPE_USERNAME', 8); // Check if it's okay to use as a username
define('TYPE_PASSWORD', 9); // Check if string is okay to use as a password
define('TYPE_EMAIL', 10); // Check if string is a valid email

/* General purpose */
define('DAY_IN_SECONDS', 86400);
define('SECONDS_IN_HOUR', 3600);
?>