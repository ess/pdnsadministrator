<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2008 Roger Libiez http://www.iguanadons.net
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

class upgrade extends qsfglobal
{
	function upgrade_console( $step )
	{
		switch($step)
		{
			default:
				echo "<form action='{$this->self}?mode=upgrade&amp;step=2' method='post'>
				<table border='0' cellpadding='4' cellspacing='0'>\n";

				check_writeable_files();

				echo "<tr><td colspan='2' align='center'><b>To determine what version you are running, check the bottom of your AdminCP page. Or check the CHANGES file and look for the latest revision mentioned there.</b></td></tr>
				<tr><td colspan='2' align='center'><b>Upgrade from what version?</b></td></tr>
				    <tr>
				        <td><input type='radio' name='from' value='1.1.1' id='111' checked='checked' />
					<label for='111'>PDNS-Admin 1.1.1</label></td>
				    </tr>
				    <tr>
				        <td><input type='radio' name='from' value='1.1' id='11' />
					<label for='11'>PDNS-Admin 1.1</label></td>
				    </tr>
				    <tr>
				        <td><input type='radio' name='from' value='1.0' id='10' />
					<label for='10'>PDNS-Admin 1.0</label></td>
				    </tr>
				    <tr>
				        <td colspan='2' align='center'><br /><input type='submit' value='Continue' /></td>
				    </tr>
				  </table>
				</form>\n";
			break;

			case 2:
				$db = new $this->modules['database']($this->sets['db_host'], $this->sets['db_user'], $this->sets['db_pass'], $this->sets['db_name'],
					$this->sets['db_port'], $this->sets['db_socket']);

				if ( !$db->connection )
				{
					echo 'Couldn\'t select database: ' . mysql_error();
					break;
				}
				$this->db = $db;

				$queries = array();
				$templates_add = false;
				$templates_update = false;
				$this->sets = $this->get_settings($this->sets);

				// Missing breaks are deliberate. Upgrades from older versions need to step through all of this.
				switch($this->post['from'])
				{
					case '1.0': // 1.0 to 1.1
						$templates_update[] = true;

					case '1.1': // 1.1 to 1.1.1

					case '1.1.1': // 1.1.1 to 1.1.2
						$templates_update[] = 'ADMIN_COPYRIGHT';
						$templates_update[] = 'DOMAINS_EDIT';
						$templates_update[] = 'MAIN_COPYRIGHT';

						break;
				}

				if ( ( $templates_add || $templates_update ) && !is_readable('skin_default.xml')) {
					echo 'Templates can not be updated. skin_default.xml file is unreadable.';
					break;
				}

				execute_queries($queries, $this->db);

				if( $templates_add )
				{
					if( $templates_add === true )
						$templates_add = null;

					$xmlInfo = new xmlparser();
					$xmlInfo->parse('skin_default.xml');
					$templatesNode = $xmlInfo->GetNodeByPath('QSFMOD/TEMPLATES');
					packageutil::insert_templates('default', $this->db, $templatesNode, $templates_add);
					unset($templatesNode);
					$xmlInfo = null;
				}

				if( $templates_update )
				{
					$temps = array();

					if( $templates_update === true )
						$temps = null;
					else
					{
						foreach( $templates_update as $update )
						{
							if( $templates_add === false || !in_array( $update, $templates_add ) )
								$temps[] = $update;
						}
					}
					$xmlInfo = new xmlparser();
					$xmlInfo->parse('skin_default.xml');
					$templatesNode = $xmlInfo->GetNodeByPath('QSFMOD/TEMPLATES');
					packageutil::insert_templates('default', $this->db, $templatesNode, $temps);
					unset($templatesNode);
					$xmlInfo = null;
				}

				$this->write_sets();

				echo "Upgrade complete. You can <a href=\"../index.php\">return to your site</a> now.<br />";
				echo "Please DELETE THE INSTALL DIRECTORY NOW for security purposes!!";
				break;
		}
	}
}
?>