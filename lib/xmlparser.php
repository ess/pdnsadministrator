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

/**
 * Simple XML parser
 *
 * @author monte at NOT-SP-AM dot ohrt dot com
 * @link http://php.net/xml
 * @since 1.1.5
 **/
class xmlparser
{
    var $xml_obj = null;
    var $output = array();
    
    function xmlparser()
    {
       $this->xml_obj = xml_parser_create();
       xml_set_object($this->xml_obj,$this);
       xml_set_character_data_handler($this->xml_obj, 'dataHandler'); 
       xml_set_element_handler($this->xml_obj, 'startHandler', 'endHandler');
    
    }
    
    // Call this before attempting to reuse the parser class
    function reset()
    {
	    if ($this->xml_obj !== null) {
		    $this->output = array();
		    xml_parser_free($this->xml_obj);
		    $this->xml_obj = xml_parser_create();
		    xml_set_object($this->xml_obj,$this);
		    xml_set_character_data_handler($this->xml_obj, 'dataHandler'); 
		    xml_set_element_handler($this->xml_obj, 'startHandler', 'endHandler');
	    }
    }
    
    function parse($path)
    {
       if (!($fp = @fopen($path, "r"))) {
           return "Cannot open XML data file: $path";
       }
    
       while ($data = fread($fp, 4096)) {
           if (!xml_parse($this->xml_obj, $data, feof($fp))) {
               return (sprintf('XML error: %s at line %d',
               xml_error_string(xml_get_error_code($this->xml_obj)),
               xml_get_current_line_number($this->xml_obj)));
               xml_parser_free($this->xml_obj);
           }
       }
    
       return true;
    }
    
    function parseTar($tarTool, $filename)
    {
		// First try and find the filename
		$tarTool->rewind();
		
		$foundFile = $tarTool->next_file();
		while ($foundFile != $filename && $foundFile !== false) {
			$foundFile = $tarTool->skip_file();
		}
	
		if ($foundFile === false) {
			return "Cannot open XML data file: $filename";
		} else {
			// Now allow parsing chunk by chunk
			$size = $tarTool->currentStat['size'];
			
			while ($size > 0) {
				$chunk = 4096;
				if ($size < $chunk) $chunk = $size;
				$size -= $chunk;
				while ($data = $tarTool->_read($chunk)) {
					if (!xml_parse($this->xml_obj, $data, $size==0)) {
					   return (sprintf('XML error: %s at line %d',
					   xml_error_string(xml_get_error_code($this->xml_obj)),
					   xml_get_current_line_number($this->xml_obj)));
					   xml_parser_free($this->xml_obj);
					}
			   }
			}
		}
		
		return true;
    }
    
    function parseArray($array)
    {
       foreach ($array as $data) {
           if (!xml_parse($this->xml_obj, $data)) {
               return (sprintf('XML error: %s at line %d',
               xml_error_string(xml_get_error_code($this->xml_obj)),
               xml_get_current_line_number($this->xml_obj)));
               xml_parser_free($this->xml_obj);
           }
       }
    
       return true;
    }
    
    function parseString($string)
    {
	   if (!xml_parse($this->xml_obj, $string)) {
		   return (sprintf('XML error: %s at line %d',
		   xml_error_string(xml_get_error_code($this->xml_obj)),
		   xml_get_current_line_number($this->xml_obj)));
		   xml_parser_free($this->xml_obj);
	   }
    
       return true;
    }

    function startHandler($parser, $name, $attribs)
    {
       $_content = array('name' => $name);
       if(!empty($attribs))
         $_content['attrs'] = $attribs;
       array_push($this->output, $_content);
    }
    
    function dataHandler($parser, $data)
    {
       if(!empty($data)) {
           $_output_idx = count($this->output) - 1;
           if (isset($this->output[$_output_idx]['content'])) {
               $this->output[$_output_idx]['content'] .= $data;
           } else {
               $this->output[$_output_idx]['content'] = $data;
           }
       }
    }
    
    function endHandler($parser, $name){
       if(count($this->output) > 1) {
           $_data = array_pop($this->output);
           $_output_idx = count($this->output) - 1;
           $this->output[$_output_idx]['child'][] = $_data;
       }     
    }

    /**
     * Addition to allow fetching part of the xml structure
     *
     * @author jbernau at muc dot de
     * @link http://php.net/xml
     * @since 1.1.5
     **/
    function GetNodeByPath($path, $tree = false)
    {
        if ($tree) {
            $tree_to_search = $tree;
        } else {
            $tree_to_search = $this->output;
        }
        
        if ($path == '') {
            return null; 
        }
        
        $arrPath = explode('/',$path);
        
        foreach($tree_to_search as $key => $val) {
            if (gettype($val) == 'array') {
                $nodename = $val['name'];
                
                if ($nodename == $arrPath[0]) { 
                    
                    if (count($arrPath) == 1)  { 
                        return $val;
                    }
                    
                    array_shift($arrPath);
                    
                    $new_path = implode($arrPath,'/');
                    
                    return $this->GetNodeByPath($new_path,$val['child']);
                }
            }
        }
        // Should never reach this point
        return null; 
    }
}
?>