<?php
/**
 * Based heavily on phpMyAdmin (http://www.phpmyadmin.net)
 * $Id: dump_functions.php,v 1.4 2005/03/30 04:24:06 jason Exp $
 **/

if (strstr($_SERVER['PHP_SELF'], '/dump_functions.php')) {
	echo 'This file is used internally by dump_create.php';
}

$pma_version = '2.2.5';

function makedump($table_select, $what, $db, $crlf = "\n")
{
	global $dump_buffer, $tmp_buffer;
	
	$tables     = mysql_list_tables($db);
	$num_tables = mysql_numrows($tables);

	$dump_buffer = '';
	$tmp_buffer  = '';
	$i = 0;
	while ($i < $num_tables) {
		$table = mysql_tablename($tables, $i);

		if (!isset($table_select[$table])) {
			$i++;
			continue;
		}

		if ($what != 'dataonly') {
			$dump_buffer .= PMA_getTableDef($db, $table, $crlf) . ';' . $crlf . $crlf;
		}

		if (($what == 'data') || ($what == 'dataonly')) {
			$tmp_buffer  = '';
			PMA_getTableContent($db, $table, 0, 0, 'PMA_myHandler', $crlf);
			$dump_buffer .= $tmp_buffer . $crlf;
		}
		$i++;
	}
	return $dump_buffer;
}

function PMA_sqlAddslashes($a_string = '', $is_like = FALSE)
{
	if ($is_like) {
		$a_string = str_replace('\\', '\\\\\\\\', $a_string);
	} else {
		$a_string = str_replace('\\', '\\\\', $a_string);
	}
	$a_string = str_replace('\'', '\\\'', $a_string);

	return $a_string;
}

function PMA_backquote($a_name, $do_it = TRUE)
{
	if ($do_it
		&& !empty($a_name) && $a_name != '*') {
		return '`' . $a_name . '`';
	} else {
		return $a_name;
	}
}

function PMA_myHandler($sql_insert, $crlf)
{
	global $tmp_buffer;

	$eol_dlm = (isset($GLOBALS['extended_ins']) && ($GLOBALS['current_row'] < $GLOBALS['rows_cnt']))
			 ? ','
			 : ';';
	if (empty($GLOBALS['asfile'])) {
		echo htmlspecialchars($sql_insert . $eol_dlm . $crlf);
	} else {
		$tmp_buffer .= $sql_insert . $eol_dlm . $crlf;
	}
}

function PMA_htmlFormat($a_string = '')
{
	return (empty($GLOBALS['asfile']) ? htmlspecialchars($a_string) : $a_string);
}

function PMA_getTableDef($db, $table, $crlf = "\n", $error_url =  null)
{
	global $drop;
	global $use_backquotes;

	$schema_create = '';

	if (!empty($drop)) {
		$schema_create .= 'DROP TABLE IF EXISTS ' . PMA_backquote(PMA_htmlFormat($table), $use_backquotes) . ';' . $crlf;
	}

	mysql_query('SET SQL_QUOTE_SHOW_CREATE = 0');
	$result = mysql_query('SHOW CREATE TABLE ' . PMA_backquote($db) . '.' . PMA_backquote($table));

	if ($result != FALSE && mysql_num_rows($result) > 0) {
		$tmpres = mysql_fetch_array($result);
		$schema_create .= str_replace("\n", $crlf, PMA_htmlFormat($tmpres[1]));
	}

	mysql_free_result($result);
	return $schema_create;
}

function PMA_getTableContentFast($db, $table, $add_query = '', $handler, $crlf, $error_url)
{
	global $use_backquotes;
	global $rows_cnt;
	global $current_row;

	$local_query = 'SELECT * FROM ' . PMA_backquote($db) . '.' . PMA_backquote($table) . $add_query;
	$result = mysql_query($local_query);
	if ($result != FALSE) {
		$fields_cnt = mysql_num_fields($result);
		$rows_cnt   = mysql_num_rows($result);

		for ($j = 0; $j < $fields_cnt; $j++) {
			$field_set[$j] = PMA_backquote(mysql_field_name($result, $j), $use_backquotes);
			$type = mysql_field_type($result, $j);
			if ($type == 'tinyint' || $type == 'smallint' || $type == 'mediumint' || $type == 'int' ||
				$type == 'bigint'  ||$type == 'timestamp') {
				$field_num[$j] = TRUE;
			} else {
				$field_num[$j] = FALSE;
			}
		}

		if (isset($GLOBALS['showcolumns'])) {
			$fields = implode(', ', $field_set);
			$schema_insert = 'INSERT INTO ' . PMA_backquote(PMA_htmlFormat($table), $use_backquotes)
			               . ' (' . PMA_htmlFormat($fields) . ') VALUES (';
		} else {
			$schema_insert = 'INSERT INTO ' . PMA_backquote(PMA_htmlFormat($table), $use_backquotes)
			               . ' VALUES (';
		}

		$search	   = array("\x00", "\x0a", "\x0d", "\x1a"); //\x08\\x09, not required
		$replace	  = array('\0', '\n', '\r', '\Z');
		$current_row  = 0;

		@set_time_limit($GLOBALS['cfgExecTimeLimit']);
		while ($row = mysql_fetch_row($result)) {
			$current_row++;
			for ($j = 0; $j < $fields_cnt; $j++) {
				if (!isset($row[$j])) {
					$values[]	 = 'NULL';
				} else if ($row[$j] == '0' || $row[$j] != '') {
					if ($field_num[$j]) {
						$values[] = $row[$j];
					}
					else {
						$values[] = "'" . str_replace($search, $replace, PMA_sqlAddslashes($row[$j])) . "'";
					}
				} else {
					$values[]	 = "''";
				}
			}

			if (isset($GLOBALS['extended_ins'])) {
				if ($current_row == 1) {
					$insert_line  = $schema_insert . implode(', ', $values) . ')';
				} else {
					$insert_line  = '(' . implode(', ', $values) . ')';
				}
			}
			else {
				$insert_line = $schema_insert . implode(', ', $values) . ')';
			}
			unset($values);

			$handler($insert_line, $crlf);
		}
	}
	mysql_free_result($result);
	return TRUE;
}

function PMA_getTableContent($db, $table, $limit_from = 0, $limit_to = 0, $handler, $crlf = "\n", $error_url = null)
{
	if ($limit_from > 0) {
		$limit_from--;
	} else {
		$limit_from = 0;
	}
	if ($limit_to > 0 && $limit_from >= 0) {
		$add_query  = " LIMIT $limit_from, $limit_to";
	} else {
		$add_query  = '';
	}
	PMA_getTableContentFast($db, $table, $add_query, $handler, $crlf, $error_url);
}

function PMA_splitSqlFile(&$ret, $sql, $release)
{
    $sql          = trim($sql);
    $sql_len      = strlen($sql);
    $char         = '';
    $string_start = '';
    $in_string    = FALSE;
    $time0        = time();
    for ($i = 0; $i < $sql_len; ++$i) {
        $char = $sql[$i];

        // We are in a string, check for not escaped end of strings except for
        // backquotes that can't be escaped
        if ($in_string) {
            for (;;) {
                $i         = strpos($sql, $string_start, $i);
                // No end of string found -> add the current substring to the
                // returned array
                if (!$i) {
                    $ret[] = $sql;
                    return TRUE;
                }
                // Backquotes or no backslashes before quotes: it's indeed the
                // end of the string -> exit the loop
                else if ($string_start == '`' || $sql[$i-1] != '\\') {
                    $string_start      = '';
                    $in_string         = FALSE;
                    break;
                }
                // one or more Backslashes before the presumed end of string...
                else {
                    // ... first checks for escaped backslashes
                    $j                     = 2;
                    $escaped_backslash     = FALSE;
                    while ($i-$j > 0 && $sql[$i-$j] == '\\') {
                        $escaped_backslash = !$escaped_backslash;
                        $j++;
                    }
                    // ... if escaped backslashes: it's really the end of the
                    // string -> exit the loop
                    if ($escaped_backslash) {
                        $string_start  = '';
                        $in_string     = FALSE;
                        break;
                    }
                    // ... else loop
                    else {
                        $i++;
                    }
                } // end if...elseif...else
            } // end for
        } // end if (in string)

        // We are not in a string, first check for delimiter...
        else if ($char == ';') {
            // if delimiter found, add the parsed part to the returned array
            $ret[]      = substr($sql, 0, $i);
            $sql        = ltrim(substr($sql, min($i + 1, $sql_len)));
            $sql_len    = strlen($sql);
            if ($sql_len) {
                $i      = -1;
            } else {
                // The submited statement(s) end(s) here
                return TRUE;
            }
        } // end else if (is delimiter)

        // ... then check for start of a string,...
        else if (($char == '"') || ($char == '\'') || ($char == '`')) {
            $in_string    = TRUE;
            $string_start = $char;
        } // end else if (is start of string)

        // ... for start of a comment (and remove this comment if found)...
        else if ($char == '#'
                 || ($char == ' ' && $i > 1 && $sql[$i-2] . $sql[$i-1] == '--')) {
            // starting position of the comment depends on the comment type
            $start_of_comment = (($sql[$i] == '#') ? $i : $i-2);
            // if no "\n" exits in the remaining string, checks for "\r"
            // (Mac eol style)
            $end_of_comment   = (strpos(' ' . $sql, "\012", $i+2))
                              ? strpos(' ' . $sql, "\012", $i+2)
                              : strpos(' ' . $sql, "\015", $i+2);
            if (!$end_of_comment) {
                // no eol found after '#', add the parsed part to the returned
                // array if required and exit
                if ($start_of_comment > 0) {
                    $ret[]    = trim(substr($sql, 0, $start_of_comment));
                }
                return TRUE;
            } else {
                $sql          = substr($sql, 0, $start_of_comment)
                              . ltrim(substr($sql, $end_of_comment));
                $sql_len      = strlen($sql);
                $i--;
            } // end if...else
        } // end else if (is comment)

        // ... and finally disactivate the "/*!...*/" syntax if MySQL < 3.22.07
        else if ($release < 32270
                 && ($char == '!' && $i > 1  && $sql[$i-2] . $sql[$i-1] == '/*')) {
            $sql[$i] = ' ';
        } // end else if

        // loic1: send a fake header each 30 sec. to bypass browser timeout
        $time1     = time();
        if ($time1 >= $time0 + 30) {
            $time0 = $time1;
            header('X-pmaPing: Pong');
        } // end if
    } // end for

    // add any rest to the returned array
    if (!empty($sql) && ereg('[^[:space:]]+', $sql)) {
        $ret[] = $sql;
    }

    return TRUE;
}

function define_mysql_version()
{
    $result = mysql_query('SELECT VERSION() AS version');
    if ($result != FALSE && @mysql_num_rows($result) > 0) {
        $row   = mysql_fetch_array($result);
        $match = explode('.', $row['version']);
    } else {
        $result = @mysql_query('SHOW VARIABLES LIKE \'version\'');
        if ($result != FALSE && @mysql_num_rows($result) > 0) {
            $row   = mysql_fetch_row($result);
            $match = explode('.', $row[1]);
        }
    }

    if (!isset($match) || !isset($match[0])) {
        $match[0] = 3;
    }
    if (!isset($match[1])) {
        $match[1] = 21;
    }
    if (!isset($match[2])) {
        $match[2] = 0;
    }

    define('PMA_MYSQL_INT_VERSION', (int)sprintf('%d%02d%02d', $match[0], $match[1], intval($match[2])));
}
?>