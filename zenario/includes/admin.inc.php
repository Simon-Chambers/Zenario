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

//Add missing definitions to avoid php warnings if we access them
function checkBoxDefinition(&$box, &$definition) {
	if (!is_array($definition)) {
		return;
	}
	if (!is_array($box)) {
		$box = array();
	}
	
	foreach ($definition as $tag => &$value) {
		if (is_array($value)) {
			if (!isset($box[$tag]) || !is_array($box[$tag])) {
				$box[$tag] = $value;
			} else {
				checkBoxDefinition($box[$tag], $value);
			}
		} else {
			if (!isset($box[$tag])) {
				$box[$tag] = $value;
			}
		}
	}
}


//Set up the floating box functionality
function setupAdminFloatingBoxes() {
	require funIncPath(__FILE__, __FUNCTION__);
}
function setupAdminSlotControls(&$slotContents, $ajaxReload) {
	return require funIncPath(__FILE__, __FUNCTION__);
}

//Write the JavaScript command needed to use the floating box above
function floatingBoxJS($message, $buttons = false, $showWarning = false, $addCancelButton = false) {
	
	if (!$buttons) {
		$buttons = '<input type="button" value="'. adminPhrase('_OK'). '" />';
	}
	
	if ($addCancelButton) {
		$buttons .= '<input type="button" value="'. adminPhrase('_CANCEL'). '" />';
	}
	
	return 'zenarioA.floatingBox(\''. jsOnClickEscape($message). '\', \''. jsOnClickEscape($buttons). '\', '. ($showWarning ===  2 || $showWarning === 'error'? '2' : ($showWarning? '1' : '0')). ');';
}


function fillAdminSlotControlPluginInfo($moduleId, $instanceId, $isVersionControlled, $cID, $cType, $level, $isNest, &$info, &$actions) {
	
	$module = getModuleDetails($moduleId);
	
	$skLink = 'zenario/admin/organizer.php?fromCID='. (int) $cID. '&fromCType='. urlencode($cType);
	$modulesLink = '#zenario__modules/nav/modules/panel//' . $moduleId;
	
	$info['module_name']['label'] =
		adminPhrase('Module: <a href="[[link]]">[[display_name]]</a>',
			array(
				'link' => htmlspecialchars($skLink . $modulesLink),
				'class_name' => htmlspecialchars($module['class_name']),
				'display_name' => htmlspecialchars($module['display_name'])));
	
	if ($isVersionControlled) {
		$pluginAdminName =
		$ucPluginAdminName = htmlspecialchars($module['display_name']);
		
		$info['module_name']['css_class'] = 'zenario_slotControl_wireframe';
		
		unset($info['reusable_plugin_name']);
		unset($info['reusable_plugin_usage']);
	
	} else {
		if ($isNest) {
			$pluginAdminName = adminPhrase('nest');
			$ucPluginAdminName = adminPhrase('Nest');
		} else {
			$pluginAdminName = adminPhrase('plugin');
			$ucPluginAdminName = adminPhrase('Plugin');
		}
		
		$pluginsLink = '#zenario__modules/nav/modules/panel/item//' . $moduleId. '//'. $instanceId;
		
		$info['module_name']['css_class'] = 'zenario_slotControl_reusable';
		
		$mrg = getPluginInstanceDetails($instanceId);
		$mrg['instance_name'] = htmlspecialchars($mrg['instance_name']);
		$mrg['content_items'] = checkInstancesUsage($instanceId, $publishedOnly = false, $itemLayerOnly = true);
		$mrg['layouts'] = checkInstancesUsageOnLayouts($instanceId);
		$mrg['plugins_link'] = htmlspecialchars($skLink. $pluginsLink);
		$mrg['content_items_link'] = htmlspecialchars($skLink. '#zenario__modules/nav/instances/panel/item_buttons/usage_item//'. (int) $instanceId. '//');
		$mrg['layouts_link'] = $skLink. htmlspecialchars('#zenario__modules/nav/instances/panel/item_buttons/usage_template//'. (int) $instanceId. '//');
		
		//Not used on any layouts
		if (!$mrg['layouts']) {
			//Not used on any content items
			if (!$mrg['content_items']) {
				$info['reusable_plugin_details']['label'] = adminPhrase('Plugin: <a href="[[plugins_link]]">[[instance_name]]</a>', $mrg);
			
			//Used on this content item only
			} elseif ($mrg['content_items'] == 1 && $level == 1) {
				$info['reusable_plugin_details']['label'] = adminPhrase('Plugin: <a href="[[plugins_link]]">[[instance_name]]</a>, used on this content item only', $mrg);
			
			} elseif ($mrg['content_items'] == 1) {
				$info['reusable_plugin_details']['label'] = adminPhrase('Plugin: <a href="[[plugins_link]]">[[instance_name]]</a>, used on <a href="[[content_items_link]]">1 content item</a>', $mrg);
			
			} else {
				$info['reusable_plugin_details']['label'] = adminPhrase('Plugin: <a href="[[plugins_link]]">[[instance_name]]</a>, used on <a href="[[content_items_link]]">[[content_items]] content items</a>', $mrg);
			}
		
		//Just used on this layout
		} elseif ($mrg['layouts'] == 1 && $level == 2) {
			//Not used on any content items
			if (!$mrg['content_items']) {
				$info['reusable_plugin_details']['label'] = adminPhrase('Plugin: <a href="[[plugins_link]]">[[instance_name]]</a>, used on this layout only', $mrg);
			
			//Used on this content item only
			} elseif ($mrg['content_items'] == 1 && $level == 1) {
				$info['reusable_plugin_details']['label'] = adminPhrase('Plugin: <a href="[[plugins_link]]">[[instance_name]]</a>, used on this layout and on this content item (are you sure you want that setup?)', $mrg);
			
			} elseif ($mrg['content_items'] == 1) {
				$info['reusable_plugin_details']['label'] = adminPhrase('Plugin: <a href="[[plugins_link]]">[[instance_name]]</a>, used on this layout and on <a href="[[content_items_link]]">1 content item</a>', $mrg);
			
			} else {
				$info['reusable_plugin_details']['label'] = adminPhrase('Plugin: <a href="[[plugins_link]]">[[instance_name]]</a>, used on this layout and on <a href="[[content_items_link]]">[[content_items]] content items</a>', $mrg);
			}
		
		} else {
			//Not used on any content items
			if (!$mrg['content_items']) {
				$info['reusable_plugin_details']['label'] = adminPhrase('Plugin: <a href="[[plugins_link]]">[[instance_name]]</a>, used on <a href="[[layouts_link]]">[[layouts]] layouts</a>', $mrg);
			
			//Used on this content item only
			} elseif ($mrg['content_items'] == 1 && $level == 1) {
				$info['reusable_plugin_details']['label'] = adminPhrase('Plugin: <a href="[[plugins_link]]">[[instance_name]]</a>, used on <a href="[[layouts_link]]">[[layouts]] layouts</a> and on this content item', $mrg);
			
			} elseif ($mrg['content_items'] == 1) {
				$info['reusable_plugin_details']['label'] = adminPhrase('Plugin: <a href="[[plugins_link]]">[[instance_name]]</a>, used on <a href="[[layouts_link]]">[[layouts]] layouts</a> and on <a href="[[content_items_link]]">1 content item</a>', $mrg);
			
			} else {
				$info['reusable_plugin_details']['label'] = adminPhrase('Plugin: <a href="[[plugins_link]]">[[instance_name]]</a>, used on <a href="[[layouts_link]]">[[layouts]] layouts</a> and on <a href="[[content_items_link]]">[[content_items]] content items</a>', $mrg);
			}
		}
	}
	
	if (isset($actions) && is_array($actions)) {
		foreach ($actions as &$action) {
			if (is_array($action) && !empty($action['label'])) {
				$action['label'] = str_replace('~plugin~', $pluginAdminName, str_replace('~Plugin~', $ucPluginAdminName, $action['label']));
			}
		}
	}
}




function timeDiff($a, $b, $lowerLimit = false) {
	$sql = "
		SELECT
			datediff(a, b),
			hour(timediff(a, b)),
			minute(timediff(a, b)),
			second(timediff(a, b))
		FROM (
			SELECT
				'". sqlEscape($a). "' AS a,
				'". sqlEscape($b). "' AS b
		) AS ab";
	
	$result = sqlQuery($sql);
	$row = sqlFetchRow($result);
	
	if ($lowerLimit && $lowerLimit > ((int) $row[3] + 60 * ((int) $row[2] + 60 * ((int) $row[1] + 24 * (int) $row[0])))) {
		return true;
	}
	
	$singular = array(adminPhrase('1 day'), adminPhrase('1 hour'), adminPhrase('1 minute'), adminPhrase('1 second'));
	$plural = array(adminPhrase('[[n]] days'), adminPhrase('[[n]] hours'), adminPhrase('[[n]] minutes'), adminPhrase('[[n]] seconds'));
	
	foreach ($singular as $i => $phrase) {
		if ($row[$i] || $i == 3) {
			if ($row[$i] == 1) {
				return $phrase;
			} else {
				return adminPhrase($plural[$i], array('n' => $row[$i]));
			}
		}
	}
}



//Content Items

function getStatusPhrase($status) {
	switch ($status) {
		case 'first_draft':
			return adminPhrase('First Draft');
		case 'hidden':
			return adminPhrase('Hidden');
		case 'hidden_with_draft':
			return adminPhrase('Hidden with Draft');
		case 'published':
			return adminPhrase('Published');
		case 'published_with_draft':
			return adminPhrase('Published with Draft');
		case 'trashed':
			return adminPhrase('Trashed');
		case 'trashed_with_draft':
			return adminPhrase('Trashed with Draft');
	}
	
	return '';
}


function checkContentTypeRunning($cType) {
	return
		$cType == 'html' || (
			($moduleId = getRow('content_types', 'module_id', array('content_type_id' => $cType)))
		 && (getModuleStatus($moduleId) == 'module_running')
		 && (moduleDir(getModuleName($moduleId)))
		);
}

function createDraft(&$cIDTo, $cIDFrom, $cType, &$cVersionTo, $cVersionFrom = false, $languageId = false, $adminId = false, $useCIDIfItDoesntExist = false, $cTypeFrom = false) {
	require funIncPath(__FILE__, __FUNCTION__);
}

function updateVersion($cID, $cType, $cVersion, $version = array(), $forceMarkAsEditsMade = false) {
	
	if ($forceMarkAsEditsMade) {
		//A Content Item is counted as have been edited if the last modified date time is more than the date created date time.
		//In a few rare cases the same request may create a new draft and apply some changes.
		//In that case we'll up the last modified date time by one second, as a hack to mark it as edited
		$result = sqlSelect("SELECT DATE_ADD(NOW(), INTERVAL 1 SECOND)");
		$row = sqlFetchRow($result);
		$version['last_modified_datetime'] = $row[0];
	} else {
		$version['last_modified_datetime'] = now();
	}
	
	$version['last_author_id'] = session('admin_userid');
	updateRow('versions', $version, array('id' => $cID, 'type' => $cType, 'version' => $cVersion));
}

function checkIfVersionChanged($cIDOrVersion, $cType = false, $cVersion = false) {
	
	if (is_numeric($cIDOrVersion)) {
		$cIDOrVersion = getRow(
			'versions',
			array('last_author_id', 'last_modified_datetime', 'creating_author_id', 'created_datetime'),
			array('id' => $cIDOrVersion, 'type' => $cType, 'version' => $cVersion));
	}
	
	return $cIDOrVersion['last_author_id'] &&
		($cIDOrVersion['last_modified_datetime'] != $cIDOrVersion['created_datetime']
	  || $cIDOrVersion['last_author_id'] != $cIDOrVersion['creating_author_id']);

}

function publishContent($cID, $cType, $adminId = false) {
	if (!$adminId) {
		$adminId = session('admin_userid');
	}
	
	if (!($content = getRow('content', array('admin_version', 'alias', 'status'), array('id' => $cID, 'type' => $cType)))
	 || !($cVersion = $content['admin_version'])
	 || !($version = getRow('versions', array('publication_date'), array('id' => $cID, 'type' => $cType, 'version' => $cVersion)))) {
		return false;
	}
	
	
	$oldStatus = $content['status'];
	
	$content['status'] = 'published';
	$content['lock_owner_id'] = 0;
	$content['locked_datetime'] = null;
	$content['visitor_version'] = $cVersion;
	
	$version['publisher_id'] = $adminId;
	$version['published_datetime'] = now();
	
	if (!$version['publication_date']
	 && setting('auto_set_release_date') == 'on_publication_if_not_set') {
		$version['publication_date'] = now();
	}
	
	updateRow('content', $content, array('id' => $cID, 'type' => $cType));
	updateRow('versions', $version, array('id' => $cID, 'type' => $cType, 'version' => $cVersion));
	
	removeUnusedVersionControlledPluginSettings($cID, $cType, $content['admin_version']);
	syncInlineFileContentLink($cID, $cType, $content['admin_version']);
	
	cms_core::publishContent($cID, $cType, $cVersion, $cVersion-1, $adminId);
	
	$sql = "
		DELETE FROM ". DB_NAME_PREFIX. "content_cache
		WHERE content_id = ". (int) $cID. "
		  AND content_type = '". sqlEscape($cType). "'
		  AND content_version < ". (int) $cVersion;
	sqlQuery($sql);

	hideOrShowContentItemsMenuNode($cID, $cType, $oldStatus, 'published');

	sendSignal("eventContentPublished",array("cID" => $cID,"cType" => $cType, "cVersion" => $cVersion));
}


//Get/set the content of a WYSIWYG Editor
function getContent($cID, $cType, $cVersion, $slotName = false, $moduleName = 'zenario_wysiwyg_editor', $settingName = 'html') {

	$moduleId = getModuleIdByClassName($moduleName);
	
	if ($slotName === false) {
		$slotName = pluginMainSlot($cID, $cType, $cVersion, $moduleId);
	}
	
	$key = array(
			'module_id' => $moduleId,
			'content_id' => $cID,
			'content_type' => $cType,
			'content_version' => $cVersion,
			'slot_name' => $slotName);
	
	if ($instanceId = getRow('plugin_instances', 'id', $key)) {
		return getRow(
			'plugin_settings',
			'value',
			array(
				'instance_id' => $instanceId,
				'name' => $settingName,
				'is_content' => 'version_controlled_content'));
	}
	
	return false;
}


function saveContent($content, $cID, $cType, $cVersion, $slotName = false, $moduleName = 'zenario_wysiwyg_editor', $settingName = 'html') {
	
	$moduleId = getModuleIdByClassName($moduleName);
	
	if ($slotName === false) {
		$slotName = pluginMainSlot($cID, $cType, $cVersion, $moduleId);
	}
	
	$key = array(
			'module_id' => $moduleId,
			'content_id' => $cID,
			'content_type' => $cType,
			'content_version' => $cVersion,
			'slot_name' => $slotName);
	
	if (!$instanceId = getRow('plugin_instances', 'id', $key)) {
		$instanceId = insertRow('plugin_instances', $key);
	}
	
	setRow(
		'plugin_settings',
		array(
			'value' => $content,
			'is_content' => 'version_controlled_content'),
		array(
			'instance_id' => $instanceId,
			'name' => $settingName));
	
	syncInlineFileContentLink($cID, $cType, $cVersion);
	
	updateVersion($cID, $cType, $cVersion);
}


//Get/set the content of any version-controlled plugin (except nests)
function getPluginContent($key) {
	
	$instance = getRow('plugin_instances', array('id', 'module_id', 'framework', 'css_class'), $key);
	
	if (!empty($instance)) {
		$instance['class_name'] = getModuleClassName($instance['module_id']);
		$instance['settings'] = getRowsArray('plugin_settings', true, array('instance_id' => $instance['id'], 'nest' => 0));
		
		foreach ($instance['settings'] as &$setting) {
			unset($setting['instance_id']);
		}
	}
	
	return $instance;
}

function setPluginContent($key, $instance = false) {
	
	if ($instanceId = getRow('plugin_instances', 'id', $key)) {
		
		deleteRow('plugin_settings', array('instance_id' => $instanceId, 'nest' => 0));
		
		if ($instance && !empty($instance['settings'])) {
			updateRow('plugin_instances', array('framework' => $instance['framework'], 'css_class' => $instance['css_class']), $instanceId);
	
			foreach ($instance['settings'] as $setting) {
				$setting['instance_id'] = $instanceId;
				$setting['nest'] = 0;
				insertRow('plugin_settings', $setting);
			}
		}
	}
}



//Workaround for problems with absolute and relative URLs in TinyMCE:
	//When loading, convert all relative URLs to absolute URLs so Admins can always see the images
function addAbsURLsToAdminBoxField(&$field) {
	foreach (array('value', 'current_value') as $value) {
		if (isset($field[$value])) {
			foreach (array('"', "'") as $quote) {
				$field[$value] = 
					str_replace(
						$quote. 'zenario/file.php',
						$quote. htmlspecialchars(absCMSDirURL()). 'zenario/file.php',
						$field[$value]);
			}
		}
	}
}

//Workaround for problems with absolute and relative URLs in TinyMCE:
	//Second, convert all absolute URLs to relative URLs when saving
function stripAbsURLsFromAdminBoxField(&$field) {
	foreach (array('value', 'current_value') as $value) {
		if (isset($field[$value])) {
			foreach (array('"', "'") as $quote) {
				$field[$value] = 
					str_replace(
						$quote. htmlspecialchars(absCMSDirURL()),
						$quote,
						$field[$value]);
			}
		}
	}
}


//Scan a Content Item's HTML and other information, and come up with a list of inline files that relate to it
//Note there is simmilar logic in zenario/admin/db_updates/data_conversion/local.inc.php for migration
function syncInlineFileContentLink($cID, $cType, $cVersion) {
	require funIncPath(__FILE__, __FUNCTION__);
}

function syncInlineFileLinks($usage, &$files, &$html, &$htmlChanged) {
	require funIncPath(__FILE__, __FUNCTION__);
}

function syncInlineFiles(&$files, $key) {
	//Mark all existing images as not in use
	updateRow('inline_file_link', array('in_use' => 0), $key);
	
	//Add in the ones that we actually found, or mark them as in use if they are already there
	foreach ($files as $file) {
		$values = array('in_use' => 1);
		$key['file_id'] = $file['id'];
		setRow('inline_file_link', $values, $key);
	}
}

//Check to see if a file is not used, and delete it
//Note that any files in the docstore directory are never deleted from the database.
//Shared files, and files used for cont
function deleteUnusedInlineFile($fileId, $allowDeleteShared = false) {
	if (!checkRowExists('users', array('image_id' => $fileId))
	 && !checkRowExists('groups', array('image_id' => $fileId))
	 && !checkRowExists('versions', array('file_id' => $fileId))
	 && !checkRowExists('layouts', array('image_id' => $fileId))
	 && !checkRowExists('inline_file_link', array('file_id' => $fileId, 'foreign_key_to' => array('!' => 'reusable_plugin')))
	 && !checkRowExists('plugin_settings', array('foreign_key_to' => 'file', 'foreign_key_id' => $fileId))) {
		
		$file = getRow('files', array('usage', 'shared'), $fileId);
		
		if (!$allowDeleteShared && $file['shared']) {
			return;
		} else if ($file['usage'] == 'favicon' || $file['usage'] == 'mobile_icon') {
			return;
		} else {
			deleteFileFromCmsStorage($fileId);
		}
		
	}
}

function deleteFileFromCmsStorage($fileId) {
	if ($docstoreFilePath = docstoreFilePath($fileId, false)) {
		//unlink the file
		if (@unlink($docstoreFilePath)) {
			@rmdir(dirname($docstoreFilePath));
		}
	}
	
	deleteRow('files', $fileId);
}

function deleteUnusedFilesByName($usage, $filename, $excludeFileId) {
	//Look for files with the same name, that are not in use
	$result = getRows('files', array('id'), array('filename' => $filename, 'usage' => $usage));
	while ($file = sqlFetchAssoc($result)) {
		if ($file['id'] != $excludeFileId) {
			deleteUnusedInlineFile($file['id']);
		}
	}
}

//Delete from the inline_file_link table, and then completely delete anything that is now no longer used
function deleteUnusedFilesByLinkedKey($key) {
	$fileIds = array();
	$result = getRows('inline_file_link', 'file_id', $key);
	while ($file = sqlFetchAssoc($result)) {
		$fileIds[$file['file_id']] = $file['file_id'];
	}
	
	deleteRow('inline_file_link', $key);
	
	foreach ($fileIds as $fileId) {
		deleteUnusedInlineFile($fileId);
	}
}

//Look for User/Group/Template files that are not in use, and remove them
function deleteUnusedImagesByUsage($usage) {
	
	if ($usage == 'template') {
		$usage = 'layout';
	}
	
	if ($usage != 'group' && $usage != 'layout' && $usage != 'user' && $usage != 'admin') {
		return;
	}
	
	$sql = "
		SELECT f.id
		FROM ". DB_NAME_PREFIX. "files AS f
		LEFT JOIN `". DB_NAME_PREFIX. sqlEscape($usage). "s` AS u
		   ON u.image_id = f.id
		WHERE u.image_id IS NULL
		  AND f.location = 'db'
		  AND f.`usage` = '". sqlEscape($usage). "'";
	
	$result = sqlQuery($sql);
	while ($file = sqlFetchAssoc($result)) {
		deleteUnusedInlineFile($file['id']);
	}
}



function deleteDraft($cID, $cType, $allowCompleteDeletion = true, $adminId = false) {
	if (!$adminId) {
		$adminId = session('admin_userid');
	}
	
	$content = getRow('content', array('status', 'admin_version', 'visitor_version'), array('id' => $cID, 'type' => $cType));
	$cVersion = $content['admin_version'];
	$content['lock_owner_id'] = 0;
	$content['locked_datetime'] = null;
	
	if ($content['status'] == 'first_draft') {
		$content['status'] = 'deleted';
	
	} elseif ($content['status'] == 'published_with_draft') {
		$content['status'] = 'published';
		$content['admin_version'] = $content['visitor_version'];
	
	} elseif ($content['status'] == 'hidden_with_draft') {
		$content['status'] = 'hidden';
		--$content['admin_version'];
	
	} elseif ($content['status'] == 'trashed_with_draft') {
		$content['status'] = 'trashed';
		--$content['admin_version'];
	
	} else {
		return;
	}
	
	
	updateRow('content', $content, array('id' => $cID, 'type' => $cType));
	
	//Add a safety catch, that logically should never be reached, but is there just so we don't delete the current version
	if ($cVersion == $content['visitor_version']) {
		return;
	
	} else {
		if ($content['status'] == 'deleted' && $allowCompleteDeletion) {
			deleteContentItem($cID, $cType);
		} else {
			deleteVersion($cID, $cType, $cVersion);
			sendSignal("eventContentDeleted",array("cID" => $cID,"cType" => $cType, "cVersion" => $cVersion));
		}
	}
	
	return;
}

function deleteVersion($cID, $cType, $cVersion) {
	deleteRow('versions', array('id' => $cID, 'type' => $cType, 'version' => $cVersion));
	deleteRow('plugin_item_link', array('content_id' => $cID, 'content_type' => $cType, 'content_version' => $cVersion));
	deleteRow('content_cache', array('content_id' => $cID, 'content_type' => $cType, 'content_version' => $cVersion));
	
	deleteVersionControlledPluginSettings($cID, $cType, $cVersion);
	
	deleteUnusedFilesByLinkedKey(array('foreign_key_to' => 'content', 'foreign_key_id' => $cID, 'foreign_key_char' => $cType, 'foreign_key_version' => $cVersion));
}

function deleteArchive($cID, $cType, $cVersion) {
	for ($v = $cVersion; $v > 0; --$v) {
		deleteVersion($cID, $cType, $v);
	}
}

function deleteContentItem($cID, $cType) {
	$content = array('id' => $cID, 'type' => $cType);
	
	$result = getRows('versions', array('id', 'type', 'version'), $content);
	while ($version = sqlFetchAssoc($result)) {
		deleteVersion($version['id'], $version['type'], $version['version']);
		sendSignal('eventContentDeleted',array('cID' => $version['id'], 'cType' => $version['type'], 'cVersion' => $version['version']));
	}
	
	removeItemFromMenu($cID, $cType);
	removeEquivalence($cID, $cType);
	removeItemFromPluginSettings('content', $cID, $cType);
	setRow('content', array('status' => 'deleted', 'admin_version' => 0, 'visitor_version' => 0, 'alias' => ''), $content);
}

function trashContent($cID, $cType, $adminId = false) {
	
	if (!$adminId) {
		$adminId = session('admin_userid');
	}

	$cVersion = getRow('content', 'admin_version', array('id' => $cID, 'type' => $cType));
	updateRow('content', array('visitor_version' => 0, 'status' => 'trashed', 'alias' => ''), array('id' => $cID, 'type' => $cType));
	updateRow('versions', array('concealer_id' => $adminId, 'concealed_datetime' => now()), array('id' => $cID, 'type' => $cType, 'version' => $cVersion));
	
	removeItemFromMenu($cID, $cType);
	removeEquivalence($cID, $cType);
	removeItemFromPluginSettings('content', $cID, $cType);
	
	sendSignal("eventContentTrashed",array("cID" => $cID,"cType" => $cType));
}

//$status == 'first_draft' || $status == 'published_with_draft' || $status == 'published'
function hideContent($cID, $cType, $adminId = false) {
	
	if (!$adminId) {
		$adminId = session('admin_userid');
	}
	
	//If this a draft that's not been modified since the previous version, delete the draft
	$content = getRow('content', array('status', 'admin_version'), array('id' => $cID, 'type' => $cType));
	$oldStatus = $content['status'];
	
	if (($oldStatus == 'published_with_draft' || $oldStatus == 'hidden_with_draft' || $oldStatus == 'trashed_with_draft')
	 && !contentLastModifiedBy($cID, $cType)) {
		deleteDraft($cID, $cType, $allowCompleteDeletion = false);
		$content = getRow('content', array('status', 'admin_version'), array('id' => $cID, 'type' => $cType));
	}
	
	//Update the Content Item's status to "hidden"
	updateRow('content', array('visitor_version' => 0, 'status' => 'hidden', 'lock_owner_id' => 0), array('id' => $cID, 'type' => $cType));
	updateRow('versions', array('concealer_id' => $adminId, 'concealed_datetime' => now()), array('id' => $cID, 'type' => $cType, 'version' => $content['admin_version']));
	
	hideOrShowContentItemsMenuNode($cID, $cType, $oldStatus, 'hidden');
	
	sendSignal("eventContentHidden",array("cID" => $cID,"cType" => $cType));
}

//If a Content Item is published/hidden, its Menu Node may be shown/hidden as well
//Check for this case, and clear the cache if needed
function hideOrShowContentItemsMenuNode($cID, $cType, $oldStatus, $newStatus = false) {
	if (getMenuItemFromContent($cID, $cType)) {
		if (!$newStatus) {
			$newStatus = getContentStatus($cID, $cType);
		}
		
		if (isPublished($oldStatus) != isPublished($newStatus)) {
			$sql = $ids = $values = false;
			cms_core::reviewDatabaseQueryForChanges($sql, $ids, $values, $table = 'menu_nodes');
		}
	}
}

//Delete the Menu Node for a Content Item
function removeItemFromMenu($cID, $cType) {
	$languageId = getContentLang($cID, $cType);
	$equivId = $cID;
	langEquivalentItem($equivId, $cType, true);
	
	//Look up any Menu Nodes that point to this Item
	$result = getRows('menu_nodes', 'id', array('equiv_id' => $equivId, 'content_type' => $cType));
	while ($row = sqlFetchAssoc($result)) {
		//Check if any child Menu Nodes exist
		$childrenExist = checkRowExists('menu_nodes', array('parent_id' => $row['id']));
		
		//Check if this Menu Node has translations in another Language than this one
		$otherEquivsExist =
			($equivResult = getRows('menu_text', 'language_id', array('menu_id' => $row['id'])))
		 && ($equiv = sqlFetchAssoc($equivResult))
		 && ($equiv['language_id'] != $languageId || sqlFetchAssoc($equivResult));
		
		
		if ($childrenExist) {
			if ($otherEquivsExist) {
			} else {
				//If this Menu Node has children, only remove the link to this item but keep it in the database as an unlinked Node
				updateRow('menu_nodes', array('equiv_id' => 0, 'content_type' => '', 'target_loc' => 'none'), $row['id']);
			}
		} else {
			if ($otherEquivsExist) {
				//If other languages are still using this Menu Node we cannot delete it completely
				removeMenuText($row['id'], $languageId);
			} else {
				deleteMenuNode($row['id']);
			}
		}
	}
}

function removeItemFromPluginSettings($keyTo, $keyId = 0, $keyChar = '') {
	$sql = "
		DELETE
		FROM ". DB_NAME_PREFIX. "plugin_settings
		WHERE dangling_cross_references = 'remove'
		  AND foreign_key_to = '". sqlEscape($keyTo). "'
		  AND foreign_key_id = ". (int) $keyId. "
		  AND foreign_key_char = '". sqlEscape($keyChar). "'";
	sqlQuery($sql);
	
	$sql = "
		SELECT
			pi.module_id,
			m.class_name,
			ps.instance_id,
			ps.nest,
			pi.content_id,
			pi.content_type,
			pi.content_version,
			pi.slot_name
		FROM ". DB_NAME_PREFIX. "plugin_settings AS ps
		INNER JOIN ". DB_NAME_PREFIX. "plugin_instances AS pi
		   ON pi.id = ps.instance_id
		INNER JOIN ". DB_NAME_PREFIX. "modules AS m
		   ON m.id = pi.module_id
		WHERE dangling_cross_references = 'delete_instance'
		  AND foreign_key_to = '". sqlEscape($keyTo). "'
		  AND foreign_key_id = ". (int) $keyId. "
		  AND foreign_key_char = '". sqlEscape($keyChar). "'";
	
	$result = sqlQuery($sql);
	while ($row = sqlFetchAssoc($result)) {
		if ($row['nest']) {
			if (inc($row['class_name'])) {
				//Delete this egg from the nest
				call_user_func(array($row['class_name'], 'removePlugin'), $row['class_name'], $row['nest'], $row['instance_id']);
			}
		
		} elseif ($row['content_id']) {
			//Clear the settings for this version controlled module
			deleteRow('plugin_settings', array('instance_id' => $row['instance_id']));
		
		} else {
			//Delete this instance
			deletePluginInstance($row['instance_id']);
		}
	}
}

//Check if a Content Item is in a state where it could be deleted/trashed/hidden. Note that these functions don't check for locks.
function allowDelete($cID, $cType, $status = false) {
	if (!$status) {
		$status = getRow('content', 'status', array('id' => $cID, 'type' => $cType));
	}
	
	if ($status == 'first_draft') {
		return !isSpecialPage($cID, $cType) || allowRemoveEquivalence($cID, $cType);
	
	} else {
		return $status == 'published_with_draft' || $status == 'hidden_with_draft' || $status == 'trashed_with_draft';
	}
}

//checkPriv("_PRIV_PUBLISH_CONTENT_ITEM")
function allowTrash($cID, $cType, $status = false, $lastModified = false) {
	if (isSpecialPage($cID, $cType) && !allowRemoveEquivalence($cID, $cType)) {
		return false;
	} else {
		if ($status === false) {
			$status = getRow('content', 'status', array('id' => $cID, 'type' => $cType));
		}
		
		if ($status == 'published' || $status == 'published_with_draft' || $status == 'hidden_with_draft') {
			return true;
		} else {
			return false;
		}
	}	
}

//checkPriv("_PRIV_PUBLISH_CONTENT_ITEM")
function allowHide($cID, $cType, $status = false) {
	
	if (isSpecialPage($cID, $cType) && !allowRemoveEquivalence($cID, $cType)) {
		return false;
	} else {
		if ($status === false) {
			$status = getRow('content', 'status', array('id' => $cID, 'type' => $cType));
		}
		
		return
			$status == 'first_draft'
		 || $status == 'published_with_draft'
		 || $status == 'trashed_with_draft'
		 || $status == 'hidden_with_draft'
		 || $status == 'published';
	}	
}






function rerenderWorkingCopyImages($workingCopyImages = true, $thumbnailWorkingCopyImages = true, $removeOldWorkingCopies = false, $jpegOnly = false) {
	require funIncPath(__FILE__, __FUNCTION__);
}





function allowDeleteLanguage($langId) {
	return $langId != setting('default_language');
}

function deleteLanguage($langId) {
	//Remove all of the Content Items in a Language
	$result = getRows('content', array('id', 'type'), array('language_id' => $langId));
	while ($content = sqlFetchAssoc($result)) {
		deleteContentItem($content['id'], $content['type']);
	}
	
	//Remove any remaining Menu translations in a Language
	deleteRow('menu_text', array('language_id' => $langId));
	
	//Remove any Menu Nodes that now do not have translations
	$sql = "
		SELECT mn.id
		FROM ". DB_NAME_PREFIX. "menu_nodes AS mn
		LEFT JOIN ". DB_NAME_PREFIX. "menu_text AS mt
		   ON mt.menu_id = mn.id
		WHERE mt.menu_id IS NULL";
	$result = sqlQuery($sql);
	while ($menu = sqlFetchAssoc($result)) {
		deleteMenuNode($menu['id']);
	}
	
	//Remove any Visitor Phrases, except for Visitor Pharses from the Common Features Module
	deleteRow('visitor_phrases', array('language_id' => $langId, 'module_class_name' => array('!1' => 'zenario_common_features', '!2' => '')));
	
	deleteRow('languages', $langId);
}

function contentLastModifiedBy($cID, $cType) {
	$sql = "
		SELECT last_author_id
		FROM ". DB_NAME_PREFIX. "versions
		WHERE id = ". (int) $cID. "
		  AND type = '". sqlEscape($cType). "'
		ORDER BY version DESC
		LIMIT 1";
	
	if (($result = sqlQuery($sql)) && ($row = sqlFetchAssoc($result))) {
		return $row['last_author_id'];
	} else {
		return false;
	}
}


function importPhrasesForModule($moduleClassName, $langId = false) {

	//Check if this Module uses the old Visitor phrases system, with phrases in CSV files
	if ($path = moduleDir($moduleClassName, 'phrases/', true)) {
		$importFiles = scanModulePhraseDir($moduleClassName);
		if (!empty($importFiles)) {
	
			//Check which languages this site uses
			if ($langId === false) {
				$installedLanguages = getRowsArray('languages', 'id', array());
			} else {
				$installedLanguages = array($langId);
			}
	
			if (!empty($installedLanguages)) {
		
				//For every language in the site, try to find a matching csv file
				foreach ($installedLanguages as $installedLang) {
					$bestMatch = false;
					foreach ($importFiles as $languageId => $file) {
						if ($languageId == $installedLang) {
							//If a language on the site matches a language availible to the module, import that
							$bestMatch = $file;
							break;
				
						} elseif (substr($languageId, 0, 2) == substr($installedLang, 0, 2)) {
							//Otherwise if there is a close match, use that
							$bestMatch = $file;
						}
					}
			
					if ($bestMatch) {
						importVisitorLanguagePack(CMS_ROOT. $path. $bestMatch, $installedLang, $adding = false, $scanning = false, $forceLanguageIdOverride = true);
					}
				}
			}
		}
	}
}

function importPhrasesForModules($langId = false) {
	foreach (getModules($onlyGetRunningPlugins = true, $ignoreUninstalledPlugins = true, $dbUpdateSafemode = true) as $module) {
		importPhrasesForModule($module['class_name'], $langId);
	}
}

function addNeededSpecialPages() {
	require funIncPath(__FILE__, __FUNCTION__);
}



function getItemIconClass($cID, $cType, $checkForSpecialPage = true, $status = false) {
	
	if ($status === false) {
		$status = getContentStatus($cID, $cType);
	}
	
	$homepage = $specialPage = false;
	if ($checkForSpecialPage) {
		if ($pageType = isSpecialPage($cID, $cType)) {
			if ($pageType == 'zenario_home') {
				$homepage = true;
			} else {
				$specialPage = true;
			}
		}
	}
	
	switch ($status) {
		case 'first_draft':
			return $homepage? 'home_content_draft' : ($specialPage? 'special_content_draft' : 'content_draft');
		
		case 'published':
			return $homepage? 'home_content_published' : ($specialPage? 'special_content_published' : 'content_published');
		
		case 'published_with_draft':
			return $homepage? 'home_content_draft_published' : ($specialPage? 'special_content_draft_published' : 'content_draft_published');
		
		case 'hidden':
			return $specialPage? 'special_content_hidden' : 'content_hidden';
		
		case 'hidden_with_draft':
			return $specialPage? 'special_content_draft_hidden' : 'content_draft_hidden';
		
		case 'trashed':
			return $specialPage? 'special_content_trashed' : 'content_trashed';
		
		case 'trashed_with_draft':
			return $specialPage? 'special_content_draft_trashed' : 'content_draft_trashed';
	}
	
	return '';
}

function getContentStatusIcon($cID, $cType, $status = false) {
	return '<div class="'. getItemIconClass($cID, $cType, false, $status). '"></div>';
}



function getContentItemVersionToolbarIcon(&$content, $cVersion, $prefix = '') {
	return $prefix. getContentItemVersionStatus($content, $cVersion);
}

function getContentItemVersionStatus($content, $cVersion) {
	
	if ($cVersion == $content['visitor_version']) {
		switch ($content['status']) {
			case 'published_with_draft':
			case 'published':
				return 'published';
		}
	
	} elseif ($cVersion == $content['admin_version']) {
		switch ($content['status']) {
			case 'hidden':
				return 'hidden';
			case 'trashed':
				return 'trashed';
			default:
				return 'draft';
		}
	
	} elseif ($cVersion == $content['admin_version'] - 1) {
		switch ($content['status']) {
			case 'hidden_with_draft':
				return 'hidden';
			case 'trashed_with_draft':
				return 'trashed';
		}
	}

	return 'archived';
}





//Validation function for checking aliases
function validateAlias($alias, $cID = false, $cType = false, $equivId = false) {
	$error = array();
	$dummy1 = $dummy2 = false;
	
	if ($alias!="") {
		if (preg_match('/\s/', $alias)) {
			$error[] = adminPhrase("You must not have a space in your Alias.");
		}
		
		if (preg_match('/[A-Z]/', $alias)) {
			$error[] = adminPhrase("An Alias cannot have Upper Case Characters.");
		}
		
		if ($alias == 'admin' || is_dir(CMS_ROOT. $alias)) {
			$error[] = adminPhrase("You cannot use an Alias if a directory exists with that name (e.g., 'admin' or 'zenario').");
		
		} elseif (is_numeric($alias)) {
			$error[] = adminPhrase("You must enter a non-numeric Alias.");
		
		} elseif (preg_match('/[^a-zA-Z 0-9_-]/', $alias)) {
			$error[] = adminPhrase("An Alias can only contain the letters a-z, numbers, underscores or hyphens.");
		
		} elseif (getCIDAndCTypeFromTagId($dummy1, $dummy2, $alias)) {
			$error[] = adminPhrase("You cannot make an Alias of the format 'html_1', 'document_2', ...");
		}
		
		
		if ($cID && $cType && !$equivId) {
			$equivId = equivId($cID, $cType);
		}
		
		$sql = "
			SELECT id, type
			FROM ". DB_NAME_PREFIX. "content
			WHERE alias = '". sqlEscape($alias). "'";
		
		if ($equivId && $cType) {
			$sql .= "
			  AND (equiv_id != ". (int) $equivId. " OR type != '". sqlEscape($cType). "')";
		}
		
		$sql .= "
			LIMIT 1";
		
		if (($result = sqlQuery($sql))
		 && ($row = sqlFetchAssoc($result))) {
			$tag = formatTag($row['id'], $row['type']);
			$error[] = adminPhrase('Please select a unique friendly name. "[[alias]]" is already taken by "[[tag]]".', array('alias' => $alias, 'tag' => $tag));
		}
	}
	
	if (empty($error)) {
		return false;
	} else {
		return $error;
	}
}

function tidyAlias($alias) {
	
	$alias = strtolower($alias);
	$alias = str_replace(" ","-",$alias);
	$alias = preg_replace("/[^a-z0-9-_]/","",$alias);
	
	return $alias;
}

//ADMINISTRATOR

//Takes a row from an admin table, and returns the admin's name in the standard format
function formatAdminName($adminDetails = false) {
	
	if (!$adminDetails) {
		$adminDetails = session('admin_userid');
	}
	
	if (!is_array($adminDetails)) {
		$adminDetails = getRow('admins', array('first_name', 'last_name', 'username', 'authtype'), $adminDetails);
	}
	
	if ($adminDetails['authtype'] == 'super') {
		return $adminDetails['first_name']. ' '. $adminDetails['last_name']. ' ('. $adminDetails['username']. ', super)';
	} else {
		return $adminDetails['first_name']. ' '. $adminDetails['last_name']. ' ('. $adminDetails['username']. ')';
	}
}

function loadAdminPerms(&$perms, $adminId) {
	$result = getRows('action_admin_link', 'action_name', array('admin_id' => $adminId));
	while ($perm = sqlFetchRow($result)) {
		$perms[$perm[0]] = true;
	}
}

function saveAdminPerms(&$perms, $adminId) {
	require funIncPath(__FILE__, __FUNCTION__);
}

function deleteAdmin($admin_id, $undo = false) {
	$sql = "
		UPDATE ". DB_NAME_PREFIX. "admins SET
			status = '". ($undo? "active" : "deleted"). "',
			modified_date = NOW(),
			password_salt = '',
			reset_password_salt = '',
			password_needs_changing = 1
		WHERE authtype = 'local'
		  AND id = ". (int) $admin_id;
	sqlQuery($sql);
}


//When doing database updates, we need to log an admin.
//This means we may need to deal with old versions of the Admin Table
//This function will check to see how up to date the table is, and which columns we can access
function checkAdminTableColumnsExist() {
	if (!defined('CHECK_ADMIN_TABLE_COLUMNS_EXIST')) {
		foreach (array(
			3 => "reset_password, reset_password_salt, reset_password_time", //Added in revision #19495
			2 => "authtype", //Added in revision #14000
			1 => "password_salt, password_needs_changing", //Added in revision #2521
			0 => "1"
		) as $level => $columns) {
			if (@sqlSelect("
				SELECT ". $columns. "
				FROM ". DB_NAME_PREFIX. "admins
				LIMIT 1"
			)) {
				define('CHECK_ADMIN_TABLE_COLUMNS_EXIST', $level);
				break;
			}
		}
	}
	
	return CHECK_ADMIN_TABLE_COLUMNS_EXIST;
}


//Check to see if an admin exists and if the supplied password matches their password
// 0 means they didn't exist
// false means they exist but their password wasn't correct
// true means they exist and that password was right

//If the admin exists, then the details are returned even if the password was wrong.
//Also, if you're only after their details and not the password check, then you can
//set the password to false to avoid checking passwords.
function checkPasswordAdmin($adminUsernameOrEmail, &$details, $password, $checkViaEmail = false) {
	return require funIncPath(__FILE__, __FUNCTION__);
}

//Sets an admin's password, overwriting the current value
//By default changing someone's password removes the password_needs_changing flag, but
//you can override this and set it instead by passing a value of 1 to $requireChange
function setPasswordAdmin($adminId, $password, $requireChange = null, $isPasswordReset = false) {
	
	//Generate a random salt for this password. If someone gets hold of the encrypted value of
	//the password in the database, having a salt on it helps to stop dictonary attacks.
	$salt = randomString(8);
	
	if (checkAdminTableColumnsExist() < 1) {
		$sql = "
			UPDATE ". DB_NAME_PREFIX. "admins SET
				modified_date = NOW(),
				password = '". sqlEscape($password). "'
			WHERE id = ". (int) $adminId;
		
	} elseif ($isPasswordReset && checkAdminTableColumnsExist() >= 3) {
		$sql = "
			UPDATE ". DB_NAME_PREFIX. "admins SET
				modified_date = NOW(),";
		
		if ($requireChange !== null) {
			$sql .= "
				password_needs_changing = ". (int) $requireChange. ",";
		}
		
		$sql .= "
				reset_password = '". sqlEscape(hashPassword($salt, $password)). "',
				reset_password_salt = '". sqlEscape($salt). "',
				reset_password_time = NOW()
			WHERE id = ". (int) $adminId;
	
	} else {
		$sql = "
			UPDATE ". DB_NAME_PREFIX. "admins SET
				modified_date = NOW(),
				password_salt = '". sqlEscape($salt). "',";
		
		if ($requireChange !== null) {
			$sql .= "
				password_needs_changing = ". (int) $requireChange. ",";
		}
		
		if (checkAdminTableColumnsExist() >= 3) {
			$sql .= "
				reset_password = NULL,
				reset_password_salt = NULL,";
		}
		
		$sql .= "
				password = '". sqlEscape(hashPassword($salt, $password)). "'
			WHERE id = ". (int) $adminId;
	}
	
	$result = sqlQuery($sql);
}

function hashPassword($salt, $password) {
	if ($hash = hashPasswordSha2($salt, $password)) {
		return $hash;
	} else {
		return hashPasswordSha1($salt, $password);
	}
}

function hashPasswordSha2($salt, $password) {
	if ($hash = @hash('sha256', $salt. $password, true)) {
		return 'sha256'. base64_encode($hash);
	} else {
		return false;
	}
}

//Old sha1 function for passwords created before zenario 6.0.5. Or if sha2 is not enabled on a server.
function hashPasswordSha1($salt, $password) {
	$result = sqlSelect(
		"SELECT SQL_NO_CACHE SHA('". sqlEscape($salt. $password). "')");
	$row = sqlFetchRow($result);
	return $row[0];

}

function cancelPasswordChange($adminId) {
	
	if (checkAdminTableColumnsExist() >= 1) {
		$sql = "
			UPDATE ". DB_NAME_PREFIX. "admins SET
				password_needs_changing = 0
			WHERE id = ". (int) $adminId;
		$result = sqlQuery($sql);
	}
}

//Reset someone's password, returning the reset password
//A randomly generated string is used
function resetPasswordAdmin($adminId) {
	$newPassword = randomString();
	setPasswordAdmin($adminId, $newPassword, 1, true);
	return $newPassword;
}


function adminLogoutOnclick() {

	if (!setting('site_enabled') && checkRowExists('languages', array())) {
		$logoutMsg =
			adminPhrase('Are you sure you want to logout? Visitors will not be able to see your site as it is not enabled.');
	} else {
		$logoutMsg =
			adminPhrase('Are you sure you want to logout?');
	}
	
	$url = 'zenario/admin/welcome.php?task=logout';
	
	if (cms_core::$cID && cms_core::$cType) {
		$url .= '&cID='. cms_core::$cID. '&cType='. cms_core::$cType;
	}
	
	return 
	
	'onclick="'. 
		floatingBoxJS(
			$logoutMsg,
			'<input type="button" class="submit_selected" value="'. adminPhrase('Logout'). '" onclick="document.location.href = URLBasePath + \''. htmlspecialchars($url). '\';"/>',
			true, true).
	' return false;" href="'. htmlspecialchars(absCMSDirURL(). $url). '"';
}


// Templates/Layouts

function getSlotsOnTemplate($templateFamily, $templateFileBaseName) {
	return getRowsArray(
		'template_slot_link',
		'slot_name',
		array('family_name' => $templateFamily, 'file_base_name' => $templateFileBaseName),
		array('slot_name'));
}

function getDefaultTemplateId($cType) {
	if (!$layoutId = getRow('content_types', 'default_layout_id', array('content_type_id' => $cType))) {
		$layoutId = getRow('layouts', 'layout_id', array('content_type' => $cType));
	}
	
	return $layoutId;
}


function getTemplateFamilyDetails($familyName) {
	return getRow('template_families', array('family_name', 'skin_id', 'missing'), $familyName);
}

function saveTemplate($submission, &$layoutId, $sourceTemplateId = false) {
	
	$duplicating = (bool) $sourceTemplateId;
	
	$values = array();
	if (isset($submission['skin_id'])) {
		$values['skin_id'] = $submission['skin_id'];
	}
	if (isset($submission['css_class'])) {
		$values['css_class'] = $submission['css_class'];
	}
	if (isset($submission['name'])) {
		$values['name'] = $submission['name'];
	}
	
	if (isset($submission['content_type'])) {
		if (!$layoutId
		 || $duplicating
		 || (!checkRowExists('versions', array('layout_id' => $layoutId)) && !checkRowExists('content_types', array('default_layout_id' => $layoutId)))) {
			$values['content_type'] = $submission['content_type'];
		}
	}
	
	if (isset($submission['file_base_name'])) {
		$values['file_base_name'] = $submission['file_base_name'];
	}
	
	if ($layoutId && !$duplicating) {
		updateRow('layouts', $values, $layoutId);
	
	} else {
		$values['family_name'] = pullFromArray($submission, 'templateFamily', 'family_name');
		
		$layoutId = insertRow('layouts', $values);
	}
	
	if ($duplicating) {
		
		$details = getRow(
			'layouts',
			array('css_class', 'image_id', 'head_html', 'head_visitor_only', 'foot_html', 'foot_visitor_only'),
			$sourceTemplateId);
		
		updateRow('layouts', $details, $layoutId);
				
		$sql = "
			REPLACE INTO ". DB_NAME_PREFIX. "plugin_layout_link (
				module_id,
				instance_id,
				family_name,
				layout_id,
				slot_name
			) SELECT 
				module_id,
				instance_id,
				family_name,
				". (int) $layoutId.  ",
				slot_name
			FROM ". DB_NAME_PREFIX. "plugin_layout_link
			WHERE layout_id = ". (int) $sourceTemplateId;
		sqlQuery($sql);
	}
	
	if (($family = pullFromArray($submission, 'templateFamily', 'family_name'))
	 && (!getTemplateFamilyDetails($family))) {
		insertRow('template_families', array('family_name' => $family));
	}
}


function changeContentItemLayout($cID, $cType, $cVersion, $newLayoutId, $check = false, $warnOnChanges = false) {
	
	$oldLayoutId = contentItemTemplateId($cID, $cType, $cVersion);
	
	if (!$oldLayoutId || $oldLayoutId == $newLayoutId) {
		return false;
	}
	
	$slotChanges =
	$slotsLost = false;
	
	$layoutId =
	$layout =
	$slotContents =
	$pluginsOnLayout =
	$nonMatches =
	$matches =
	$toPlace = array('old' => array(), 'new' => array());
	
	$layoutId['old'] = $oldLayoutId;
	$layoutId['new'] = $newLayoutId;
	
	//Get information on the templates we're using
	foreach (array('old', 'new') as $oon) {
		//Loop through the slots on the templates, seeing what Modules are placed where
		$layout[$oon] = getTemplateDetails($layoutId[$oon]);
		getSlotContents($slotContents[$oon], $cID, $cType, $cVersion, $layoutId[$oon], $layout[$oon]['family_name'], $layout[$oon]['filename'], false, false, false, false, $runPlugins = false);
		
		foreach ($slotContents[$oon] as $slotName => $slot) {
			if (!empty($slot['content_id']) && !empty($slot['module_id'])) {
				
				//For the old Template, only count a slot if there is Content in there
				if ($oon == 'new'
				 || ((
					$sql = "
						SELECT 1
						FROM ". DB_NAME_PREFIX. "plugin_settings AS ps
						INNER JOIN ". DB_NAME_PREFIX. "plugin_setting_defs AS psd
						   ON psd.module_id = ". (int) $slot['module_id']. "
						  AND psd.name = ps.name
						  AND psd.default_value != ps.value
						WHERE ps.nest = 0
						  AND ps.value IS NOT NULL
						  AND ps.is_content IN('version_controlled_setting', 'version_controlled_content')
						  AND ps.instance_id = ". (int) $slot['instance_id']. "
						LIMIT 1")
				  && ($result = sqlQuery($sql))
				  && (sqlFetchRow($result)))
				 || ((
					$sql = "
						SELECT 1
						FROM ". DB_NAME_PREFIX. "plugin_settings AS ps
						INNER JOIN ". DB_NAME_PREFIX. "nested_plugins AS np
						   ON np.instance_id = ps.instance_id
						  AND np.id = ps.nest
						  AND np.is_tab = 1
						INNER JOIN ". DB_NAME_PREFIX. "plugin_setting_defs AS psd
						   ON psd.module_id = np.module_id
						  AND psd.name = ps.name
						  AND psd.default_value != ps.value
						WHERE ps.nest != 0
						  AND ps.value IS NOT NULL
						  AND ps.is_content IN('version_controlled_setting', 'version_controlled_content')
						  AND ps.instance_id = ". (int) $slot['instance_id']. "
						LIMIT 1")
				  && ($result = sqlQuery($sql))
				  && (sqlFetchRow($result)))) {
				  	$pluginsOnLayout[$oon][$slotName] =
					$nonMatches[$oon][$slotName] = $slot['class_name'];
				}
			}
		}
	}
	
	
	//Loop through the Modules placed in the previous Template
	foreach (array_keys($nonMatches['old']) as $slotname) {
		
		//Does this slot match with what's in the new Template?
		if (isset($nonMatches['new'][$slotname]) && $nonMatches['new'][$slotname] == $nonMatches['old'][$slotname]) {
			
			//If so, note down this match, and remove all other mention of it so we don't touch anything
			$matches['old'][$slotname] = $slotname;
			$matches['new'][$slotname] = $slotname;
			unset($nonMatches['old'][$slotname]);
			unset($nonMatches['new'][$slotname]);
		}
	}
	
	$slotChanges = !empty($nonMatches['old']);
	if ($slotChanges) {
		//Try to handle the case where the same number of Plugins exist, but they are
		//just in different places, by adding them into slots as we see them
		$changes = true;
		while ($changes) {
			$changes = false;
			foreach ($nonMatches['old'] as $oSlotName => $oClassName) {
				foreach ($nonMatches['new'] as $nSlotName => $nClassName) {
					if ($oClassName == $nClassName) {
						$matches['old'][$oSlotName] = $nSlotName;
						$matches['new'][$nSlotName] = $oSlotName;
					
						unset($nonMatches['old'][$oSlotName]);
						unset($nonMatches['new'][$nSlotName]);
					
						$changes = true;
						continue 3;
					}
				}
			}
		}
	}
	$slotsLost = !empty($nonMatches['old']);
	
	if ($check) {
		if ($slotsLost || ($slotChanges && $warnOnChanges)) {
			$html =
				'<table class="zenario_changeLayoutDestinations"><tr>
					<th>'. adminPhrase('Old layout'). '</th>
					<th>'. adminPhrase('Contents'). '</th>
					<th>'. adminPhrase('New layout'). '</th>
				</tr>';
			
			foreach ($pluginsOnLayout['old'] as $oSlotName => $className) {
				$html .=
					'<tr>
						<td>'. htmlspecialchars($oSlotName). '</td>
						<td>'. htmlspecialchars(getModuleDisplayNameByClassName($className)). '</td>';
				
				if (empty($matches['old'][$oSlotName])) {
					$html .= '<td class="zenario_changeLayoutDestinationMissing">'. adminPhrase('data will NOT be copied'). '</td>';
				} else {
					$html .= '<td>'. htmlspecialchars($matches['old'][$oSlotName]). '</td>';
				}
				
				$html .= '</tr>';
			}
			
			$html .= '<table>';
			return $html;
		
		} else {
			return false;
		}
		
	} else {
		
		//Change the Layout
		$version = array('layout_id' => $newLayoutId);
		updateVersion($cID, $cType, $cVersion, $version, true);
		
		//Loop through all of the matched slots
		foreach ($matches['old'] as $oSlotName => $nSlotName) {
			//Move Plugins/data between slots as needed
			if ($oSlotName != $nSlotName) {
				$oldSlot =
					array(
					'module_id' => $slotContents['old'][$oSlotName]['module_id'],
					'content_id' => $cID,
					'content_type' => $cType,
					'content_version' => $cVersion,
					'slot_name' => $oSlotName);
				
				$newSlot = $oldSlot;
				$newSlot['slot_name'] = $nSlotName;
				
				//Delete any existing data that would cause a primary key collision
				//(this shouldn't happen, but you might get bad/left over data).
				if ($badData = getRow('plugin_instances', 'id', $newSlot)) {
					deletePluginInstance($badData);
				}
				
				//Update the Instance from the old slot to the new slot
				updateRow('plugin_instances', $newSlot, $oldSlot);
			}
		}
	}
}




/* Get Module and Style information in Skins */

//$skin['family_name']. '/skins/'. $skin['name']
function skinDescriptionFilePath($familyName, $name) {
	foreach (array(
		'description.yaml',
		'description.yml',
		'description.xml',
		'skin.xml'
	) as $file) {
		if (file_exists(CMS_ROOT. (
			$path = getSkinPath($familyName, $name). $file
		))) {
			return $path;
		}
	}
	return false;
}

//Load a Skin's description, looking at any Skins it extends if needed
//Note: works more or less the same was as the loadModuleDescription() function
function loadSkinDescription($skin, &$tags) {
	
	$tags = array();
	if (!is_array($skin)) {
		$skin = getSkinFromId($skin);
	}
	
	if ($skin) {
		$limit = 20;
		$skinsWeHaveRead = array();
		$familyName = $skin['family_name'];
		$name = $skin['name'];
	
		while (--$limit
		 && empty($skinsWeHaveRead[$name])
		 && ($skinsWeHaveRead[$name] = true)
		 && ($path = skinDescriptionFilePath($familyName, $name))) {
		
			if (!$tagsToParse = zenarioReadTUIXFile(CMS_ROOT. $path)) {
				echo adminPhrase('[[path]] appears to be in the wrong format or invalid.', array('path' => CMS_ROOT. $path));
				return false;
			} else {
				if (!empty($tagsToParse['extension_of_skin'])) {
					$name = $tagsToParse['extension_of_skin'];
				}
			
				zenarioParseTUIX($tags, $tagsToParse, 'skin_description');
				unset($tagsToParse);
			}
		}
	}
	
	if (!empty($tags['skin_description'])) {
		$tags = $tags['skin_description'];
		
		//Convert the old editor_styles format from 6.1.0 and earlier to the new style_formats format
		if (!empty($tags['editor_styles']) && is_array($tags['editor_styles'])) {
			if (empty($tags['style_formats'])) {
				$tags['style_formats'] = array();
			}
			foreach ($tags['editor_styles'] as $css_class_name => $displayName) {
				$tags['style_formats'][] = array(
					'title' => $displayName,
					'selector' => '*',
					'classes' => $css_class_name
				);
			}
		}
		unset($tags['editor_styles']);
		
		//Don't show an empty format list!
		if (empty($tags['style_formats'])) {
			unset($tags['style_formats']);
		}
		
		//Convert the old pickable_css_class_names format to the new format
		if (!empty($tags['pickable_css_class_names']) && is_array($tags['pickable_css_class_names'])) {
			foreach ($tags['pickable_css_class_names'] as $tagName => $details) {
				$tagName = explode(' ', $tagName);
				$tagName = $tagName[0];
				if (in($tagName, 'content_item', 'layout', 'plugin', 'menu_node')) {
					if (empty($tags['pickable_css_class_names'][$tagName. 's'])) {
						$tags['pickable_css_class_names'][$tagName. 's'] = array();
					}
					$tags['pickable_css_class_names'][$tagName. 's'][] = $details;
					unset($tags['pickable_css_class_names'][$tagName]);
				}
			}
		}
		
		return $tags;
	} else {
		return array();
	}
}


//Attempt to load a list of CSS Class Names from the description.yaml in the current Skin to add choices for plugins
function getSkinCSSClassNames($skin, $type, $moduleClassName = '') {
	$values = array();
	
	$desc = false;
	if (loadSkinDescription($skin, $desc)) {
		
		if (!empty($desc['pickable_css_class_names'][$type. 's'])
		 && is_array($desc['pickable_css_class_names'][$type. 's'])) {
			foreach ($desc['pickable_css_class_names'][$type. 's'] as $swatch) {
				$module_css_class_name = arrayKey($swatch, 'module_css_class_name');
				$css_class_name = arrayKey($swatch, 'css_class_name');
				$label = arrayKey($swatch, 'label');
				
				if ($type != 'plugin' || $moduleClassName == $module_css_class_name) {
					if ($css_class_name) {
						$values[$css_class_name] = $css_class_name;
						//$values[$css_class_name] =
						//	$css_class_name.
						//	($label? ' ('. (string) $label. ')' : '');
					}
				}
			}
		}
	}
	
	asort($values);
	
	return $values;
}


//Get a list of all skins in the filesystem, but not in the database
function getAllNewSkins($templateFamily) {
	clearstatcache();
	$newSkins = array();
	
	foreach (scandir($dir = CMS_ROOT. zenarioTemplatePath($templateFamily). 'skins/') as $file) {
		if ($file != '.' && $file != '..' && $file != '.svn' && file_exists($dir. $file. '/skin.xml')) {
			if (!checkRowExists('skins', array('family_name' => $templateFamily, 'name' => $file))) {
				$newSkins[] = $file;
			}
		}
	}
	
	return $newSkins;
}

function checkSiteHasMultipleTemplateFamilies() {
	return ($result = getRows('template_families', array('family_name'), array()))
		&& (sqlFetchRow($result))
		&& (sqlFetchRow($result));
}

function checkSkinInFS($template_family, $skinName) {
	return $template_family && $skinName && is_dir(CMS_ROOT. getSkinPath($template_family, $skinName));
}

function checkTemplateFileInFS($template_family, $fileBaseName) {
	return $template_family && $fileBaseName && is_file(CMS_ROOT. zenarioTemplatePath($template_family, $fileBaseName));
}

function checkTemplateFamilyInFS($template_family) {
	return $template_family && is_dir(CMS_ROOT. zenarioTemplatePath($template_family));
}

//Include a checksum calculated from the modificaiton dates of any css and js files in the Core or the Skin
//Not that this is only calculated for admins, and cached for visitors
function getCSSJSCodeHash($updateDB = true, $forceScan = false, $justScan = false) {
	$mTimes = '';
	$dirs = array(CMS_ROOT. 'zenario/js/', CMS_ROOT. 'zenario/styles/');
	$lessFiles = array();
	
	if (file_exists($dir = CMS_ROOT. zenarioTemplatePath('grid_templates'))) {
		$dirs[] = $dir;
	}
	
	//Loop through the zenario_custom/templates directory, looking for Skins
	if (is_dir($dir = CMS_ROOT. zenarioTemplatePath())) {
		foreach (scandir($dir) as $family) {
			if (substr($family, 0, 1) != '.' && is_dir($dir2 = $dir. $family)) {
				$dirs[] = $dir2;
				
				if (is_dir($dir3 = $dir2. '/skins/')) {
					foreach (scandir($dir3) as $skin) {
						if (substr($skin, 0, 1) != '.' && is_dir($dir4 = $dir3. $skin)) {
							//For each Skin, add it to the list of directories
							$dirs[] = $dir4;
							
							//Recurse through its subdirectories, adding each subdirectory as well
							//If we see any .less files, also keep a record of them
								//Note: this code is currently commented out!
							foreach(
								new RecursiveIteratorIterator(
									new RecursiveDirectoryIterator($dir4),
									RecursiveIteratorIterator::SELF_FIRST
								)
							as $path => $SplFileInfo) {
								if (strpos($path, '/.') === false
								 && strpos($path, '\\.') === false) {
									if ($SplFileInfo->isDir()) {
										$dirs[] = $path;
									//} else if (strtolower(substr($path, -5) == '.less')) {
										//$lessFiles[] = $path;
									}
								}
							}
						}
					}
				}
			}
		}
	}
	
	
	//Also check for the js/ and adminstyles/ directories in Modules, and Module Swatches
	foreach (array(
		'zenario/modules/',
		'zenario_extra_modules/',
		'zenario_custom/modules/',
		'zenario_custom/frameworks/'
	) as $mDir) {
		if (is_dir($dir = CMS_ROOT. $mDir)) {
			foreach (scandir($dir) as $module) {
				if (substr($module, 0, 1) != '.') {
					if ($mDir != 'zenario_custom/frameworks/') {
						foreach (array('/adminstyles/', '/js/', '/js/templates/') as $dir2) {
							if (is_dir($dir3 = $dir. $module. $dir2)) {
								$dirs[] = $dir3;
							}
						}
					}
					
					if (is_dir($dir2 = $dir. $module. '/frameworks/')) {
						foreach (scandir($dir2) as $framework) {
							if (substr($framework, 0, 1) != '.'
							 && (file_exists($dir3 = $dir2. $framework. '/framework.html')
							  || file_exists($dir3 = $dir2. $framework. '/framework.twig.html'))) {
								$mTimes .= filemtime($dir3);
							}
						}
					}
				}
			}
		}
	}
	
	foreach ($dirs as $dir) {
		chdir($dir);
		$mTimes .= $dir. '-'. print_r(array_map('filemtime', scandir('.')), true);
	}
	chdir(CMS_ROOT);
	
	
	$css_js_version = hash64($mTimes, 15). substr((string) setting('site_id'), 0, 2);
	
	if ($justScan) {
		return $css_js_version;
	
	} elseif ($forceScan || ($css_js_version != setting('css_js_version'))) {
		
		if ($updateDB) {
			//Clear the page cache completely if a Skin or a Template Family has changed
			$sql = '';
			$ids = $values = array();
			cms_core::reviewDatabaseQueryForChanges($sql, $ids, $values, $table = 'template_family');
			
			
			//Mark all current Template Families/Template Files/Skins as missing
			updateRow('template_families', array('missing' => 1), array());
			updateRow('template_files', array('missing' => 1), array());
			updateRow('skins', array('missing' => 1), array());
			
			//Ensure that there is always a Template File and Family in the database to cover
			//any Layouts and Skins that are also in the database, even if they would be missing.
			foreach (getDistinctRowsArray('layouts', array('family_name')) as $row) {
				setRow('template_families', array('missing' => 1), $row);
			}
			foreach (getDistinctRowsArray('layouts', array('family_name', 'file_base_name')) as $row) {
				setRow('template_files', array('missing' => 1), $row);
			}
			foreach (getDistinctRowsArray('skins', array('family_name')) as $row) {
				setRow('template_families', array('missing' => 1), $row);
			}
			
			//Check that all of the template-families, files and skins in the filesystem are
			//registered in the database, and add any newly found files/directories.
			if (is_dir($dir = CMS_ROOT. zenarioTemplatePath())) {
				foreach (scandir($dir) as $family) {
					if ($family && substr($family, 0, 1) != '.' && is_dir($dir2 = $dir. $family)) {
						setRow('template_families', array('missing' => 0), array('family_name' => $family));
						
						foreach (scandir($dir2) as $templateFile) {
							if (substr($templateFile, 0, 1) != '.'
							 && substr($templateFile, -8) == '.tpl.php'
							 && is_file($dir2. '/'. $templateFile)) {
								setRow(
									'template_files',
									array('missing' => 0),
									array(
										'family_name' => $family,
										'file_base_name' => substr($templateFile, 0, -8)));
							}
						}
						
						if (is_dir($dir3 = $dir2. '/skins/')) {
							foreach (scandir($dir3) as $skin) {
								if (substr($skin, 0, 1) != '.' && is_dir($dir4 = $dir3. $skin)) {
									$row = array('family_name' => $family, 'name' => $skin);
									setRow('skins', array('missing' => 0), $row);
									
									//Also update the Skin's description
									$desc = false;
									if (loadSkinDescription($row, $desc)) {					
										updateRow('skins',
											array(
												'display_name' => ifNull(arrayKey($desc, 'display_name'), $row['name']),
												'type' => ifNull(arrayKey($desc, 'type'), 'usable'),
												'extension_of_skin' => arrayKey($desc, 'extension_of_skin'),
												'css_class' => arrayKey($desc, 'css_class')),
											$row);
									}
								}
							}
						}
					}
				}
			}
			
			//Delete anything that is missing *and* not used
			foreach(getRowsArray('skins', 'id', array('missing' => 1)) as $skinId) {
				if (!checkSkinInUse($skinId)) {
					deleteSkinAndClearForeignKeys($skinId);
				}
			}
			foreach(getRowsArray('template_files', array('family_name', 'file_base_name'), array('missing' => 1)) as $tf) {
				if (!checkRowExists('layouts', $tf)) {
					deleteRow('template_files', $tf);
				}
			}
			foreach(getRowsArray('template_families', 'family_name', array('missing' => 1)) as $familyName) {
				if (!checkTemplateFamilyInUse($familyName)) {
					deleteRow('template_families', $familyName);
				}
			}
		}
		
		setSetting('css_js_version', $css_js_version, $updateDB);
	}
}


function checkTemplateFamilyInUse($template_family) {
	return
		checkRowExists('layouts', array('family_name' => $template_family))
	 || checkRowExists('skins', array('family_name' => $template_family))
	 || checkRowExists('template_files', array('family_name' => $template_family));
}

function checkSkinInUse($skinId) {
	return
		checkRowExists('layouts', array('skin_id' => $skinId))
	 || checkRowExists('template_families', array('skin_id' => $skinId));
}

function checkLayoutInUse($layoutId) {
	return
		checkRowExists('versions', array('layout_id' => $layoutId));
}

function generateLayoutFileBaseName($layoutName) {
	return substr(str_replace('~20', ' ', encodeItemIdForStorekeeper($layoutName, '')), 0, 255);
}

//Delete a Layout from the system
function deleteLayout($layout, $deleteFromDB) {
	
	if (!is_array($layout)) {
		$layout = getRow('layouts', array('layout_id', 'family_name', 'file_base_name'), $layout);
	}
	
	if ($deleteFromDB && $layout && !empty($layout['layout_id'])) {
		//Delete the layout from the database
		sendSignal('eventTemplateDeleted',array('layoutId' => $layout['layout_id']));
	
		deleteRow('layouts', array('layout_id' => $layout['layout_id']));
		deleteRow('plugin_layout_link', array('layout_id' => $layout['layout_id']));
		deleteUnusedImagesByUsage('template');
	}
	
	//Check whether anything else uses this Template File
	if (!checkRowExists(
		'layouts',
		array(
			'family_name' => $layout['family_name'],
			'file_base_name' => $layout['file_base_name'])
	)) {
		//If not, attempt to delete the Layout's files
		foreach (array(
			CMS_ROOT. zenarioTemplatePath($layout['family_name'], $layout['file_base_name']),
			CMS_ROOT. zenarioTemplatePath($layout['family_name'], $layout['file_base_name'], true)
		) as $filePath) {
			if (file_exists($filePath)
			 && is_writable($filePath)) {
				@unlink($filePath);
			}
		}
	}
}

//Given a Layout that has just been renamed or duplicated from another Layout, try to copy its Template Files
function copyLayoutFiles($layout, $newName = false, $newFamilyName = false) {
	
	if (!is_array($layout)) {
		$layout = getRow('layouts', array('family_name', 'file_base_name'), $layout);
	}
	
	if (!$newName) {
		$newName = encodeItemIdForStorekeeper($layout['name'], '');
	}
	if (!$newFamilyName) {
		$newFamilyName = $layout['family_name'];
	}
	
	$copies = array(
		CMS_ROOT. zenarioTemplatePath($layout['family_name'], $layout['file_base_name'])
	  => 
		CMS_ROOT. zenarioTemplatePath($newFamilyName, $newName),
		
		CMS_ROOT. zenarioTemplatePath($layout['family_name'], $layout['file_base_name'], true)
	  => 
		CMS_ROOT. zenarioTemplatePath($newFamilyName, $newName, true)
	);
	
	foreach ($copies as $filePath => $newFilePath) {
		if (file_exists($filePath)) {
			if (!is_readable($filePath)
			 || file_exists($newFilePath)
			 || !is_writable(dirname($newFilePath))) {
				return false;
			}
		}
	}
	
	foreach ($copies as $filePath => $newFilePath) {
		if (file_exists($filePath)) {
			copy($filePath, $newFilePath);
		}
	}
	
	return true;
}

function deleteSkinAndClearForeignKeys($skinId) {
	updateRow('layouts', array('skin_id' => 0), array('skin_id' => $skinId));
	updateRow('template_families', array('skin_id' => 0), array('skin_id' => $skinId));
	deleteRow('skins', $skinId);
}



//Functions for languages



function saveLanguage($submission, $lang) {
	
	if (($valid = validateLanguage($submission)) && ($valid['valid'])) {
		//Build up an insert/update statement using the values we have for the fields
		$sql = "";
		foreach(getFields(DB_NAME_PREFIX, 'languages') as $field => $details) {
			if (isset($submission[$field])) {
				addFieldToSQL($sql, DB_NAME_PREFIX. 'languages', $field, $submission, true, $details);
			}
		}
	
		$sql .= "
			WHERE id = '". sqlEscape($lang). "'";
		
		$result = sqlQuery($sql);
	}
}



//Check a VLP to see if the three bare-minimum required phrases are there
//If they are, then the VLP can be added as a language
function checkIfLanguageCanBeAdded($languageId) {
	$sql = "
		SELECT COUNT(*)
		FROM ". DB_NAME_PREFIX. "visitor_phrases
		WHERE language_id = '". sqlEscape($languageId). "'
		  AND code IN ('__LANGUAGE_ENGLISH_NAME__', '__LANGUAGE_LOCAL_NAME__', '__LANGUAGE_FLAG_FILENAME__')
		  AND module_class_name = 'zenario_common_features'";

	$result = sqlQuery($sql);
	list($count) = sqlFetchRow($result);
	return $count == 3;
}

function aliasURLIsValid($url) {
	return preg_match("/^[0-9A-Za-z\.\-]*\.[0-9A-Za-z\.\-\/]*$/",$url);
}

function getLanguageSelectListOptions(&$field) {
	$ord = 0;
	
	if (!isset($field['values']) || !is_array($field['values'])) {
		$field['values'] = array();
	}
	
	foreach (getLanguages() as $lang) {
		$field['values'][$lang['id']] = array('ord' => ++$ord, 'label' => $lang['english_name']. ' ('. $lang['id']. ')');
	}
}




//Function gets a list of all drafts being worked upon by an author
//Returns both a text string in the for of <option>
//Pass it the value of the current administrator, and the ID of the page
//they're currently on, and then it will show this one "selected" in the pull-down

//Functions for Menus

function getMenuItemStorekeeperDeepLink($menuId, $langId = false, $sectionId = false, $menuIdIsParent = false) {
	if ($langId === false) {
		$langId = ifNull(session('user_lang'), setting('default_language'));
	
	} elseif ($langId === true) {
		$langId = setting('default_language');
	}
	
	$menuDetails = false;
	if ($menuId) {
		$menuDetails = getMenuNodeDetails($menuId);
	}
	
	if (!$sectionId && $menuDetails) {
		$sectionId = $menuDetails['section_id'];
	}
	
	//Build up a link to the current parent in Storekeeper
	if ($langId == setting('default_language')) {
		$path = 'zenario__menu/nav/default_language/panel/item//'. $langId. '//item//'. $sectionId. '//';
				 
	} else {
		$path = 'zenario__menu/nav/by_language/panel/item//'. $langId. '//item//'. $sectionId. '//';
	}
	
	if ($menuId) {
		$path .= $menuId;
	}
	
	//Old logic from before we had hierarchy view
	/*
	if ($menuDetails) {
		$limit = 20;
		$ancestors = array($menuId);
		if ($ancestorId = $menuDetails['parent_id']) {
			do {
				$ancestors[] = $ancestorId;
			} while (--$limit && $ancestorId = getMenuParent($ancestorId));
		}
		
		for ($i = count($ancestors) - 1; $i >= 0; --$i) {
			$path .= '//'. ($menuIdIsParent || $i? 'item//' : ''). $ancestors[$i];
		}
	}
	*/
	
	return $path;
}

function getMenuPath($menuId, $langId = false, $separator = ' -> ', $useOrdinal = false) {
	if ($langId === false) {
		$langId = ifNull(session('user_lang'), setting('default_language'));
	
	} elseif ($langId === true) {
		$langId = setting('default_language');
	}
	
	$sql = "
		SELECT
			GROUP_CONCAT(";
			
	if ($useOrdinal) {
		if (!defined('MENU_MAX_ORDINAL')) {
			$sql2 = "
				SELECT MAX(ordinal)
				FROM ". DB_NAME_PREFIX. "menu_nodes";
			$result = sqlQuery($sql2);
			$row = sqlFetchRow($result);
			define('MENU_MAX_ORDINAL', ceil(log($row[0] + 1, 10)));
		}
		
		$sql .= "
				LPAD(mi.ordinal, ". (int) MENU_MAX_ORDINAL. ", '0')";
	
	} else {
		$sql .= "
				(
					SELECT CONCAT(mt.name, IF(mt.language_id = '". sqlEscape($langId). "', '', CONCAT(' (', mt.language_id, ')')))
					FROM ". DB_NAME_PREFIX. "menu_text AS mt
					WHERE mt.menu_id = mi.id
					ORDER BY
						mt.language_id = '". sqlEscape($langId). "' DESC,
						mt.language_id = '". sqlEscape(setting('default_language')). "' DESC
					LIMIT 1
				)";
	}
	
	$sql .= "
				ORDER BY mh.separation DESC SEPARATOR '". sqlEscape($separator). "'
			)
		FROM ". DB_NAME_PREFIX. "menu_hierarchy AS mh
		INNER JOIN ". DB_NAME_PREFIX. "menu_nodes AS mi
		   ON mi.id = mh.ancestor_id
		WHERE mh.child_id = ". (int) $menuId;
	
	if (($result = sqlQuery($sql)) && ($row = sqlFetchRow($result))) {
		return $row[0];
	} else {
		return '';
	}
}


function getMenuItemLevel($mID) {
	$sql = "
		SELECT IFNULL(MAX(separation), 0) + 1
		FROM ". DB_NAME_PREFIX. "menu_hierarchy
		WHERE child_id = ". (int) $mID;
	
	if (($result = sqlQuery($sql)) && ($row = sqlFetchRow($result))) {
		return $row[0];
	} else {
		return 0;
	}
}

function saveMenuDetails($submission, $menuId = false, $resyncIfNeeded = true, $skipSectionChecks = false) {
	return require funIncPath(__FILE__, __FUNCTION__);
}

function saveMenuText($menuId, $languageId, $submission, $neverCreate = false) {

	$textExists = getRow('menu_text', array('name'), array('menu_id' => $menuId, 'language_id' => $languageId));
	
	//Update or create a new entry, depending on whether one already exists
	if (!$textExists) {
		if ($neverCreate) {
			return;
		}
		
		$submission['menu_id'] = $menuId;
		$submission['language_id'] = $languageId;
		
		//For new translations of an existing Menu Node with an external URL, default the URL to the
		//URL of an existing translation if it was not provided.
		if (!isset($submission['ext_url'])
		 && (($url = getRow('menu_text', 'ext_url', array('menu_id' => $menuId, 'language_id' => setting('default_language'))))
		  || ($url = getRow('menu_text', 'ext_url', array('menu_id' => $menuId))))) {
			$submission['ext_url'] = $url;
		}
	
	} else {
		unset($submission['menu_id']);
		unset($submission['language_id']);
	}
	
	$sql = "";
	$hadUsefulField = false;
	foreach(getFields(DB_NAME_PREFIX, 'menu_text') as $field => $details) {
		if (isset($submission[$field])) {
			addFieldToSQL($sql, DB_NAME_PREFIX. 'menu_text', $field, $submission, $textExists, $details);
			
			if ($field != 'language_id' && $field != 'menu_id') {
				$hadUsefulField = true;
			}
		}
	}
	
	if ($sql && $hadUsefulField) {
		if ($textExists) {
			$sql .= "
				WHERE language_id = '". sqlEscape($languageId). "'
				  AND menu_id = ". (int) $menuId;	
		}
		
		sqlQuery($sql);
		
		if (isset($submission['name'])) {
			if (!$textExists) {
				sendSignal('eventMenuNodeTextAdded', array('menuId' => $menuId, 'languageId' => $languageId, 'newText' => $submission['name']));
			
			} elseif ($submission['name'] != $textExists['name']) {
				sendSignal('eventMenuNodeTextUpdated', array('menuId' => $menuId, 'languageId' => $languageId, 'newText' => $submission['name'], 'oldText' => $textExists['name']));
			}
		}
	}
}

function removeMenuText($menuId, $languageId) {
	deleteRow('menu_text', array('language_id' => $languageId, 'menu_id' => $menuId));
}

function moveMenuNode($ids, $newSectionId, $newParentId, $newNeighbourId, $languageId = false) {
	require funIncPath(__FILE__, __FUNCTION__);
}


//Delete a Menu Item and all of its children
function deleteMenuNode($id, $firstCall = true) {
	if (!$id) {
		return;
	}
	
	$result = getRows('menu_nodes', 'id', array('parent_id' => $id));
	while ($row = sqlFetchAssoc($result)) {
		deleteMenuNode($row['id'], false);
	}
	
	$content = getContentFromMenu($id);
	
	deleteRow('menu_nodes', array('id' => $id));
	deleteRow('menu_text', array('menu_id' => $id));
	deleteRow('menu_positions', array('menu_id' => $id));
	deleteRow('menu_hierarchy', array('child_id' => $id));
	deleteRow('menu_hierarchy', array('ancestor_id' => $id));
	
	//If this Content Item had any other Menu Nodes, make sure that one of the remaining is a primary
	if ($content) {
		ensureContentItemHasPrimaryMenuItem($content['equiv_id'], $content['content_type']);
	}
	
	if ($firstCall) {
		$sql = "
			DELETE f.*
			FROM ". DB_NAME_PREFIX. "files AS f
			LEFT JOIN ". DB_NAME_PREFIX. "menu_nodes AS mni
			   ON mni.image_id = f.id
			LEFT JOIN ". DB_NAME_PREFIX. "menu_nodes AS mnr
			   ON mnr.rollover_image_id = f.id
			WHERE mni.image_id IS NULL
			  AND mnr.rollover_image_id IS NULL
			  AND f.location = 'db'
			  AND f.`usage` = 'menu'";
	
		sqlUpdate($sql);
	}
}

function ensureContentItemHasPrimaryMenuItem($equivId, $cType) {
	$sql = "
		UPDATE ". DB_NAME_PREFIX. "menu_nodes SET
			redundancy = 'primary'
		WHERE equiv_id = ". (int) $equivId. "
		  AND content_type = '". sqlEscape($cType). "'
		ORDER BY redundancy = 'primary' DESC
		LIMIT 1";
	sqlUpdate($sql);
}

function addNewMenuItemToMenuHierarchy($sectionId, $menuId, $parentId = false) {

	$sql = "
		INSERT INTO ". DB_NAME_PREFIX. "menu_hierarchy (
			section_id, child_id, ancestor_id, separation
		) VALUES (
			". (int) $sectionId. ", ". (int) $menuId. ", ". (int) $menuId. ", 0
		)";
	sqlUpdate($sql);
	
	$sql = "
		INSERT INTO ". DB_NAME_PREFIX. "menu_positions (
			parent_tag,
			tag,
			section_id, menu_id, is_dummy_child
		) VALUES (
			'". (int) $sectionId. "_". (int) $parentId. "_0',
			'". (int) $sectionId. "_". (int) $menuId. "_0',
			". (int) $sectionId. ", ". (int) $menuId. ", 0
		)";
	sqlUpdate($sql);
	
	$sql = "
		INSERT INTO ". DB_NAME_PREFIX. "menu_positions (
			parent_tag,
			tag,
			section_id, menu_id, is_dummy_child
		) VALUES (
			'". (int) $sectionId. "_". (int) $menuId. "_0',
			'". (int) $sectionId. "_". (int) $menuId. "_1',
			". (int) $sectionId. ", ". (int) $menuId. ", 1
		)";
	sqlUpdate($sql);
	
	if ($parentId) {
		$sql = "
			INSERT INTO ". DB_NAME_PREFIX. "menu_hierarchy (
				section_id, child_id, ancestor_id, separation
			) SELECT
				section_id, ". (int) $menuId. ", ancestor_id, separation + 1
			FROM ". DB_NAME_PREFIX. "menu_hierarchy
			WHERE child_id = ". (int) $parentId;
		sqlUpdate($sql);
	}
}


function recalcMenuHierarchy($sectionId) {
	deleteRow('menu_hierarchy', array('section_id' => $sectionId));
	deleteRow('menu_positions', array('section_id' => $sectionId, 'menu_id' => array('!' => 0)));
	recalcMenuPositionsTopLevel();
	
	$sql = "
		INSERT INTO ". DB_NAME_PREFIX. "menu_hierarchy(
			section_id, child_id, ancestor_id, separation
		) SELECT
			section_id, id, id, 0
		FROM ". DB_NAME_PREFIX. "menu_nodes
		WHERE section_id = ". (int) $sectionId;
	sqlUpdate($sql);
	
	$sql = "
		INSERT INTO ". DB_NAME_PREFIX. "menu_positions (
			parent_tag,
			tag,
			section_id, menu_id, is_dummy_child
		) SELECT
			CONCAT(section_id, '_', parent_id, '_', 0),
			CONCAT(section_id, '_', id, '_', 0),
			section_id, id, 0
		FROM ". DB_NAME_PREFIX. "menu_nodes
		WHERE section_id = ". (int) $sectionId;
	sqlUpdate($sql);
	
	$sql = "
		INSERT INTO ". DB_NAME_PREFIX. "menu_positions (
			parent_tag,
			tag,
			section_id, menu_id, is_dummy_child
		) SELECT
			CONCAT(section_id, '_', id, '_', 0),
			CONCAT(section_id, '_', id, '_', 1),
			section_id, id, 1
		FROM ". DB_NAME_PREFIX. "menu_nodes
		WHERE section_id = ". (int) $sectionId;
	sqlUpdate($sql);
	
	$ancestors = array();
	recalcMenuHierarchyR($sectionId, 0, $ancestors, 0);
}

function recalcMenuHierarchyR($sectionId, $parentId, &$ancestors, $separation) {
	
	if ($parentId) {
		$sql = "
			SELECT id, section_id
			FROM ". DB_NAME_PREFIX. "menu_nodes
			WHERE parent_id = ". (int) $parentId;
	
	} else {
		$sql = "
			SELECT id, section_id
			FROM ". DB_NAME_PREFIX. "menu_nodes
			WHERE section_id = ". (int) $sectionId. "
			  AND parent_id = 0";
	}
	$result = sqlSelect($sql);
	
	++$separation;
	while ($row = sqlFetchAssoc($result)) {
		
		if ($row['section_id'] != $sectionId) {
			updateRow('menu_nodes', array('section_id' => $sectionId), $row['id']);
		}
		
		foreach ($ancestors as $ancestor => $separationOffset) {
			$sql = "
				INSERT INTO ". DB_NAME_PREFIX. "menu_hierarchy SET
					section_id = ". (int) $sectionId. ",
					child_id = ". (int) $row['id']. ",
					ancestor_id = ". (int) $ancestor. ",
					separation = ". (int) ($separation - $separationOffset);
			sqlUpdate($sql);
		}
		
		$ancestors[$row['id']] = $separation;
		recalcMenuHierarchyR($sectionId, $row['id'], $ancestors, $separation);
		unset($ancestors[$row['id']]);
	}
}


function recalcMenuPositionsTopLevel() {
	
	//First, do some tidying up.
	//Delete any positions from sections that do not exist.
	$sql = "
		DELETE mp.*
		FROM ". DB_NAME_PREFIX. "menu_positions AS mp
		LEFT JOIN ". DB_NAME_PREFIX. "menu_sections AS ms
		   ON ms.id = mp.section_id
		WHERE ms.id IS NULL";
	sqlUpdate($sql);
	
	//Delete all of the top-level entries
	deleteRow('menu_positions', array('menu_id' => 0));
	
	//Insert new entries
	$sql = "
		INSERT INTO ". DB_NAME_PREFIX. "menu_positions (
			parent_tag,
			tag,
			section_id, menu_id, is_dummy_child
		) SELECT
			'0',
			CONCAT(id, '_', 0, '_', 0),
			id, 0, 0
		FROM ". DB_NAME_PREFIX. "menu_sections";
	sqlUpdate($sql);
	
	//Insert new entries
	$sql = "
		INSERT INTO ". DB_NAME_PREFIX. "menu_positions (
			parent_tag,
			tag,
			section_id, menu_id, is_dummy_child
		) SELECT
			CONCAT(id, '_', 0, '_', 0),
			CONCAT(id, '_', 0, '_', 1),
			id, 0, 1
		FROM ". DB_NAME_PREFIX. "menu_sections";
	sqlUpdate($sql);
}













function tidyKeywords($keywords) {
	/* Strip white space around commas in keywords */

	$pattern = '/\s*,\s*/i';
	$replacement = ",";

	$output = preg_replace($pattern,$replacement,$keywords);

	$pattern = '/^,|,$/i';
	$replacement = "";

	$output = preg_replace($pattern,$replacement,$output);

	return $output;
}









/*  Functions for Categories  */

function checkIfCategoryExists($categoryName, $catId = false, $parentCatId = false) {

	$sql = "SELECT name
			FROM " . DB_NAME_PREFIX . "categories
			WHERE name = '" . sqlEscape($categoryName) . "'";
	
	if ($catId) {
		$sql .= "
			  AND id != ". (int) $catId;
	}
	
	if ($parentCatId) {
		$sql .= " AND parent_id = " . (int) $parentCatId;
	}
	
	$result = sqlQuery($sql);
	return sqlNumRows($result);
}

function setupCategoryCheckboxes(&$field, $showTotals = false, $cID = false, $cType = false, $cVersion = false) {
	$field['values'] = array();
	
	$ord = 0;
	$result = getRows('categories', array('id', 'parent_id', 'name'), array(), 'name');
	
	while ($row = sqlFetchAssoc($result)) {
		$field['values'][$row['id']] = array('label' => $row['name'], 'parent' => $row['parent_id'], 'ord' => ++$ord);
		
		if ($showTotals) {
			$sql = "
				SELECT COUNT(DISTINCT c.id, c.type)
				FROM ". DB_NAME_PREFIX. "category_item_link AS cil
				INNER JOIN ". DB_NAME_PREFIX. "content AS c
				   ON c.equiv_id = cil.equiv_id
				  AND c.type = cil.content_type
				  AND c.status NOT IN ('trashed','deleted')
				WHERE cil.category_id = ". (int) $row['id'];
			$result2 = sqlQuery($sql);
			$row2 = sqlFetchRow($result2);
			
			$field['values'][$row['id']]['label'] .= ' ('. $row2[0]. ')';
		}
	}
	
	if ($cID && $cType && $cVersion) {
		$field['value'] = inEscape(getRowsArray('category_item_link', 'category_id', array('equiv_id' => equivId($cID, $cType), 'content_type' => $cType)), true);
	}
}

function countCategoryChildren($id, $recurseCount = 0) {
	$count = 0;
	++$recurseCount;
	
	$sql = "SELECT id
			FROM " . DB_NAME_PREFIX . "categories
			WHERE parent_id = " . (int) $id;
			
	$result = sqlQuery($sql);
	while ($row = sqlFetchAssoc($result)) {
		++$count;
		if ($recurseCount<=10) {
			$count += countCategoryChildren($row['id'], $recurseCount);
		}
	}
	
	return $count;
}

function getCategoryAncestors($id, &$categoryAncestors, $recurseCount = 0) {
	$recurseCount++;
	
	if ($parentId = getRow('categories', 'parent_id', $id)) {
		$categoryAncestors[] = $parentId;
		
		if ($recurseCount<=10) {
			getCategoryAncestors($parentId, $categoryAncestors, $recurseCount);
		}
	}
}

function getCategoryPath($id) {
	$path = '';
	$categoryAncestors = array();
	getCategoryAncestors($id, $categoryAncestors);
	
	foreach ($categoryAncestors as $parentId) {
		if ($parentId) {
			$path = getRow('categories', 'name', $parentId). ' -> '. $path;
		}
	}
	
	return $path. getRow('categories', 'name', $id);
}




/* modules */

function getModuleIconURL($moduleName) {
	
	foreach (array('.gif', '.png', '.jpg') as $ext) {
		if ($path = moduleDir($moduleName, 'module_icon'. $ext, true)) {
			return absCMSDirURL(). $path;
		}
	}
	
	return absCMSDirURL(). 'zenario/api/module_base_class/module_icon.gif';
}


function readModuleDependencies($targetModuleClassName, &$desc) {
	$modules = array();
	
	if (!empty($desc['inheritance']['inherit_description_from_module'])) {
		$dep = $desc['inheritance']['inherit_description_from_module'];
		$modules[$dep] = $dep;
	}
	if (!empty($desc['inheritance']['inherit_frameworks_from_module'])) {
		$dep = $desc['inheritance']['inherit_frameworks_from_module'];
		$modules[$dep] = $dep;
	}
	if (!empty($desc['inheritance']['include_javascript_from_module'])) {
		$dep = $desc['inheritance']['include_javascript_from_module'];
		$modules[$dep] = $dep;
	}
	if (!empty($desc['inheritance']['inherit_settings_from_module'])) {
		$dep = $desc['inheritance']['inherit_settings_from_module'];
		$modules[$dep] = $dep;
	}
	
	if (!empty($desc['dependencies']) && is_array($desc['dependencies'])) {
		foreach($desc['dependencies'] as $moduleClassName => $bool) {
			//An attempt at Backwards compatability for Modules before 6.0
			if ($moduleClassName == 'module') {
				$moduleClassName = $bool;
				$bool = true;
			}
			
			if (engToBoolean($bool)) {
				$modules[$moduleClassName] = $moduleClassName;
			}
		}
	}
	
	//Make sure this Module isn't listed as a dependancy of itself
	unset($modules[$targetModuleClassName]);
	
	return $modules;
}


function runModule($id, $test) {
	$desc = false;
	$missingModules = array();
	$module = getModuleDetails($id);
	
	if (checkIfDBUpdatesAreNeeded()) {
		return adminPhrase("Core databases need to be applied. Please log out and then log back in again before attempting to install any modules.");
	
	//Check to see if there are any other versions of the same module running.
	//If we find another version running, don't let this version be activated!
	} elseif (checkRowExists('modules', array('id' => array('!' => $id), 'class_name' => $module['class_name'], 'status' => 'module_running'))) {
		return adminPhrase('_ANOTHER_VERSION_OF_PLUGIN_IS_INSTALLED');
	
	} elseif (!loadModuleDescription($module['class_name'], $desc)) {
		return adminPhrase("This Module's description file is missing or not valid.");
	
	} elseif (empty($desc['required_cms_version'])) {
		return adminPhrase("This Module's description file does not state it's required version number.");
	
	} elseif (version_compare($desc['required_cms_version'], preg_replace('/[a-z-]/', '', ZENARIO_CMS_VERSION), '>')) {
		return adminPhrase('Sorry, this Module requires Zenario [[version]] or later to run. Please update your copy of the CMS.', array('version' => $desc['required_cms_version']));
	
	} elseif ($installation_check = moduleDir($module['class_name'], 'installation_check.php', true)) {
		require CMS_ROOT. $installation_check;
		$installation_status = checkInstallationCanProceed(ZENARIO_CMS_NUMERIC_VERSION);
		
		if ($installation_status !== true) {
			if (is_array($installation_status)) {
				$error_string = '';
				foreach ($installation_status as $error) {
					$error_string .= ($error_string? '<br/>' : ''). $error;
				}
				return $error_string;
			
			} else {
				$installation_status = (string) $installation_status;
				if (!empty($installation_status)) {
					return $installation_status;
				} else {
					return adminPhrase("This Module cannot be run because the checkInstallationCanProceed() function in its installation_check.php file returned false.");
				}
			}
		}
	}
	
	foreach (readModuleDependencies($module['class_name'], $desc) as $moduleClassName) {
		if (!inc($moduleClassName)) {
			$missingModules[$moduleClassName] = $moduleClassName;
		}
	}
	
	if (!empty($missingModules)) {
		$module['missing_modules'] = '';
		foreach ($missingModules as $moduleClassName) {
			$module['missing_modules'] .=
				($module['missing_modules']? ', ' : '').
				ifNull(getModuleDisplayNameByClassName($moduleClassName), $moduleClassName);
		}
		
		if (count($missingModules) > 1) {
			return adminPhrase(
				'Cannot run the Module "[[display_name]]" as it depends on the following Modules, which are not present or not running: [[missing_modules]]',
				$module);
		
		} else {
			return adminPhrase(
				'Cannot run the Module "[[display_name]]" as it depends on the "[[missing_modules]]" Module, which is not present or not running.',
				$module);
		}
	
	} else {
		require CMS_ROOT. moduleDir($module['class_name'], 'module_code.php');
		
		if (!class_exists($module['class_name'])) {
			return adminPhrase(
				'Cannot run the Module "[[display_name]]" as its class "[[class_name]]" is not defined in its module_code.php file.',
				$module);
		
		} elseif ($test) {
			return false;
		
		} else {
			updateRow('modules', array('status' => 'module_running'), $id);
			
			//Have a safety feature whereby if the installation fails, the module will be immediately uninstalled
			//However this shouldn't be used for upgrading a module to a different version
			checkIfDBUpdatesAreNeeded($andDoUpdates = true, $uninstallPluginOnFail = $id);
			
			//Add any content types this module has
			setupModuleContentTypesFromXMLDescription($module['class_name']);
			
			$modules = array();
			getModuleCodeHash($modules, true);
		}
	}
}

function suspendModule($id) {
	suspendModuleCheckForDependencies(getModuleDetails($id));
	updateRow('modules', array('status' => 'module_suspended'), $id);
	
	$modules = array();
	getModuleCodeHash($modules, true);
}

function succeedModule($id, $preview = false) {
	
	$instances = array();
	
	//Check to see if this module is compatible with any other modules
	$sql = "
		SELECT module_id
		FROM ". DB_NAME_PREFIX. "module_dependencies
		WHERE `type` = 'allow_upgrades'
		  AND dependency_class_name = '". sqlEscape(getModuleClassName($id)). "'
	  UNION
		SELECT p.id AS module_id
		FROM ". DB_NAME_PREFIX. "module_dependencies AS d
		INNER JOIN ". DB_NAME_PREFIX. "modules AS p
		   ON d.dependency_class_name = p.class_name
		WHERE d.`type` = 'allow_upgrades'
		  AND d.module_id = ". (int) $id;
	$result = sqlQuery($sql);
	
	//Update all of those module's instances to be instances for this module
	while($row = sqlFetchAssoc($result)) {
		if ($preview) {
			$wireframes = 0;
			$sql = "
				SELECT content_id, name
				FROM ". DB_NAME_PREFIX. "plugin_instances
				WHERE module_id = ". (int) $row['module_id'];
			$result2 = sqlQuery($sql);
			
			while($instance = sqlFetchAssoc($result2)) {
				if ($instance['content_id']) {
					++$wireframes;
				} else {
					$instances[$instance['name']] = $instance['name'];
				}
			}
			
			if ($wireframes) {
				$instances[] = adminPhrase('[[n]] plugin(s)', array('n' => $wireframes));
			}
		
		} else {
			$sql = "
				UPDATE ". DB_NAME_PREFIX. "plugin_instances SET
					module_id = ". (int) $id. "
				WHERE module_id = ". (int) $row['module_id'];
			sqlQuery($sql);
			
			$sql = "
				UPDATE ". DB_NAME_PREFIX. "plugin_item_link SET
					module_id = ". (int) $id. "
				WHERE module_id = ". (int) $row['module_id'];
			sqlQuery($sql);
			
			$sql = "
				UPDATE ". DB_NAME_PREFIX. "plugin_layout_link SET
					module_id = ". (int) $id. "
				WHERE module_id = ". (int) $row['module_id'];
			sqlQuery($sql);
		}
	}
	
	return $instances;
}


function addNewModules($skipIfFilesystemHasNotChanged = true, $runModulesOnInstall = false, $dbUpdateSafeMode = false) {
	$module_description_hash = false;
	$moduleDirs = moduleDirs(array(
		'module_code.php',
		'description.yaml', 'description.yml',
		'description.xml', 'db_updates/description.xml'));
	
	if ($skipIfFilesystemHasNotChanged) {
		chdir(CMS_ROOT);
		$module_description_hash = hash64(print_r(array_map('filemtime', $moduleDirs), true), 15);
		
		if ($module_description_hash == setting('module_description_hash')) {
			return;
		}
	}
	
	$foundModules = array();
	
	foreach ($moduleDirs as $moduleName => $moduleDir) {
		$desc = false;
		if (loadModuleDescription($moduleName, $desc)) {
			
			$foundModules[$moduleName] = true;
			
			$sql = "
				INSERT INTO ". DB_NAME_PREFIX. "modules SET
					class_name = '". sqlEscape($moduleName). "',
					vlp_class = '". sqlEscape($desc['vlp_class_name']). "',
					display_name = '". sqlEscape($desc['display_name']). "',
					default_framework = '". sqlEscape($desc['default_framework']). "',
					css_class_name = '". sqlEscape($desc['css_class_name']). "',
					nestable = ". engToBoolean($desc['nestable']);
			
			if ($runModulesOnInstall && engToBoolean($desc['start_running_on_install'])) {
				$sql .= ",
					status = 'module_running'";
			}
			
			if (!$dbUpdateSafeMode) {
				$sql .= ",
					is_pluggable = ". engToBoolean($desc['is_pluggable']). ",
					can_be_version_controlled = ". engToBoolean(engToBoolean($desc['is_pluggable'])? $desc['can_be_version_controlled'] : 0). ",
					missing = 0";
			}
			
			$sql .= "
				ON DUPLICATE KEY UPDATE
					vlp_class = VALUES(vlp_class),
					display_name = VALUES(display_name),
					default_framework = VALUES(default_framework),
					css_class_name = VALUES(css_class_name),
					nestable = VALUES(nestable)";
			
			if ($runModulesOnInstall && engToBoolean($desc['start_running_on_install'])) {
				$sql .= ",
					status = 'module_running'";
			}
			
			if (!$dbUpdateSafeMode) {
				$sql .= ",
					is_pluggable = VALUES(is_pluggable),
					can_be_version_controlled = VALUES(can_be_version_controlled),
					missing = 0";
			}
			
			sqlQuery($sql);
		}
	}
	
	//Mark modules that are not in the system
	if (!$dbUpdateSafeMode) {
		foreach (getRowsArray('modules', 'class_name', array()) as $id => $moduleName) {
			if (!isset($foundModules[$moduleName])) {
				setRow('modules', array('missing' => 1), $id);
			}
		}
	}
	
	if ($module_description_hash) {
		setSetting('module_description_hash', $module_description_hash);
	}
}





function suspendModuleCheckForDependencies($module) {
	
	//Check that the core does not depend on the module
	$sql = "
		SELECT 1
		FROM ". DB_NAME_PREFIX. "module_dependencies
		WHERE dependency_class_name = '". sqlEscape($module['class_name']). "'
		  AND module_id = 0";
	$result = sqlQuery($sql);
	
	if (sqlFetchRow($result)) {
		echo adminPhrase(
			'Cannot Suspend the module &quot;[[module]]&quot; as the Core CMS depends on it.',
			array('module' => htmlspecialchars(getModuleDisplayNameByClassName($module['class_name']))));
		exit;
	}
	
	//Check that the module has no other modules that are inheriting from this one
	$sql = "
		SELECT module_id, module_class_name
		FROM ". DB_NAME_PREFIX. "module_dependencies
		WHERE dependency_class_name = '". sqlEscape($module['class_name']). "'
		  AND `type` = 'dependency'";
	$result = sqlQuery($sql);
	
	while ($row = sqlFetchAssoc($result)) {
		if (getModuleStatus($row['module_id']) == 'module_running') {
			echo adminPhrase(
				'Cannot Suspend the module &quot;[[moduleName]]&quot; as the &quot;[[dependencyName]]&quot; module depends on it.',
				array(
					'moduleName' => htmlspecialchars(getModuleDisplayNameByClassName($module['class_name'])),
					'dependencyName' => htmlspecialchars(getModuleDisplayNameByClassName($row['module_class_name']))));
			exit;
		}
	}
}


function uninstallModuleCheckForDependencies($module) {
	//Check that the module has no dependencies
	$sql = "
		SELECT module_class_name
		FROM ". DB_NAME_PREFIX. "module_dependencies
		WHERE dependency_class_name = '". sqlEscape($module['class_name']). "'
		  AND `type` = 'dependency'";
	$result = sqlQuery($sql);
	
	if ($row = sqlFetchAssoc($result)) {
		echo adminPhrase(
			'Cannot Reset the module &quot;[[moduleName]]&quot; as the &quot;[[dependencyName]]&quot; module depends on it.',
			array(
				'moduleName' => htmlspecialchars(getModuleDisplayNameByClassName($module['class_name'])),
				'dependencyName' => htmlspecialchars(getModuleDisplayNameByClassName($row['module_class_name'])))
		);
		exit;
	}
}

//Completely removes all traces of a module from a site.
function uninstallModule($moduleId, $uninstallRunningModules = false) {
	require funIncPath(__FILE__, __FUNCTION__);
}



/* Modules, Slots and Instances */


//List all of the Frameworks available to a Module
function listModuleFrameworks($className, $limit = 10, $recursive = false) {
	if (!--$limit) {
		return false;
	}
	
	//Search this module's inheritances for frameworks first
	$sql = "
		SELECT dependency_class_name
		FROM ". DB_NAME_PREFIX. "module_dependencies
		WHERE type = 'inherit_frameworks'
		  AND module_class_name = '". sqlEscape($className). "'
		LIMIT 1";
	
	$frameworks = array();
	if (($result = sqlQuery($sql))
	 && ($row = sqlFetchRow($result))) {
		$frameworks = listModuleFrameworks($row[0], $limit, true);
	}
	
	//Look through this module's framework directories
	foreach (array(
		'zenario/modules/',
		'zenario_extra_modules/',
		'zenario_custom/modules/',
		'zenario_custom/frameworks/'
	) as $moduleDir) {
		if (is_dir($path = CMS_ROOT. $moduleDir. $className. '/frameworks/')) {
			foreach(scandir($path) as $themeName) {
				if (substr($themeName, 0, 1) != '.') {
					//Only list a framework if the .html file is present
					if (is_file($path. $themeName. '/framework.html')) {
						$frameworks[$themeName] = array(
							'name' => $themeName,
							'label' => $themeName,
							'path' => $path. $themeName. '/framework.html',
							'filename' => 'framework.html',
							'module_class_name' => $className);
					} elseif (is_file($path. $themeName. '/framework.twig.html')) {
						$frameworks[$themeName] = array(
							'name' => $themeName,
							'label' => $themeName,
							'path' => $path. $themeName. '/framework.twig.html',
							'filename' => 'framework.twig.html',
							'module_class_name' => $className);
					}
				}
			}
		}
	}
	
	if (!$recursive) {
		ksort($frameworks);
		$ord = 0;
		foreach ($frameworks as &$framework) {
			$framework['ord'] = ++$ord;
		}
	}
	
	return $frameworks;
}

//Attempt to add a new instance
function createNewInstance($moduleId, $instanceName, &$instanceId, &$errors, $onlyValidate = false, $forceName = false) {
	
	if (!$moduleId) {
		return false;
	}

	//Exit if called somewhere it shouldn't be
	if (!checkIfModuleUsesPluginInstances($moduleId)) {
		exit;
	}
	
	if (!$instanceName) {
		$errors[] = adminPhrase('_ERROR_INSTANCE_NAME');
		return false;
	}
	
	//Check to see if an instance of that name already exists
	if (checkRowExists('plugin_instances', array('name' => $instanceName))) {
		if (!$forceName) {
			$errors[] = adminPhrase('_ERROR_INSTANCE_NAME_EXISTS');
			return false;
		
		//Have the option to attempt to force a unique name
		} else {
			$sql = "
				SELECT COUNT(*)
				FROM ". DB_NAME_PREFIX. "plugin_instances
				WHERE name LIKE '". sqlEscape($instanceName). " (%)'";
			$result = sqlQuery($sql);
			$row = sqlFetchRow($result);
			
			$instanceName .= ' ('. ($row[0] + 2). ')';
		}
	}
	
	if ($onlyValidate) {
		return true;
	}
	
	//Insert a new record into the instances table
	$sql = "
		INSERT INTO ". DB_NAME_PREFIX. "plugin_instances (module_id, name)
		SELECT id, '". sqlEscape($instanceName). "'
		FROM ". DB_NAME_PREFIX. "modules
		WHERE id = ". (int) $moduleId;
	
	sqlUpdate($sql, false);  //No need to check the cache as this new instance is not used anywhere yet
	$instanceId = sqlInsertId();
	
	return true;
}

//Remove any Wireframe modules that are not actually being used for a Content Item
function removeUnusedVersionControlledPluginSettings($cID, $cType, $cVersion) {
	$slotContents = array();
	getSlotContents($slotContents, $cID, $cType, $cVersion, false, false, false, false, false, false, $runPlugins = false);
	
	$result = getRows('plugin_instances', array('id', 'slot_name'), array('content_id' => $cID, 'content_type' => $cType, 'content_version' => $cVersion));
	while ($instance = sqlFetchAssoc($result)) {
		if ($instance['id'] != arrayKey($slotContents, $instance['slot_name'], 'instance_id')) {
			deletePluginInstance($instance['id']);
		}
	}
}

//Copy Wireframe modules from one Content Item to another, as part of creating a new draft.
//Logic similar to removeUnusedVersionControlledPluginSettings() above needs to be used to check that only Settings that are actually being used are copied
function duplicateVersionControlledPluginSettings($cIDTo, $cIDFrom, $cType, $cVersionTo, $cVersionFrom, $cTypeFrom = false, $slotName = false) {
	$cTypeTo = $cType;
	if (!$cTypeFrom) {
		$cTypeFrom = $cType;
	}
	$slotContents = array();
	getSlotContents($slotContents, $cIDFrom, $cTypeFrom, $cVersionFrom, false, false, false, false, false, false, $runPlugins = false);
	
	$result = getRows('plugin_instances', array('id', 'slot_name'), array('content_id' => $cIDFrom, 'content_type' => $cTypeFrom, 'content_version' => $cVersionFrom));
	while ($instance = sqlFetchAssoc($result)) {
		if (!$slotName || $slotName == $instance['slot_name']) {
			if ($instance['id'] == arrayKey($slotContents, $instance['slot_name'], 'instance_id')) {
				$nest = 0;
				renameInstance($instance['id'], $nest, false, true, $cIDTo, $cTypeTo, $cVersionTo, $instance['slot_name']);
			}
		}
	}
}

//Duplicate or rename an instance if possible
//Also has the functionality to convert a Plugin between a Wireframe and a Reusable or vice versa when duplicating
function renameInstance(&$instanceId, &$nest, $newName, $createNewInstance, $cID = false, $cType = false, $cVersion = false, $slotName = false) {
	return require funIncPath(__FILE__, __FUNCTION__);
}

//Check how many content items use a Library plugin
function checkInstancesUsageOnLayouts($instanceIds, $activeOnly = true) {
	
	$usage = 0;
	if ($instanceIds) {
		$sql = "
			SELECT COUNT(DISTINCT l.layout_id)
			FROM ". DB_NAME_PREFIX. "plugin_layout_link AS l
			INNER JOIN ". DB_NAME_PREFIX. "layouts AS t
			   ON t.layout_id = l.layout_id
			INNER JOIN ". DB_NAME_PREFIX. "template_slot_link AS s
			   ON s.family_name = t.family_name
			  AND s.file_base_name = t.file_base_name
			  AND s.slot_name = l.slot_name
			WHERE l.instance_id IN (". preg_replace('/[^\d,]/', '', $instanceIds). ")";
		
		if ($activeOnly) {
			$sql .= "
			  AND t.status = 'active'";
		}
		
		$result = sqlQuery($sql);
		$row = sqlFetchRow($result);
		$usage += $row[0];
	}
	
	return $usage;
}

//Check how many content items use a Library plugin
function checkInstancesUsage($instanceIds, $publishedOnly = false, $itemLayerOnly = false) {
	
	$layoutIds = array();
	if (!$itemLayerOnly) {
		$sql2 = "
			SELECT l.layout_id
			FROM ". DB_NAME_PREFIX. "plugin_layout_link AS l
			INNER JOIN ". DB_NAME_PREFIX. "layouts AS t
			   ON t.layout_id = l.layout_id
			INNER JOIN ". DB_NAME_PREFIX. "template_slot_link AS s
			   ON s.family_name = t.family_name
			  AND s.file_base_name = t.file_base_name
			  AND s.slot_name = l.slot_name
			WHERE l.instance_id IN (". inEscape($instanceIds, 'numeric'). ")";
		
		$result = sqlQuery($sql2);
		while ($row = sqlFetchRow($result)) {
			$layoutIds[] = $row[0];
		}
		
		if (empty($layoutIds)) {
			$itemLayerOnly = true;
		}
	}
	
	$usage = 0;
	if ($instanceIds) {
		$sql = "
			SELECT COUNT(DISTINCT c.tag_id) AS ciu_". (int) $instanceIds. "_". engToBoolean($publishedOnly). "_". engToBoolean($itemLayerOnly). "
			FROM ". DB_NAME_PREFIX. "content AS c
			INNER JOIN ". DB_NAME_PREFIX. "versions as v
			   ON c.id = v.id
			  AND c.type = v.type";
		
		if ($publishedOnly) {
			$sql .= "
			  AND v.version = c.visitor_version";
		} else {
			$sql .= "
			  AND v.version IN (c.admin_version, c.visitor_version)";
		}
		
		$sql .= "
			INNER JOIN ". DB_NAME_PREFIX. "layouts AS l
               ON l.layout_id = v.layout_id";
		
		if ($itemLayerOnly) {
			$sql .= "
				INNER JOIN ". DB_NAME_PREFIX. "plugin_item_link as pil";
		} else {
			$sql .= "
				LEFT JOIN ". DB_NAME_PREFIX. "plugin_item_link as pil";
		}
		
		$sql .= "
		   ON pil.instance_id IN (". inEscape($instanceIds, 'numeric'). ")
		  AND pil.content_id = c.id
		  AND pil.content_type = c.type
		  AND pil.content_version = v.version";
		
		if ($itemLayerOnly) {
			$sql .= "
				INNER JOIN ". DB_NAME_PREFIX. "template_slot_link as t";
		} else {
			$sql .= "
				LEFT JOIN ". DB_NAME_PREFIX. "template_slot_link as t";
		}
		
		$sql .= "
			   ON t.family_name = l.family_name
			  AND t.file_base_name = l.file_base_name
			  AND t.slot_name = pil.slot_name";
		
		if ($publishedOnly) {
			$sql .= "
			WHERE c.status IN ('published_with_draft', 'published')";
		} else {
			$sql .= "
			WHERE c.status IN ('first_draft', 'published_with_draft', 'hidden', 'hidden_with_draft', 'trashed_with_draft', 'published')";
		}
		
		if (!$itemLayerOnly) {
			$sql .= "
			  AND (
				t.slot_name IS NOT NULL
			   OR
				v.layout_id IN (". inEscape($layoutIds, 'numeric'). ")
			  )";
		}
		
		$result = sqlQuery($sql);
		$row = sqlFetchRow($result);
		$usage = $row[0];
	}
	
	return $usage;
}


//Replace one instance with another
function replacePluginInstance($oldmoduleId = false, $oldInstanceId, $newmoduleId = false, $newInstanceId, $cID = false, $cType = false, $cVersion = false, $slotName = false) {
	
	if ((!$oldmoduleId && !($oldmoduleId = getRow('plugin_instances', 'module_id', $oldInstanceId)))
	 || (!$newmoduleId && !($newmoduleId = getRow('plugin_instances', 'module_id', $newInstanceId)))) {
		return;
	}
	
	//Replace the slot
	foreach (array('plugin_item_link', 'plugin_layout_link') as $table) {
		updateRow(
			$table,
			array('module_id' => $newmoduleId, 'instance_id' => $newInstanceId),
			array('module_id' => $oldmoduleId, 'instance_id' => $oldInstanceId));
	}
	
	//Remove the item level placement if needed
	if ($cID && $cType && $cVersion && $slotName) {
		$layoutId = contentItemTemplateId($cID, $cType, $cVersion);
		$templateFamily = getRow('layouts', 'family_name', $layoutId);
		
		$templateLevelInstanceId = getPluginInstanceInTemplateSlot($slotName, $templateFamily, $layoutId);
		$templateLevelmoduleId = getPluginInTemplateSlot($slotName, $templateFamily, $layoutId);
		
		if ($templateLevelmoduleId == $newmoduleId && $templateLevelInstanceId == $newInstanceId) {
			updatePluginInstanceInItemSlot('', $slotName, $cID, $cType, $cVersion);
		}
	}
}

function deletePluginInstance($instanceId) {
	deleteRow('plugin_instances', $instanceId);
	
	foreach (array(
		'nested_plugins', 'plugin_instance_cache',
		'plugin_settings', 'plugin_item_link', 'plugin_layout_link'
	) as $table) {
		deleteRow($table, array('instance_id' => $instanceId));
	}
	
	sendSignal('eventPluginInstanceDeleted', array('instanceId' => $instanceId));
}

function deleteVersionControlledPluginSettings($cID, $cType, $cVersion) {
	$result = getRows('plugin_instances', array('id'), array('content_id' => $cID, 'content_type' => $cType, 'content_version' => $cVersion));
	while ($row = sqlFetchAssoc($result)) {
		deletePluginInstance($row['id']);
	}
}

//Check how many items use a Layout or a Template Family
function checkTemplateUsage($layoutId, $templateFamily = false, $publishedOnly = false, $skinId = false) {
	$sql = "
		SELECT COUNT(DISTINCT c.tag_id) AS ctu_". (int) $layoutId. "_". engToBoolean($templateFamily). "_". engToBoolean($publishedOnly). "_". (int) $skinId. "
		FROM ". DB_NAME_PREFIX. "content AS c
		INNER JOIN ". DB_NAME_PREFIX. "versions as v
		   ON c.id = v.id
		  AND c.type = v.type";
	
	if ($publishedOnly) {
		$sql .= "
		  AND v.version = c.visitor_version
		INNER JOIN ". DB_NAME_PREFIX. "layouts AS t
		   ON t.layout_id = v.layout_id
		INNER JOIN ". DB_NAME_PREFIX. "template_families AS f
		   ON f.family_name = t.family_name
		WHERE c.status IN ('published_with_draft', 'published')";
	
	} else {
		$sql .= "
		  AND v.version IN (c.admin_version, c.visitor_version)
		INNER JOIN ". DB_NAME_PREFIX. "layouts AS t
		   ON t.layout_id = v.layout_id
		INNER JOIN ". DB_NAME_PREFIX. "template_families AS f
		   ON f.family_name = t.family_name
		WHERE c.status IN ('first_draft', 'published_with_draft', 'hidden_with_draft', 'trashed_with_draft', 'published')";
	}
	
	if ($templateFamily) {
		$sql .= "
		  AND t.family_name = '". sqlEscape($templateFamily). "'";
	} else {
		$sql .= "
		  AND v.layout_id = ". (int) $layoutId;
	}
	
	if ($skinId) {
		$sql .= "
		  AND IF(t.skin_id != 0, t.skin_id, f.skin_id) = ". (int) $skinId;
	}
	
	$result = sqlQuery($sql);
	$row = sqlFetchRow($result);
	return $row[0];
}

//Attempt to check if this module can work with instances
function checkIfModuleUsesPluginInstances($moduleId) {
	return getRow('modules', 'is_pluggable', $moduleId);
}



//Update or remove a modules in slots
function updatePluginInstanceInItemSlot($instanceId, $slotName, $cID, $cType = false, $cVersion = false, $moduleId = false, $copySwatchUp = false) {
	
	if (!$cVersion) {
		$cVersion = getLatestVersion($cID, $cType);
	}
	
	if (!$moduleId && $instanceId) {
		$details = getPluginInstanceDetails($instanceId);
		$moduleId = $details['module_id'];
	}
	
	if ($moduleId || $instanceId !== '') {
		$placementId = setRow(
			'plugin_item_link',
			array(
				'module_id' => $moduleId,
				'instance_id' => $instanceId),
			array(
				'slot_name' => $slotName,
				'content_id' => $cID,
				'content_type' => $cType,
				'content_version' => $cVersion));
		
	} else {
		deleteRow(
			'plugin_item_link',
			array(
				'slot_name' => $slotName,
				'content_id' => $cID,
				'content_type' => $cType,
				'content_version' => $cVersion));
	}
}

function getNestablemoduleIds() {
	$nestablemoduleIds = array();
	
	if ($pluginNestId = getModuleIdByClassName('zenario_plugin_nest')) {
		$nestablemoduleIds[$pluginNestId] = true;
		
		$sql = "
			SELECT module_id
			FROM ". DB_NAME_PREFIX. "module_dependencies
			WHERE dependency_class_name = 'zenario_plugin_nest'
			  AND `type` = 'dependency'";
		$result = sqlQuery($sql);
		while($row = sqlFetchAssoc($result)) {
			$nestablemoduleIds[$row['module_id']] = true;
		}
	}
	
	return $nestablemoduleIds;
}

function getNestDetails($nestedItemId, $instanceId = false) {

	$sql = "
		SELECT
			tab,
			ord,
			instance_id,
			module_id,
			framework,
			css_class,
			is_tab,
			name_or_title
		FROM ". DB_NAME_PREFIX. "nested_plugins
		WHERE id = ". (int) $nestedItemId;
	
	if ($instanceId !== false) {
		$sql .= "
		  AND instance_id = ". (int) $instanceId;
	}
	
	$result = sqlQuery($sql);
	return sqlFetchAssoc($result);
}

function updatePluginInstanceInTemplateSlot($instanceId, $slotName, $templateFamily, $layoutId, $moduleId = false, $cID = false, $cType = false, $cVersion = false, $copySwatchUp = false, $copySwatchDown = false) {
	
	if ($cID && $cType && !$cVersion) {
		$cVersion = getLatestVersion($cID, $cType);
	}
	
	if (!$moduleId && $instanceId) {
		$details = getPluginInstanceDetails($instanceId);
		$moduleId = $details['module_id'];
	}
	
	if ($moduleId) {
		$placementId = setRow(
			'plugin_layout_link',
			array(
				'module_id' => $moduleId,
				'instance_id' => $instanceId),
			array(
				'slot_name' => $slotName,
				'family_name' => $templateFamily,
				'layout_id' => $layoutId));
		
	} else {
		deleteRow(
			'plugin_layout_link',
			array(
				'slot_name' => $slotName,
				'family_name' => $templateFamily,
				'layout_id' => $layoutId));
	}
}

//Remove the "hide plugin on this content item" option if it has been set
function unhidePlugin($cID, $cType, $cVersion, $slotName) {
	
	if ($cID && $cType && $cVersion) {
		deleteRow(
			'plugin_item_link',
			array(
				'module_id' => 0,
				'instance_id' => 0,
				'content_id' => $cID,
				'content_type' => $cType,
				'content_version' => $cVersion,
				'slot_name' => $slotName));
	}
}


function slotAdminBoxCheckForChanges(&$box, $level) {
	foreach (array(
		'_empty',
		'_opaque',
		'_transparent',
		'_reusable',
		'_wireframe',
		'_plugin',
		'_instance',
		'_html',
		'_html_overwrite',
		'_html_output_in_admin_mode',
		'_html_text',
		'_cookie_consent'
	) as $field) {
		if (isset($box['tabs']['slot']['fields'][$level. $field]['current_value'])) {
			if (arrayKey($box['tabs']['slot']['fields'][$level. $field], 'value') != $box['tabs']['slot']['fields'][$level. $field]['current_value']) {
				return true;
			}
		}
	}
	
	return false;
}





function checkAuth() {
	//This funciton is now no longer used
	return false;
}


/* Docstore functionality */

function initialiseDocStore () {
	$errors = array();
	
	//Check the docstore directory is correctly defined, exists, and has the correct permissions
	if (!setting('docstore_dir')) {
		$errors[] = adminPhrase('_NOT_DEFINED_DOCSTORE_DIR'). '<br />'. adminPhrase('_FIX_DOCSTORE_DIR_TO_USE_DOCSTORE');
	
	} else {
		$docstorepath = setting('docstore_dir');
		
		if (!file_exists($docstorepath)) {
			$mrg = array('dirpath' => $docstorepath);
			$errors[] = adminPhrase('_FIX_DOCSTORE_DIR_TO_USE_DOCSTORE'). '<br />'. adminPhrase('_DIRECTORY_DOES_NOT_EXIST', $mrg);
		
		} elseif (!is_readable($docstorepath) || !is_writeable($docstorepath)) {
			$mrg = array('dirpath' => $docstorepath);
			$errors[] = adminPhrase('_FIX_DOCSTORE_DIR_TO_USE_DOCSTORE'). '<br />'. adminPhrase('_DIRECTORY_NOT_READ_AND_WRITEABLE', $mrg);
		}
	}
	
	return count($errors)? $errors : false;
}

//check if a Visitor Phrase is protected
function checkIfPhraseIsProtected($languageId, $moduleClass, $phraseCode, $adding) {

	$sql = "
		SELECT";
	
	//Are we adding a new VLP, or updating an existing one?
	if (!$adding) {
		//If we are editing an existing one, do not overwrite a protected phrase
		$sql .= "
			protect_flag";
	
	} else {
		//If we are adding a new VLP, don't allow anything that already exists (and has a value) to be overwritten
		$sql .= "
			local_text IS NOT NULL AND local_text != ''";
	}
	
	$sql .= "
		FROM " . DB_NAME_PREFIX . "visitor_phrases
		WHERE language_id = '". sqlEscape($languageId). "'
		  AND module_class_name = '". sqlEscape($moduleClass). "'
		  AND code = '". sqlEscape($phraseCode). "'";
	
	
	//Return true for protected, false for exists bug not protected, and 0 for when the phrase does not exist
	if ($row = sqlFetchRow(sqlQuery($sql))) {
		return (bool) $row[0];
	} else {
		return 0;
	}
}

//Update a Visitor Phrase from the importer
function importVisitorPhrase($languageId, $moduleClass, $phraseCode, $localText, $adding, &$numberOf) {
	
	//Don't attempt to add empty phrases
	if (!$phraseCode || $localText === null || $localText === false || $localText === '') {
		return;
	}
	
	//Check if the phrase is protected
	if ($protected = checkIfPhraseIsProtected($languageId, $moduleClass, $phraseCode, $adding)) {
		++$numberOf['protected'];
		
	} else {
		//Update or insert the phrase
		setRow(
			'visitor_phrases',
			array(
				'local_text' => trim($localText)),
			array(
				'language_id' => $languageId,
				'module_class_name' => $moduleClass,
				'code' => $phraseCode));
		
		//checkIfPhraseIsProtected() returns false for phrases that are unprotected, and 0 for phrases that do not exist
		if ($protected === 0) {
			++$numberOf['added'];
		
		} else {
			++$numberOf['updated'];
		}
	}

}


//Given an uploaded XML file, pharse that file looking for visitor language phrases
function importVisitorLanguagePack($file, &$languageId, $adding, $scanning = false, $forceLanguageIdOverride = false, $realFilename = false) {
	return require funIncPath(__FILE__, __FUNCTION__);
}


function scanModulePhraseDir($moduleName, $scanMode = true) {
	$importFiles = array();
	if ($path = moduleDir($moduleName, 'phrases/', true)) {
		foreach (scandir($path) as $file) {
			if (is_file($path. $file) && substr($file, 0, 1) != '.') {
				$numberOf = importVisitorLanguagePack($path. $file, $languageId, $adding = true, $scanMode);
				if (!$numberOf['upload_error']) {
					if ($scanMode === 'number and file') {
						$numberOf['file'] = $file;
						$importFiles[$languageId] = $numberOf;
					
					} elseif ($scanMode === 'full scan') {
						$importFiles[$languageId] = $numberOf['added'];
					
					} else {
						$importFiles[$languageId] = $file;
					}
				}
			}
		}
	}
	
	return $importFiles;
}














//Functions for the database updater
define('RUN_EVERY_UPDATE', 'RUN_EVERY_UPDATE');

//Get an array containing the patch levels of all installed modules.
function getAllCurrentRevisionNumbers() {

	$modulesToRevisionNumbers = array();
	
	//Attempt to get all of the rows from the revision numbers table
	$sql = "
		SELECT path, patchfile, revision_no
		FROM ". DB_NAME_PREFIX. "local_revision_numbers";
	
	//If we fail, rather than exist with an error message and crash the entire admin section,
	//just return that there are no updates
	if (!($result = @sqlSelect($sql))) {
		return $modulesToRevisionNumbers;
	}
	
	//Put all of the revision numbers we found into an array, and return it
	while ($row = sqlFetchAssoc($result)) {
		//Convert anything saying "zenario_extra_modules/" or "zenario_custom/modules/" to "zenario/modules/"
		//Also account for the directory path rearrangements that happened in zenario 6
		if (($chop = chopPrefixOffOfString($row['path'], 'plugins/'))
		 || ($chop = chopPrefixOffOfString($row['path'], 'zenario/plugins/'))
		 || ($chop = chopPrefixOffOfString($row['path'], 'zenario_extra_modules/'))
		 || ($chop = chopPrefixOffOfString($row['path'], 'zenario_custom/modules/'))) {
			$row['path'] = 'zenario/modules/'. $chop;
		}
		
		if (!chopPrefixOffOfString($row['path'], 'zenario/')) {
			$row['path'] = 'zenario/'. $row['path'];
		}
		
		//Note down this number
		//If there are overlaps, resolve this bug by picking the biggest number of the two
		if (!isset($modulesToRevisionNumbers[$row['path']. '/'. $row['patchfile']])
		 || $modulesToRevisionNumbers[$row['path']. '/'. $row['patchfile']] < $row['revision_no']) {
			$modulesToRevisionNumbers[$row['path']. '/'. $row['patchfile']] = $row['revision_no'];
		}
	}
	
	return $modulesToRevisionNumbers;
}


//Check the current revisions as recorded in the revision_numbers tables
//to see if database updates are needed from the updates directory
function checkIfDBUpdatesAreNeeded($andDoUpdates = false, $uninstallPluginOnFail = false, $quickCheckForUpdates = true) {
	return require funIncPath(__FILE__, __FUNCTION__);
}


//Update the revision number for a module
function setModuleRevisionNumber($revisionNumber, $path, $updateFile) {
	
	//Ignore updates in some cases
	if ($revisionNumber === RUN_EVERY_UPDATE) {
		return;
	}
	
	//Account for the directory path rearrangements that happened in zenario 6
	//Convert back to the old format when saving
	if ($chop = chopPrefixOffOfString($path, 'zenario/')) {
		$path = $chop;
	
	//Convert back from the my_zenario_module format as well
	} else
	if (($chop = chopPrefixOffOfString($path, 'zenario_extra_modules/'))
	 || ($chop = chopPrefixOffOfString($path, 'zenario_custom/modules/'))) {
		$path = 'modules/'. $chop;
	}
			
	$sql = "
		REPLACE INTO  ". DB_NAME_PREFIX. "local_revision_numbers SET
		  path = '". sqlEscape($path). "',
		  patchfile = '". sqlEscape($updateFile). "',
		  revision_no = ". (int) $revisionNumber;
	
	sqlQuery($sql);
}


//Run a patch file, making the revisions needed
//If $currentRevision and $latestRevisionNumber are set, it will use revision control for updates;
//i.e. updates that have already been applied can be skipped
function performDBUpdate($path, $updateFile, $uninstallPluginOnFail, $currentRevision = RUN_EVERY_UPDATE, $latestRevisionNumber = RUN_EVERY_UPDATE) {
	
	
	//Check the extension, to see if this is a description for a module
	if ($updateFile == 'description.yaml'
	 || $updateFile == 'description.yml'
	 || $updateFile == 'description.xml') {
		
		//The path will be of the form 
		//	zenario/modules/'. $module['class_name']. '/...
		//Get the module's directory name from the path!
		if (chopPrefixOffOfString($path, 'zenario/modules/')
		 || chopPrefixOffOfString($path, 'zenario_custom/modules/')) {
			$paths = explode('/', $path, 4);
			$moduleName = $paths[2];
		
		} elseif (chopPrefixOffOfString($path, 'zenario_extra_modules/')) {
			$paths = explode('/', $path, 3);
			$moduleName = $paths[1];
		
		} else {
			echo 'Could not work out which Module ', $path, $updateFile. ' is for.';
			exit;
		}
		
		//Attempt to apply the XML file
		if (!setupModuleFromXMLDescription($moduleName)) {
			exit;
		}
		
		//If this is an installed module, update any content types settings too
		if (!$uninstallPluginOnFail) {
			setupModuleContentTypesFromXMLDescription($moduleName);
		}
	
	//Otherwise assume the file will be a php file with a series of revisions
	} else {
		//Set the inputs into global variables, so we can remember them for this revision
		//without needing to add extra parameters to every function (which would make the update files look messy!)
		cms_core::$dbupPath = $path;
		cms_core::$dbupUpdateFile = $updateFile;
		cms_core::$dbupCurrentRevision = $currentRevision;
		cms_core::$dbupUninstallPluginOnFail = $uninstallPluginOnFail;
		
		//Run the update file
		require_once CMS_ROOT. $path. '/'. $updateFile;
		
		$path = cms_core::$dbupPath;
		$updateFile = cms_core::$dbupUpdateFile;
		$currentRevision = cms_core::$dbupCurrentRevision;
		$uninstallPluginOnFail = cms_core::$dbupUninstallPluginOnFail;
	}
	
	//Update the current revision in the database to the latest, so this will not be triggered again.
	setModuleRevisionNumber($latestRevisionNumber, $path, $updateFile);
}

function needRevision($revisionNumber) {

	//Check the latest revision number, and if we have applied this revision yet
	//If we have already applied the revision, we can stop without processing it any further
	
	//Note that there is functionality to override this and always apply a revision!
	if (cms_core::$dbupCurrentRevision !== RUN_EVERY_UPDATE && $revisionNumber <= cms_core::$dbupCurrentRevision) {
		return false;
	} else {
		return true;
	}
}

//This function is used for database revisions. It's called from the patch files.
//WARNING: It expects to already be connected to the correct database, and to have
//the cms_core::$dbupPath, cms_core::$dbupUpdateFile and cms_core::$dbupCurrentRevision global variables set
function revision($revisionNumber) {

	//The first arguement to this function should be the revision number.
	//All remaining arguements will be the SQL statements for that revision.
	
	//Check the latest revision number, and if we have applied this revision yet
	//If we have already applied the revision, we can stop without processing it any further
	
	//Note that there is functionality to override this and always apply a revision!
	if (!needRevision($revisionNumber)) {
		return;
	}
	//If the above wasn't true, then we'll need to apply the update
	
	
	//Loop through all of the arguments given after the first
	$i = 1;
	$count = func_num_args();
	while ($i < $count && ($sql = func_get_arg($i++))) {
		
		//Run the SQL, using str_replace to subsitute in the values of DB_NAME_PREFIX
		$sql = addConstantsToString($sql, false);
		$result = @cms_core::$lastDB->query($sql);
		
		//Handle errors
		if ($result === false) {
			$errNo = sqlErrno();
			
			//Ignore "column already exists" errors
			if ($errNo == 1060 && !preg_match('/\s*CREATE\s*TABLE\s*/i', $sql)) {
				continue;
			
			//Ignore "primary key already exists" errors
			} elseif ($errNo == 1068 && !preg_match('/\s*CREATE\s*TABLE\s*/i', $sql)) {
				continue;
			
			//Ignore errors if we try to drop columns or unique keys that do not exist
			} elseif ($errNo == 1091) {
				continue;
			
			//Ignore "Table doesn't exist" and "Table already exists" error messages when renaming tables
			} elseif (($errNo == 1146 || $errNo == 1050) && preg_match('/\s*ALTER\s*TABLE\s*\S*\s*RENAME\s*TO\s*\S*\s*/i', $sql)) {
				continue;
			
			//Ignore "Table already created" error messages
			} elseif ($errNo == 1050 && preg_match('/\s*CREATE\s*TABLE\s*/i', $sql)) {
				continue;
			}
			
			
			//Otherwise we can't recover from this error
			
			//Report the error
			echo "Database query error: ".sqlErrno().", ".sqlError().", $sql";
			
			//If this was the installation of a Module, then remove everything that the Module has installed
			if (cms_core::$dbupUninstallPluginOnFail) {
				uninstallModule(cms_core::$dbupUninstallPluginOnFail, true);
			}
			
			//Stop
			exit;
		}
	}
	
	//Update the revision number for this module, or set it if it was not there.
	//I'm doing this with each revision, just in case we get an error in one
	//- the previous revisions won't be applied.
	if ($revisionNumber && cms_core::$dbupCurrentRevision !== RUN_EVERY_UPDATE) {
		setModuleRevisionNumber($revisionNumber, cms_core::$dbupPath, cms_core::$dbupUpdateFile);
	
	} else {
		updateDataRevisionNumber();
	}
}


//Process a table join, and add the words "LEFT JOIN" to the start if they are missing
function prefixTableJoin($tableJoin) {
	$tableJoin = preg_replace('/\s\s+/', ' ', trim($tableJoin));
	$words = preg_split('/\W+/', strtoupper($tableJoin), 4);
	
	if (arrayKey($words, 1) != 'JOIN'
	 && arrayKey($words, 2) != 'JOIN'
	 && arrayKey($words, 1) != 'STRAIGHT_JOIN'
	 && arrayKey($words, 2) != 'STRAIGHT_JOIN') {
		$tableJoin = 'LEFT JOIN '. $tableJoin;
	}
	
	return $tableJoin;
}

//Take a string, and add any defined constants in using the [[CONSTANT_NAME]] format
function addConstantsToString($sql, $replaceUnmatchedConstants = true) {
	//Get a list of defined constants
	$constants = get_defined_constants();
	
	$constantValues = array_values($constants);
	$constants = array_keys($constants);
	
	//Add our standard substitution pattern to the keys
	array_walk($constants, 'addConstantToString');
	
	$sql = str_replace($constants, $constantValues, $sql);
	
	if ($replaceUnmatchedConstants) {
		$sql = str_replace('[[SQL_IN]]', '', $sql);
		$sql = preg_replace('/\[\[\w+\]\]/', 'NULL', $sql);
	}
	
	return $sql;
}

function addConstantToString(&$value, $key) {
	$value = '[['. $value. ']]';
}

function moduleDescriptionFilePath($moduleName) {
	if (($path = moduleDir($moduleName, 'description.yaml', true))
	 || ($path = moduleDir($moduleName, 'description.yml', true))
	 || ($path = moduleDir($moduleName, 'description.xml', true))) {
		return $path;
	} else {
		return false;
	}
}


function loadModuleDescription($moduleName, &$tags) {
	
	if (!moduleDir($moduleName, '', true)) {
		return false;
	}
	
	$tags = array();
	$limit = 20;
	$modulesWeHaveRead = array();
	$baseModuleName = $inherit_description_from_module = $moduleName;
	$settingGroup = 'own';
	
	while (--$limit && $inherit_description_from_module && empty($modulesWeHaveRead[$inherit_description_from_module])) {
		$modulesWeHaveRead[$inherit_description_from_module] = true;
		$baseModuleName = $inherit_description_from_module;
		$inherit_description_from_module = false;
		
		//Attempt to open and read the description file
		if ($path = moduleDescriptionFilePath($baseModuleName)) {
			
			if (!$tagsToParse = zenarioReadTUIXFile(CMS_ROOT. $path)) {
				echo adminPhrase('[[path]] appears to be in the wrong format or invalid.', array('path' => CMS_ROOT. $path));
				return false;
			
			} else {
				if (!empty($tagsToParse['inheritance']['inherit_description_from_module'])) {
					$inherit_description_from_module = trim((string) $tagsToParse['inheritance']['inherit_description_from_module']);
				}
				
				zenarioParseTUIX($tags, $tagsToParse, 'module_description', '', $settingGroup);
				unset($tagsToParse);
			}
		}
		
		$settingGroup = 'inherited';
	}
	
	
	$replaces = array();
	$replaces['[[MODULE_DIRECTORY_NAME]]'] = $baseModuleName;
	$replaces['[[ZENARIO_CMS_VERSION]]'] = ZENARIO_CMS_VERSION;
	
	//If the is_pluggable property is missing...
	if (!isset($tags['module']['is_pluggable'])) {
		//This property has an old name, check that for backwards-compatability purposes
		if (isset($tags['module']['uses_instances'])) {
			$tags['module']['is_pluggable'] = $tags['module']['uses_instances'];
		} else {
			//Otherwise attempt to intelligently guess whether this Module uses instances by looking for
			//the showSlot function in its module code.
			if (($path = moduleDir($baseModuleName, 'module_code.php', true))
			 && ($contents = file_get_contents($path))
			 && (preg_match('/function\s+showSlot/', $contents))) {
				$replaces['[[HAS_SHOWSLOT_FUNCTION]]'] = true;
			} else {
				$replaces['[[HAS_SHOWSLOT_FUNCTION]]'] = false;
			}
		}
	}
	unset($contents);
	
	
	$tagsToParse = zenarioReadTUIXFile(CMS_ROOT. 'zenario/api/module_base_class/description.yaml');
	zenarioParseTUIX($tags, $tagsToParse, 'module_description', '', 'inherited');
	unset($tagsToParse);
	
	$tags = $tags['module_description'];
	
	foreach ($tags as &$tag) {
		if (is_string($tag) && isset($replaces[$tag])) {
			$tag = $replaces[$tag];
		}
	}
	
	return true;
}

//Get the XML description of a module, and apply it
function setupModuleFromXMLDescription($moduleClassName) {
	return require funIncPath(__FILE__, __FUNCTION__);
}

function scanModulePermissionsInTUIXDescription($moduleClassName) {
	return require funIncPath(__FILE__, __FUNCTION__);
}


//Work out a slot to put this Plugin into, favouring empty "Main" slots. Default to Main_3.
function getTemplateMainSlot($templateFamilyName, $templateFileBaseName) {
	$sql = "
		SELECT tsl.slot_name
		FROM ". DB_NAME_PREFIX. "template_slot_link AS tsl
		LEFT JOIN ". DB_NAME_PREFIX. "layouts AS t
		   ON tsl.family_name = t.family_name
		  AND tsl.file_base_name = t.file_base_name
		LEFT JOIN ". DB_NAME_PREFIX. "plugin_layout_link AS pitl
		   ON tsl.family_name = pitl.family_name
		  AND t.layout_id = pitl.layout_id
		  AND tsl.slot_name = pitl.slot_name
		WHERE tsl.family_name = '". sqlEscape($templateFamilyName). "'
		  AND tsl.file_base_name = '". sqlEscape($templateFileBaseName). "'
		GROUP BY tsl.slot_name
		ORDER BY
			pitl.slot_name IS NULL DESC,
			tsl.slot_name LIKE 'Main_3%' DESC,
			tsl.slot_name LIKE 'Main%' DESC,
			tsl.slot_name
		LIMIT 1";
	
	if (($result = sqlQuery($sql)) && ($row = sqlFetchAssoc($result)) && ($row['slot_name'])) {
		return $row['slot_name'];
	}
	
	return 'Main_3';
}

//Given a content item, and a module id, work out which slots have wireframes from that plugin on them
function pluginMainSlot($cID, $cType, $cVersion, $moduleId = false, $limitToOne = true, $forceLayoutId = false) {
	
	if (!$moduleId) {
		$moduleId = getModuleIdByClassName('zenario_wysiwyg_editor');
	}
	
	$sql = "
		SELECT tsl.slot_name
		FROM ". DB_NAME_PREFIX. "versions AS v
		INNER JOIN ". DB_NAME_PREFIX. "layouts AS t
		   ON t.layout_id = ". ((int) $forceLayoutId? (int) $forceLayoutId : "v.layout_id"). "
		INNER JOIN ". DB_NAME_PREFIX. "template_slot_link AS tsl
		   ON tsl.family_name = t.family_name
		  AND tsl.file_base_name = t.file_base_name
		LEFT JOIN ". DB_NAME_PREFIX. "plugin_item_link AS piil
		   ON piil.content_id = v.id
		  AND piil.content_type = v.type
		  AND piil.content_version = v.version
		  AND piil.slot_name = tsl.slot_name
		LEFT JOIN ". DB_NAME_PREFIX. "plugin_layout_link AS pitl
		   ON pitl.layout_id = t.layout_id
		  AND pitl.slot_name = tsl.slot_name
		WHERE v.id = ". (int) $cID. "
		  AND v.type = '". sqlEscape($cType). "'
		  AND v.version = ". (int) $cVersion. "
		  AND IFNULL(piil.module_id, pitl.module_id) = ". (int) $moduleId. "
		  AND IFNULL(piil.instance_id, pitl.instance_id) = 0
		GROUP BY tsl.slot_name
		ORDER BY
			pitl.slot_name IS NULL ASC,
			tsl.slot_name LIKE 'Main_3%' DESC,
			tsl.slot_name LIKE 'Main%' DESC,
			tsl.slot_name";
	
	if ($limitToOne) {
		$sql .= "
			LIMIT 1";
	}
	
	$slots = array();
	$result = sqlQuery($sql);
	while ($row = sqlFetchAssoc($result)) {
		if ($row['slot_name']) {
			if ($limitToOne) {
				return $row['slot_name'];
			} else {
				$slots[] = $row['slot_name'];
			}
		}
	}
	
	if ($limitToOne) {
		return false;
	} else {
		return $slots;
	}
}

//As above, but just use the Layout in the calculation
function pluginMainSlotOnLayout($layoutId, $moduleId = false, $limitToOne = true) {
	
	if (!$moduleId) {
		$moduleId = getModuleIdByClassName('zenario_wysiwyg_editor');
	}
	
	$sql = "
		SELECT tsl.slot_name
		FROM ". DB_NAME_PREFIX. "layouts AS t
		INNER JOIN ". DB_NAME_PREFIX. "template_slot_link AS tsl
		   ON tsl.family_name = t.family_name
		  AND tsl.file_base_name = t.file_base_name
		INNER JOIN ". DB_NAME_PREFIX. "plugin_layout_link AS pitl
		   ON pitl.layout_id = t.layout_id
		  AND pitl.slot_name = tsl.slot_name
		WHERE pitl.layout_id = ". (int) $layoutId. "
		  AND pitl.module_id = ". (int) $moduleId. "
		  AND pitl.instance_id = 0
		GROUP BY tsl.slot_name
		ORDER BY
			pitl.slot_name IS NULL ASC,
			tsl.slot_name LIKE 'Main_3%' DESC,
			tsl.slot_name LIKE 'Main%' DESC,
			tsl.slot_name";
	
	if ($limitToOne) {
		$sql .= "
			LIMIT 1";
	}
	
	$slots = array();
	$result = sqlQuery($sql);
	while ($row = sqlFetchAssoc($result)) {
		if ($row['slot_name']) {
			if ($limitToOne) {
				return $row['slot_name'];
			} else {
				$slots[] = $row['slot_name'];
			}
		}
	}
	
	if ($limitToOne) {
		return false;
	} else {
		return $slots;
	}
}


//Set up the Content Types of a module
function setupModuleContentTypesFromXMLDescription($moduleName) {
	require funIncPath(__FILE__, __FUNCTION__);
}


//Read the pagination-types of a module from an XML description
function getPluginPaginationTypesFromDescription($moduleName, &$paginationTypes) {
	
	$paginationTypes = array();
	$desc = false;
	if (!loadModuleDescription($moduleName, $desc)) {
		return false;
	}
	
	//Record any pagination types
	if (!empty($desc['pagination_types']) && is_array($desc['pagination_types'])) {
		foreach ($desc['pagination_types'] as $pagination_type) {
			if (!empty($pagination_type['function_name']) && !empty($pagination_type['label'])) {
				$paginationTypes[$moduleName. '::'. $pagination_type['function_name']] = adminPhrase($pagination_type['label']);
			}
		}
	}
	
	return true;
}


function getPluginInstanceUsageStorekeeperDeepLink($instanceId, $moduleId = false) {
	
	if (!$moduleId) {
		$instance = getPluginInstanceDetails($instanceId);
		$moduleId = $instance['module_id'];
	}
	
	return absCMSDirURL(). 'zenario/admin/organizer.php#'.
			'zenario__modules/nav/modules/panel/item//'. (int) $moduleId. '//item_buttons/view_content_items//'. (int) $instanceId. '//';
}

function getTemplateUsageStorekeeperDeepLink($layoutId) {
	return absCMSDirURL(). 'zenario/admin/organizer.php#'.
			'zenario__layouts/nav/layouts/panel/view_content//'. (int) $layoutId.  '//';
}

function getTemplateFamilyUsageStorekeeperDeepLink($templateFamily) {
	return absCMSDirURL(). 'zenario/admin/organizer.php#'.
			'zenario__layouts/nav/template_families/panel/view_content//'. encodeItemIdForStorekeeper($templateFamily).  '//';
}




//Functions for site backups and restores


function exitIfUploadError() {
	$error = arrayKey($_FILES, 'Filedata', 'error');
	switch ($error) {
		case UPLOAD_ERR_INI_SIZE:
		case UPLOAD_ERR_FORM_SIZE:
			echo adminPhrase('Your file was too large to be uploaded.');
			exit;
		case UPLOAD_ERR_PARTIAL:
		case UPLOAD_ERR_NO_FILE:
			echo adminPhrase('There was a problem whilst uploading your file.');
			exit;
		case UPLOAD_ERR_NO_TMP_DIR:
			echo 'UPLOAD_ERR_NO_TMP_DIR';
			exit;
		case UPLOAD_ERR_CANT_WRITE:
			echo 'UPLOAD_ERR_CANT_WRITE';
			exit;
		case UPLOAD_ERR_EXTENSION:
			echo 'UPLOAD_ERR_EXTENSION';
			exit;
	}
}

function initialiseBackupFunctions($includeWarnings = false) {
	
	$errors = array();
	$warnings = array();
	
	//Check the docstore directory is correctly defined, exists, and has the correct permissions
	if (!setting('docstore_dir')) {
		$errors[] = adminPhrase('_NOT_DEFINED_DOCSTORE_DIR'). '<br />'. adminPhrase('_FIX_DOCSTORE_DIR_TO_BACKUP');
	
	} else {
		//$docpath = setting('docstore_dir');
		
		//if (!file_exists($docpath)) {
			//$mrg = array('dirpath' => $docpath);
			//$errors[] = adminPhrase('_FIX_DOCSTORE_DIR_TO_BACKUP'). '<br />'. adminPhrase('_DIRECTORY_DOES_NOT_EXIST', $mrg);
		
		//} elseif (!is_readable($docpath) || !is_writeable($docpath)) {
			//$mrg = array('dirpath' => $docpath);
			//$errors[] = adminPhrase('_FIX_DOCSTORE_DIR_TO_BACKUP'). '<br />'. adminPhrase('_DIRECTORY_NOT_READ_AND_WRITEABLE', $mrg);
		//}
	}
	
	//Check the backup directory is correctly defined, exists, and has the correct permissions
	if (!setting('backup_dir')) {
		$errors[] = adminPhrase('_NOT_DEFINED_BACKUP_DIR');
	
	} else {
		$dirpath = setting('backup_dir');
		
		if (!file_exists($dirpath)) {
			$mrg = array('dirpath' => $dirpath);
			$errors[] = adminPhrase('_DIRECTORY_DOES_NOT_EXIST', $mrg);
		
		} elseif (!is_readable($dirpath) || !is_writeable($dirpath)) {
			$mrg = array('dirpath' => $dirpath);
			$errors[] = adminPhrase('_DIRECTORY_NOT_READ_AND_WRITEABLE', $mrg);
		}
	}
	
	//Check if there are any admins with management rights in the database
	$sql = "
		SELECT 1
		FROM ". DB_NAME_PREFIX. "admins AS a
		INNER JOIN ". DB_NAME_PREFIX. "action_admin_link AS aal
		   ON aal.admin_id = a.id
		WHERE a.status = 'active'
		  AND aal.action_name IN ('_ALL', '_PRIV_EDIT_ADMIN')
		LIMIT 1";
	
	$result = sqlQuery($sql);
	if (!sqlFetchRow($result)) {
		$warnings[] = adminPhrase('_NO_ADMINS_TO_BACKUP');
	}
	
	
	if ($includeWarnings) {
		$errors = array_merge($errors, $warnings);
	}
	
	return count($errors)? $errors : false;

}


function generateFilenameForBackups() {
	//Get the current date and time, and create a filename with that timestamp
	$sql = "SELECT DATE_FORMAT(NOW(), '%Y-%m-%d-%H.%i')";
	$result = sqlQuery($sql);
	$row = sqlFetchRow($result);
	return preg_replace('/[^\w-\\.]/', '', httpHost() ."-backup-". $row[0]. '-r'. getLatestRevisionNumber(). '.sql.gz');
}


//Look up the name of every table in the database which matches a certain pattern,
//and return them in an array.
//We're interested in returning tables with the patten:
	//PREFIX - i_m_p_ - table_name
function lookupImportedTables($refiner) {
	
	$refiner .= 'i_m_p_';
	
	$prefixLength = strlen($refiner);

	$importedTables = array();
	$sql = "SHOW TABLES";
	$result = sqlQuery($sql);
	
	while($row = sqlFetchRow($result)) {
		if ($refiner === false || substr($row[0], 0, $prefixLength) === $refiner) {
			$importedTables[] = $row[0];
		}
	}
	
	return $importedTables;
}

//Look up the name of every CMS table in the database, and return them in an array.
function lookupExistingCMSTables($dbUpdateSafeMode = false) {
	
	//Get a list of Modules that are installed on the site
	//Note - don't do this if the modules table might not be present
	$modules = array();
	if (!$dbUpdateSafeMode) {
		$result = getRows('modules', array('id', 'status'), array());
		while ($row = sqlFetchAssoc($result)) {
			if ($row['status'] != 'module_not_initialized') {
				$modules[$row['id']] = true;
			}
		}
	}
	
	//Get a list of tables that are used on the site
	$usedTables = array();
	foreach (array('local-DROP.sql', 'local-admin-DROP.sql') as $file) {
		if ($tables = file_get_contents(CMS_ROOT. 'zenario/admin/db_install/'. $file)) {
			foreach(preg_split('@`\[\[DB_NAME_PREFIX\]\](\w+)`@', $tables, -1,  PREG_SPLIT_DELIM_CAPTURE) as $i => $table) {
				if ($i % 2) {
					$usedTables[$table] = true;
				}
			}
		}
	}
	
	//If the count looks wrong, don't use this check
	//(There are over 50 tables used in the CMS, as per zenario/admin/db_install/local-DROP.sql and local-admin-DROP.sql)
	if (count($usedTables) < 50) {
		$usedTables = false;
	}
	

	$prefixLength = strlen(DB_NAME_PREFIX);
	
	$existingTables = array();
	$sql = "SHOW TABLES";
	$result = sqlQuery($sql);
	
	while($row = sqlFetchRow($result)) {
		//Check whether this table matches the global or the local prefix
		$matchesLocal = substr($row[0], 0, $prefixLength) === DB_NAME_PREFIX;
		
		//If we get no matches, we're not interested
		if (!$matchesLocal) {
			continue;
		}
		
		//Strip the prefix off of the tablename
		$tableName = substr($row[0], $prefixLength);
		$prefix = 'DB_NAME_PREFIX';
		
		
		$moduleId = false;
		if (substr($tableName, 0, 3) == 'mod' && ($moduleId = (int) preg_replace('/mod(\d*)_.*/', '\1', $tableName))) {
			$inUse = empty($modules) || isset($modules[$moduleId]);
		
		} else {
			$inUse = empty($usedTables) || isset($usedTables[$tableName]);
		}
		
		
		//Mark anything that begins with v_ as a view
		if (substr($tableName, 0, 2) == 'v_' || preg_match('/plg\d*_v_/', $tableName) || preg_match('/mod\d*_v_/', $tableName)) {
			$view = true;
		} else {
			$view = false;
		}
		
		//A few tables should be dropped by the "reset site" feature; mark these
		if ($view) {
			//Ignore views
			$reset = 'no';
		
		} else if ($moduleId) {
			//Any module tables should be just dropped
			$reset = 'drop';
		
		} else {
			//Any other tables should be ignored
			$reset = 'no';
		}
		
		
		//Add the table to our list
		$existingTables[] = array(
			'name' => $tableName,
			'actual_name' => $row[0],
			'prefix' => $prefix,
			'in_use' => $inUse,
			'reset' => $reset,
			'view' => $view);
	}
	
	return $existingTables;
}




//Suggest what the path of the backup/docstore/dropbox
function suggestDir($dir) {
	$root = CMS_ROOT;
	
	if (windowsServer() && strpos(CMS_ROOT, '\\') !== false && strpos(CMS_ROOT, '/') === false) {
		$s = '\\';
	} else {
		$s = '/';
	}
	
	if (defined('SUBDIRECTORY') && substr($root, -strlen(SUBDIRECTORY)) == SUBDIRECTORY) {
		$root = substr($root, 0, -strlen(SUBDIRECTORY));
	}
	
	$docroot_arr = explode($s, $root);
	array_pop($docroot_arr);
	$suggestedDir = implode($s, $docroot_arr) . $s;
	
	$suggestedDir .= $dir;
	
	return $suggestedDir;
}


function apacheMaxFilesize() {
	$postMaxSize = (int) preg_replace('/\D/', '', ini_get('post_max_size'));
	$postMaxSizeMag = strtoupper(preg_replace('/\d/', '', ini_get('post_max_size')));
	
	$uploadMaxFilesize = (int) preg_replace('/\D/', '', ini_get('upload_max_filesize'));
	$uploadMaxFilesizeMag = strtoupper(preg_replace('/\d/', '', ini_get('upload_max_filesize')));
	
	switch ($postMaxSizeMag) {
		case 'G':
			$postMaxSize *= 1024;
		case 'M':
			$postMaxSize *= 1024;
		case 'K':
			$postMaxSize *= 1024;
	}
	
	switch ($uploadMaxFilesizeMag) {
		case 'G':
			$uploadMaxFilesize *= 1024;
		case 'M':
			$uploadMaxFilesize *= 1024;
		case 'K':
			$uploadMaxFilesize *= 1024;
	}
	
	if ($postMaxSize < $uploadMaxFilesize) {
		return $postMaxSize;
	} else {
		return $uploadMaxFilesize;
	}
}


//Scan and Write the Docstore Directory
	//Note: back when the backups included the docstore directory, this used to be used to add the files
	//into the backup
//function writeDocstoreDirectory(&$gzFile, $dir = '.[[SITENAME]].') {
//	
//	//Scan the current directory
//	foreach (scandir(docstoreDirectoryPath($dir)) as $name) {
//		$part = $dir. '/'. $name;
//		
//		if ($name != '.' && $name != '..') {
//			//If we find a directory, write it down then scan it too.
//			if (is_dir(docstoreDirectoryPath($part))) {
//				gzwrite($gzFile, "DIR;\n". $part. ";\n");
//				writeDocstoreDirectory($gzFile, $part);
//			
//			//If we find a file, write down it's name and path, then write down
//			//its contents (in hexadecimal)
//			} elseif (is_file(docstoreDirectoryPath($part))) {
//				gzwrite($gzFile, "FILE;\n". $part. ";\n");
//				
//				$f = fopen(docstoreDirectoryPath($part), 'rb');
//				while ($chunk = fread($f, 1000)) {
//					gzwrite($gzFile, bin2hex($chunk));
//				}
//				fclose($f);
//				
//				gzwrite($gzFile, ";\n");
//			}
//		}
//	}
//
//}

//Start a new SQL statement if we see the statement that we are working on get longer than this:
define('MYSQL_CHUNK_SIZE', 3000);

//Create a backup of the database
function createDatabaseBackupScript(&$gzFile) {
	require funIncPath(__FILE__, __FUNCTION__);
}





//Reverse of the function bin2hex; converts hexadecimal back to binary
if (!function_exists('hex2bin')) {
	function hex2bin($hex) {
		return pack('H*', $hex);
	}
}

//Define some constants to make the states clearer below
define ('NEXTPLEASE', 1);
define ('CREATEDIR', 2);
define ('FILENAME', 3);
define ('FILECONTENTS', 4);

define ('READ_BACKUP_CHUNK_SIZE', 10000);

//Given a backup, restore the database from it
function restoreDatabaseFromBackup($filename, $plainSql, $prefix, &$failures) {
	return require funIncPath(__FILE__, __FUNCTION__);
}


//Reset a site, putting all of its tables back to an initial state
function resetSite() {
	
	//Make sure to load the values of site_disabled_title and site_disabled_message,
	//which aren't usually loaded into memory, so we can restore them later.
	//Also save the values of email_address_admin, email_address_from and email_name_from
	//which restoreLocationalSiteSettings() doesn't cover.
	$site_disabled_title = setting('site_disabled_title');
	$site_disabled_message = setting('site_disabled_message');
	$email_address_admin = setting('email_address_admin');
	$email_address_from = setting('email_address_from');
	$email_name_from = setting('email_name_from');
	
	//Delete all module tables
	foreach (lookupExistingCMSTables() as $table) {
		if ($table['reset'] == 'drop') {
			$sql = "DROP TABLE `". $table['actual_name']. "`";
			sqlQuery($sql);
		}
	}
	
	//look up the revision numbers of the admin tables from the local_revision_numbers table
	$sql = "
		SELECT `path`, revision_no
		FROM ". DB_NAME_PREFIX. "local_revision_numbers
		where patchfile = 'admin.inc.php'";
	$revisions = array();
	$result = sqlQuery($sql);
	while ($row = sqlFetchAssoc($result)) {
		$revisions[] = $row;
	}
	
	//Rerun some of the scripts from the installer to give us a blank site
	require_once CMS_ROOT. 'zenario/includes/welcome.inc.php';
	$error = false;
	(runSQL(false, 'local-DROP.sql', $error)) &&
	(runSQL(false, 'local-CREATE.sql', $error)) &&
	(runSQL(false, 'local-INSERT.sql', $error));
	
	
	//Add the admin-related revision numbers back in
	foreach ($revisions as &$revision) {
		$sql = "
			REPLACE INTO ". DB_NAME_PREFIX. "local_revision_numbers SET
				patchfile = 'admin.inc.php',
				`path` = '". sqlEscape($revision['path']). "',
				revision_no = ". (int) $revision['revision_no'];
		@sqlSelect($sql);
	}
	
	//Populate the Modules table with all of the Modules in the system,
	//and install and run any Modules that should running by default.
	addNewModules($skipIfFilesystemHasNotChanged = false, $runModulesOnInstall = true, $dbUpdateSafeMode = true);
	
	restoreLocationalSiteSettings();
	setSetting('site_disabled_title', $site_disabled_title);
	setSetting('site_disabled_message', $site_disabled_message);
	setSetting('email_address_admin', $email_address_admin);
	setSetting('email_address_from', $email_address_from);
	setSetting('email_name_from', $email_name_from);
	
	if ($error) {
		echo $error;
		exit;
	
	} else {
		//Give the newly reset site a new key, and log the admin in
		setSetting('site_id', generateRandomSiteIdentifierKey());
		setAdminSession(session('admin_userid'), session('admin_global_id'));
		
		return true;
	}
}

function restoreLocationalSiteSettings() {
	//Attempt to keep the directory, primary domain and ssl site settings from the existing installation or the installer,
	//as the chances are that their values in the backup will be wrong
	foreach (array(
		'backup_dir', 'docstore_dir',
		'primary_domain', 'use_cookie_free_domain', 'cookie_free_domain',
		'admin_use_ssl', 'admin_use_ssl_port'
	) as $setting) {
		$sql = "
			INSERT INTO ". DB_NAME_PREFIX. "site_settings
			SET name = '". sqlEscape($setting). "',
				value = '". sqlEscape(arrayKey(cms_core::$siteConfig, $setting)). "'
			ON DUPLICATE KEY UPDATE
				value = '". sqlEscape(arrayKey(cms_core::$siteConfig, $setting)). "'";
		@sqlSelect($sql);
	}
	
	$sql = "
		DELETE FROM ". DB_NAME_PREFIX. "site_settings
		WHERE name IN ('css_js_version', 'module_code_hash', 'module_description_hash')";
	@sqlSelect($sql);
}

//This function generates a random key which can be used to identify a site.
//Not intended to be secure; it's more to prevent Admins accidently causing bad data
//(e.g. by restoring a backup on which they don't have an account and continuing to use the site,
//or installing two sites in different directories on the same domain, and switching between the two)
function generateRandomSiteIdentifierKey() {
	return substr(base64_encode(microtime()), 3);
}




function convertMySQLToJqueryDateFormat($value) {
	return str_replace(
		array('[[_MONTH_SHORT_%m]]', '%Y', '%y', '%c', '%m', '%e', '%d'),
		array('M', 'yy', 'y', 'm', 'mm', 'd', 'dd'),
		$value);
}

//Format the values for the date-format select lists in the installer and the site-settings
function formatDateFormatSelectList(&$field, $javascript = false) {
	
	//	//Check the current date
	//	$dd = date('d');
	//	$mm = date('m');
	//	$yy = date('y');
	//
	//	//Would the current date be a good example date (i.e. the day/month/year must all be different numbers)?
	//	if ($dd == $mm || $dd == $yy || $yy == $mm) {
	//		//If not, use a different sample
	//		$exampleDate = '1999-09-19';
	//	} else {
	//		//If so, use the current date
	//		$exampleDate = date('Y-m-d');
	//	}
	
	$exampleDate = date('Y-m-d');
	$ddmmyyyyDate = '2222-03-04';
	
	//Ensure the chosen value is in the list of values!
	if (!empty($field['value'])
	 && empty($field['values'][$field['value']])) {
		$field['values'][$field['value']] = array();
	}
	
	foreach ($field['values'] as $value => &$details) {
		if ($javascript) {
			$value = str_replace(
				array('yy', 'y', 'm', '%c%c', 'd', '%e%e', 'M'),
				array('%Y', '%y', '%c', '%m', '%e', '%d', '%b'),
				$value);
		}
		$value = str_replace(
			array('[[_WEEKDAY_%w]]', '[[_MONTH_LONG_%m]]', '[[_MONTH_SHORT_%m]]'),
			array('%W', '%M', '%b'),
			$value);
		
		$sql = "SELECT DATE_FORMAT('" . sqlEscape($exampleDate) . "', '" . sqlEscape($value) . "')";
		$result = sqlQuery($sql);
		$row = sqlFetchRow($result);
		$example = $row[0];
		
		$sql = "SELECT DATE_FORMAT('" . sqlEscape($ddmmyyyyDate) . "', '" . sqlEscape($value) . "')";
		$result = sqlQuery($sql);
		$row = sqlFetchRow($result);
		$ddmmyyy = str_replace(
			array('04', '4', '03', '3', '2', 'Monday', 'Mon', 'March', 'Mar'),
			array('dd', 'd', 'mm', 'm', 'y', 'Day', 'Day', 'Month', 'Mon'),
			$row[0]);
		
		$details['label'] = $example. ' ('. $ddmmyyy. ')';
	}
}


//Generic handler for misc. AJAX requests from admin boxes
function handleAdminBoxAJAX() {
	
	if (request('fileUpload')) {
		exitIfUploadError();
		putUploadFileIntoCacheDir($_FILES['Filedata']['name'], $_FILES['Filedata']['tmp_name'], request('_html5_backwards_compatibility_hack'));
	
	} else if (request('fetchFromDropbox')) {
		putDropboxFileIntoCacheDir(post('name'), post('link'));
	
	} else {
		exit;
	}
}

//See also: function getPathOfUploadedFileInCacheDir()

function putDropboxFileIntoCacheDir($filename, $dropboxLink) {
	putUploadFileIntoCacheDir($filename, false, false, $dropboxLink);
}

function putUploadFileIntoCacheDir($filename, $tempnam, $html5_backwards_compatibility_hack = false, $dropboxLink = false) {
	if (!checkDocumentTypeIsAllowed($filename)) {
		echo
			'<p>', adminPhrase('You must select a known file format, for example .doc, .docx, .jpg, .pdf, .png or .xls.'), '</p>',
			'<p>', adminPhrase('Please also check that your filename does not contain any of the following characters:'), '</p>',
			'<pre>', htmlspecialchars('\\ / : * ? " < > |'), '</pre>';
		exit;
	}
	
	if ($tempnam) {
		$sha = sha1_file($tempnam);
	} elseif ($dropboxLink) {
		$sha = sha1($dropboxLink);
	} else {
		exit;
	}
	
	if (!cleanDownloads()
	 || !($dir = createCacheDir($sha, 'uploads', false))) {
		echo adminPhrase('Could not upload this file because your cache/ directory is not writable!');
		exit;
	}
	
	$file = array();
	$file['filename'] = $filename;
	
	//Check if the file is already uploaded
	if (!file_exists($path = CMS_ROOT. $dir. $file['filename'])
	 || !filesize($path = CMS_ROOT. $dir. $file['filename'])) {
		
		if ($dropboxLink) {
			$failed = true;
		
			touch($path);
			chmod($path, 0666);
		
			//Attempt to use wget to fetch the file
			if (!windowsServer()) {
				try {
					//Don't fetch via ssh, as this doesn't work when calling wget from php
					$httpDropboxLink = str_replace('https://', 'http://', $dropboxLink);
					
					//$output = $return_var = false;
					//$return = exec('wget '. escapeshellarg($httpDropboxLink). ' -O '. escapeshellarg($path), $output, $return_var);
					//var_dump($output);
					//var_dump($return_var);
					//var_dump($return);
					//var_dump($path);
					//var_dump(filesize($path));
					
					exec('wget -q '. escapeshellarg($httpDropboxLink). ' -O '. escapeshellarg($path));
					
					$failed = false;
		
				} catch (Exception $e) {
					//echo 'Caught exception: ',  $e->getMessage(), "\n";
				}
			}
		
			//If that didn't work, try using php
			if ($failed || !filesize($path)) {
				$in = fopen($dropboxLink, 'r');
				$out = fopen($path, 'w');
				while (!feof($in)) {
					fwrite($out, fread($in, 65536));
				}
				fclose($out);
				fclose($in);
			}
		
			if ($failed || !filesize($path)) {
				echo adminPhrase('Could not get the file from Dropbox!');
				exit;
			}
		} else {
			move_uploaded_file($tempnam, $path);
		}
	}
	
	if ($image = getimagesize($path)) {
		$file['width'] = $image[0];
		$file['height'] = $image[1];
		
		$file['id'] = encodeItemIdForStorekeeper($sha. '/'. $file['filename']. '/'. $file['width']. '/'. $file['height']);
	} else {
		$file['id'] = encodeItemIdForStorekeeper($sha. '/'. $file['filename']);
	}
	
	$file['link'] = 'zenario/file.php?getUploadedFileInCacheDir='. $file['id'];
	
	if ($html5_backwards_compatibility_hack) {
		echo '
			<html>
				<body>
					<script type="text/javascript">
						self.parent.zenarioAB.uploadComplete([', json_encode($file), ']);
					</script>
				</body>
			</html>';
	} else {
		header('Content-Type: text/javascript; charset=UTF-8');
		//jsonEncodeForceObject($tags);
		echo json_encode($file);
	}
	
	exit;
}




function deleteImage($id) {
	$sql = "DELETE 
			FROM " . DB_NAME_PREFIX . "images
			WHERE id = " . (int) $id;
			
	$result = sqlQuery($sql);
}

function deleteImageByCheckSum($checksum, $cID = false, $cType = false, $cVersion = false) {
	$sql = "
		DELETE 
		FROM ". DB_NAME_PREFIX. "images
		WHERE checksum_inc_name = '". sqlEscape($checksum). "'";
	
	if ($cID) {
		if (!$cVersion) {
			$cVersion = getLatestVersion($cID, $cType);
		}
		
		$sql .= "
			AND content_item_id = ". (int) $cID. "
			AND content_item_type = '". sqlEscape($cType). "'
			AND content_item_version = ". (int) $cVersion;
	}
			
	$result = sqlQuery($sql);
}

function deleteMovieByCheckSum($checksum, $cID = false, $cType = false, $cVersion = false) {
	$sql = "
		DELETE 
		FROM ". DB_NAME_PREFIX. "movies
		WHERE checksum_inc_name = '". sqlEscape($checksum). "'";
	
	if ($cID) {
		if (!$cVersion) {
			$cVersion = getLatestVersion($cID, $cType);
		}
		
		$sql .= "
			AND content_item_id = ". (int) $cID. "
			AND content_item_type = '". sqlEscape($cType). "'
			AND content_item_version = ". (int) $cVersion;
	}
			
	$result = sqlQuery($sql);
}






//Include extranet functionality
function getGroupDBDetails($groupId) {

	$sql = "
		SELECT id, name, public, allow_opt_in_opt_out, members_are_employees, active
		FROM ". DB_NAME_PREFIX. "groups
		WHERE id = ". (int) $groupId;
	
	$result = sqlQuery($sql);
	return sqlFetchAssoc($result);
}






//Validate the input to a form about user details
function validateGroup($groupDetails) {
	
	$errArray = array();
	
	if(isset($groupDetails['name']) && !$groupDetails['name']) {
		$errArray[] = adminPhrase('_MSG_ENTER_NAME_GROUP');
	}
	
	if (isset($groupDetails['name']) && alreadyExistsGroupName($groupDetails['name'], $groupDetails['groupId'])) {
		$errArray[] = adminPhrase('_MSG_NAME_GROUP_ALREADY_EXISTS');
	}
	
	return array('valid' => !count($errArray), 'errArray' => $errArray);
}


function alreadyExistsGroupName($name,  $groupId = false) {
	$groupId =  $groupId;
	$sql = "
		SELECT name
		FROM ".DB_NAME_PREFIX."groups
		WHERE name = '" . sqlEscape( $name ) . "'";
	if ($groupId) {
		$sql .= "
			AND id <> " . (int) $groupId;
	}
	$result = sqlQuery($sql);
	if ( $row = sqlFetchArray( $result ) ) {
		return true;
	} else {
		return false;
	}
}

//Smart group description function 
function getSmartGroupDescription($smartGroupId) {
	return require funIncPath(__FILE__, __FUNCTION__);
}

//Generate a hierarchical select list from a table with a parent_id column
function generateHierarchicalSelectList($table, $labelCol, $parentIdCol = 'parent_id', $ids = array(), $orderBy = array(), $flat = false, $parentId = 0, $pad = '    ') {
	$output = array();
	$cols = array($labelCol, $parentIdCol);
	generateHierarchicalListR($output, $table, $cols, $parentIdCol, $ids, $orderBy, $parentId, 0);
	
	$ord = 0;
	foreach ($output as &$row) {
		$row = array(
			'ord' => ++$ord,
			'label' => str_repeat($pad, $row['level']). $row[$labelCol]);
		
		if ($flat) {
			$row = $row['label'];
		}
	}
	
	return $output;
}

//Generate a hierarchical list from a table with a parent_id column
function generateHierarchicalList($table, $cols = array(), $parentIdCol = 'parent_id', $ids = array(), $orderBy = array(), $parentId = 0) {
	$output = array();
	
	if (!is_array($cols)) {
		$cols = array($cols);
	}
	if (!in_array($parentIdCol, $cols)) {
		$cols[] = $parentIdCol;
	}
	
	generateHierarchicalListR($output, $table, $cols, $parentIdCol, $ids, $orderBy, $parentId, 0);
	
	return $output;
}

function generateHierarchicalListR(&$output, $table, $cols, $parentIdCol, $ids, $orderBy, $parentId, $level) {
	
	$ids[$parentIdCol] = $parentId;
	
	foreach (getRowsArray($table, $cols, $ids, $orderBy) as $id => $row) {
		//This line in theory shouldn't be needed if the data integrety is good,
		//but I have included it to stop infinite loops if it is not!
		if (!isset($output[$id])) {
			$row['level'] = $level;
			$output[$id] = $row;
			
			generateHierarchicalListR($output, $table, $cols, $parentIdCol, $ids, $orderBy, $id, $level + 1);
		}
	}
	
}

function generateDocumentFolderSelectList($flat = false) {
	return generateHierarchicalSelectList('documents', 'folder_name', 'folder_id', array('type' => 'folder'), 'ordinal', $flat);
}



function registerDataset(
	$label, $table, $system_table = '',
	$extends_admin_box = '', $extends_organizer_panel = '',
	$view_priv = '', $edit_priv = ''
) {
	return 
		setRow(
			'custom_datasets',
			array(
				'label' => $label,
				'system_table' => $system_table,
				'extends_admin_box' => $extends_admin_box,
				'extends_organizer_panel' => $extends_organizer_panel,
				'view_priv' => $view_priv,
				'edit_priv' => $edit_priv),
			array('table' => $table));
}

function registerDatasetSystemField($datasetId, $type, $tabName, $fieldName, $dbColumn = false, $validation = 'none', $valuesSource = '') {
	
	if ($dbColumn === false) {
		$dbColumn = $fieldName;
	}
	
	//Try to catch the case where a system field was automatically registered
	if ($fieldId = getRow(
		'custom_dataset_fields',
		'id',
		array(
			'dataset_id' => $datasetId,
			'tab_name' => $tabName,
			'field_name' => $fieldName,
			'is_system_field' => 1)
	)) {
		
		//In this case, update the existing record and register it properly
		updateRow(
			'custom_dataset_fields',
			array(
				'type' => $type,
				'db_column' => $dbColumn,
				'validation' => $validation,
				'values_source' => $valuesSource),
			$fieldId
		);
		
		return $fieldId;
	
	} else {
		//Otherwise register a new field
		return 
			setRow(
				'custom_dataset_fields',
				array(
					'type' => $type,
					'tab_name' => $tabName,
					'field_name' => $fieldName,
					'validation' => $validation,
					'values_source' => $valuesSource),
				array('dataset_id' => $datasetId, 'db_column' => $dbColumn, 'is_system_field' => 1));
	}
}

function deleteDatasetField($fieldId) {
	if (($field = getDatasetFieldDetails($fieldId))
	 && ($dataset = getDatasetDetails($field['dataset_id']))
	 && (!$field['protected'])
	 && (!$field['is_system_field'])) {
		
		sendSignal('eventDatasetFieldDeleted', array('datasetId' => $dataset['id'], 'fieldId' => $field['id']));
		
		if ($field['type'] == 'checkboxes') {
			$sql = "
				DELETE fv.*, vl.*
				FROM ". DB_NAME_PREFIX. "custom_dataset_field_values AS fv
				INNER JOIN ". DB_NAME_PREFIX. "custom_dataset_values_link AS vl
				ON vl.value_id = fv.id
				WHERE fv.field_id = ". (int) $field['id'];
			sqlQuery($sql);
		} else {
			$sql = "
				ALTER TABLE `". DB_NAME_PREFIX. sqlEscape($dataset['table']). "`
				DROP COLUMN `". sqlEscape($field['db_column']). "`";
			sqlQuery($sql);
		}
		
		deleteRow('custom_dataset_fields', $field['id']);
		deleteRow('custom_dataset_field_values', array('field_id' => $field['id']));
		
		return true;
	} else {
		return false;
	}
}

function getDatasetFieldDefinition($type) {
	
	switch ($type) {
		case 'checkboxes':
			return '';
		
		case 'checkbox':
		case 'group':
			return " tinyint(1) NOT NULL default 0";
		
		case 'radios':
		case 'select':
			return " int(10) unsigned NOT NULL default 0";
		
		case 'centralised_radios':
		case 'centralised_select':
		case 'text':
		case 'url':
			return " varchar(255) NOT NULL default ''";
		
		case 'editor':
		case 'textarea':
			return " TEXT";
		
		case 'date':
			return " date";
		
		default:
			return false;
	}
}

function listCustomFields($dataset, $flat = true, $filter = false, $customOnly = true) {
	$dataset = getDatasetDetails($dataset);
	
	$key = array('dataset_id' => $dataset['id']);
	
	if (is_array($filter)) {
		$key['type'] = $filter;
	} else {
		switch ($filter) {
			//enum('group','checkbox','checkboxes','date','editor','radios','centralised_radios','select','centralised_select','text','textarea','url','other_system_field')
		
			case 'boolean_and_list_only':
				$key['type'] = array('checkbox', 'checkboxes', 'radios', 'select', 'centralised_radios', 'centralised_select');
				break;
		
			case 'group_boolean_and_list_only':
				$key['type'] = array('group', 'checkbox', 'checkboxes', 'radios', 'select', 'centralised_radios', 'centralised_select');
				break;
		
			case 'text_only':
				$key['type'] = array('text', 'textarea', 'url');
				break;
		
			case 'country_only':
				$key['type'] = array('centralised_radios', 'centralised_select');
				$key['values_source'] = 'zenario_country_manager::getActiveCountries';
				break;
		
			case 'boolean_and_groups_only':
				$key['type'] = array('checkbox', 'group');
				break;
		
			case 'groups_only':
				$key['type'] = 'group';
				break;
		
			default:
				$key['type'] = array('!' => 'other_system_field');
		}
	}
	
	if ($customOnly) {
		$key['is_system_field'] = 0;
	}
	
	if ($flat) {
		$columns = array('label', 'field_name');
	} else {
		$columns = true;
	}
	
	$fields = getRowsArray('custom_dataset_fields', $columns, $key, array('field_name', 'label'));
	
	$i = 0;
	foreach ($fields as &$field) {
		if (!$customOnly) {
			if ($field['label'] && $field['field_name']) {
				$field['label'] = adminPhrase('System field [[field_name]] ("[[label]]")', $field);
			
			} elseif ($field['field_name']) {
				$field['label'] = adminPhrase('System field [[field_name]]', $field);
			} else {
				$field['label'] = adminPhrase('Custom field "[[label]]"', $field);
			}
		}
		
		if ($flat) {
			$field = $field['label'];
		} else {
			$field['ord'] = ++$i;
		}
	}
	
	return $fields;
}

//Given a field, get details on each of its child-fields.
function getCustomFieldsChildren($field, &$fields) {
	
	$sql = "
		SELECT *
		FROM ". DB_NAME_PREFIX. "custom_dataset_fields
		WHERE dataset_id = ". (int) $field['dataset_id']. "
		  AND parent_id = ". (int) $field['id']. "
		  AND is_system_field = 0";
	$result = sqlQuery($sql);
	while ($child = sqlFetchAssoc($result)) {
		if (!isset($fields[$child['id']])) {
			$fields[$child['id']] = $child;
			getCustomFieldsChildren($child, $fields);
		}
	}
}

function getCustomTabsParents($tab, &$fields) {
	$tab['parent_id'] = $tab['parent_field_id'];
	getCustomFieldsParents($tab, $fields);
}

function getCustomFieldsParents($field, &$fields) {
	if (empty($field['parent_id'])) {
		return;
	}
	
	$sql = "
		SELECT *
		FROM ". DB_NAME_PREFIX. "custom_dataset_fields
		WHERE dataset_id = ". (int) $field['dataset_id']. "
		  AND id = ". (int) $field['parent_id']. "
		  AND is_system_field = 0";
	
	$result = sqlQuery($sql);
	if ($parent = sqlFetchAssoc($result)) {
		if (!isset($fields[$parent['id']])) {
			$fields[$parent['id']] = $parent;
			getCustomFieldsParents($parent, $fields);
		}
	}
}

/*
function getCustomFieldsParents($field, &$checkedFields) {
	
	if ($field['parent_id'] && !isset($checkedFields[$field['parent_id']])) {
		
	}
	
	while ($field['parent_id']
	
	if ($field['parent_id']) {
		
	}
	
}
*/

function checkColumnExistsInDB($table, $column) {
	$sql = "
		SHOW COLUMNS
		FROM `". DB_NAME_PREFIX. sqlEscape($table). "`
		WHERE Field = '". sqlEscape($column). "'";
	return ($result = sqlQuery($sql)) && (sqlFetchRow($result));
}

function createDatasetFieldInDB($fieldId, $oldName = false) {
	if (($field = getDatasetFieldDetails($fieldId))
	 && ($dataset = getDatasetDetails($field['dataset_id']))) {
		
		$exists = checkColumnExistsInDB($dataset['table'], $field['db_column']);
		
		$oldExists = false;
		if (!$exists && $oldName && $oldName != $field['db_column']) {
			$oldExists = checkColumnExistsInDB($dataset['table'], $oldName);
		}
		
		$keys = array();
		if (($exists && ($columnName = $field['db_column']))
		 || ($oldExists && ($columnName = $oldName))) {
			
			$sql = "
				SHOW KEYS
				FROM `". DB_NAME_PREFIX. sqlEscape($dataset['table']). "`
				WHERE Column_name = '". sqlEscape($columnName). "'";
			$result = sqlQuery($sql);
			while ($row = sqlFetchAssoc($result)) {
				if ($row['Key_name'] != 'PRIMARY') {
					$keys[$row['Key_name']] = $row['Key_name'];
				}
			}
		}
		
		
		//Start building the query we'll need
		$sql = "
			ALTER TABLE `". DB_NAME_PREFIX. sqlEscape($dataset['table']). "`";
		
		if ($field['type'] == 'checkboxes') {
			if ($exists) {
				//Make sure checkboxes don't have columns defined
				$sql .= "
					DROP COLUMN";
			
			} else {
				//nothing doing for checkboxes
				return;
			}
			
		} else {
			//Rename an existing column
			if ($oldExists) {
				$sql .= "
					CHANGE COLUMN `". sqlEscape($oldName). "` ";
			
			//Modify an existing column
			} elseif ($exists) {
				$sql .= "
					MODIFY COLUMN";
			
			//Create a new column
			} else {
				$sql .= "
					ADD COLUMN";
			}
		}
		
		$sql .= " `". sqlEscape($field['db_column']). "`";
		
		$def = getDatasetFieldDefinition($field['type']);
		
		if ($def === false) {
			echo adminPhrase('Error: bad field type!');
			exit;
		} else {
			$sql .= $def;
		}
		
		//Drop any existing keys
		foreach ($keys as $key) {
			$sqlK = "
				ALTER TABLE `". DB_NAME_PREFIX. sqlEscape($dataset['table']). "`
				DROP KEY `". sqlEscape($key). "`";
			sqlQuery($sqlK);
		}
		
		//Update the column
		sqlQuery($sql);
		
		
		//Add a key if needed
		if ($field['type'] != 'checkboxes'
		 && ($field['sortable'])) {
			$sql = "
				ALTER TABLE `". DB_NAME_PREFIX. sqlEscape($dataset['table']). "`
				ADD KEY (`". sqlEscape($field['db_column']). "`";
			
			if ($field['type'] == 'editor' || $field['type'] == 'textarea') {
				$sql .= "(255)";
			}
			
			$sql .= ")";
			sqlQuery($sql);
		}
	}
}


//Admin AJAX functions
function zenarioAJAXShortenPath($path, $type) {
	if (false) {
		$path =
			str_replace('/panel/', '/',
			str_replace('/panels/', '/',
			str_replace('/nav/', '/',
			str_replace('/collection_buttons/', '/',
			str_replace('/item_buttons/', '/',
			str_replace('/inline_buttons/', '/',
			str_replace('/hidden_nav/', '/',
			str_replace('/items/', '/',
				$path
			))))))));
	}
	
	return $path;
}



//A recursive function that comes up with a list of all of the Storekeeper paths that a TUIX file references
function logTUIXFileContentsR(&$paths, &$tags, $type, $path = '') {
	
	if (is_array($tags)) {
		if (!empty($tags['panel'])) {
			$shortPath = zenarioAJAXShortenPath($path. '/panel', $type);
			$paths[$shortPath] = true;
		}
		if (!empty($tags['panels']) && is_array($tags['panels'])) {
			foreach ($tags['panels'] as $panelName => &$panel) {
				$shortPath = zenarioAJAXShortenPath($path. '/panels/'. $panelName, $type);
				$paths[$shortPath] = true;
			}
		}
		
		foreach ($tags as $tagName => &$tag) {
			if ($path === '') {
				$thisPath = $tagName;
			} else {
				$thisPath = $path. '/'. $tagName;
			}
			logTUIXFileContentsR($paths, $tag, $type, $thisPath);
		}
	}
}



//Scan the TUIX files, and come up with a list of what paths are in what files
function logTUIXFileContents(&$modules) {
	
	$tuixFiles = array();
	$result = getRows('tuix_file_contents', true, array());
	while ($tf = sqlFetchAssoc($result)) {
		$key = $tf['module_class_name']. '/'. $tf['type']. '/'. $tf['filename'];
		$key2 = $tf['path']. '//'. $tf['setting_group'];
		
		if (empty($tuixFiles[$key])) {
			$tuixFiles[$key] = array();
		}
		$tuixFiles[$key][$key2] = $tf;
	}
	
	$contents = array();
	foreach ($modules as $module) {
		foreach (array('admin_boxes', 'admin_toolbar', 'slot_controls', 'storekeeper', 'organizer') as $type) {
			if ($dir = moduleDir($module['class_name'], 'tuix/'. $type. '/', true, false, false)) {
				
				if ($type == 'organizer') {
					$type = 'storekeeper';
				}
				
				foreach (scandir($dir) as $file) {
					if (substr($file, 0, 1) != '.') {
						$key = $module['class_name']. '/'. $type. '/'. $file;
						$filemtime = null;
						$md5_file = null;
						$changes = true;
						$first = true;
						
						//Check the modification time and the checksum of the file. If either are the same as before,
						//there's no need to update this row.
						if (!empty($tuixFiles[$key])) {
							foreach ($tuixFiles[$key] as $key2 => &$tf) {
								
								//Note that this is an array of arrays, but I only need to check the first one
								if ($first) {
									$filemtime = filemtime($dir. $file);
									
									if ($tf['last_modified'] == $filemtime) {
										$changes = false;
									
									} else {
										$md5_file = md5_file($dir. $file);
										
										if ($tf['checksum'] == $md5_file) {
											$changes = false;
										}
									}
								}
								
								//Note that this is an array of arrays, but I only need to check the first one
								if (!$changes) {
									$tf['status'] = 'unchanged';
								}
							}
							unset($tf);
							
							if (!$changes) {
								continue;
							}
						} else {
							$tuixFiles[$key] = array();
						}
						
						//If there have been changes, or if this is the first time we've seen this file,
						//read it, then loop through it looking for all of the TUIX paths it contains
							//Note that as we know there are changes, I'm overriding the normal timestamp logic in zenarioReadTUIXFile()
						if (($tags = zenarioReadTUIXFile($dir. $file, false))
						 && (!empty($tags))
						 && (is_array($tags))) {
						 	
						 	if ($filemtime === null) {
						 		$filemtime = filemtime($dir. $file);
						 	}
						 	if ($md5_file === null) {
						 		$md5_file = md5_file($dir. $file);
						 	}
						 	
						 	$pathsFound = false;
							if ($type == 'storekeeper' || $type == 'organizer') {
								//For Storekeeper, run zenarioAJAXShortenPath() to get their short paths
								$paths = array();
								logTUIXFileContentsR($paths, $tags, $type);
								
								foreach ($paths as $path => $dummy) {
									$pathsFound = true;
									$settingGroup = '';
									
									$key2 = $path. '//'. $settingGroup;
									$tuixFiles[$key][$key2] = array(
										'type' => $type,
										'path' => $path,
										'setting_group' => $settingGroup,
										'module_class_name' => $module['class_name'],
										'filename' => $file,
										'last_modified' => $filemtime,
										'checksum' => $md5_file,
										'status' => empty($tuixFiles[$key][$key2])? 'new' : 'updated'
									);
								}
							}
							
							if (!$pathsFound) {
								//For anything else, just read the top-level path
								//Note - also do this for Storekeeper if no paths were found above,
								//as logTUIXFileContentsR() will miss files that have navigation definitions but no panel definitions
								foreach ($tags as $path => &$tag) {
									
									$settingGroup = '';
									if ($type == 'admin_boxes') {
										if ($path == 'plugin_settings' && !empty($tag['module_class_name'])) {
											$settingGroup = $tag['module_class_name'];
										
										} elseif ($path == 'advanced_search' && !empty($tag['storekeeper_path'])) {
											$settingGroup = $tag['storekeeper_path'];
										
										} elseif ($path == 'site_settings' && !empty($tag['setting_group'])) {
											$settingGroup = $tag['setting_group'];
										}
									
									} elseif ($type == 'slot_controls') {
										if (!empty($tag['module_class_name'])) {
											$settingGroup = $tag['module_class_name'];
										}
									}
									
									$key2 = $path. '//'. $settingGroup;
									$tuixFiles[$key][$key2] = array(
										'type' => $type,
										'path' => $path,
										'setting_group' => $settingGroup,
										'module_class_name' => $module['class_name'],
										'filename' => $file,
										'last_modified' => $filemtime,
										'checksum' => $md5_file,
										'status' => empty($tuixFiles[$key][$key2])? 'new' : 'updated'
									);
								}
							}
						}
						unset($tags);
					}
				}
			}
		}
	}
	
	
	
	//Loop through the array we've generated, and take actions as appropriate
	foreach ($tuixFiles as $key => &$tuixFile) {
		foreach ($tuixFile as $key2 => $tf) {
			
			//Where we could no longer find files, delete them
			if (empty($tf['status'])) {
				$sql = "
					DELETE FROM ". DB_NAME_PREFIX. "tuix_file_contents
					WHERE type = '". sqlEscape($tf['type']). "'
					  AND path = '". sqlEscape($tf['path']). "'
					  AND setting_group = '". sqlEscape($tf['setting_group']). "'
					  AND module_class_name = '". sqlEscape($tf['module_class_name']). "'
					  AND filename = '". sqlEscape($tf['filename']). "'";
				sqlSelect($sql);
			
			//Add/update newly added/edited files
			} else if ($tf['status'] != 'unchanged') {
				$sql = "
					INSERT INTO ". DB_NAME_PREFIX. "tuix_file_contents
					SET type = '". sqlEscape($tf['type']). "',
						path = '". sqlEscape($tf['path']). "',
						setting_group = '". sqlEscape($tf['setting_group']). "',
						module_class_name = '". sqlEscape($tf['module_class_name']). "',
						filename = '". sqlEscape($tf['filename']). "',
						last_modified = ". (int) $tf['last_modified']. ",
						checksum = '". sqlEscape($tf['checksum']). "'
					ON DUPLICATE KEY UPDATE
						last_modified = VALUES(last_modified),
						checksum = VALUES(checksum)";
				sqlSelect($sql);
			}
			
		}
	}
}






//This function scans the Module directory for Modules with certain TUIX files, reads them, and turns them into a php array
	//You should initialise $modules and $tags to empty arrays before calling this function.
function loadTUIX(&$modules, &$tags, $type, $requestedPath = '', $settingGroup = '', $compatibilityClassNames = false) {
	$modules = array();
	$tags = array();
	
	//Ensure that the core plugin is included first, if it is there...
	$modules['zenario_common_features'] = array();
	
	if ($type == 'welcome') {
		foreach (scandir($dir = CMS_ROOT. 'zenario/admin/welcome/') as $file) {
			if (substr($file, 0, 1) != '.') {
				$tagsToParse = zenarioReadTUIXFile($dir. $file);
				zenarioParseTUIX($tags, $tagsToParse, 'welcome');
				unset($tagsToParse);
			}
		}
	
	} else {
		//Try to check the tuix_file_contents table to see which files we need to include
		foreach (modulesAndTUIXFiles($type, $requestedPath, $settingGroup, true, true, $compatibilityClassNames) as $module) {
			if (empty($modules[$module['class_name']])) {
				setModulePrefix($module);
				
				$modules[$module['class_name']] = array(
					'class_name' => $module['class_name'],
					'depends' => getModuleDependencies($module['class_name']),
					'included' => false,
					'files' => array());
			}
			$modules[$module['class_name']]['files'][] = $module['filename'];
		}
	}
	
	//Ensure that the core plugin is included first, if it is there... but remove it again if it wasn't.
	if (empty($modules['zenario_common_features'])) {
		unset($modules['zenario_common_features']);
	}
	
	//Include every Module's XML files in dependency order
	$limit = 9999;
	do {
		$progressBeingMade = false;
		
		foreach ($modules as $className => &$module) {
			//No need to include a Module twice
			if ($module['included']) {
				continue;
			}
			
			//Make sure that we include files in dependency order by skipping over Modules whose dependencies
			//are still to be included.
			foreach ($module['depends'] as $depends) {
				if (!empty($modules[$depends['dependency_class_name']])
				 && !$modules[$depends['dependency_class_name']]['included']) {
					continue 2;
				}
			}
			
			//Include any xml files in the directory
			if ($dir = moduleDir($module['class_name'], 'tuix/'. $type. '/', true)) {
				
				if (!isset($module['files'])) {
					$module['files'] = array();
					
					foreach (scandir($dir) as $file) {
						if (substr($file, 0, 1) != '.') {
							$module['files'] = scandir($dir);
						}
					}
				}
				
				foreach ($module['files'] as $file) {
					$tagsToParse = zenarioReadTUIXFile($dir. $file);
					zenarioParseTUIX($tags, $tagsToParse, $type, $className, $settingGroup, $compatibilityClassNames, $requestedPath);
					unset($tagsToParse);
					
					if (!isset($module['paths'])) {
						$module['paths'] = array();
					}
					$module['paths'][$file] = $dir. $file;
				}
			}
			
			$module['included'] = true;
			$progressBeingMade = true;
		}
	//Loop while includes are still being done
	} while ($progressBeingMade && --$limit);
	
	//Readjust the start to get rid of the outer tag
	if (!isset($tags[$type])) {
		echo adminPhrase('The requested path "[[path]]" was not found in the system. If you have just updated or added files to the CMS, you will need to reload the page.', array('path' => $requestedPath));
		exit;
	}
	$tags = $tags[$type];
}

function tuixCacheDir($path) {
	
	$strlen = strlen(CMS_ROOT);
	if (substr($path, 0, $strlen) == CMS_ROOT) {
		$path = substr($path, $strlen);
	}
	$path = str_replace('/tuix/', '/', $path);
	$path = str_replace('/zenario/', '/', $path);
	
	$dir = dirname($path);
	$dir = str_replace('%', ' ', rawurlencode($dir));
	$file = basename($path);
	$file = explode('.', $file, 2);
	$file = $file[0];
	
	cleanDownloads();
	return createCacheDir($dir, $type = 'tuix', $onlyForCurrentVisitor = true, $ip = false). $file. '.json';
}


function zenarioReadTUIXFile($path, $useCache = true, $updateCache = true) {
	$type = explode('.', $path);
	$type = $type[count($type) - 1];
	
	//Attempt to use a cached copy of this TUIX file
		//JSON is a lot faster to read than the other formats, so for speed purposes we create cached JSON copies of files
	$cachePath = false;
	if ($useCache || $updateCache) {
		$cachePath = tuixCacheDir($path);
	}
	if ($useCache && $cachePath
	 && file_exists($path)
	 && file_exists($cachePath)
	 && filemtime($cachePath) > filemtime($path)
	 && ($tags = json_decode(file_get_contents($cachePath), true))) {
		return $tags;
	}
	
	switch ($type) {
		case 'xml':
			$xml = simplexml_load_file($path);
			
			$tags = array();
			zenarioReadTUIXFileR($tags, $xml);
			
			//Hack to get the 
			
			break;
			
		case 'yml':
		case 'yaml':
			
			$contents = file_get_contents($path);
			
			if ($contents === false) {
				echo 'Could not read file '. $path;
				exit;
			
			} else
			if ((preg_match("/[\n\r](\t* +\t|\t+ {4})/", "\n". $contents) !== 0)
			 || (preg_match("/[\n\r](\t+[^\t])/", "\n". $contents) === 1
			 &&  preg_match("/[\n\r]( +[^ ])/", "\n". $contents) === 1)) {
				echo 'The YAML file '. $path. ' contains a mixture of tabs and spaces for indentation and cannot be read';
				exit;
			}
			
			if (defined('USE_NATIVE_YAML_EXTENSION') && function_exists('yaml_parse')) {
				
				$parsedContents = '';
				foreach (preg_split("/([\n\r][ \t]+)/", $contents, -1, PREG_SPLIT_DELIM_CAPTURE) as $i => $line) {
					if ($i % 2) {
						$parsedContents .= str_replace("\t", '    ', $line);
					} else {
						$parsedContents .= $line;
					}
				}
				
				$tags = yaml_parse($parsedContents);
				unset($parsedContents);
				
			} else {
				require_once CMS_ROOT. 'zenario/libraries/mit/spyc/Spyc.php';
				$tags = Spyc::YAMLLoad($path);
			}
			unset($contents);
			
			break;
			
		default:
			$tags = array();
	}
	
	if (!is_array($tags) || $tags === NULL) {
		echo 'Error in file '. $path;
		exit;
	}
	
	//Backwards compatability hack so that Modules created before we moved the
	//site settings don't immediately break!
	if (!empty($tags['zenario__administration']['nav']['configure_settings']['panel']['items']['settings']['panel'])
	 && empty($tags['zenario__administration']['panels']['site_settings'])) {
		$tags['zenario__administration']['panels']['site_settings'] =
			$tags['zenario__administration']['nav']['configure_settings']['panel']['items']['settings']['panel'];
		unset($tags['zenario__administration']['nav']['configure_settings']['panel']['items']['settings']['panel']);
	}
	
	//Save this array in the cache as a JSON file, for faster loading next time
	if ($updateCache && $cachePath) {
		@file_put_contents($cachePath, json_encode($tags));
		@chmod($cachePath, 0666);
	}
	
	return $tags;
}

function zenarioReadTUIXFileR(&$tags, &$xml) {
	$lastKey = null;
	$children = false;
	foreach ($xml->children() as $child) {
		$children = true;
		$key = preg_replace('/[^\w-]/', '', $child->getName());
		
		//Strip underscores from the begining of tag names
		if (substr($key, 0, 1) == '_') {
			$key = substr($key, 1);
		}
		
		//Hack to try and stop repeated parents with the same name overriding each other
		if (isset($tags[$key]) && $lastKey === $key) {
			$i = 2;
			while (isset($tags[$key. ' ('. $i. ')'])) {
				++$i;
			}
			$key = $key. ' ('. $i. ')';
			
		} else {
			$lastKey = $key;
		}
		
		if (!isset($tags[$key])) {
			$tags[$key] = array();
		}
		
		zenarioReadTUIXFileR($tags[$key], $child);
		
	}
	
	if ($children) {
		foreach ($xml->attributes() as $key => $child) {
			$tags[$key] = (string) $child;
		}
	} else {
		$tags = trim((string) $xml);
	}
	
}


//This function reads a single xml files, and merges it into the information that we've already read

	//$thisTags = zenarioReadTUIXFile($path, $moduleClassName);

function zenarioParseTUIX(&$tags, &$par, $type, $moduleClassName = false, $settingGroup = '', $compatibilityClassNames = array(), $requestedPath = false, $tag = '', $path = '', $goodURLs = array(), $level = 0, $ord = 1, $parent = false, $parentsParent = false, $parentsParentsParent = false) {
	
	if ($path === '') {
		$tag = $type;
		$path = '';
	}
	$path .= $tag. '/';
	
	$isPanel = $tag == 'panel' || $parent == 'panels';
	$lastWasPanel = $parent == 'panel' || $parentsParent == 'panels';
	
	//Note that I'm stripping the "storekeeper/" from the start of the URL, and the final "/" from the end
	$url = substr($path, strlen($type. '/'), -1);
	
	//Check to see if we should include this tag and its children
	$goFurther = true;
	$includeThisSubTree = false;
	if ($type == 'storekeeper' || $type == 'organizer') {
		
		//If this tag is a panel, then it's valid to link to this tag
		if ($isPanel) {
			//Record the link to this panel.
			//Note that I'm stripping the "storekeeper/" from the start of the URL, and the final "/" from the end
			array_unshift($goodURLs, zenarioAJAXShortenPath($url, $type));
		
			//If the current tag is a panel, and we have a specific path requested, don't include it if it is not on the requested path
			if ($requestedPath && (strlen($requestedPath) < ($sl = strlen($goodURLs[0])) || substr($requestedPath, 0, $sl) != $goodURLs[0])) {
				$goFurther = false;
			}
		}
		
		
		//Purely client-side panels need to be completely in the map; panels which are loaded via AJAX only need some tags in the map
		
		//If a specific path has been requested, show all of the tags under than path
		if ($requestedPath) {
			$includeThisSubTree = true;
		
		//If getting an overall map, only show certain needed tags, to save space
		} elseif (isset($tags[$tag]) && is_array($tags[$tag])) {
			//If this tag was included from parsing another file, it shouldn't be removed now
			$includeThisSubTree = true;
		
		} elseif (!$isPanel && !$lastWasPanel && ($level < 4 || $parent == 'nav' || $parentsParent == 'nav' || $parentsParentsParent == 'nav')) {
			//The left hand nav always needs to be sent
			$includeThisSubTree = true;
		
		} elseif ($parent == 'refiners') {
			//Always include refiner tags
			$includeThisSubTree = true;
		
		} elseif ($parent == 'columns' && $ord == 1) {
			//The first column always needs to be sent as it is used as a fallback
			$includeThisSubTree = true;
		
		} else {
			switch ($tag) {
				case 'back_link':
				case 'link':
				case 'panel':
				case 'panels':
				case 'php':
					$includeThisSubTree = true;
					break;
				case 'always_show':
				case 'show_by_default':
					if ($parentsParent == 'columns') {
						$includeThisSubTree = true;
					}
					break;
				case 'client_side':
				case 'encode_id_column':
					if ($parent == 'db_items') {
						$includeThisSubTree = true;
					}
					break;
				case 'branch':
				case 'path':
				case 'refiner':
					if ($parent == 'link') {
						$includeThisSubTree = true;
					}
					break;
				case 'db_items':
				case 'default_sort_column':
				case 'default_sort_desc':
				case 'default_sort_column':
				case 'no_return':
				case 'refiner_required':
				case 'reorder':
				case 'title':
				case '_path_here':
					if ($lastWasPanel) {
						$includeThisSubTree = true;
					}
					break;
				case 'column':
				case 'lazy_load':
					if ($parent == 'reorder') {
						$includeThisSubTree = true;
					}
					break;
				case 'use_different_view_options':
				case 'use_different_view_options_per_refiner_id':
				case 'use_different_view_options_per_id_in_previous_refiner':
					if ($parentsParent == 'refiners') {
						$includeThisSubTree = true;
					}
					break;
			}
		}
		
	
	} elseif ($type == 'admin_boxes') {
		if ($level == 1 && $url != $requestedPath) {
			$goFurther = false;
		
		//Have an option to bypass the filters below and show everything
		} elseif ($settingGroup === true) {
			
		//Filter by setting group
		} elseif ($requestedPath == 'plugin_settings' || $requestedPath == 'site_settings' || $requestedPath == 'advanced_search') {
			//Check attributes for keys and values.
			//For module Settings, use the "module_class_name" attribute to only show the related settings
			//However compatilibity now includes inheriting module Settings, so include module Settings from
			//compatible modules as well
			if ($requestedPath == 'plugin_settings'
			 && !empty($par['module_class_name'])
			 && empty($compatibilityClassNames[(string) $par['module_class_name']])) {
				$goFurther = false;
			
			//For Site Settings, only show settings from the current settings group
			} else
			if ($requestedPath == 'site_settings'
			 && !empty($par['setting_group'])
			 && $par['setting_group'] != $settingGroup) {
				$goFurther = false;
			
			//For Advanced Searches, only show fields for the current Storekeepr Path
			} else
			if ($requestedPath == 'advanced_search'
			 && !empty($par['storekeeper_path'])
			 && $par['storekeeper_path'] != $settingGroup) {
				$goFurther = false;
			}
		}
		
		$includeThisSubTree = true;
		
	
	} elseif ($type == 'module_description') {
		//Only the basic descriptive tags, <dependencies> and <inheritance> are copied using Module inheritance.
		if ($settingGroup == 'inherited') {
			switch ($tag) {
				case 'admin_floating_box_tabs':
				case 'content_types':
				case 'jobs':
				case 'pagination_types':
				case 'preview_images':
				case 'signals':
				case 'special_pages':
					if (!$parentsParent) {
						$goFurther = false;
					}
					break;
			}
		
		}
		
		$includeThisSubTree = true;
	} else {
		$includeThisSubTree = true;
	}
	
	
	//Add an entry for this tag in the sk array, if it doesn't already have one
	$isEmptyArray = false;
	if (!(isset($tags[$tag]) && is_array($tags[$tag]))) {
		$isEmptyArray = true;
		if ($moduleClassName) {
			//Note down which module owns this tag
			$tags[$tag] = array('class_name' => $moduleClassName);
		}
	}
	
	//Recursively scan each child-tag
	$children = 0;
	if (is_array($par)) {
		foreach ($par as $key => &$child) {
			++$children;
			$isEmptyArray = true;
			
			if ($goFurther) {
				if (zenarioParseTUIX($tags[$tag], $child, $type, $moduleClassName, $settingGroup, $compatibilityClassNames, $requestedPath, $key, $path, $goodURLs, $level + 1, $children, $tag, $parent, $parentsParent)) {
					$includeThisSubTree = true;
				}
			}
		}
	}
	
	
	if (!$includeThisSubTree) {
		unset($tags[$tag]);
		return false;
	
	} else {
		//If this tag had no children, then note down its value
		if (!is_array($par)) {
			
			//Do not allow empty variables to overwrite arrays if they are not empty
			if (empty($par) && !empty($tags[$tag]) && is_array($tags[$tag]) && !$isEmptyArray) {
				//Do nothing
			
			//Module/Skin description files are read in reverse dependancy order, so don't overwrite existing tags
			} else if (isset($tags[$tag]) && ($type == 'module_description' || $type == 'skin_description')) {
				//Do nothing
				
			} else {
				$tags[$tag] = trim((string) $par);
			}
		
		//If this tag has a Storekeeper Panel...
		} elseif ($type == 'storekeeper' || $type == 'organizer') {
			if ($isPanel) {
				//..note down the path of the panel...
				$tags[$tag]['_path_here'] = $goodURLs[0];
				
				//...and also the link to the panel above if there is one.
				if (isset($goodURLs[1])
				 && !isset($tags[$tag]['back_link'])
				
					//Note that panels defined against a top level item (which is deprecated) should not count as the natural
					//back-link for a panel defined against a second-level item or in the panels container,
					//as we've removed the ability to have nav-links there
				 //&& !(($parentsParent == 'storekeeper' || $parentsParentsParent == 'organizer') && ($parent == 'nav' || $parent == 'panels'))
				 ) {
					$tags[$tag]['back_link'] = $goodURLs[1];
				}
			}
		}
		
		return true;
	}
}


//Strip out any tags/sections that require a priv that the current admin does not have
//Also count each tags' children
function zenarioParseTUIX2(&$tags, &$removedColumns, $type, $requestedPath = '', $mode = false, $path = '', $parentKey = false, $parentParentKey = false, $parentParentParentKey = false) {
	
	//Keep track of the path to this point
	if (!$path) {
		$path = $requestedPath;
	} else {
		$path .= ($path? '/' : ''). $parentKey;
	}
	
	$count = 0;
	$noPrivs = array();
	
	$countItems = false;
	$orderItems = false;
	if ($mode == 'csv' || $mode == 'xml') {
		//Don't count or order anything
		
	} elseif ($type == 'storekeeper' || $type == 'organizer') {
		$countItems = $parentKey === false
					|| $parentKey == 'items'
					|| $parentKey == 'panels'
					|| $parentKey == 'columns'
					|| $parentKey == 'hidden_nav'
					|| $parentKey == 'item_buttons'
					|| $parentKey == 'inline_buttons'
					|| $parentKey == 'collection_buttons'
					|| $parentKey == 'nav';
		
		$orderItems = $countItems && $parentKey != 'items';
	
	} elseif ($type == 'admin_boxes' || $type == 'welcome') {
		$orderItems = $parentKey == 'tabs'
					|| $parentKey == 'fields'
					|| $parentKey == 'values';
	
	} elseif ($type == 'slot_controls') {
		$orderItems = $parentKey == 'info'
					|| $parentKey == 'notes'
					|| $parentKey == 'actions'
					|| $parentKey == 'overridden_info'
					|| $parentKey == 'overridden_actions';
	
	} elseif ($type == 'admin_toolbar') {
		$orderItems = ($parentParentKey === false && ($parentKey == 'sections' || $parentKey == 'toolbars'))
					|| ($parentParentParentKey == 'sections' && $parentKey == 'buttons');
	}
	
	if (is_array($tags)) {
		//Strip out any tags/sections that require a priv that the current admin does not have
		foreach ($tags as $key => &$value) {
			if ((string) $key == 'priv') {
				if (!checkPriv((string) $value)) {
					return false;
				}
			
			} elseif ((string) $key == 'local_admins_only') {
				if (engToBoolean($value) && session('admin_global_id')) {
					return false;
				}
			
			} elseif ((string) $key == 'superadmins_only') {
				if (engToBoolean($value) && !session('admin_global_id')) {
					return false;
				}
			
			} elseif (!zenarioParseTUIX2($value, $removedColumns, $type, $mode, $requestedPath, $path, (string) $key, $parentKey, $parentParentKey)) {
				$noPrivs[] = $key;
			
			} elseif (is_array($value) && ($countItems || $orderItems)) {
				++$count;
				
				if ($orderItems
				 && !isset($value['ord'])) {
					$value['ord'] = $count;
				}
			}
		}
		
		foreach($noPrivs as $key) {
			unset($tags[$key]);
		}
		unset($tags['priv']);
		
		//Add counts to items, buttons and top/second level items
		if ($countItems && !isset($tags['count']) && $countItems) {
			$tags['count'] = $count;
		}
		
		//Strip the class_name tag out except where needed
		if ($type == 'storekeeper' || $type == 'organizer') {
			if ($parentKey != 'ajax'
			 && $parentParentKey != 'columns'
			 && $parentKey != 'combine_items'
			 && $parentParentKey != 'refiners'
			 && $parentKey != 'pick_items'
			 && $parentKey != 'reorder'
			 && $parentKey != 'upload'
			 && $parentKey !== false) {
				unset($tags['class_name']);
			}
		
		} elseif ($type == 'admin_boxes') {
			if ($parentKey !== false
			 && $parentParentKey != 'tabs'
			 && $parentParentKey != 'fields') {
				unset($tags['class_name']);
			}
		
		} elseif ($type == 'admin_toolbar') {
			if ($parentKey != 'ajax'
			 && $parentParentKey != 'buttons'
			 && $parentKey != 'pick_items'
			 && $parentParentKey != 'toolbars') {
				unset($tags['class_name']);
			}
		
		} elseif ($type == 'help' || $type == 'welcome') {
			unset($tags['class_name']);
		}
		
		//Don't send any SQL to the client
		if ($type == 'storekeeper' || $type == 'organizer') {
			if ($parentKey === false || $parentKey == 'panel' || $parentParentKey = 'panels') {
				if (!checkPriv('_PRIV_VIEW_DEV_TOOLS')) {
					if (isset($tags['db_items'])) {
						$db_items = array();
						foreach (array('client_side', 'encode_id_column') as $tag) {
							if (isset($tags['db_items'][$tag])) {
								$db_items[$tag] = $tags['db_items'][$tag];
							}
						}
						
						if (!empty($tags['db_items']['hierarchy_column'])) {
							$db_items['hierarchy_column'] = true;
						}
						
						if (empty($db_items)) {
							$db_items['exists'] = true;
						}
						
						$tags['db_items'] = $db_items;
					}
					
					if (isset($tags['columns']) && is_array($tags['columns'])) {
						foreach ($tags['columns'] as &$col) {
							if (is_array($col)) {
								if (!empty($col['db_column'])) {
									$col['db_column'] = true;
								}
								if (!empty($col['search_column'])) {
									$col['search_column'] = true;
								}
								if (!empty($col['sort_column'])) {
									$col['sort_column'] = true;
								}
								
								if (isset($col['table_join'])) {
									unset($col['table_join']);
								}
								
								if (empty($col)) {
									$col = true;
								}
							}
						}
					}
					
					if (isset($tags['refiners']) && is_array($tags['refiners'])) {
						foreach ($tags['refiners'] as &$refiner) {
							if (is_array($refiner)) {
								if (isset($refiner['sql'])) {
									unset($refiner['sql']);
								}
								if (isset($refiner['table_join'])) {
									unset($refiner['table_join']);
								}
								
								if (empty($refiner)) {
									$refiner = true;
								}
							}
						}
					}
				}
			
			} elseif (($parentParentKey === false || $parentParentKey == 'panel' || $parentParentParentKey == 'panels') && $parentKey == 'columns') {
				//If this is a Storekeeper request for a specific panel, get a list of columns for that
				//panel that are server side only, so that we can later remove these from the output.
				if (zenarioAJAXShortenPath($path, $type) == $requestedPath. '/columns') {
					$removedColumns = array();
					foreach ($tags as $key => &$value) {
						if (is_array($value) && engToBoolean(arrayKey($value, 'server_side_only'))) {
							$removedColumns[] = $key;
						}
					}
				}
			}
		}
	}
	
	
	return true;
}




function sortTUIX(&$tags) {
	if (is_array($tags)) {
		uasort($tags, 'sortTUIXCompare');
	}
}

function sortTUIXCompare($a, $b) {
	$ordA = $ordB = 999999;
	if (isset($a['ord'])) {
		$ordA = $a['ord'];
	}
	if (isset($b['ord'])) {
		$ordB = $b['ord'];
	}
	
	if ($ordA === $ordB) {
		return 0;
	} else if ($ordA < $ordB) {
		return -1;
	} else {
		return 1;
	}
}





//
// Translation functionality
//



//Add two Content Items into an equivalence
function recordEquivalence($cID1, $cID2, $cType, $onlyValidate = false) {
	//Get the two equivalence keys of the Content Items
	$equiv1 = equivId($cID1, $cType);
	$equiv2 = false;
	
	if ($cID2) {
		$equiv2 = equivId($cID2, $cType);
	}
	
	//Try to get the cID for the default Language
	$default = getRow('content', array('id', 'alias'), array('equiv_id' => $equiv1, 'type' => $cType, 'language_id' => setting('default_language')));
	
	if (!$default && $equiv2) {
		$default = getRow('content', array('id', 'alias'), array('equiv_id' => $equiv2, 'type' => $cType, 'language_id' => setting('default_language')));
	}
	
	//If we are merging two different equivs, check the merge will not give us any overlaps
	if ($equiv1 && $equiv2 && $equiv1 != $equiv2) {
		$result = getRows('content', 'language_id', array('equiv_id' => $equiv1, 'type' => $cType));
		while ($row = sqlFetchAssoc($result)) {
			if (checkRowExists('content', array('equiv_id' => $equiv2, 'type' => $cType, 'language_id' => $row['language_id']))) {
				return false;
			}
		}
	}
	
	//Update any existing equivalences to match the new equiv id, which should be the cID for the default Language
	if (!$onlyValidate) {
		if (!empty($default['id'])) {
			$newEquivId = $default['id'];
		} else {
			$newEquivId = $equiv1;
		}
		
		foreach (array($equiv1, $equiv2) as $currentEquivId) {
			if ($currentEquivId && $newEquivId && $currentEquivId != $newEquivId) {
				//Automaticaly update the aliases of any newly added Content Items if they don't currently have aliases
				if (!empty($default['alias'])) {
					updateRow('content', array('alias' => $default['alias']), array('equiv_id' => $currentEquivId, 'type' => $cType, 'alias' => ''));
				}
				
				//Change the old equivId to the new equivId
				updateRow('categories', array('landing_page_equiv_id' => $newEquivId), array('landing_page_equiv_id' => $currentEquivId, 'landing_page_content_type' => $cType));
				updateRow('content', array('equiv_id' => $newEquivId), array('equiv_id' => $currentEquivId, 'type' => $cType));
				updateRow('menu_nodes', array('equiv_id' => $newEquivId), array('equiv_id' => $currentEquivId, 'content_type' => $cType));
				updateRow('special_pages', array('equiv_id' => $newEquivId), array('equiv_id' => $currentEquivId, 'content_type' => $cType));
				
				if (!checkRowExists('translation_chains', array('equiv_id' => $newEquivId, 'type' => $cType))) {
					updateRow('translation_chains', array('equiv_id' => $newEquivId), array('equiv_id' => $currentEquivId, 'type' => $cType));
				}
				
				tidyTranslationsTable($currentEquivId, $cType);
			}
		}
		
		return $newEquivId;
	}
	
	return true;
}

function tidyTranslationsTable($equivId, $cType) {
	if (!checkRowExists('content', array('equiv_id' => $equivId, 'type' => $cType))) {
		deleteRow('translation_chains', array('equiv_id' => $equivId, 'type' => $cType));
		deleteRow('category_item_link', array('equiv_id' => $equivId, 'content_type' => $cType));
		deleteRow('group_content_link', array('equiv_id' => $equivId, 'content_type' => $cType));
		deleteRow('user_content_link', array('equiv_id' => $equivId, 'content_type' => $cType));
	}
}

function copyTranslationsTable($oldEquivId, $newEquivId, $cType) {
	$chain = getRow('translation_chains', true, array('equiv_id' => $oldEquivId, 'type' => $cType));
	$chain['equiv_id'] = $newEquivId;
	
	//Insert a new row into the translation_chains table for the new chain
	setRow('translation_chains', $chain, array('equiv_id' => $newEquivId, 'type' => $cType));
	
	$sql = "
		INSERT IGNORE INTO ". DB_NAME_PREFIX. "category_item_link (
			equiv_id, content_type, category_id
		) SELECT ". (int) $newEquivId. ", content_type, category_id
		FROM ". DB_NAME_PREFIX. "category_item_link
		WHERE equiv_id = ". (int) $oldEquivId. "
		  AND content_type = '". sqlEscape($cType). "'";
	sqlQuery($sql);
	
	$sql = "
		INSERT IGNORE INTO ". DB_NAME_PREFIX. "group_content_link (
			equiv_id, content_type, group_id
		) SELECT ". (int) $newEquivId. ", content_type, group_id
		FROM ". DB_NAME_PREFIX. "group_content_link
		WHERE equiv_id = ". (int) $oldEquivId. "
		  AND content_type = '". sqlEscape($cType). "'";
	sqlQuery($sql);
	
	$sql = "
		INSERT IGNORE INTO ". DB_NAME_PREFIX. "user_content_link (
			equiv_id, content_type, user_id
		) SELECT ". (int) $newEquivId. ", content_type, user_id
		FROM ". DB_NAME_PREFIX. "user_content_link
		WHERE equiv_id = ". (int) $oldEquivId. "
		  AND content_type = '". sqlEscape($cType). "'";
	sqlQuery($sql);
}

function resyncEquivalence($cID, $cType) {
	recordEquivalence($cID, false, $cType);
}

function allowRemoveEquivalence($cID, $cType) {
	//Check if this is a special page
	if (($specialPage = isSpecialPage($cID, $cType))
	 && ($specialPage = getRow('special_pages', array('equiv_id', 'create_lang_equiv_content'), array('page_type' => $specialPage)))) {
		//Never allow the main special page to be unlinked.
		//And never allow any special page to be unlinked if create_lang_equiv_content is set.
		return $cID != $specialPage['equiv_id'] && !$specialPage['create_lang_equiv_content'];
	} else {
		return true;
	}
}


//Remove a content equivalence link from the database
function removeEquivalence($cID, $cType) {
	
	
	$content = getRow('content', array('alias', 'equiv_id', 'tag_id'), array('id' => $cID, 'type' => $cType));
	
	//Two cases here:
	if ($content['equiv_id'] != $cID) {
		//1. This Content Item is not in the default language.
		//   In this case, we only need change its equiv_id
		$vals = array();
		$vals['equiv_id'] = $cID;
		
		//Check if another Content Item is using this alias; if so, we need to remove the alias.
		if ($content['alias'] && checkRowExists('content', array('alias' => $content['alias'], 'tag_id' => array('!' => $content['tag_id'])))) {
			$vals['alias'] = '';
		}
		
		updateRow('content', $vals, array('id' => $cID, 'type' => $cType));
		copyTranslationsTable($content['equiv_id'], $vals['equiv_id'], $cType);
	
	} else {
		//2. This Content Item is the default language.
		//   In this case, we only need change its equiv_id for everything *else*
		$newEquivId = false;
		$result = getRows('content', array('id', 'alias'), array('equiv_id' => $content['equiv_id'], 'type' => $cType));
		while ($row = sqlFetchAssoc($result)) {
			if ($row['id'] != $cID) {
				if (!$newEquivId) {
					$newEquivId = $row['id'];
				}
				$vals = array('equiv_id' => $newEquivId);
				
				//Check if another Content Item is using the same alias as the default page; if so, we need to remove the alias.
				if ($content['alias'] && $content['alias'] == $row['alias']) {
					$vals['alias'] = '';
				}
				
				updateRow('content', $vals, array('id' => $row['id'], 'type' => $cType));
			}
		}
		if ($newEquivId) {
			copyTranslationsTable($cID, $newEquivId, $cType);
		}
	}
}

