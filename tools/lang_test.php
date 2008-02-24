<?php
/**
 * Creates a blank language called blank.php in ../languages. You can use this
 * language to quickly identify hardcoded English.
 * $Id: lang_test.php,v 1.1 2003/08/24 16:38:34 jason Exp $
 **/

$if = implode('', file('../languages/en.php'));
$of = fopen('../languages/blank.php', 'w');

$if = preg_replace('~//.+~', '', $if);
$if = preg_replace('~= \'.*\';~', '= \'BLANK\';', $if);
$if = preg_replace('~".*?"~', '\'BLANK\'', $if);
$if = preg_replace('~(class|function) en~', '\\1 blank', $if);
$if = str_replace('English', 'Empty', $if);

fwrite($of, $if);
fclose($of);