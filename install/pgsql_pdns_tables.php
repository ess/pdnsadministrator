<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2011 Roger Libiez http://www.iguanadons.net
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

if (!defined('PDNS_INSTALLER')) {
	exit('Use index.php to install.');
}

$queries[] = "DROP SEQUENCE IF EXISTS domains_id_seq";
$queries[] = "DROP SEQUENCE IF EXISTS records_id_seq";

$queries[] = "CREATE SEQUENCE domains_id_seq START 1 INCREMENT 1 MAXVALUE 2147483647 MINVALUE 1 CACHE 1";
$queries[] = "CREATE SEQUENCE records_id_seq START 1 INCREMENT 1 MAXVALUE 2147483647 MINVALUE 1 CACHE 1";

$queries[] = "DROP TABLE IF EXISTS domains";
$queries[] = "CREATE TABLE domains (
  id int2 DEFAULT nextval('domains_id_seq') NOT NULL,
  name varchar(255) NOT NULL DEFAULT '',
  master varchar(128) DEFAULT NULL,
  last_check int(10) unsigned DEFAULT NULL,
  type varchar(6) NOT NULL DEFAULT '',
  notified_serial int(10) unsigned DEFAULT NULL,
  account varchar(40) DEFAULT NULL,
  PRIMARY KEY (id)
)";

$queries[] = "CREATE UNIQUE INDEX name_index ON domains(name)";

$queries[] = "DROP TABLE IF EXISTS records";
$queries[] = "CREATE TABLE records (
  id int2 DEFAULT nextval('records_id_seq') NOT NULL,
  domain_id int(10) unsigned DEFAULT NULL,
  name varchar(255) DEFAULT NULL,
  type varchar(6) DEFAULT NULL,
  content varchar(255) DEFAULT NULL,
  ttl int(10) unsigned DEFAULT NULL,
  prio int(10) unsigned DEFAULT NULL,
  change_date int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (id)
)";

$queries[] = "CREATE INDEX rec_name_index ON records(name)";
$queries[] = "CREATE INDEX nametype_index ON records(name,type)";
$queries[] = "CREATE INDEX domain_id ON records(domain_id)";

$queries[] = "DROP TABLE IF EXISTS supermasters";
$queries[] = "CREATE TABLE supermasters (
  ip varchar(25) NOT NULL,
  nameserver varchar(255) NOT NULL,
  account varchar(40) DEFAULT NULL
)";
?>