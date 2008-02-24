<?php
$php_version = PHP_VERSION;
$os = defined('PHP_OS') ? PHP_OS : 'unknown';
$safe_mode = get_cfg_var('safe_mode') ? 'on' : 'off';
$register_globals = get_cfg_var('register_globals') ? 'on' : 'off';
$gpc = get_cfg_var('magic_quotes_gpc') ? 'on' : 'off';
$gpc_runtime = get_cfg_var('magic_quotes_runtime') ? 'on' : 'off';
$server = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown';

if (extension_loaded('mysql')) {
	$mysql_client = '<span class="avil_yes">Available</span> (' . mysql_get_client_info() . ')';
} else {
	$mysql_client = '<span class="avil_no">Not available</span>';
}

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' dir='ltr'>
<head>

<title>$qsf->name</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1' />

<style type='text/css'>
 <!--
  body        {background-color:#FFFFFF; color:#000000; font-size:10px; font-family:Verdana, Arial, Helvetica, sans-serif; margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom:0px; cursor:default;}
  .normal, td {font-size:11px; font-family:Verdana, Arial, Helvetica, sans-serif; font-weight:normal;}
  .input      {font-size:11px; background-color:#FFFFFF; color:#000000; font-family:Verdana, Arial, Helvetica, sans-serif; border:1px solid #555555; padding:1px;}
  .select     {font-size:11px; background-color:#FFFFFF; color:#000000; font-family:Verdana, Arial, Helvetica, sans-serif;}
  a           {background-color:transparent; color:#000000;}
  a:hover     {background-color:transparent; color:#FF0000;}
  .main       {font-size:17px; background-color:#EEEEEE; color:#000000; font-weight:bold;}
  .subheader  {font-size:14px; background-color:#B9DAFC; color:#000000; font-weight:bold; border:1px solid #555555; padding:1px; text-align:center;}
  .left       {font-size:10px; background-color:#DDDDDD; color:#000000; width:30%;}
  .copyright  {font-size:10px; text-align:center; line-height:14px;}
  .grey       {background-color:transparent; color:#888888;}
  .tiny       {font-size:9px;}
  .avil_yes   {color: green;font-weight:bold;}
  .avil_no    {color: red;font-weight:bold;}
  hr          {height:1px; border:0px; border-top:1px solid; border-color:#666666;}
  img         {border:0px;}
  form        {margin:0px;}
 //-->
</style>

</head>
<body>

<table width='100%' border='0' cellspacing='0' cellpadding='0'>
 <tr>
  <td align='center'>
   <br /><br />
   <table width='75%' style='background-color:#454545' cellpadding='0' cellspacing='0' border='0'>
    <tr>
     <td align='left'>
      <table width='100%' cellpadding='8' cellspacing='1' border='0'>
       <tr>
        <td colspan='2' style='padding:0px'>
         <table width='100%' cellpadding='0' cellspacing='0' border='0'>
          <tr>
           <td style='margin:0px; background-color:#5CA0E6; padding-left:10px;'>
            <h1>$qsf->name Installer</h1>
           </td>
           <td class='main' align='right' style='background-color:#5CA0E6; padding-right:10px;'>
            $qsf->version
           </td>
          </tr>
         </table>
        </td>
       </tr>
       <tr>
        <td class='left' align='left' valign='top'>
         <b>PHP Version:</b> $php_version<hr />
         <b>Operating System:</b> $os<hr />
         <b>Safe mode:</b> $safe_mode<hr />
         <b>Register globals:</b> $register_globals<hr />
         <b>Magic Quotes:</b> gpc $gpc, runtime $gpc_runtime<hr />
         <b>Server Software:</b> $server<hr />
         <b>MySQL Client:</b> $mysql_client
        </td>
        <td class='main'>";
?>