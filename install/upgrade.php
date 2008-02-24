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

require_once $set['include_path'] . '/global.php';
require_once $set['include_path'] . '/lib/xmlparser.php';
require_once $set['include_path'] . '/lib/packageutil.php';

/**
 * Board Upgrade
 *
 * @author Jason Warner <jason@mercuryboard.com>
 */
class upgrade extends qsfglobal
{
	function upgrade_board( $step )
	{
		switch($step) {
		default:
			echo "<form action='{$this->self}' method='get'>
			    <table border='0' cellpadding='4' cellspacing='0'>\n";

			check_writeable_files();

			echo "<tr><td colspan='2' align='center'><b>Upgrade from what version?</b></td></tr>";

			include 'templates/upgradefromversion.php';

			echo "    </table>
			    </form>\n";

			break;

		// Step 1.5 simply updates the database info
		case 15:
			$this->sets['db_host']   = $this->post['db_host'];
			$this->sets['db_name']   = $this->post['db_name'];
			$this->sets['db_user']   = $this->post['db_user'];
			$this->sets['db_pass']   = $this->post['db_pass'];
			$this->sets['db_port']   = $this->post['db_port'];
			$this->sets['db_socket'] = $this->post['db_socket'];
			$this->sets['prefix']    = trim(preg_replace('/[^a-zA-Z0-9_]/', '', $this->post['prefix']));

			if (!$this->write_db_sets('../settings.php')) {
				echo 'settings.php could not be updated.<br /><br />CHMOD settings.php to 0666.';
				break;
			}
			// Fall through to the next case

		case 2:
			@set_time_limit(300);

			// Check to see if all upgrade files are intact
			$check = $this->get['from'];
			while ($check <= LATEST)
			{
				if (!is_readable("./upgrade_$check.php")) {
					echo "A file required for upgrading was not found: upgrade_$check.php";
					break 2;
				}
				$check++;
			}

			$db = new $this->modules['database']($this->sets['db_host'], $this->sets['db_user'], $this->sets['db_pass'], $this->sets['db_name'],
				$this->sets['db_port'], $this->sets['db_socket'], $this->sets['prefix']);

			if (!$db->connection) {
				if ($this->get['step'] == 15) {
					$sets_error = '<br />Could not connect with the specified information.';
				} else {
					$sets_error = null;
				}

				include 'templates/upgradefromdatabase.php';
				break;
			}

			if (!is_writeable('../settings.php')) {
				echo "settings.php cannot be updated.<br /><br />CHMOD settings.php to 0666.";
				break;
			}

			$queries = array();
			$pre = $this->sets['prefix'];
			$full_template_list = false;
			$template_list = array();
			$new_permissions = array();

			$this->sets['installed'] = 1;

			$this->pre  = $this->sets['prefix'];
			$this->db   = $db;

			// We can't get settings from the database unless we're already running >= 1.1.0
			if ($this->get['from'] >= 10) {
				$this->sets = $this->get_settings($this->sets);
			}

			$this->perms = new $this->modules['permissions']($this);

			while ($this->get['from'] <= LATEST)
			{
				include "./upgrade_{$this->get['from']}.php";
				$this->get['from']++;

				// This gets really complicated so be careful
				if (is_bool($need_templates)) {
					if ($need_templates) {
						$full_template_list = true;
					}
				} else {
					$template_list = array_unique(array_merge($template_list, $need_templates));
				}
			}

			if (!$this->write_db_sets('../settings.php')) {
				echo 'settings.php could not be updated.<br /><br />CHMOD settings.php to 0666.';
				break;
			}

			/**
			 * The order this next block executes is important.
			 * 1. Verify we can upgrade templates
			 * 2. Upgrade board
			 * 3. Upgrade templates
			 *
			 * Because the query used to upgrade templates is for
			 * the most recent version of the board, we must run
			 * it after the board is fully upgraded.
			 **/

			if ($need_templates && !is_readable(SKIN_FILE)) {
				echo 'No templates could be loaded from ' . SKIN_FILE;
				break;
			}

			execute_queries($queries, $this->db);

			$queries = array();
			
			// Check the default skin still exists
			$result = $this->db->fetch("SELECT * FROM %pskins WHERE skin_dir='default'");
			if (!$result) {
				$this->db->query("INSERT INTO %pskins (skin_name, skin_dir) VALUES ('QSF Comet', 'default')");
				$full_template_list = true;
			}
			
			$skinsupdated = "The following templates were upgraded:<br /><br /><span class='tiny'>";
			$didsomething = false;
			$result = $this->db->query("SELECT * FROM %pskins");

			while ($row = $this->db->nqfetch($result))
			{
				$skin = $row['skin_dir'];

				// QSF or MB default skin in default location
				if (($row['skin_name'] == 'QSF Comet' || $row['skin_name'] == 'Candy Corn') && $skin == 'default') {
					if ($full_template_list || $template_list) {
						if ($full_template_list) {
							$template_list = null;
							$this->db->query("DELETE FROM %ptemplates WHERE template_skin='default'");
						
							$skinsupdated .= $row['skin_name'] . ": Full Template Replacement<br />";
						} else {
							$template_list_string = '';
							foreach ($template_list as $temp_name) {
								$template_list_string .= "'$temp_name',";
								$skinsupdated .= $row['skin_name'] . ": " . $temp_name ."<br />";
							}
							$template_list_string = substr($template_list_string, 0, -1);
							$this->db->query("DELETE FROM %ptemplates WHERE template_name IN ($template_list_string) AND template_skin='default'");
						}
						
						// Create template
						$xmlInfo = new xmlparser();
						$xmlInfo->parse(SKIN_FILE);
						$templatesNode = $xmlInfo->GetNodeByPath('QSFMOD/TEMPLATES');
						packageutil::insert_templates('default', $this->db, $templatesNode, $template_list);
						unset($templatesNode);
						$xmlInfo = null;
						
						$didsomething = true;
					}
					if ($row['skin_name'] == 'Candy Corn') {
						$this->db->query("UPDATE %pskins SET skin_name='QSF Comet' WHERE skin_dir='%s'", $skin);
					}
				}
				else
				{
					// Other skins
					$xmlInfo = new xmlparser();
					$xmlInfo->parse(SKIN_FILE);
					$templatesNode = $xmlInfo->GetNodeByPath('QSFMOD/TEMPLATES');
					packageutil::list_templates($templatesNode);
					$temps_to_insert = array();
						
					foreach ($temp_names as $temp_name)
					{
						$miss = $this->db->query("SELECT template_name FROM %ptemplates WHERE template_skin='%s' AND template_name='%s'",
							$skin, $temp_name);

						if ($this->db->num_rows($miss) < 1) {
							$skinsupdated .= $row['skin_name'] . ": Added: " . $temp_name ."<br />";
							$temps_to_insert[] = $temp_name;
						}
					}
					
					if ($temps_to_insert) {
						$templatesNode = $xmlInfo->GetNodeByPath('QSFMOD/TEMPLATES');
						packageutil::insert_templates($skin, $this->db, $templatesNode, $temps_to_insert);
						$didsomething = true;
					}
					$xmlInfo = null;
				}

				/* Iterate over all our templates. This is excessive, but only needs to be done once anyway. */
				$sql = "SELECT template_html, template_name FROM {$this->pre}templates WHERE template_skin='{$skin}'";
				$query = $this->db->query($sql);

				while ($row2 = $this->db->nqfetch($query))
				{
					if( strstr( $row2['template_html'], '{$messageclass}' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('{$messageclass}', '<MODLET messagelink(class)>', $row2['template_html']);
						$updated_temps[] = $row['template_name'];
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '{$MessageLink}' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('{$MessageLink}', '<MODLET messagelink(text)>', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '$mercury' ) ) {
						$didsomething = true;
               		        	        $row2['template_html'] = str_replace('$mercury', '$qsf', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '$qsfboard' ) ) {
						$didsomething = true;
                       	        		$row2['template_html'] = str_replace('$qsfboard', '$quicksilverforums', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '$qsf->lang->main_powered' ) ) {
						$didsomething = true;
	                                	$row2['template_html'] = str_replace('$qsf->lang->main_powered', '$qsf->lang->powered', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '$qsf->lang->main_seconds' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('$qsf->lang->main_seconds', '$qsf->lang->seconds', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '$this->lang->pm_inbox' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('$this->lang->pm_inbox', '$foldername', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '$this->lang->board_topics_new' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('$this->lang->board_topics_new', '$this->lang->main_topics_new', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '$this->lang->forum_topics_new' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('$this->lang->forum_topics_new', '$this->lang->main_topics_new', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '$this->lang->recent_topics_new' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('$this->lang->recent_topics_new', '$this->lang->main_topics_new', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], 'post_mbcode_' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('post_mbcode_', 'mbcode_', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '$qsf->tree' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('$qsf->tree', '$qsf->htmlwidgets->tree', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '$admin->tree' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('$admin->tree', '$admin->htmlwidgets->tree', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '$this->tree' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('$this->tree', '$this->htmlwidgets->tree', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '{$active[\'TOTALCOUNT\']}' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('{$active[\'TOTALCOUNT\']}', 'Skin Update Required', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '{$active[\'MEMBERCOUNT\']}' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('{$active[\'MEMBERCOUNT\']}', 'Skin Update Required', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '{$active[\'GUESTCOUNT\']}' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('{$active[\'GUESTCOUNT\']}', 'Skin Update Required', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
					if( strstr( $row2['template_html'], '{$active[\'USERS\']}' ) ) {
						$didsomething = true;
						$row2['template_html'] = str_replace('{$active[\'USERS\']}', 'Skin Update Required', $row2['template_html']);
						$skinsupdated .= $row['skin_name'] . " Modified: " . $row2['template_name'] . "<br />";
						$this->db->query("UPDATE %ptemplates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s'",
							$row2['template_html'], $skin, $row2['template_name']);
					}
				}
			}

			$this->write_sets();

			// New fields in forum tables need to be fixed in case the old install was a conversion
			$this->updateForumTrees();
			$this->RecountForums();
			
			// Check if new permissions need to be added
			if (!empty($new_permissions)) {
				foreach ($new_permissions as $id => $default)
				{
					// Groups
					while ($this->perms->get_group())
					{
						$perm_on = $default;
						if ($this->perms->auth('is_admin')) $perm_on = true;
						if (!$this->perms->auth('do_anything')) $perm_on = false;
						if ($this->perms->is_guest) $perm_on = false;
						$this->perms->add_perm($id, $perm_on);
						$this->perms->update();
					}
			
					// Users
					while ($this->perms->get_group(true))
					{
						$perm_on = $default;
						if ($this->perms->auth('is_admin')) $perm_on = true;
						if (!$this->perms->auth('do_anything')) $perm_on = false;
						if ($this->perms->is_guest) $perm_on = false;
						$this->perms->add_perm($id, $perm_on);
						$this->perms->update();
					}
				}
			}

			$message ='';
			if ($didsomething) {
				$message = $skinsupdated . "</span>";
			}
			echo $message . "<br />Upgrade successful.<br />";
			echo "<a href='../index.php'>To the board</a>";
			break;
		}
	}
}
?>
