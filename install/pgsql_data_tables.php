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

$queries[] = "DROP SEQUENCE groups_id_seq";
$queries[] = "DROP SEQUENCE logs_id_seq";
$queries[] = "DROP SEQUENCE users_id_seq";

$queries[] = "CREATE SEQUENCE groups_id_seq START 1 INCREMENT 1 MAXVALUE 2147483647 MINVALUE 1 CACHE 1";
$queries[] = "CREATE SEQUENCE logs_id_seq START 1 INCREMENT 1 MAXVALUE 2147483647 MINVALUE 1 CACHE 1";
$queries[] = "CREATE SEQUENCE users_id_seq START 1 INCREMENT 1 MAXVALUE 2147483647 MINVALUE 1 CACHE 1";
$queries[] = "CREATE SEQUENCE zone_id_seq START 1 INCREMENT 1 MAXVALUE 2147483647 MINVALUE 1 CACHE 1";

$queries[] = "DROP TABLE groups";
$queries[] = "CREATE TABLE groups (
  group_id int2 DEFAULT nextval('groups_id_seq') NOT NULL,
  group_name varchar(255) NOT NULL default '',
  group_type varchar(20) NOT NULL default '',
  group_perms text NOT NULL default '',
  PRIMARY KEY  (group_id)
)";

$queries[] = "DROP TABLE logs";
$queries[] = "CREATE TABLE logs (
  log_id int4 DEFAULT nextval('logs_id_seq') NOT NULL,
  log_user int4 NOT NULL default '0',
  log_time int4 NOT NULL default '0',
  log_action varchar(20) NOT NULL default '',
  log_data1 int4 NOT NULL default '0',
  log_data2 int2 NOT NULL default '0',
  log_data3 int2 NOT NULL default '0',
  PRIMARY KEY  (log_id)
)";

$queries[] = "DROP TABLE settings";
$queries[] = "CREATE TABLE settings (
  settings_id int2 NOT NULL default '0',
  settings_data text NOT NULL default '',
  PRIMARY KEY  (settings_id)
)";

$queries[] = "DROP TABLE skins";
$queries[] = "CREATE TABLE skins (
  skin_name varchar(32) NOT NULL default '',
  skin_dir varchar(32) NOT NULL default '',
  PRIMARY KEY  (skin_dir)
)";

$queries[] = "DROP TABLE templates";
$queries[] = "CREATE TABLE templates (
  template_skin varchar(32) NOT NULL default 'default',
  template_set varchar(20) NOT NULL default '',
  template_name varchar(36) NOT NULL default '',
  template_html text NOT NULL default '',
  template_displayname varchar(255) NOT NULL default '',
  template_description varchar(255) NOT NULL default '',
  UNIQUE (template_name,template_skin)
)";

$queries[] = "DROP TABLE users";
$queries[] = "CREATE TABLE users (
  user_id int4 DEFAULT nextval('users_id_seq') NOT NULL,
  user_name varchar(255) NOT NULL default '',
  user_password varchar(32) NOT NULL default '',
  user_group int2 NOT NULL default '2',
  user_skin varchar(32) NOT NULL default 'default',
  user_language varchar(6) NOT NULL default 'en',
  user_email varchar(100) NOT NULL default '',
  user_created int4 NOT NULL default '0',
  user_lastlogon int4 NOT NULL default '0',
  user_perms text NOT NULL default '',
  PRIMARY KEY  (user_id)
)";

$queries[] = "DROP TABLE IF EXISTS zones";
$queries[] = "CREATE TABLE zones (
  id int4 DEFAULT nextval('zone_id_seq') NOT NULL,
  domain_id int4 NOT NULL default '0',
  owner int4 NOT NULL default '0',
  comment text NOT NULL default '',
  PRIMARY KEY  (id)
)";

$queries[] = "INSERT INTO groups (group_id, group_name, group_type, group_perms) VALUES (1, 'Administrators', 'ADMIN', 'a:6:{s:14:\"create_domains\";b:1;s:14:\"delete_domains\";b:1;s:11:\"do_anything\";b:1;s:12:\"edit_domains\";b:1;s:8:\"is_admin\";b:1;s:9:\"site_view\";b:1;}')";
$queries[] = "INSERT INTO groups (group_id, group_name, group_type, group_perms) VALUES (2, 'Domain Administrators', 'DOM_ADMIN', 'a:6:{s:14:\"create_domains\";b:1;s:14:\"delete_domains\";b:1;s:11:\"do_anything\";b:1;s:12:\"edit_domains\";b:1;s:8:\"is_admin\";b:0;s:9:\"site_view\";b:1;}')";
$queries[] = "INSERT INTO groups (group_id, group_name, group_type, group_perms) VALUES (3, 'Users', 'USER', 'a:6:{s:14:\"create_domains\";b:0;s:14:\"delete_domains\";b:0;s:11:\"do_anything\";b:1;s:12:\"edit_domains\";b:0;s:8:\"is_admin\";b:0;s:9:\"site_view\";b:1;}')";
$queries[] = "INSERT INTO groups (group_id, group_name, group_type, group_perms) VALUES (4, 'Guests', 'GUEST', 'a:6:{s:14:\"create_domains\";b:0;s:14:\"delete_domains\";b:0;s:11:\"do_anything\";b:1;s:12:\"edit_domains\";b:0;s:8:\"is_admin\";b:0;s:9:\"site_view\";b:1;}')";

$queries[] = "INSERT INTO settings (settings_id, settings_data) VALUES (1, '{$settings}')";
$queries[] = "INSERT INTO skins (skin_name, skin_dir) VALUES ('Ashlander', 'default')";
$queries[] = "INSERT INTO users (user_id, user_name, user_group) VALUES (1, 'Guest', 4)";
?>