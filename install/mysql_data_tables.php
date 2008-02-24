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

if (!defined('INSTALLER')) {
	exit('Use index.php to install.');
}

$queries[] = "DROP TABLE IF EXISTS groups";
$queries[] = "CREATE TABLE groups (
  group_id tinyint(3) unsigned NOT NULL auto_increment,
  group_name varchar(255) NOT NULL default '',
  group_type varchar(20) NOT NULL default '',
  group_perms text NOT NULL default '',
  PRIMARY KEY  (group_id)
) ENGINE=InnoDB";

$queries[] = "DROP TABLE IF EXISTS logs";
$queries[] = "CREATE TABLE logs (
  log_id int(10) unsigned NOT NULL auto_increment,
  log_user int(10) unsigned NOT NULL default '0',
  log_time int(10) unsigned NOT NULL default '0',
  log_action varchar(20) NOT NULL default '',
  log_data1 int(12) unsigned NOT NULL default '0',
  log_data2 smallint(4) unsigned NOT NULL default '0',
  log_data3 smallint(4) unsigned NOT NULL default '0',
  PRIMARY KEY  (log_id)
) ENGINE=InnoDB";

$queries[] = "DROP TABLE IF EXISTS settings";
$queries[] = "CREATE TABLE settings (
  settings_id tinyint(2) unsigned NOT NULL auto_increment,
  settings_data text NOT NULL default '',
  PRIMARY KEY  (settings_id)
) ENGINE=InnoDB";

$queries[] = "DROP TABLE IF EXISTS skins";
$queries[] = "CREATE TABLE skins (
  skin_name varchar(32) NOT NULL default '',
  skin_dir varchar(32) NOT NULL default '',
  PRIMARY KEY  (skin_dir)
) ENGINE=InnoDB";

$queries[] = "DROP TABLE IF EXISTS templates";
$queries[] = "CREATE TABLE templates (
  template_skin varchar(32) NOT NULL default 'default',
  template_set varchar(20) NOT NULL default '',
  template_name varchar(36) NOT NULL default '',
  template_html text NOT NULL default '',
  template_displayname varchar(255) NOT NULL default '',
  template_description varchar(255) NOT NULL default '',
  UNIQUE KEY Piece (template_name,template_skin),
  KEY Section (template_set,template_skin)
) ENGINE=InnoDB";

$queries[] = "DROP TABLE IF EXISTS users";
$queries[] = "CREATE TABLE users (
  user_id int(10) unsigned NOT NULL auto_increment,
  user_name varchar(255) NOT NULL default '',
  user_password varchar(32) NOT NULL default '',
  user_group tinyint(3) unsigned NOT NULL default '2',
  user_skin varchar(32) NOT NULL default 'default',
  user_language varchar(6) NOT NULL default 'en',
  user_email varchar(100) NOT NULL default '',
  user_created int(10) unsigned NOT NULL default '0',
  user_lastlogon int(10) unsigned NOT NULL default '0',
  user_perms text NOT NULL default '',
  PRIMARY KEY  (user_id)
) ENGINE=InnoDB";

$queries[] = "DROP TABLE IF EXISTS zones";
$queries[] = "CREATE TABLE zones (
  id int(11) NOT NULL auto_increment,
  domain_id int(11) NOT NULL default '0',
  owner int(11) NOT NULL default '0',
  comment text NOT NULL default '',
  PRIMARY KEY  (id)
) ENGINE=InnoDB";

$queries[] = "INSERT INTO groups (group_id, group_name, group_type, group_perms) VALUES (1, 'Administrators', 'ADMIN', 'a:6:{s:14:\"create_domains\";b:1;s:14:\"delete_domains\";b:1;s:11:\"do_anything\";b:1;s:12:\"edit_domains\";b:1;s:8:\"is_admin\";b:1;s:9:\"site_view\";b:1;}')";
$queries[] = "INSERT INTO groups (group_id, group_name, group_type, group_perms) VALUES (2, 'Domain Administrators', 'DOM_ADMIN', 'a:6:{s:14:\"create_domains\";b:1;s:14:\"delete_domains\";b:1;s:11:\"do_anything\";b:1;s:12:\"edit_domains\";b:1;s:8:\"is_admin\";b:0;s:9:\"site_view\";b:1;}')";
$queries[] = "INSERT INTO groups (group_id, group_name, group_type, group_perms) VALUES (3, 'Users', 'USER', 'a:6:{s:14:\"create_domains\";b:0;s:14:\"delete_domains\";b:0;s:11:\"do_anything\";b:1;s:12:\"edit_domains\";b:0;s:8:\"is_admin\";b:0;s:9:\"site_view\";b:1;}')";
$queries[] = "INSERT INTO groups (group_id, group_name, group_type, group_perms) VALUES (4, 'Guests', 'GUEST', 'a:6:{s:14:\"create_domains\";b:0;s:14:\"delete_domains\";b:0;s:11:\"do_anything\";b:1;s:12:\"edit_domains\";b:0;s:8:\"is_admin\";b:0;s:9:\"site_view\";b:1;}')";

$queries[] = "INSERT INTO settings (settings_id, settings_data) VALUES (1, '{$settings}')";
$queries[] = "INSERT INTO skins (skin_name, skin_dir) VALUES ('Ashlander', 'default')";
$queries[] = "INSERT INTO users (user_id, user_name, user_group) VALUES (1, 'Guest', 4)";
?>