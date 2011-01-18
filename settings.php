<?php
if (!defined('PDNSADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

$set = array();

$set['db_name'] = '';
$set['db_user'] = 'pdns';
$set['db_pass'] = '';
$set['db_host'] = 'localhost';
$set['db_port'] = 3306;
$set['db_socket'] = '';
$set['dbtype'] = '';
$set['installed'] = 0;
$set['admin_email'] = 'webmaster@localhost';
?>