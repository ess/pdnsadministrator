<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2010 Roger Libiez http://www.iguanadons.net
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

if (!defined('PDNSADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

require_once $set['include_path'] . '/lib/tar.php';
require_once $set['include_path'] . '/lib/xmlparser.php';

/**
 * Contains funtions to handle common package routines
 *
 * @author Geoffrey Dunn <geoff@warmage.com>
 * @since 1.3.0
 **/
class packageutil
{
	/**
	 * Run through node for query elements and run as sql
	 *
	 * @param object Database object
	 * @param array  XML node with query elements
	 *
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.3.0
	 **/
	function run_queries(&$db, &$nodes)
	{
		foreach ($nodes['child'] as $node) {
			if ($node['name'] == 'QUERY') {
				// Build up query and data
				$query = '';
				$data = array();
				foreach ($node['child'] as $element) {
					switch($element['name'])
					{
					case 'SQL':
						$query = $element['content'];
						break;
					case 'DATA':
						if (isset($element['content'])) {
							$data[] = $element['content'];
						} else {
							$data[] = 0;
						}
						break;
					}
				}

				if ($query) {
					array_unshift($data, $query);
					$db->query($data);
				}
			}
		}
	}

	/**
	 * Run through node and get a list of template names
	 *
	 * @param array  XML node with query elements
	 *
	 * @return array list of template names present
	 *
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.3.0
	 **/
	function list_templates(&$nodes)
	{
		$templates_names = array();

		foreach ($nodes['child'] as $node) {
			if ($node['name'] == 'TEMPLATE') {
				$temp_name = '';

				foreach ($node['child'] as $element) {
					if (isset($element['content'])) {
						switch($element['name']) {
						case 'NAME':
							$temp_name = $element['content'];
							break;
						}
					}
				}
				if (empty($temp_name)) {
					return "ERROR: No data available for template\n";
				}

				$templates_names[] = $temp_name;
			}
		}
		return $templates_names;
	}

	/**
	 * Run through node for templates to insert
	 *
	 * @param string Skin folder name to use
	 * @param object Database object
	 * @param array  XML node with query elements
	 * @param array  Optional list of template names to insert 
	 *
	 * @return array list of template names inserted
	 *
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.3.0
	 **/
	function insert_templates($skin_dir, &$db, &$nodes, $template_names = null)
	{
		$templates_inserted = array();

		foreach ($nodes['child'] as $node) {
			if ($node['name'] == 'TEMPLATE') {
				$temp_set = '';
				$temp_name = '';
				$temp_display = '';
				$temp_desc = '';
				$temp_html = '';

				foreach ($node['child'] as $element) {
					if (isset($element['content'])) {
						switch($element['name']) {
						case 'SET':
							$temp_set = $element['content'];
							break;
						case 'NAME':
							$temp_name = $element['content'];
							break;
						case 'DISPLAYNAME':
							$temp_display = $element['content'];
							break;
						case 'DESCRIPTION':
							$temp_desc = $element['content'];
							break;
						case 'HTML':
							$temp_html = trim($element['content']);
							break;
						}
					}
				}
				if (empty($temp_set) || empty($temp_name) || empty($temp_display)) {
					return "ERROR: No data available for template\n";
				}
				if ($template_names === null || in_array($temp_name, $template_names)) {
					$db->query("REPLACE INTO templates
						(template_skin, template_set, template_name, template_html, template_displayname, template_description)
						VALUES ('%s', '%s', '%s', '%s', '%s', '%s')",
						$skin_dir, $temp_set, $temp_name, $temp_html, $temp_display, $temp_desc);
					$templates_inserted[] = $temp_name;
				}
			}
		}
		return $templates_inserted;
	}

	/**
	 * Pulls out details for a summary of what the package is and does
	 *
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.3.0
	 * @return array Structured array of packages info
	 **/
	function fetch_package_details($filename)
	{
		if (!is_readable($filename)) return false;

		if (strtolower(substr($filename, -4)) == '.tar' ||
			(strtolower(substr($filename, -7)) == '.tar.gz' &&
			$tarTool->can_gunzip()))
		{
			if ($tarTool->open_file_reader(filename)) {
				// Okay. Look at packages.txt to find our xml file
				$xmlFilename = $tarTool->extract_file('package.txt');
				
				if ($xmlFilename === false) return false;

				$xmlInfo->parseTar($tarTool, $xmlFilename);
			} else {
				return false;
			}
		}
		else if (strtolower(substr($file, -4)) == '.xml')
		{
			$xmlFilename = $filename;
			$xmlInfo->parse($filename);
		}
		else
		{
			return false; // give up
		}

		$results = array('file' => $xmlFilename);

		$node = $xmlInfo->GetNodeByPath('QSFMOD/TYPE');
		$results['type'] = $node['content'];

		$node = $xmlInfo->GetNodeByPath('QSFMOD/TITLE');
		$results['title'] = $node['content'];

		$node = $xmlInfo->GetNodeByPath('QSFMOD/DESCRIPTION');

		if (isset($node['content']) && $node['content'])
			$results['desc'] = $node['content'];
		else
			$results['desc'] = '';

		$node = $xmlInfo->GetNodeByPath('QSFMOD/VERSION');
		$results['version'] = $node['content'];

		$node = $xmlInfo->GetNodeByPath('QSFMOD/AUTHORNAME');
		$results['author'] = $node['content'];

		return $results;
	}

	/**
	 * Looks through all XML docs in packages folder and pulls basic list data for them
	 *
	 * @author Geoffrey Dunn <geoff@warmage.com>
	 * @since 1.3.0
	 * @return array Structured array of packages info
	 **/
	function scan_packages($folder = '../packages/')
	{
		if (substr($folder, -1) != '/') {
			$folder .= '/';
		}

		$packages = array();

		$tarTool = new archive_tar();

		$xmlInfo = new xmlparser();

		$dp = opendir($folder);
		while (($file = readdir($dp)) !== false)
		{
			if (strtolower(substr($file, -4)) == '.tar' ||
				(strtolower(substr($file, -7)) == '.tar.gz' &&
				$tarTool->can_gunzip()))
			{
				if ($tarTool->open_file_reader($folder . $file)) {
					// Okay. Look at packages.txt to find our xml file
					$xmlFilename = $tarTool->extract_file('package.txt');

					if ($xmlFilename === false) continue;

					$xmlInfo->parseTar($tarTool, $xmlFilename);
				} else {
					continue;
				}

			}
			else if (strtolower(substr($file, -4)) == '.xml')
			{
				$xmlInfo->parse($folder . $file);
			}
			else
			{
				continue; // skip file
			}

			$node = $xmlInfo->GetNodeByPath('QSFMOD/TYPE');
			$package_type = $node['content'];

			$node = $xmlInfo->GetNodeByPath('QSFMOD/TITLE');
			$package_title = $node['content'];

			$node = $xmlInfo->GetNodeByPath('QSFMOD/DESCRIPTION');

			if (isset($node['content']) && $node['content'])
				$package_desc = $node['content'];
			else
				$package_desc = '';

			$node = $xmlInfo->GetNodeByPath('QSFMOD/VERSION');
			$package_version = $node['content'];

			$node = $xmlInfo->GetNodeByPath('QSFMOD/AUTHORNAME');
			$package_author = $node['content'];

			$packages[] = array(
						'file' => $file,
						'type' => $package_type,
						'title' => $package_title,
						'desc' => $package_desc,
						'version' => $package_version,
						'author' => $package_author);

			$xmlInfo->reset();
		}

		closedir($dp);

		return $packages;
	}
}
?>