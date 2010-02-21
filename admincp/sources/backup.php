<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2010 Roger Libiez http://www.iguanadons.net
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

if (!defined('PDNSADMIN') || !defined('PDNS_ADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

require_once $set['include_path'] . '/admincp/admin.php';

/**
 * Database backup
 *
 * @author Aaron Smith-Hayes <davionkalhen@gmail.com>
 * @since 1.0.0
 **/
class backup extends admin
{
	/**
	 * Database backup
	 *
	 * @author Aaron Smith-Hayes <davionkalhen@gmail.com>
	 * @since 1.0.0
	 * @return string HTML
	 **/
	function execute()
	{
		if (!isset($this->get['s'])) {
			$this->get['s'] = '';
		}

		switch($this->get['s'])
		{
		case 'create':
			$this->set_title($this->lang->backup_create);
			$this->tree($this->lang->backup_create);

			return $this->create_backup();
			break;

		case 'restore':
			$this->set_title($this->lang->backup_restore);
			$this->tree($this->lang->backup_restore);

			return $this->restore_backup();
			break;
		}
	}

	/**
	 * Generate a backup
	 *
	 * @author Aaron Smith-Hayes <davionkalhen@gmail.com>
	 * @since 1.0.0
	 * @return string HTML
	 **/
	function create_backup()
	{
		if(!isset($this->post['submit'] ) )
			return eval($this->template('ADMIN_BACKUP'));

		$filename = "backup_".$this->version."-".date('y-m-d-H-i-s').".sql";
		$options = '';

		foreach($this->post as $key => $value )
			$$key = $value;
		if(isset($insert))
			$options .= ' -c';
		if(isset($droptable))
			$options .= ' --add-drop-table';

		$tables = implode( ' ', $this->get_db_tables() );

		$mbdump = "mysqldump ".$options." --password=".$this->db->pass." --host=".$this->db->host." --user=".$this->db->user;
		$mbdump .= " --result-file='../packages/".$filename."' ".$this->db->db." ".$tables;

		if( ($fp = popen($mbdump, 'r') ) === false )
			return $this->message($this->lang->backup_create, $this->lang->backup_failed);

		$buf = '';
		while( $c = fgetc($fp) )
			$buf .= $c;
		pclose($fp);
		$this->chmod('../packages/'.$filename, 0777);
		return $this->message($this->lang->backup_create, $this->lang->backup_created ." ../packages/".$filename."<br />". $this->lang->backup_output .": ".$buf, $filename, "../packages/".$filename);
	}

	/**
	 * Restore a backup
	 *
	 * @author Aaron Smith-Hayes <davionkalhen@gmail.com>
	 * @since 1.0.0
	 * @return string HTML
	 **/
	function restore_backup()
	{
		if (!isset($this->get['restore']))
		{
			if ( ($dir = opendir('../packages') ) === false )
				return $this->message($this->lang->backup_restore, $this->lang->backup_no_packages);

			$backups = array();
			while( ($file = readdir($dir) ) )
			{
				if(strtolower(substr($file, -4) ) != '.sql')
					continue;
				$backups[] = $file;
			}
			closedir($dir);

			if(count($backups) <= 0 )
				return $this->message($this->lang->backup_restore, $this->lang->backup_none);

			$output = $this->lang->backup_warning . '<br /><br />';
			$output .= $this->lang->backup_found . ':<br /><br />';
			$count = 0;

			foreach( $backups as $bkup )
			{
				$output .= "<a href='{$this->self}?a=backup&amp;s=restore&amp;restore=".$bkup."'>".$bkup."</a><br />";
			}
			return $this->message($this->lang->backup_restore, $output);
		}

		if(!file_exists('../packages/' . $this->get['restore']) )
			return $this->message($this->lang->backup_restore, $this->lang->backup_noexist);

		$mbimport = "mysql --password=".$this->db->pass." --host=".$this->db->host." --user=".$this->db->user." ".$this->db->db." < ../packages/".$this->get['restore'];
		if( ($fp = popen($mbimport, 'r') ) === false )
			return $this->message($this->lang->backup_restore, $this->lang->backup_import_fail);

		$output = '';
		while($c = fgetc($fp) )
			$output .= $c;
		return $this->message($this->lang->backup_restore, $this->lang->backup_restore_done ."<br />". $this->lang->backup_output .": ".$output);
	}
}
?>