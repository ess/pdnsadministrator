<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns='http://www.w3.org/1999/xhtml' xml:lang='en' dir='ltr'>
<head>

<title><?php echo $qsf->name; ?></title>
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
            <h1><?php echo $qsf->name; ?> Installer</h1>
           </td>
           <td class='main' align='right' style='background-color:#5CA0E6; padding-right:10px;'>
            <?php echo $qsf->version; ?>
           </td>
          </tr>
         </table>
        </td>
       </tr>
       <tr>
        <td class='left' align='left' valign='top'>
         <b>PHP Version:</b> <?php echo PHP_VERSION; ?><hr />
         <b>Operating System:</b> <?php echo (defined('PHP_OS') ? PHP_OS : 'unknown'); ?><hr />
         <b>Safe mode:</b> <?php echo (get_cfg_var('safe_mode') ? 'on' : 'off'); ?><hr />
         <b>Register globals:</b> <?php echo (get_cfg_var('register_globals') ? 'on' : 'off'); ?><hr />
         <b>Magic Quotes:</b> gpc <?php echo (get_cfg_var('magic_quotes_gpc') ? 'on' : 'off') . ', runtime ' . (get_cfg_var('magic_quotes_runtime') ? 'on' : 'off'); ?><hr />
         <b>Server Software:</b> <?php echo (isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown'); ?>
        </td>
        <td class='main'>
