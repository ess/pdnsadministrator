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

if (!defined('PDNSADMIN')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

$this->nohtml = 1;

$percent  = round(($this->db->querytime / $totaltime) * 100, 1);
if (($query = substr($this->query, 0, -12)))
	$normview = $this->self . '?' . $query;
else
	$normview = $this->self;

$out = "
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' dir='ltr'>
<head>
<title>PDNS-Admin Debug</title>
<style type='text/css'>
<!--
body, table, tr, td {font-size:11px; font-family:Verdana, Arial, Helvetica, sans-serif}
body {background-color:#ffffff; color:#000000}
hr {height:1px; border:0 none inherit; border-top:1px solid #666666}
-->
</style>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\" />
</head>
<body>
<div>
<span style='font-size:18px; color:#660000'><b>PDNS-Admin Debug</b></span>";

if (!$this->debug_mode) {
	$out .= "<br /><br /><span style='font-size:12px'>This feature has been disabled.</span>
	</div></body></html>";
	return;
}

$out .= "
<span style='font-size:12px'><br /><br /></span>
<hr />
<span style='font-size:12px'>
<a href='#stats' style='text-decoration:none;color:#660000'>Jump to statistics</a> - <a href='$normview' style='text-decoration:none;color:#660000'>Back to normal view</a></span>
<hr />
<br />

<table width='100%' style='border:1px solid #000000' cellpadding='5' cellspacing='1'>
	<tr>
		<td style='background-color:#CCCCCC' align='center'><b>query</b></td>
		<td style='background-color:#CCCCCC' align='center'><b>execution time</b></td>
		<td style='background-color:#CCCCCC' align='center'><b>table(s)</b></td>
		<td style='background-color:#CCCCCC' align='center'><b>type</b></td>
		<td style='background-color:#CCCCCC' align='center'><b>possible keys</b></td>
		<td style='background-color:#CCCCCC' align='center'><b>key</b></td>
		<td style='background-color:#CCCCCC' align='center'><b>key length</b></td>
		<td style='background-color:#CCCCCC' align='center'><b>ref</b></td>
		<td style='background-color:#CCCCCC' align='center'><b>rows</b></td>
		<td style='background-color:#CCCCCC' align='center'><b>extra</b></td>
	</tr>";

foreach ($this->db->querydebug as $debug) {

	unset($table, $type, $key, $key_len, $ref, $rows, $Extra, $possible_keys);

	extract($debug);

	$querytime = round($querytime, 8);
	$danger = 1;

	if (empty($table))   $table   = '';
	if (empty($type))    $type    = '';
	if (empty($key))     $key     = '';
	if (empty($key_len)) $key_len = '';
	if (empty($ref))     $ref     = '';
	if (empty($rows))    $rows    = '';
	if (empty($Extra))   $Extra   = '';
	if (empty($possible_keys)) $possible_keys = '';

	if ($querytime > 0.05) $danger++;
	if ($querytime > 0.1)  $danger++;
	if ($querytime > 1)    $danger++;
	if ($type == 'ALL')    $danger++;
	if ($type == 'index')  $danger++;
	if ($type == 'range')  $danger++;
	if ($type == 'ref')    $danger++;
	if ($rows >= 200)      $danger++;

	if (!empty($possible_keys) && empty($key)) {
		$danger++;
	}

	if ((strpos($Extra, 'Using filesort') !== false) || (strpos($Extra, 'Using temporary') !== false)) {
		$danger++;
	}

	switch($danger)
	{
	case 1:  $color = '#dadada'; break;
	case 2:  $color = '#dad0d0'; break;
	case 3:  $color = '#dacaca'; break;
	case 4:  $color = '#dac0c0'; break;
	case 5:  $color = '#dababa'; break;
	case 6:  $color = '#dab0b0'; break;
	case 7:  $color = '#daaaaa'; break;
	case 8:  $color = '#da9090'; break;
	case 9:  $color = '#da8a8a'; break;
	default: $color = '#ff0000'; break;
	}

	preg_match("/(FROM|UPDATE) (\w+)/i", $query, $table);

	$out .= "
	<tr>
		<td style='background-color:$color'><pre style='font-size:12px; font-family:courier new'>" . wordwrap(trim(str_replace("\t", '', $query))) . "</pre></td>
		<td style='background-color:$color'>$querytime</td>
		<td style='background-color:$color'>{$table[2]}</td>
		<td style='background-color:$color'>$type</td>
		<td style='background-color:$color'>$possible_keys</td>
		<td style='background-color:$color'>$key</td>
		<td style='background-color:$color'>$key_len</td>
		<td style='background-color:$color'>$ref</td>
		<td style='background-color:$color'>$rows</td>
		<td style='background-color:$color'>Intensity $danger" . (empty($Extra) ? '' : "; $Extra") . '</td>
	</tr>';
}

$this->db->querytime = round($this->db->querytime, 5);
$tempcount = count($this->templater->temps);
$langcount = count(get_object_vars($this->lang));

$out .= "
</table><br />
<b>Query Server Intensity</b>:
<span style='background-color:#dadada'>&nbsp;1 </span>
<span style='background-color:#dad0d0'> 2 </span>
<span style='background-color:#dacaca'> 3 </span>
<span style='background-color:#dac0c0'> 4 </span>
<span style='background-color:#dababa'> 5 </span>
<span style='background-color:#dab0b0'> 6 </span>
<span style='background-color:#daaaaa'> 7 </span>
<span style='background-color:#da9090'> 8 </span>
<span style='background-color:#da8a8a'> 9&nbsp;</span><br />

<a id='stats'></a><br />
<b>MySQL queries</b>: {$this->db->querycount}<br />
<b>Server Load</b>: $load<br />
<b>Execution Time</b>: $totaltime<br />
<b>Query Time</b>: {$this->db->querytime} ($percent% of total execution time)<br />
<b>Loaded Language</b>: " . get_class($this->lang) . "<br />
<b>Loaded Words</b>: $langcount<br /><br />";

$out .= "<b>$tempcount Loaded Templates (Skin: $this->skin)</b>:<br />";

foreach ($this->templater->temps as $key => $val)
{
	$out .= "$key<br />";
}

$includes = get_included_files();
$out .= '<br /><b>' . count($includes) . ' Files In Use</b>:<br />';

$coef = $includes[0];

for($i = 0; $i < count($includes) - 1; $i++)
{
	$l = strlen($coef);
	for($j = 0; $j < $l + 1; $j++)
	{
		$as = substr($includes[$i], 0, $j);
		if($as == substr($includes[$i+1], 0, $j)){
			$coef = $as;
		}
	}
}

$coef = strlen($coef);

foreach ($includes as $file)
{
	$out .= './' . str_replace('\\', '/', substr($file, $coef)) . '<br />';
}

$out .= '</div></body></html>';
?>