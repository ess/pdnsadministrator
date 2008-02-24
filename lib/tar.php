<?php
/**
 * PDNS-Admin
 * Copyright (c) 2006-2007 Roger Libiez http://www.iguanadons.net
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

if (!defined('QUICKSILVERFORUMS')) {
	header('HTTP/1.0 403 Forbidden');
	die;
}

/**
 * Class that can tar/untar as well as gzip files
 *
 * Some functions are based off the work in Pear::File_Archive
 *
 * @author Geoffrey Dunn <geoff@warmage.com>
 * @since 1.3.0
 **/
class archive_tar
{
	var $full_filename;	/** Full filename we a writing to including the extentsion **/
	
	var $file_handle=false;	/** File handle for writing or reading **/
	
	var $seek_length=0;	/** Bytes to skip before reading the next file header **/
	
	var $file_writing=false;
	
	var $gz_mode=false;

	/**
	 * Open an archive for writing
	 *
	 * The reason we don't tell it the extention is the writer will choose
	 * .tar or .tar.gz depending on the modes available.
	 * When we close the file we will get that filename or we can request it
	 * directly
	 *
	 * @param string $basename folder and filename. But not the extention
	 * @param string $gzip do we want to attempt to compress the file
	 **/
	function open_file_writer($basename, $gzip=true)
	{
		$gzip = $gzip && $this->can_gzip();
		
		if ($this->file_handle !== false) {
			// Close old file first
			$this->close_file();
		}
		
		if ($gzip) {
			$this->full_filename = $basename . '.tar.gz';
			$this->file_handle = gzopen($this->full_filename, 'wb9');
		} else {
			$this->full_filename = $basename . '.tar';
			$this->file_handle = fopen($this->full_filename, 'wb');
		}
		
		$this->gz_mode = $gzip;
		$this->file_writing = true;
	}
	
	/**
	 * Open an archive for reading
	 *
	 * @param string $filename archive filename
	 *
	 * @return bool TRUE if we could open the file
	 **/
	function open_file_reader($filename)
	{
		if ($this->file_handle !== false) {
			// Close old file first
			$this->close_file();
		}
		
		// Figure out the extension
		$filenameParts = explode('.', $filename);
		$lastExt = array_pop($filenameParts);
		
		if ($lastExt == 'gz') {
			// Check we can gunzip it
			if (!$this->can_gunzip()) return false;
			
			$lastExt = array_pop($filenameParts);
			// Check it's a tar file
			if ($lastExt != 'tar') return false;

			$this->file_handle = gzopen($filename, 'rb');
			$this->gz_mode = false;
		} else {
			// Check it's a tar file
			if ($lastExt != 'tar') return false;
			
			$this->file_handle = fopen($filename, 'rb');
			
			$this->gz_mode = false;
		}
		
		$this->file_writing = false;
		$this->full_filename = $filename;
		$this->seek_length = 0;
		
		return ($this->file_handle !== false);
	}

	/**
	 * Close the file pointer and perform any cleanup
	 *
	 * @return string Filename of opened file. Useful for writing which can create .tar or .tar.gz
	 **/
	function close_file()
	{
		if ($this->file_handle !== false)
		{
			if ($this->file_writing) $this->_write(pack("a1024", ""));
			if ($this->gz_mode) {
				gzclose($this->file_handle);
			} else {
				fclose($this->file_handle);
			}

			// Todo: Perform chmod if writing
		}
		$this->file_handle = false;
		
		return $this->full_filename;
	}
	
	
	/**
	 * Extract an archive into a specified folder
	 *
	 * @param string $filename archive filename
	 * @param string $dest_folder folder to extract into
	 *
	 * @return bool TRUE on success
	 **/
	function extract($filename, $dest_folder = '.')
	{
		if (substr($dest_folder, -1) != '/') {
			$dest_folder .= '/';
		}

		if (!$this->open_file_reader($filename)) return false;

		while ($file_path = $this->next_file())
		{
			// We need to check if the dest exists!
			$full_path = $dest_folder . $file_path;
			
			$path_parts = explode('/', $full_path);
			array_pop($path_parts); // remove the filename

			$check_dir_exists = '';
			for ($i = 0; $i < count($path_parts); $i++)
			{
				$check_dir_exists .= $path_parts[$i];
				
				if (!is_dir($check_dir_exists)) {
					mkdir($check_dir_exists);
					// Todo: Perform chmod
				}
					
				$check_dir_exists .= '/';
			}
			
			$fp = fopen($full_path, 'w');
			
			fwrite($fp, $this->read_file());
			
			fclose($fp);

			// Todo: Perform chmod
		}
	}
	
	/**
	 * Add the data as a new filename in our tar file
	 *
	 * @param string $contents Contents for our file. Could be binary data
	 * @param string $filename Filename to use for the contents
	 **/
	function add_as_file($contents, $filename)
	{
		$size = strlen($contents);

		$this->_write($this->_tar_header($filename,
			$this->_gen_stat($size)));
		$this->_write($contents);
		$this->_write($this->_tar_footer($size));
	}
	
	/**
	 * Add a new file to our tar file
	 *
	 * @param string $filename Filename to use for the contents
	 **/
	function add_file($filename, $virtual_filename)
	{
		$size = filesize($filename);

		$this->_write($this->_tar_header($virtual_filename,
			stat($filename)));
		$this->_write(file_get_contents($filename));
		$this->_write($this->_tar_footer($size));
	}
	
	/**
	 * Add a folder and all it's subfolders to our tar file
	 *
	 * @param string $dirname Folder to add to our tar file
	 * @param string $virtual_path Path to store folder as in tar file
	 **/
	function add_dir($dirname, $virtual_path = '')
	{
		$dp = opendir($dirname);
	
		while (($file = readdir($dp)) !== false)
		{
			if ($file{0} == '.') {
				// Don't store hidden files
				continue;
			}
	
			$real_path = $dirname . '/' . $file;
	
			if (is_dir($real_path)) {
				$this->add_dir($real_path, $virtual_path . '/' . $file);
			} else if (is_file($real_path)) {
				$this->add_file($real_path, $virtual_path . '/' . $file);
			}
		}
	
		closedir($dp);
	}
	
	/**
	 * Read the header for the next file in our open archive
	 *
	 * @return string Filename for next file or false if eof
	 **/
	function next_file()
	{
		if ($this->file_writing) return false;
		
		if ($this->file_handle === false) return false;
		
		if ($this->seek_length > 0) $this->_read($this->seek_length);
		$this->seek_length = 0;

		$rawHeader = $this->_read(512);
		
		if (strlen($rawHeader)<512 || $rawHeader == pack("a512", ""))
			return false; // probably EOF
		
		$header = unpack(
			"a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/".
			"a8checksum/a1type/a100linkname/a6magic/a2version/".
			"a32uname/a32gname/a8devmajor/a8devminor/a155prefix",
			$rawHeader);
		$this->currentStat = array(
			2 => octdec($header['mode']),
			4 => octdec($header['uid']),
			5 => octdec($header['gid']),
			7 => octdec($header['size']),
			9 => octdec($header['mtime'])
			);
		$this->currentStat['mode']  = $this->currentStat[2];
		$this->currentStat['uid']   = $this->currentStat[4];
		$this->currentStat['gid']   = $this->currentStat[5];
		$this->currentStat['size']  = $this->currentStat[7];
		$this->currentStat['mtime'] = $this->currentStat[9];

		if ($header['magic'] == 'ustar') {
			$this->currentFilename = $this->getStandardURL(
				$header['prefix'] . $header['filename']
			    );
		} else {
			$this->currentFilename = $this->getStandardURL(
				$header['filename']
			    );
		}
		
		// could do checksum stuff here
		
		return $this->currentFilename;
	}
	
	/**
	 * Go back to the start of reading an open archive
	 **/
	function rewind()
	{
		if ($this->file_writing) return;
		
		if ($this->file_handle === false) return;

		if ($this->gz_mode) {
			gzrewind($this->file_handle);
		} else {
			rewind($this->file_handle);
		}
		$this->seek_length = 0;
	}
	
	/**
	 * Skip to the next file
	 *
	 * @return string Filename for next file or false if eof
	 **/
	function skip_file()
	{
		if ($this->file_writing) return;
		
		if ($this->file_handle === false) return;
		
		$this->read_file();
		
		return $this->next_file();
	}

	/**
	 * Read the contents of the current file
	 *
	 * @param int size in bytes to read
	 *
	 * @return string Binary contents of file
	 **/
	function read_file($size = -1)
	{
		if ($size == -1) {
			$actualLength = $this->currentStat['size'];
		} else {
			$actualLength = min($this->currentStat['size'], $size);
		}
		
		$this->seek_length = $this->currentStat['size'] - $actualLength;
		if ($this->currentStat['size'] % 512 > 0)
			$this->seek_length = 512 - ($this->currentStat['size'] % 512);
		
		return $this->_read($actualLength);
	}
	
	/**
	 * Searches through the archive for the file and returns it's contents
	 *
	 * @param string filename to look for
	 *
	 * @return string Binary contents of file or FALSE if not found
	 **/
	function extract_file($filename)
	{
		if ($this->file_writing) return false;
		
		if ($this->file_handle === false) return false;

		$this->rewind();
		
		$file = $this->next_file();
		while ($file != $filename && $file !== false) {
			$file = $this->skip_file();
		}
		
		if ($file === false) {
			return false;
		} else {
			return $this->read_file();
		}
	}
	
	
	/**
	 * Check if we can gunzip files
	 *
	 * @return bool TRUE if the zlib libaray is available to gunzip files
	 **/
	function can_gunzip()
	{
		return function_exists('gzread') && function_exists('gzopen');
	}

	/**
	 * Check if we can gzip files
	 *
	 * @return bool TRUE if the zlib libaray is available to gzip files
	 **/
	function can_gzip()
	{
		return function_exists('gzwrite') && function_exists('gzopen');
	}
	
	/**
	 * Returns the standard path
	 * Changes \ to /
	 * Removes the .. and . from the URL
	 * @param string $path a valid URL that may contain . or .. and \
	 * @static
	 */
	function getStandardURL($path)
	{
		if ($path == '.') {
		    return '';
		}
		$std = str_replace("\\", "/", $path);
		while ($std != ($std = preg_replace("/[^\/:?]+\/\.\.\//", "", $std))) ;
		$std = str_replace("/./", "", $std);
		if (strncmp($std, "./", 2) == 0) {
		    return substr($std, 2);
		} else {
		    return $std;
		}
	}
	
	function _write($data)
	{
		if (!$this->file_writing) return false;
		
		if ($this->file_handle === false) return false;
		
		if ($this->gz_mode) {
			gzwrite($this->file_handle, $data);
		} else {
			fwrite($this->file_handle, $data);
		}
		
		return true;
	}
	
	function _read($size)
	{
		if ($this->gz_mode) {
			return gzread($this->file_handle, $size);
		} else {
			return fread($this->file_handle, $size);
		}
	}
	
	/**
	 * Generates data based on the open file and the size given
	 *
	 * @return array data that looks like it came from stat()
	 **/
	function _gen_stat($size)
	{
		$stats = stat('index.php');
		
		
		$stats['size'] = $size;
		$stats[7] = $size;
		$stats['atime'] = time();
		$stats['mtime'] = time();
		$stats['ctime'] = time();
		$stats[8] = time();
		$stats[9] = time();
		$stats[10] = time();
		if ($stats['blksize'] > 0) {
			$stats['blocks'] = ceil($size / $stats['blksize']);
			$stats[12] = ceil($size / $stats['blksize']);
		}

		return $stats;
	}

	/**
	* Creates the TAR header for a file
	*
	* @param string $filename name of the file we're adding
	* @param array $stat statistics of the file (using stat function)
	* @return string A 512 byte header for the file
	* @access private
	*/
	function _tar_header($filename, $stat)
	{
		$mode = isset($stat[2]) ? $stat[2] : 0x8000;
		$uid  = isset($stat[4]) ? $stat[4] : 0;
		$gid  = isset($stat[5]) ? $stat[5] : 0;
		$size = $stat[7];
		$time = isset($stat[9]) ? $stat[9] : time();
		$link = "";
		
		if ($mode & 0x4000) {
		    $type = 5;        // Directory
		} else if ($mode & 0x8000) {
		    $type = 0;        // Regular
		} else if ($mode & 0xA000) {
		    $type = 1;        // Link
		    $link = @readlink($current);
		} else {
		    $type = 9;        // Unknown
		}
		
		$filePrefix = '';
		if (strlen($filename) > 255) {
		    return PEAR::raiseError(
			"$filename is too long to be put in a tar archive"
		    );
		} else if (strlen($filename) > 100) {
		    $filePrefix = substr($filename, 0, strlen($filename)-100);
		    $filename = substr($filename, -100);
		}
		
		$blockbeg = pack("a100a8a8a8a12a12",
		    $filename,
		    decoct($mode),
		    sprintf("%6s ",decoct($uid)),
		    sprintf("%6s ",decoct($gid)),
		    sprintf("%11s ",decoct($size)),
		    sprintf("%11s ",decoct($time))
		    );
		
		$blockend = pack("a1a100a6a2a32a32a8a8a155a12",
		    $type,
		    $link,
		    "ustar",
		    "00",
		    "Unknown",
		    "Unknown",
		    "",
		    "",
		    $filePrefix,
		    "");
		
		$checksum = 8*ord(" ");
		for ($i = 0; $i < 148; $i++) {
		    $checksum += ord($blockbeg{$i});
		}
		for ($i = 0; $i < 356; $i++) {
		    $checksum += ord($blockend{$i});
		}
		
		$checksum = pack("a8",sprintf("%6s ",decoct($checksum)));
		
		return $blockbeg . $checksum . $blockend;
	}
	
	/**
	* Creates the TAR footer for a file
	*
	* @param  int $size the size of the data that has been written to the TAR
	* @return string A string made of less than 512 characteres to fill the
	*         last 512 byte long block
	* @access private
	*/
	function _tar_footer($size)
	{
		if ($size % 512 > 0) {
			return pack("a".(512 - $size%512), "");
		} else {
			return "";
		}
	}
}
?>
