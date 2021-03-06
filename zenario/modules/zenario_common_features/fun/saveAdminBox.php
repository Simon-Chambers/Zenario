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

switch ($path) {
	case 'plugin_settings':
	case 'plugin_css_and_framework':
		return require funIncPath(__FILE__, 'plugin_settings.saveAdminBox');
	
	
	case 'zenario_reusable_plugin':
		if (engToBooleanArray($box['tabs']['instance'], 'edit_mode', 'on') && checkPriv('_PRIV_MANAGE_REUSABLE_PLUGIN')) {
			$nest = false;
			if ($box['key']['duplicate']) {
				renameInstance($box['key']['id'], $nest, $values['instance/name'], $createNewInstance = true);
			
			} else {
				renameInstance($box['key']['id'], $nest, $values['instance/name'], $createNewInstance = false);
			}
		}
		
		break;
	
	
	case 'zenario_alias':
		if (checkPriv('_PRIV_EDIT_DRAFT')) {
			$cols = array('alias' => tidyAlias($values['meta_data/alias']));
			$key = array('id' => $box['key']['cID'], 'type' => $box['key']['cType']);
			$equivKey = array('equiv_id' => equivId($box['key']['cID'], $box['key']['cType']), 'type' => $box['key']['cType']);
			
			if (getNumLanguages() > 1) {
				if ($values['meta_data/update_translations'] == 'update_all') {
					$key = $equivKey;
					$cols['lang_code_in_url'] = 'default';
				
				} else {
					$cols['lang_code_in_url'] = $values['meta_data/lang_code_in_url'];
				}
			}
			
			updateRow('content', $cols, $key);
		}
		
		break;
	
	
	case 'zenario_enable_site':
		if (checkPriv('_PRIV_EDIT_SITE_SETTING')) {
			setSetting('site_enabled', $values['site/enable_site']);
			setSetting('site_disabled_title', $values['site/site_disabled_title']);
			setSetting('site_disabled_message', $values['site/site_disabled_message']);
			
			$box['key']['id'] = $values['site/enable_site']? 'site_enabled' : 'site_disabled';
		}
		
		
		break;
	
	
	case 'zenario_delete_language':
		deleteLanguage($box['key']['id']);
		
		break;
	
	
	case 'zenario_site_reset':
		exitIfNotCheckPriv('_PRIV_RESET_SITE');
		
		resetSite();
		echo '<!--Reload_Storekeeper-->';
		exit;
	
	
	case 'zenario_menu':
		return require funIncPath(__FILE__, 'menu_node.saveAdminBox');
	
	
	case 'zenario_content':
	case 'zenario_quick_create':
		return require funIncPath(__FILE__, 'content.saveAdminBox');

	
	case 'zenario_content_layout':
		
		//Loop through each Content Item, saving each
		$cID = $cType = $cVersion = false;
		$tagIds = explode(',', $box['key']['id']);
		foreach ($tagIds as $tagId) {
			if (getCIDAndCTypeFromTagId($cID, $cType, $tagId)) {
				
				if (!checkPriv('_PRIV_EDIT_CONTENT_ITEM_TEMPLATE', $cID, $cType)) {
					continue;
				}
				
				//Create a draft if needed
				createDraft($cID, $cID, $cType, $cVersion, getLatestVersion($cID, $cType));
				
				//Update the layout
				changeContentItemLayout($cID, $cType, $cVersion, $values['layout_id']);
				
				//Mark this version as updated
				updateVersion($cID, $cType, $cVersion);
			}
		}
		
		break;
		
		
	case 'zenario_content_categories':
		exitIfNotCheckPriv('_PRIV_EDIT_CONTENT_ITEM_CATEGORIES');
		
		$cID = $cType = false;
		
		$tagIds = explode(',', $box['key']['id']);
		
		foreach ($tagIds as $tagId) {
			if (getCIDAndCTypeFromTagId($cID, $cType, $tagId)) {
				setContentItemCategories($cID, $cType, explode(',', $values['categories/categories']));
			}
		}
		
		break;
	
	case 'zenario_content_categories_add':
		exitIfNotCheckPriv('_PRIV_EDIT_CONTENT_ITEM_CATEGORIES');
		
		$cID = $cType = false;
		
		$tagIds = explode(',', $box['key']['id']);
		
		foreach ($tagIds as $tagId) {
			if (getCIDAndCTypeFromTagId($cID, $cType, $tagId)) {
				addContentItemToCategories($cID, $cType, explode(',', $values['categories_add/categories_add']));
				
				}
		}
		
		break;
	case 'zenario_content_categories_remove':
		exitIfNotCheckPriv('_PRIV_EDIT_CONTENT_ITEM_CATEGORIES');
		
		$cID = $cType = false;
		
		$tagIds = explode(',', $box['key']['id']);
		
		foreach ($tagIds as $tagId) {
			if (getCIDAndCTypeFromTagId($cID, $cType, $tagId)) {
				removeContentItemCategories($cID, $cType, explode(',', $values['categories_remove/categories_remove']));
				
				}
		}
		
		break;
	case 'site_settings':
		return require funIncPath(__FILE__, 'site_settings.saveAdminBox');
	
	
	case 'zenario_setup_language':
		return require funIncPath(__FILE__, 'setup_language.saveAdminBox');
	
	
	case 'zenario_setup_module':
		return require funIncPath(__FILE__, 'setup_module.saveAdminBox');
	
	
	case 'zenario_publish':
		$ids = (($box['key']['id']) ? $box['key']['id'] : $box['key']['cID']);
		
		foreach (explode(',', $ids) as $id) {
			$cID = $cType = false;
			if (!empty($box['key']['cID']) && !empty($box['key']['cType'])) {
				$cID = $box['key']['cID'];
				$cType = $box['key']['cType'];
			} else {
				getCIDAndCTypeFromTagId($cID, $cType, $id);
			}
			
			if ($cID && $cType && checkPriv('_PRIV_PUBLISH_CONTENT_ITEM', $cID, $cType)) {
				if ($values['publish/publish_options'] == 'immediately') {
					// Publish now
					publishContent($cID, $cType);
					if (session('last_item') == $cType. '_'. $cID) {
						$_SESSION['page_mode'] = $_SESSION['page_toolbar'] = 'preview';
					}
				} else {
					// Publish at a later date
					$scheduled_publish_datetime = $values['publish/publish_date'].' '.$values['publish/publish_hours'].':'.$values['publish/publish_mins'].':00';
					$cVersion = getRow('content', 'admin_version', array('id' => $cID, 'type' => $cType));
					updateRow('versions', array('scheduled_publish_datetime'=>$scheduled_publish_datetime), array('id' =>$cID, 'type'=>$cType, 'version'=>$cVersion));
					
					// Lock content item
					$adminId = session('admin_userid');
					updateRow('content', array('lock_owner_id'=>$adminId, 'locked_datetime'=>date('Y-m-d H:i:s')), array('id' =>$cID, 'type'=>$cType));
				}
			}
		}
		
		break;
	
	
	case 'zenario_admin':
		return require funIncPath(__FILE__, 'admin.saveAdminBox');
	
	
	case 'zenario_phrase':
		exitIfNotCheckPriv('_PRIV_MANAGE_LANGUAGE_PHRASE');
		
		if ($box['key']['id']) {
			
			$languages = getLanguages(false, false, true, true);
			foreach ($languages as $language) {
				if (!empty($values['phrase/'. $language['id']])) {
					setRow('visitor_phrases', 
						array('local_text' => $values['phrase/'. $language['id']], 'protect_flag' => $values['phrase/protect_flag_edit_mode_'. $language['id']]), 
						array('code' => $box['key']['code'], 'module_class_name' => $box['key']['module_class_name'], 'language_id' => $language['id']));
				}
			}
			
		} else {
				
			setRow(
			'visitor_phrases',
				array('local_text' => $values['phrase/'. setting('default_language')], 'protect_flag' => $values['phrase/protect_flag_edit_mode']),
				array(
				'module_class_name' => getModuleClassName($values['phrase/module']),
				'language_id' => setting('default_language'),
				'code' => $values['phrase/code']));
		}
		
		// Save each enabled languages phrases
		
		// Save extra attributes
		
		break;
	
	case 'zenario_create_vlp':
		exitIfNotCheckPriv('_PRIV_MANAGE_LANGUAGE_CONFIG');
		
		setRow(
			'visitor_phrases',
			array(
				'local_text' => $values['details/english_name'],
				'protect_flag' => 1),
			array(
				'code' => '__LANGUAGE_ENGLISH_NAME__',
				'language_id' => $values['details/language_id'],
				'module_class_name' => 'zenario_common_features'));
		
		setRow(
			'visitor_phrases',
			array(
				'local_text' => $values['details/language_local_name'],
				'protect_flag' => 1),
			array(
				'code' => '__LANGUAGE_LOCAL_NAME__',
				'language_id' => $values['details/language_id'],
				'module_class_name' => 'zenario_common_features'));
		
		setRow(
			'visitor_phrases',
			array(
				'local_text' => decodeItemIdForStorekeeper($values['details/flag_filename']),
				'protect_flag' => 1),
			array(
				'code' => '__LANGUAGE_FLAG_FILENAME__',
				'language_id' => $values['details/language_id'],
				'module_class_name' => 'zenario_common_features'));
		
		$box['key']['id'] = $values['details/language_id'];
		
		$box['popout_message'] = '<!--Open_Admin_Box:zenario_setup_language//'. $values['details/language_id']. '-->';
		
		break;
	
	case 'zenario_document_folder':
		if (isset($box['key']['add_folder']) && $box['key']['add_folder']) {
			$box['key']['id'] = 
				setRow(
					'documents',
					array(
						'type' => 'folder',
						'folder_name' => $values['details/folder_name'],
						'folder_id' => $box['key']['id'],
						'ordinal' => 0));
		} else {
			if($box['key']['id']) {
				updateRow(
					'documents',
					array(
						'type' => 'folder',
						'folder_name' => $values['details/folder_name']),
					$box['key']['id']);
			} else {
				$box['key']['id'] = 
				setRow(
					'documents',
					array(
						'type' => 'folder',
						'folder_name' => $values['details/folder_name'],
						'ordinal' => 0));
			}
		}
		break;
	
	case 'zenario_document_tag':
		$box['key']['id'] = 
			setRow(
				'document_tags',
				array(
					'tag_name' => $values['details/tag_name']),
				$box['key']['id']);
		break;
	
	case 'zenario_document_properties':
	
		//var_dump($values['upload_image/zenario_common_feature__upload']);
		
		$id = (int)$box['key']['id'];
			
		$old_image = getRowsArray('documents',
									'file_id',  //file_id
									array('id' => $id)
									);
		$new_image = explode(',',$values['zenario_common_feature__upload']);
		//Remove the old image
		
		/*
		foreach ($old_image as $image_id) {
			if (!in_array($image_id, $new_image)) {
				deleteRow('documents', array('id' => $id));
				removeFileFromDocstore($image_id);
			}
		}*/
		
		foreach ($new_image as $file) {
			if (!in_array($file, $old_image)) {
				if ($path = getPathOfUploadedFileInCacheDir($file)) {
					$fileId = addFileToDocstoreDir('document_thumbnail', $path);
					$fileDetails = array();
					$fileDetails['thumbnail_id'] = $fileId;
					//update thumbnail
					setRow('documents', $fileDetails, $id);
					
				}
			}
		}
	
	
		deleteRow('document_tag_link', array('document_id' => $box['key']['id']));
		$tagIds = explode(',', $values['details/tags']);
		foreach ($tagIds as $tagId) {
			setRow('document_tag_link', array('tag_id' => $tagId, 'document_id' => $box['key']['id']), array('tag_id' => $tagId, 'document_id' => $box['key']['id']));
		}
		break;
	case 'zenario_file_type':
		exitIfNotCheckPriv('_PRIV_EDIT_CONTENT_TYPE');
		
		insertRow('document_types', array('type' => $values['details/type'], 'mime_type' => $values['details/mime_type']));
		$box['key']['id'] = $values['details/type'];
		
		break;
	
	
	case 'zenario_image':
		exitIfNotCheckPriv('_PRIV_MANAGE_MEDIA');
		
		updateRow(
			'files',
			array(
				'filename' => $values['details/filename'],
				'alt_tag' => $values['details/alt_tag'],
				'title' => $values['details/title'],
				'floating_box_title' => $values['details/floating_box_title']),
			$box['key']['id']);
		
		break;
	
	
	case 'zenario_content_type_details':
		if (checkPriv('_PRIV_EDIT_CONTENT_TYPE')) {
			
			$vals = array(
				'content_type_name_en' => $values['details/content_type_name_en'],
				'description_field' => $values['details/description_field'],
				'keywords_field' => $values['details/keywords_field'],
				'writer_field' => $values['details/writer_field'],
				'summary_field' => $values['details/summary_field'],
				'release_date_field' => $values['details/release_date_field'],
				'enable_summary_auto_update' => $values['details/enable_summary_auto_update'],
				'default_layout_id' => $values['details/default_layout_id']);
			
			if ($values['details/summary_field'] == 'hidden') {
				$vals['enable_summary_auto_update'] = 0;
			}
			
			switch ($box['key']['id']) {
				case 'document':
				case 'picture':
				case 'html':
					//HTML/Document/Picture fields cannot currently be mandatory
					foreach (array('description_field', 'keywords_field', 'summary_field', 'release_date_field') as $field) {
						if ($vals[$field] == 'mandatory') {
							$vals[$field] = 'optional';
						}
					}
					
					break;
					
				
				case 'event':
					//Event release dates must be hidden as it is overridden by another field
					$vals['release_date_field'] = 'hidden';
			}
			
			updateRow('content_types', $vals, $box['key']['id']);
		}
		
		break;
		
}

return false;