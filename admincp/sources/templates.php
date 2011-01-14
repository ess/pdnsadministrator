<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2011 Roger Libiez http://www.iguanadons.net
 *
 * Based on Quicksilver Forums
 * Copyright (c) 2005-2011 The Quicksilver Forums Development Team
 *  http://code.google.com/p/quicksilverforums/
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
require_once $set['include_path'] . '/lib/tar.php';
require_once $set['include_path'] . '/lib/xmlparser.php';
require_once $set['include_path'] . '/lib/packageutil.php';

class templates extends admin
{
	function execute()
	{
		$sections = array(
			'Admin'          => $this->lang->temps_admin,
			'backup'         => $this->lang->temps_backup,
			'cp'             => $this->lang->temps_cp,
			'email'          => $this->lang->temps_email,
			'groups'         => $this->lang->temps_groups,
			'login'          => $this->lang->temps_login,
			'logs'           => $this->lang->temps_logs,
			'Main'           => $this->lang->temps_main,
			'users'          => $this->lang->temps_users,
			'user_control'   => $this->lang->temps_user_control,
			'settings'       => $this->lang->temps_settings,
			'supermaster'    => $this->lang->temps_supermasters,
			'templates'      => $this->lang->temps_templates
		);

		if (!isset($this->get['skin'])) {
			$this->get['skin'] = $this->skin;
		}

		if (!isset($this->get['s'])) {
			$this->get['s'] = null;
		}

		if (!isset($this->get['i'])) {
			$this->get['i'] = null;
		}

		$skins = array();

		$query = $this->db->query('SELECT * FROM skins');
		while ($s = $this->db->nqfetch($query))
		{
			$skins[$s['skin_dir']] = $s['skin_name'];
		}

		if (!isset($skins[$this->get['skin']])) {
			$this->get['skin'] = $this->skin;
		}

		switch($this->get['s'])
		{
		case 'edit_css':
			$this->tree($this->lang->edit_css);
			return $this->edit_css($this->get['skin']);
			break;

		case 'upgradeskin':
			$this->tree($this->lang->upgrade_skin);
			return $this->upgrade_skin($this->get['skin']);
			break;

		case 'add_html':
		        $this->tree($this->lang->add);
		        return $this->add_section($sections, $skins, $this->get['skin']);
		        break;

		case 'edit':
			$this->tree($this->lang->edit, "{$this->self}?a=templates&amp;s=html");
			return $this->edit_section($sections, $skins, $this->get['skin']);
			break;

		case 'skin':
			$this->set_title($this->lang->create_skin);
			$this->tree($this->lang->create_skin);
			return $this->add_skin();
			break;

		case 'editskin':
			$this->set_title($this->lang->edit_skin);
			$this->tree($this->lang->edit_skin);
			return $this->edit_skin();
			break;

		case 'load':
			$this->set_title($this->lang->install_skin);
			$this->tree($this->lang->install_skin);
			return $this->install_skin();
			break;

		case 'export':
			$this->set_title($this->lang->export_skin);
			$this->tree($this->lang->export_skin);
			return $this->export_skin();
			break;

		case 'del_html':
			$this->set_title($this->lang->delete_template);
			$this->tree($this->lang->delete_template);
			return $this->delete_list($sections, $skins, $this->get['skin']);

		case 'delete':
			$this->tree($this->lang->delete_template, "{$this->self}?a=templates&amp;s=del_html");
			return $this->delete_section($sections, $skins, $this->get['skin']);
			break;

		default:
			$this->set_title($this->lang->edit);
			$this->tree($this->lang->edit);
			return $this->template_list($sections, $skins, $this->get['skin']);
		}
	}

	function upgrade_skin($skin)
	{
		if (!isset($this->post['skin'])) {
			$skin_box = $this->htmlwidgets->select_skins($this->skin);
			$token = $this->generate_token();

			return $this->message($this->lang->select_skin, "
			<form action='{$this->self}?a=templates&amp;s=upgradeskin' method='post'>
				<div>
				{$this->lang->upgrade_skin_detail}:<br /><br />
				<select name='skin'>
					{$skin_box}
				</select>
				<input type='hidden' name='token' value='$token' />
				<input type='submit' value='{$this->lang->upgrade_skin}' />
				</div>
			</form>");
		} else {
			if( !$this->is_valid_token() ) {
				return $this->message( $this->lang->upgrade_skin, $this->lang->invalid_token );
			}

			$skin = $this->post['skin'];
			$new_temps = array();
			$updated_temps = array();
			$didsomething = false;

			/* find missing templates and dump code from default */
			$sql = "SELECT * FROM templates WHERE template_skin='default'";
			$query = $this->db->query($sql);

			while ($row = $this->db->nqfetch($query))
			{
					$sql = "SELECT template_name FROM templates WHERE template_skin='%s' AND template_name='%s'";
					$miss = $this->db->query($sql, $skin, $row['template_name']);

					if ($this->db->num_rows($miss) < 1)
					{
						$this->db->query("INSERT INTO templates (template_skin, template_set, template_name, template_html, template_displayname, template_description)
							VALUES( '%s', '%s', '%s', '%s', '%s', '%s')", $skin, $row['template_set'], $row['template_name'], $row['template_html'], $row['template_displayname'], $row['template_description']);

						$didsomething = true;
						$new_temps[] = $row['template_name'];
					}
			}

		        if ($didsomething) {
				$message = $skin . " " . $this->lang->upgrade_skin_upgraded . "<br /><br />{$this->lang->upgraded_templates}:<br /><br />" . implode('<br />', $new_temps) . implode('<br />', $updated_temps);
				return $this->message($this->lang->upgrade_skin, $message);
			} else {
				return $this->message($this->lang->upgrade_skin, "{$skin} {$this->lang->upgrade_skin_already}");
			}
		}
	}

	function install_skin()
	{
		if (!isset($this->post['submit']) && !isset($this->get['newskin'])
				&& !isset($this->get['skindetails']) && !isset($this->get['temp']))
		{
			// Now check for skins using the NEW method
			// build a list of all the xml skin files

			$new_skin_box = '';
			$packages = packageutil::scan_packages();

			foreach ($packages as $package) {
				if ($package['type'] != 'skin')
					continue; // skip other mods

				$new_skin_box .= "  <li><a href=\"{$this->self}?a=templates&amp;s=load&amp;newskin=";
				
				if (strtolower(substr($package['file'], -7)) == '.tar.gz')
				{
					$new_skin_box .= urlencode(substr($package['file'], 0, -7)) . "\" ";
				}
				else
				{
					$new_skin_box .= urlencode(substr($package['file'], 0, -4)) . "\" ";
				}

				if ($package['desc'])
					$new_skin_box .= "title=\"" . htmlspecialchars($package['desc']) . "\"";

				$new_skin_box .= ">";

				$new_skin_box .= "<strong>" . htmlspecialchars($package['title']) . "</strong></a>";
				$new_skin_box .= " " . htmlspecialchars($package['version']);
				$new_skin_box .= " (" . htmlspecialchars($package['author']) . ")";

				$new_skin_box .= "</li>\n";
			}

			return $this->message($this->lang->install_skin, eval($this->template('ADMIN_INSTALL_SKIN')));
		} else if (isset($this->get['skindetails'])) {
			// Display some preview information on the skin
		} else if (isset($this->get['newskin'])) {
			// Use new method of install

			$tarTool = new archive_tar();

			// Open and parse the XML file
			$xmlInfo = new xmlparser();

			if (file_exists('../packages/' . $this->get['newskin'] . '.xml'))
			{
				$xmlInfo->parse('../packages/' . $this->get['newskin'] . '.xml');
			}
			else if (file_exists('../packages/' . $this->get['newskin'] . '.tar'))
			{
				$tarTool->open_file_reader('../packages/' . $this->get['newskin'] . '.tar');

				$xmlFilename = $tarTool->extract_file('package.txt');
				
				$xmlInfo->parseTar($tarTool, $xmlFilename);
			}
			else if (file_exists('../packages/' . $this->get['newskin'] . '.tar.gz')
				&& $tarTool->can_gunzip())
			{
				$tarTool->open_file_reader('../packages/' . $this->get['newskin'] . '.tar.gz');

				$xmlFilename = $tarTool->extract_file('package.txt');
				
				$xmlInfo->parseTar($tarTool, $xmlFilename);
			}
			else
			{
				return $this->message($this->lang->install_skin, $this->lang->skin_none);
			}

			// Get the folder name
			$node = $xmlInfo->GetNodeByPath('QSFMOD/TYPE');
			$skin_dir = $node['attrs']['FOLDER'];

			// Run the uninstall queries
			packageutil::run_queries($this->db, $xmlInfo->GetNodeByPath('QSFMOD/UNINSTALL'));

			// Run the install queries
			packageutil::run_queries($this->db, $xmlInfo->GetNodeByPath('QSFMOD/INSTALL'));

			// Add the templates
			packageutil::insert_templates($skin_dir, $this->db, $xmlInfo->GetNodeByPath('QSFMOD/TEMPLATES'));
			
			// Extract the files

			if (file_exists('../packages/' . $this->get['newskin'] . '.tar')) {
				$tarTool->open_file_reader('../packages/' . $this->get['newskin'] . '.tar');
			} else {
				$tarTool->open_file_reader('../packages/' . $this->get['newskin'] . '.tar.gz');
			}

			$nodes = $xmlInfo->GetNodeByPath('QSFMOD/FILES');
			foreach ($nodes['child'] as $node) {
				if ($node['name'] == 'FILE') {
					$filename = $node['content'];
					$data = $tarTool->extract_file($filename);
					if ($data !== false) {
						$this->_make_dir('../' . $filename);
						$fh = fopen('../' . $filename, 'wb');
						fwrite($fh, $data);
						fclose($fh);
					}
				}
			}
			$tarTool->close_file();

			$this->chmod('../skins/' . $skin_dir, 0777, true);

			return $this->message($this->lang->install_skin, $this->lang->install_done);
		}
	}

	function export_skin()
	{
		if (!isset($this->post['skin'])) {
			$skin_box = $this->htmlwidgets->select_skins($this->skin);
			$token = $this->generate_token();

			return $this->message($this->lang->export_skin, "
			{$this->lang->export_select}:<br /><br />
			<form action='{$this->self}?a=templates&amp;s=export' method='post'>
				<div>
				<select name='skin'>
					{$skin_box}
				</select>
				<input type='hidden' name='token' value='$token' />
				<input type='submit' value='{$this->lang->export_skin}' />
				</div>
			</form>");
		} else {
			if( !$this->is_valid_token() ) {
				return $this->message( $this->lang->export_skin, $this->lang->invalid_token );
			}

			// Dump the skin data into an XML file
			$skin = $this->db->fetch("SELECT * FROM skins WHERE skin_dir='%s'", $this->post['skin']);
			
			$fullSkinName = $skin['skin_dir'] . "-" . $this->version;
			
			if (file_exists("../packages/skin_$fullSkinName.xml")) {
				unlink("../packages/skin_$fullSkinName.xml");
			}

			$xmlFile = fopen("../packages/skin_$fullSkinName.xml", 'w');
			
			if ($xmlFile === false) {
				return $this->message($this->lang->export_skin, "Error: Could not open file packages/skin_$fullSkinName.xml for writing");
			}
			
			fwrite($xmlFile, "<?xml version='1.0' encoding='utf-8'?>\n");
			fwrite($xmlFile, "<qsfmod>\n");
			fwrite($xmlFile, "  <title>Skin: " . htmlspecialchars($skin['skin_name']) . "</title>\n");
			// Skin types need to specify a folder
			fwrite($xmlFile, "  <type folder=\"" . htmlspecialchars($skin['skin_dir']) . "\">skin</type>\n");
			fwrite($xmlFile, "  <version>{$this->version}</version>\n");
			fwrite($xmlFile, "  <description></description>\n");
			fwrite($xmlFile, "  <authorname>Skin Exporter</authorname>\n");
			fwrite($xmlFile, "  <files>\n");
			fwrite($xmlFile, "    <file>packages/skin_$fullSkinName.xml</file>\n");
			$files = $this->recursive_dir("../skins/{$skin['skin_dir']}", "skins/{$skin['skin_dir']}");
			foreach ($files as $file) {
				fwrite($xmlFile, "    <file>" . htmlspecialchars($file) . "</file>\n");
			}
			fwrite($xmlFile, "  </files>\n");
			fwrite($xmlFile, "  <templates>\n");
			
        	$query = $this->db->query("SELECT * FROM templates WHERE template_skin='%s'", $skin['skin_dir']);
	        while ($row = $this->db->nqfetch($query))
			{
				fwrite($xmlFile, "    <template><set>{$row['template_set']}</set><name>{$row['template_name']}</name>\n");
				fwrite($xmlFile, "      <displayname>" . htmlspecialchars($row['template_displayname']) . "</displayname>\n");
				fwrite($xmlFile, "      <description>" . htmlspecialchars($row['template_description']) . "</description>\n");
				fwrite($xmlFile, "      <html><![CDATA[\n");
				fwrite($xmlFile, utf8_encode(trim($row['template_html'])) .  "\n");
				fwrite($xmlFile, "      ]]></html>\n");
				fwrite($xmlFile, "    </template>\n");
			}
       		       
			fwrite($xmlFile, "  </templates>\n");
			fwrite($xmlFile, "  <install>\n");
			fwrite($xmlFile, "    <query>\n");
			fwrite($xmlFile, "      <sql>INSERT INTO skins (skin_name, skin_dir) VALUES ('%s', '%s')</sql>\n");
			fwrite($xmlFile, "      <data>" . htmlspecialchars($skin['skin_name']) . "</data>\n");
			fwrite($xmlFile, "      <data>" . htmlspecialchars($skin['skin_dir']) . "</data>\n");
			fwrite($xmlFile, "    </query>\n");
			fwrite($xmlFile, "  </install>\n");
			fwrite($xmlFile, "  <uninstall>\n");
			fwrite($xmlFile, "    <query>\n");
			fwrite($xmlFile, "      <sql>DELETE FROM skins WHERE skin_dir='%s'</sql>\n");
			fwrite($xmlFile, "      <data>" . htmlspecialchars($skin['skin_dir']) . "</data>\n");
			fwrite($xmlFile, "    </query>\n");
			fwrite($xmlFile, "    <query>\n");
			fwrite($xmlFile, "      <sql>DELETE FROM templates WHERE template_skin='%s'</sql>\n");
			fwrite($xmlFile, "      <data>" . htmlspecialchars($skin['skin_dir']) . "</data>\n");
			fwrite($xmlFile, "    </query>\n");
			fwrite($xmlFile, "  </uninstall>\n");
			fwrite($xmlFile, "</qsfmod>\n");

			fclose($xmlFile);
			
			$tarTool = new archive_tar();
			$tarTool->open_file_writer("../packages/skin_$fullSkinName", true);
			// Always wise to make these first for speed
			$tarTool->add_as_file("packages/skin_$fullSkinName.xml", 'package.txt');
			$tarTool->add_file("../packages/skin_$fullSkinName.xml", "packages/skin_$fullSkinName.xml");
			// Now throw in everything else
			$tarTool->add_dir("../skins/{$skin['skin_dir']}", "skins/{$skin['skin_dir']}");
			$filename = $tarTool->close_file();
			
			@unlink("../packages/skin_$fullSkinName.xml");
			
			$this->chmod($filename, 0777);

			return $this->message($this->lang->export_skin, $this->lang->export_done, basename($filename), $filename);
		}
	}

	function edit_skin()
	{
		if (!isset($this->post['skin'])) {
			$skin_box = $this->htmlwidgets->select_skins($this->skin);
			$token = $this->generate_token();

			return $this->message($this->lang->select_skin, "
			<form action='{$this->self}?a=templates&amp;s=editskin' method='post'>
				<div>
				{$this->lang->select_skin_edit}:<br /><br />
				<select name='skin'>
					{$skin_box}
				</select>
				<input type='hidden' name='token' value='$token' />
				<input type='submit' value='{$this->lang->edit_skin}' />
				</div>
			</form>");
		} else {
			if( !$this->is_valid_token() ) {
				return $this->message( $this->lang->edit_skin, $this->lang->invalid_token );
			}

			if (!isset($this->post['submit'])) {
				$skin = $this->db->fetch("SELECT skin_name, skin_dir FROM skins WHERE skin_dir='%s'", $this->post['skin']);
				$token = $this->generate_token();

				return eval($this->template('ADMIN_EDIT_SKIN'));
			} else {
				if (isset($this->post['deleteskin'])) {
					$existing = $this->db->fetch("SELECT skin_dir FROM skins WHERE skin_dir!='%s' LIMIT 1", $this->post['skin']);
					if (!isset($existing['skin_dir'])) {
						return $this->message($this->lang->edit_skin, $this->lang->only_skin);
					}

					$this->remove_dir("../skins/{$this->post['skin']}");

					$this->db->query("DELETE FROM skins WHERE skin_dir='%s'", $this->post['skin']);
					$this->db->query("DELETE FROM templates WHERE template_skin='%s'", $this->post['skin']);
					$this->db->query("UPDATE users SET user_skin='%s' WHERE user_skin='%s'", $existing['skin_dir'], $this->post['skin']);

					return $this->message($this->lang->edit_skin, $this->lang->skin_deleted);
				} else {
					if ((trim($this->post['skin_name']) == '') || (trim($this->post['skin_dir']) == '')) {
						return $this->message($this->lang->edit_skin, $this->lang->skin_dirname);
					}

					$dup = false;

					// If we're changing the skin directory
					if ($this->post['skin_dir'] != $this->post['skin']) {
						$this->post['skin_dir'] = preg_replace('/[^a-zA-Z0-9]/', '', $this->post['skin_dir']);

						while (file_exists("../skins/{$this->post['skin_dir']}"))
						{
							$dup = true;
							$this->post['skin_dir'] .= '1';
						}

						rename("../skins/{$this->post['skin']}", "../skins/{$this->post['skin_dir']}");

						$this->db->query("UPDATE templates SET template_skin='%s' WHERE template_skin='%s'", $this->post['skin_dir'], $this->post['skin']);
						$this->db->query("UPDATE users SET user_skin='%s' WHERE user_skin='%s'", $this->post['skin_dir'], $this->post['skin']);
					}

					$this->db->query("UPDATE skins SET skin_name='%s', skin_dir='%s' WHERE skin_dir='%s'",
						$this->post['skin_name'], $this->post['skin_dir'], $this->post['skin']);

					if (!$dup) {
						return $this->message($this->lang->edit_skin, $this->lang->select_skin_edit_done);
					} else {
						return $this->message($this->lang->edit_skin, "{$this->lang->skin_dup} <b>{$this->post['skin_dir']}</b>.");
					}
				}
			}
		}
	}

	function edit_css($skin)
	{
		if (!isset($this->post['submit'])) {
			if (!isset($this->get['file'])) {
				return $this->message( $this->lang->edit_css, $this->lang->no_file );
			}

			$fname = $this->get['file'];

			$file = "../skins/" . $skin . "/" . $fname;
			$fp = fopen( $file, "r" );
			$text = fread( $fp, filesize($file) );
			fclose($fp);

			$token = $this->generate_token();

			return eval($this->template('ADMIN_CSS_EDIT'));
		} else {
			if( !$this->is_valid_token() ) {
				return $this->message( $this->lang->edit_css, $this->lang->invalid_token );
			}

			if (!isset($this->get['file'])) {
				return $this->message( $this->lang->edit_css, $this->lang->no_file );
			}

			$fname = $this->get['file'];

			/* just in-case */
			if (strtolower(substr($fname, -4)) != '.css')
				return $this->message( $this->lang->edit_css, $this->lang->no_file );

			$text = str_replace( "\r", "", $this->post['css_text'] );

			$file = "../skins/" . $skin . "/" . $fname;
			$fp = @fopen( $file, "w" ) or $this->handle_perms( $file );

			if (false === $fp)
				return $this->message($this->lang->edit_css, $this->lang->css_fioerr );

			fwrite( $fp, $text );
			fclose($fp);

			return $this->message($this->lang->edit_css, $this->lang->css_edited, $this->lang->continue, "$this->self?a=templates&amp;skin=$skin" );
		}
	}

	/**
	 * Tries to fix file permissions on un-openable files
	 * 
	 * @prama $file the file to fix
	 * @returns file handle on sucess or false on failure.
	 */
	function handle_perms($file)
	{
		$fd = false;

		if ( false !== @chmod($file, 0777) )
		{
			if ( false !== ($fd = @fopen( $file, 'w' )) )
				return $fd;
		}
		return $fd;
	}

	function remove_dir($dir)
	{
		$dp = opendir($dir);

		while (($file = readdir($dp)) !== false)
		{
			if (($file == '.') || ($file == '..')) {
				continue;
			}

			if (is_dir("$dir/$file")) {
				$this->remove_dir("$dir/$file");
			} else {
				unlink("$dir/$file");
			}
		}

		closedir($dp);
		rmdir($dir);
	}

	function delete_list($sections, $skins, $template)
	{
		$skin_box = $this->htmlwidgets->select_skins($template);

		$out = "";
		$action = 'delete';
		$query = $this->db->query("SELECT DISTINCT(template_set) as temp_set FROM templates WHERE template_skin='%s'", $template);
		while ($data = $this->db->nqfetch($query))
		{
			if (!isset($sections[$data['temp_set']])) {
				$sections[$data['temp_set']] = $data['temp_set'];
			}
			$out .= eval($this->template('ADMIN_TEMPLATE_ENTRY'));
		}
		return eval($this->template('ADMIN_LIST_TEMPLATES_DELETE'));
	}

	function template_list($sections, $skins, $template)
	{
		$skin_box = $this->htmlwidgets->select_skins($template);

		$out = "";
		$action = 'edit';
		$query = $this->db->query("SELECT DISTINCT(template_set) as temp_set FROM templates WHERE template_skin='%s'", $template);
		while ($data = $this->db->nqfetch($query))
		{
			if (!isset($sections[$data['temp_set']])) {
				$sections[$data['temp_set']] = $data['temp_set'];
			}
			$out .= eval($this->template('ADMIN_TEMPLATE_ENTRY'));
		}

		$css = "";
		$dir = "../skins/" . $template;
		$dp = opendir( $dir );
		while (($file = readdir($dp)) !== false)
		{
			$ext = strtolower(substr($file, -4));

			if ($ext == '.css') {
				$css .= "<li><a href='{$this->self}?a=templates&amp;s=edit_css&amp;skin=$template&amp;file=$file'>{$file}</a></li>";
			}
		}
		closedir($dp);

		return eval($this->template('ADMIN_LIST_TEMPLATES'));
	}

	function add_section($sections, $skins, $template)
	{
		if (!isset($this->post['form'])) {
			$skin_box = $this->htmlwidgets->select_skins($template);
			$token = $this->generate_token();
			$template_box = '';

			$query = $this->db->query("SELECT DISTINCT(template_set) as temp_set FROM templates WHERE template_skin='%s'", $template);
			while ($data = $this->db->nqfetch($query))
			{
				if (!isset($sections[$data['temp_set']])) {
					$sections[$data['temp_set']] = $data['temp_set'];
				}
				$template_box .= "<option value='{$data['temp_set']}'>{$sections[$data['temp_set']]}</option>";
			}
			return eval($this->template('ADMIN_ADD_TEMPLATE'));
        	} else {
			if( !$this->is_valid_token() ) {
				return $this->message( $this->lang->add, $this->lang->invalid_token );
			}

		        $template = $this->post['template'];
		        $template_set = !empty($this->post['ntemplate_set']) ? $this->post['ntemplate_set'] : $this->post['template_set'];

		        $name = $this->post['name'];
		        $html = $this->post['html'];
		        $title = $this->post['title'];
		        $desc = $this->post['desc'];

			if (empty($name) || empty($html) || empty($title) || empty($desc)) {
				return $this->message($this->lang->add, $this->lang->all_fields_required);
			}

		        $this->db->query("INSERT INTO templates (template_skin, template_set, template_name, template_html, template_displayname, template_description)
					VALUES ('%s', '%s', '%s', '%s', '%s', '%s')",
					$template, $template_set, $name, $html, $title, $desc);
		        return $this->message($this->lang->templates, $this->lang->template_added, $this->lang->continue, "$this->self?a=templates&amp;skin=$template");
	        }
	}

	function delete_section($sections, $skins, $template)
	{
		$title = isset($sections[$this->get['section']]) ? $sections[$this->get['section']] : $this->get['section'];
		$this->tree($title);

		if (!isset($this->post['submit']) &&!isset($this->post['submitTemp'])) {
			$query = $this->db->query("SELECT template_displayname, template_description, template_name, template_html, template_set
				FROM templates WHERE template_skin='%s' AND template_set='%s'", $this->get['skin'], $this->get['section']);

			$out = "";
			while ($data = $this->db->nqfetch($query))
			{
			   $out .= "<option value='{$data['template_name']}'>" . $data['template_name'] . "</option>";
			}

			$token = $this->generate_token();

			return $this->message($this->lang->delete_template, "
				<form action='{$this->self}?a=templates&amp;s=delete&amp;section={$this->get['section']}&amp;skin={$this->get['skin']}' method='post'>
				<div>
				{$this->lang->select_template}:<br /><br />
				<select name='template'>
					{$out}
				</select>
				<input type='hidden' name='token' value='$token' />
				<input type='submit' name='submit' value='{$this->lang->delete_template}' />
				</div></form>");
		} elseif( !isset($this->get['i'])) {
			$query = $this->db->query("SELECT template_displayname, template_description, template_name, template_html
				FROM templates WHERE template_skin='%s' AND template_name='%s'", $template, $this->post['template']);

			$name = $this->post['template'];
			$section = $this->get['section'];
			$skin = $this->get['skin'];

			$list = '';
			while ($data = $this->db->nqfetch($query))
			{
				$template_name = $data['template_name'];

				$data['template_html'] = $this->format($data['template_html'], FORMAT_HTMLCHARS);
				$list .= eval($this->template('ADMIN_TEMPLATE_DELETE_CONTENTS'));
			}

			$out = eval($this->template('ADMIN_DELETE_TEMPLATE'));
			return $this->message($this->lang->delete_template,$out);
		} else {
			if( !$this->is_valid_token() ) {
				return $this->message( $this->lang->delete_template, $this->lang->invalid_token );
			}

			$this->db->query("DELETE FROM templates WHERE template_skin='%s' AND template_name='%s'", $this->get['skin'], $this->post['submitTemp']);
			return $this->message($this->lang->delete_template,$this->lang->deleted);
		}
	}

	function edit_section($sections, $skins, $template)
	{
		$title = isset($sections[$this->get['section']]) ? $sections[$this->get['section']] : $this->get['section'];
		$this->tree($title);

		if (!isset($this->post['submitTemps'])) {
			$token = $this->generate_token();

			$query = $this->db->query("SELECT template_displayname, template_description, template_name, template_html
				FROM templates WHERE template_skin='%s' AND template_set='%s' ORDER BY template_name", $template, $this->get['section']);

			$out = "";
			while ($data = $this->db->nqfetch($query))
			{
				$data['template_html'] = $this->format($data['template_html'], FORMAT_HTMLCHARS);

				$out .= eval($this->template('ADMIN_EDIT_TEMPLATE_ENTRY'));
			}
			return eval($this->template('ADMIN_EDIT_TEMPLATE'));
		} else {
			if( !$this->is_valid_token() ) {
				return $this->message( $this->lang->templates, $this->lang->invalid_token );
			}

			$evil = 0;

			foreach ($this->post['code'] as $var => $val)
			{
				if ($var == 'MAIN' ) {
					if (stristr($val, '$copyright') === false)
					{
						$evil = 1;
						continue;
					}
				}
				if ($var == 'MAIN_COPYRIGHT' ) {
					if (stristr($val, 'Quicksilver Forums') === false ||
						stristr($val, 'http://www.quicksilverforums.com') === false)
					{
						$evil = 1;
						continue;
					}
				}
				if ($var == 'ADMIN_COPYRIGHT' ) {
					if (stristr($val, 'Quicksilver Forums') === false ||
						stristr($val, 'http://www.quicksilverforums.com') === false)
					{
						$evil = 1;
						continue;
					}
				}

				$this->db->query("UPDATE templates SET template_html='%s' WHERE template_skin='%s' AND template_name='%s' AND template_set='%s'",
					$val, $template, $var, $this->get['section']);
			}

			if (!$evil) {
				$out = $this->message($this->lang->templates, $this->lang->template_updated, $this->lang->continue, "$this->self?a=templates&amp;skin=$template");
			} else {
				$out = $this->message($this->lang->templates, $this->lang->credit);
			}
		}
		return $out;
	}

	function add_skin()
	{
		if (!isset($this->post['submit'])) {
			$skin_box = $this->htmlwidgets->select_skins(0);
			$token = $this->generate_token();

			return $this->message($this->lang->create_skin, "
			<form action='{$this->self}?a=templates&amp;s=skin' method='post'>
				<div>
				{$this->lang->create_new} <input type='text' name='new_name' size='24' maxlength='32' class='input' /> {$this->lang->based_on}
				<select name='new_based'>
					{$skin_box}
				</select><br /><br />
				<input type='hidden' name='token' value='$token' />
				<input type='submit' name='submit' value='{$this->lang->create_skin}' />
				</div>
			</form>");
		} else {
			if( !$this->is_valid_token() ) {
				return $this->message( $this->lang->create_skin, $this->lang->invalid_token );
			}

			if (trim($this->post['new_name']) == '') {
				return $this->message($this->lang->create_skin, $this->lang->skin_name);
			}

			$name = preg_replace('/[^a-zA-Z0-9]/', '', $this->post['new_name']);
			while (file_exists("../skins/$name"))
			{
				$name .= '1';
			}

			if (!$this->dir_copy("../skins/{$this->post['new_based']}/", "../skins/$name/")) {
				return $this->message($this->lang->create_skin, $this->lang->skin_chmod);
			}
			$this->chmod("../skins/$name/",0775,true);

			$this->db->query("INSERT INTO skins (skin_name, skin_dir) VALUES ('%s', '%s')", $this->post['new_name'], $name);

			$query = $this->db->query("SELECT * FROM templates WHERE template_skin='%s'", $this->post['new_based']);
			while ($r = $this->db->nqfetch($query))
			{
				$this->db->query("INSERT INTO templates (template_skin, template_set, template_name, template_html, template_displayname, template_description)
					VALUES('%s', '%s', '%s', '%s', '%s', '%s')",
					$name, $r['template_set'], $r['template_name'], $r['template_html'], $r['template_displayname'], $r['template_description']);
			}

			return $this->message($this->lang->create_skin, $this->lang->skin_created, $this->lang->continue, "$this->self?a=templates&amp;s=html&amp;skin=$name");
		}
	}
	
	function recursive_dir($dirname, $virtual_path)
	{
		$files = array();
		
		$dp = opendir($dirname);
	
		while (($file = readdir($dp)) !== false)
		{
			if ($file{0} == '.') {
				// Don't include hidden files
				continue;
			}
	
			$real_path = $dirname . '/' . $file;
	
			if (is_dir($real_path)) {
				$files = array_merge($files, $this->recursive_dir($real_path, $virtual_path . '/' . $file));
			} else if (is_file($real_path)) {
				$files[] = $virtual_path . '/' . $file;
			}
		}
	
		closedir($dp);
		
		return $files;
	}
	
	function _make_dir($filename)
	{
		$path_parts = explode('/', $filename);
		array_pop($path_parts); // remove the filename

		$check_dir_exists = '';
		for ($i = 0; $i < count($path_parts); $i++)
		{
			$check_dir_exists .= $path_parts[$i];
			
			if (!is_dir($check_dir_exists)) {
				mkdir($check_dir_exists);
				$this->chmod($check_dir_exists, 0777);
			}
				
			$check_dir_exists .= '/';
		}
	}


	/**
	 * Executes an array of queries
	 *
	 * Note: Only used for loading old templates
	 *
	 * @param $queries
	 * @param $db
	 * @author Jason Warner <jason@mercuryboard.com>
	 * @since 1.0.2
	 * @return void
	 **/
	function execute_queries($queries)
	{
		foreach ($queries as $query)
		{
			$query = str_replace('%', '%%', $query);
			// Strip out reference to template position!
			$query = preg_replace('/^(.+), template_position\)(.+), \d+\)$/s', '\1)\2)', $query);
			$this->db->query($query);
		}
	}
}
?>