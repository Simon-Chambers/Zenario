<?php
/*
 * Copyright (c) 2014, Tribal Limited
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of Zenario, Tribal Limited nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL TRIBAL LTD BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
if (!defined('NOT_ACCESSED_DIRECTLY')) exit('This file may not be directly accessed');




$directories = array(
	'cache' => array(
		'pages' => 2 * 60 * 60,
		'frameworks' => -1,
		'stats' => -1,
		'tuix' => -1,
		'uploads' => 1 * 60 * 60
	), 
	'private' => array(
		'downloads' => 5 * 60 * 60,
		'images' => 2 * 60 * 60,
		'files' => 2 * 60 * 60
	), 
	'public' => array(
		'downloads' => -1
	)
);

//Loop through each temporary directory, looking to clean each up
foreach ($directories as $mainDir => $subDirs) {

	//Check each main dir is there, and attempt to create it if it is not
	if (!is_dir($dir = CMS_ROOT. $mainDir. '/')) {
		if (!@mkdir($dir)) {
			define('ZENARIO_CLEANED_DOWNLOADS', false);
			return false;
		}
		@chmod($dir, 0777);
	}

	foreach ($subDirs as $type => $lifetime) {
	
		if (!is_writable(CMS_ROOT. $mainDir. '/')) {
			define('ZENARIO_CLEANED_DOWNLOADS', false);
			return false;
	
		//Check if it is there, and create it if it is not
		} elseif (!is_dir($dir = CMS_ROOT. $mainDir. '/'. $type. '/')) {
			if (!@mkdir($dir)) {
				define('ZENARIO_CLEANED_DOWNLOADS', false);
				return false;
			}
			@chmod($dir, 0777);
	
		} elseif (!is_writable($dir)) {
			define('ZENARIO_CLEANED_DOWNLOADS', false);
			return false;
	
		//Otherwise check the file times, looking for out of date files
		} elseif ($lifetime != -1) {
			foreach (scandir($dir) as $folder) {
				if ($folder != '.' && $folder != '..') {
				
					//Check the modification date of the file called "accessed", or just grab any other file if that's not present
					if (is_dir($dir. $folder)) {
						if (!is_file($accessed = $dir. $folder. '/accessed')) {
							foreach (scandir($dir. $folder) as $file) {
								if (substr($file, 0, 1) != '.') {
									$accessed = $dir. $folder. '/'. $file;
									break;
								}
							}
						}
					} else {
						$accessed = $dir. $folder;
					}
				
					$empty = true;
					if ($accessed) {
						//Use the last access time for preference, but default to the modified time otherwise
						$timeA = @fileatime($accessed);
						$timeM = @filemtime($accessed);
				
						if (!$timeA || $timeA < $timeM) {
							$timeA = $timeM;
						}
				
						$empty = $timeA < $time - $lifetime;
					}
			
					//If the file or directory is completely out of date, delete it
					if ($empty) {
						if (is_dir($dir. $folder)) {
							deleteCacheDir($dir. $folder);
						} else {
							@unlink($dir. $folder);
						}
					}
				}
			}
		}
	}
}


if (cms_core::$lastDB) {
	//Delete any temporary files left over from WYSIWYG Editor uploads
	$sql = "
		DELETE FROM ". DB_NAME_PREFIX. "files
		WHERE `usage` = 'editor_temp_file'
		  AND created_datetime < DATE_SUB(NOW(), INTERVAL 1 DAY)";
	sqlSelect($sql);
}


define('ZENARIO_CLEANED_DOWNLOADS', true);
return true;