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


//Warning:
	//This update will always be run with each update; no matter what the version numbers are!



//Add Storekeeper thumbnails for images in the files table
	//Note: this is written in a slightly odd way to try and be as memory efficient as possible and not copy large variables
$docstoreDir = setting('docstore_dir');

$skWidth = 180;
$skHeight = 130;
$skListWidth = 24;
$skListHeight = 23;

$sql = "
	SELECT id, location, path, filename, data, mime_type, width, height
	FROM ". DB_NAME_PREFIX. "files
	WHERE mime_type IN ('image/gif', 'image/png', 'image/jpeg', 'image/pjpeg')
	  AND width != 0
	  AND height != 0
	  AND ((storekeeper_width = 0 AND storekeeper_height = 0)
	    OR storekeeper_width > ". (int) $skWidth. "
	    OR storekeeper_height > ". (int) $skHeight. "
	    OR storekeeper_data IS NULL
	  )";
$result = sqlQuery($sql);

while($img = sqlFetchAssoc($result)) {
	if ($img['location'] == 'docstore') {
		if ($docstoreDir && is_file($docstoreDir. '/'. $img['path'])) {
			$img['data'] = file_get_contents($docstoreDir. '/'. $img['path']. '/'. $img['filename']);
		} else {
			continue;
		}
	}
	
	resizeImageString($img['data'], $img['mime_type'], $img['width'], $img['height'], $skWidth, $skHeight);
	$img['data'] = "
		UPDATE ". DB_NAME_PREFIX. "files SET
			storekeeper_data = '". sqlEscape($img['data']). "',
			storekeeper_width = ". (int) $img['width']. ",
			storekeeper_height = ". (int) $img['height']. "
		WHERE id = ". (int) $img['id'];
	sqlUpdate($img['data']);
	unset($img);
}

$sql = "
	SELECT id, location, path, filename, data, mime_type, width, height
	FROM ". DB_NAME_PREFIX. "files
	WHERE mime_type IN ('image/gif', 'image/png', 'image/jpeg', 'image/pjpeg')
	  AND width != 0
	  AND height != 0
	  AND ((storekeeper_list_width = 0 AND storekeeper_list_height = 0)
	    OR storekeeper_list_width > ". (int) $skListWidth. "
	    OR storekeeper_list_height > ". (int) $skListHeight. "
	    OR storekeeper_list_data IS NULL
	  )";
$result = sqlQuery($sql);

while($img = sqlFetchAssoc($result)) {
	if ($img['location'] == 'docstore') {
		if ($docstoreDir && is_file($docstoreDir. '/'. $img['path'])) {
			$img['data'] = file_get_contents($docstoreDir. '/'. $img['path']. '/'. $img['filename']);
		} else {
			continue;
		}
	}
	
	resizeImageString($img['data'], $img['mime_type'], $img['width'], $img['height'], $skListWidth, $skListHeight);
	$img['data'] = "
		UPDATE ". DB_NAME_PREFIX. "files SET
			storekeeper_list_data = '". sqlEscape($img['data']). "',
			storekeeper_list_width = ". (int) $img['width']. ",
			storekeeper_list_height = ". (int) $img['height']. "
		WHERE id = ". (int) $img['id'];
	sqlUpdate($img['data']);
	unset($img);
}
