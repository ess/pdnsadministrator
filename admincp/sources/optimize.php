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

class optimize extends admin
{
	function execute()
	{
		$this->set_title($this->lang->optimize);
		$this->tree($this->lang->optimize);

		$optimize_result = $this->opt_tables();

		$output = $this->message($this->lang->optimize, $this->lang->optimized);
		$output .= $optimize_result;
		return $output;
	}

	function opt_tables()
	{
		$tables = implode( ', ', $this->get_db_tables() );

		$result = $this->db->optimize($tables);

		$show_headers = true;

		$out = $this->table;

		while ($row = $this->db->nqfetch($result))
		{
			if ($show_headers) {
				$out .= "<span class=\"head\">\n";

				foreach ($row as $col => $data)
				{
					$out .= "<span class='starter'>$col</span>\n";
				}

				$out .= "</span>\n<p></p>";

				$show_headers = false;
			}

			foreach ($row as $col => $data)
			{
				$out .= "<span class='starter'>" . $this->format($data, FORMAT_HTMLCHARS) . "</span>\n";
			}
			$out .= "<p class='list_line'></p>\n";
		}
		return $out . $this->etable;
	}
}
?>