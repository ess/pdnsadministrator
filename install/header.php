<?php
$php_version = PHP_VERSION;
$os = defined('PHP_OS') ? PHP_OS : 'unknown';
$safe_mode = get_cfg_var('safe_mode') ? 'on' : 'off';
$register_globals = get_cfg_var('register_globals') ? 'on' : 'off';
$server = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown';

if (extension_loaded('mysql')) {
	$mysql_client = '<span class="avil_yes">Available</span> (' . mysql_get_client_info() . ')';
} else {
	$mysql_client = '<span class="avil_no">Not available</span>';
}

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.1//EN\" \"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd\">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' dir='ltr'>
<head>

<title>$pdns->name</title>
<meta http-equiv='Content-Type' content='text/html; charset=iso-8859-1' />

<style type='text/css'>
  body        {background:black url('./background3.png'); color:#c0c0c0; font: 12px Arial, sans-serif; margin-left:0px; margin-right:0px; margin-top:0px; margin-bottom:0px; cursor:default;}
  .input      {font:13px Arial, sans-serif; background-color:#a8a8a8; color:black; border:1px solid #292929; padding:1px;}
  .select     {font:13px Arial, sans-serif; background-color:#a8a8a8; color:black;}
  a           {color:#c0c0c0;}
  a:hover     {color:white;}
  .main       {font:bold 16px Arial, sans-serif; background-color:#292929; color:#c0c0c0; padding-right:10px; border: 1px solid #3e3e3e; border-left: 3px double #3e3e3e;}
  .subheader  {font:bold 16px Arial, sans-serif; background-color:#292929; color:#c0c0c0; border:1px solid #3e3e3e; padding:1px; text-align:center; width:90%;}
  .left       {font:10px Arial; background-color:#161616; color:#c0c0c0; border: 1px solid #3e3e3e; width:25%;}
  .right      {font:14px Arial, sans-serif; background-color:#161616; color:#c0c0c0; border: 1px solid #3e3e3e; width:75%;}
  .copyright  {font:10px Arial; text-align:center; line-height:14px;}
  .tiny       {font-size:10px;}
  .avil_yes   {color:green; font-weight:bold;}
  .avil_no    {color:red; font-weight:bold;}
  hr          {height:1px; border:0px; border-top:1px solid; border-color:#c0c0c0;}
  img         {border:0px;}
  form        {margin:0px;}
</style>

</head>
<body>

<table width='100%' cellspacing='0' cellpadding='0'>
 <tr>
  <td align='center'>
   <br /><br />
   <table width='85%' style='background-color:#161616' cellpadding='0' cellspacing='0'>
    <tr>
     <td align='left'>
      <table width='100%' cellpadding='8' cellspacing='1'>
       <tr>
        <td colspan='2' style='padding:0px'>
         <table width='100%' cellpadding='0' cellspacing='0' border='0'>
          <tr>
           <td class='main'>
            <h1>$pdns->name Installer</h1>
           </td>
           <td class='main' align='right'>
            $pdns->version
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
         <b>Server Software:</b> $server<hr />
         <b>MySQL Client:</b> $mysql_client
        </td>
        <td class='right'>";
?>