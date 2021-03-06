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

//If a language has not yet been enabled, then we cannot do anything.
if (!setting('default_language')) {
	return;
}


//Look for any special pages
if ($resultSp = getRows('special_pages', true, array())) {
	while ($sp = sqlFetchAssoc($resultSp)) {
		
		$thisLang = false;
		$equivs = array();
		$langsToCreate = array();
		
		if (!$sp['equiv_id']) {
			//If the special page hasn't been created yet, make sure it is created.
			$langsToCreate[setting('default_language')] = true;
		
		} else {
			$thisLang = getContentLang($sp['equiv_id'], $sp['content_type']);
			$equivs = equivalences($sp['equiv_id'], $sp['content_type']);
		}
		
		//If the create_lang_equiv_content flag is set, also ensure that an equiv exists for each extra Language
		if ($sp['create_lang_equiv_content']) {
			$result = getRows('languages', array('id'), array());
			while ($row = sqlFetchAssoc($result)) {
				if (!isset($langsToCreate[$row['id']]) && !isset($equivs[$row['id']]) && $row['id'] != $thisLang) {
					$langsToCreate[$row['id']] = true;
				}
			}
		
		//Otherwise to ensure that the special page exists for the default language we may need to create one
		} elseif ($thisLang && $thisLang != setting('default_language') && !isset($equivs[setting('default_language')])) {
			$langsToCreate[setting('default_language')] = true;
		}
		
		if (!empty($langsToCreate)) {
			//Attempt to get details on the Plugin associated with this special page
			if ($sp['module_class_name'] && ($module = getModuleDetails($sp['module_class_name'], 'class'))) {
				$desc = false;
				if (!loadModuleDescription($module['class_name'], $desc)) {
					continue;
				}
				
				//Look through the special_pages tags for this special page
				//(Though most Modules that have special pages only have one special page.)
				if (!empty($desc['special_pages']) && is_array($desc['special_pages'])) {
					foreach($desc['special_pages'] as $page) {
						if (!empty($page['page_type']) && $page['page_type'] == $sp['page_type']) {
							foreach ($langsToCreate as $langId => $dummy) {
								//Create a new page
								$cID = $cVersion = false;
								$cType = 'html';
								createDraft($cID, $cIDFrom = false, $cType, $cVersion, $cVersionFrom = false, $langId);
								
								//Try to add an alias (so long as the alias is not taken)
								if ($alias = arrayKey($page, 'default_alias')) {
									//Attempt to make the alias unique if we're adding multiple languages
									if (checkRowExists('content', array('alias' => $alias))) {
										$alias .= '-'. $langId;
									}
									
									if (!is_array(validateAlias($alias))) {
										setRow('content', array('alias' => $alias), array('id' => $cID, 'type' => $cType));
									} else {
										$alias = '';
									}
								} else {
									$alias = '';
								}
								
								setRow('versions', array('title' => arrayKey($page, 'default_title')), array('id' => $cID, 'type' => $cType, 'version' => $cVersion));
								
								if (!$sp['equiv_id']) {
									$sp['equiv_id'] = $cID;
									$sp['content_type'] = $cType;
								} else {
									//For multilingal sites, make sure that translations of special pages are marked as translations
									$sp['equiv_id'] = recordEquivalence($sp['equiv_id'], $cID, $cType);
								}
								
								//Update the special pages table
								updateRow(
									'special_pages',
									array(
										'equiv_id' => $sp['equiv_id'],
										'content_type' => $sp['content_type']),
									array(
										'page_type' => $sp['page_type']));
								
								
								//Work out a free main slot to put a Plugin in
								$template = getRow('layouts', array('layout_id', 'file_base_name', 'family_name'), getDefaultTemplateId('html'));
								$slotName = getTemplateMainSlot($template['family_name'], $template['file_base_name']);
								
								//Check if this Plugin is Slotable, and if so attempt to put this Plugin on the page
								if ($module['is_pluggable']) {
									//Insert this Plugin onto the page
									if ($module['can_be_version_controlled']) {
										//Prefer a Wireframe Plugin if the Plugin allows it
										updatePluginInstanceInItemSlot(0, $slotName, $cID, $cType, $cVersion, $module['id']);
									
									} else {
										//Otherwise set a Reusable Instance there
										if (!$instanceId = getRow('plugin_instances', 'id', array('module_id' => $module['id'], 'content_id' => 0))) {
											//Create a new reusable instance if one does not already exist
											$errors = array();
											createNewInstance(
												$module['id'],
												$desc['default_instance_name'],
												$instanceId,
												$errors, $onlyValidate = false, $forceName = true);
										}
										
										updatePluginInstanceInItemSlot($instanceId, $slotName, $cID, $cType, $cVersion, $module['id']);
									}
								
								//Otherwise have the option to place a HTML Snippet in there
								} elseif ((arrayKey($page, 'default_content')) && ($snippetId = getRow('modules', 'id', array('class_name' => 'zenario_wysiwyg_editor')))) {
									
									//Try to find an editor
									if ($editorSlot = pluginMainSlot($cID, $cType, $cVersion)) {
										$instanceId = insertRow(
											'plugin_instances',
											array(
												'module_id' => $snippetId,
												'content_id' => $cID,
												'content_type' => $cType,
												'content_version' => $cVersion,
												'slot_name' => $editorSlot));
									
										setRow(
											'plugin_settings',
											array(
												'instance_id' => $instanceId,
												'name' => 'html',
												'value' => arrayKey($page, 'default_content'),
												'is_content' => 'version_controlled_content',
												'format' => 'translatable_html'));
									}
								}
								
								//Insert Menu Nodes
								$redundancy = 'primary';
								if (arrayKey($page, 'menu_title')) {
									
									if ($menu = getMenuItemFromContent($cID, $cType, false, 'Main', true)) {
										$menuId = $menu['id'];
									
									} else {
										$menuId = saveMenuDetails(array(
											'section_id' => 'Main',
											'redundancy' => $redundancy,
											'name' => arrayKey($page, 'menu_title'),
											'rel_tag' => arrayKey($page, 'menu_rel_tag'),
											'target_loc' => 'int',
											'content_id' => $cID,
											'content_type' => $cType,
											'hide_private_item' => 
												engToBooleanArray($page, 'only_show_to_visitors_who_are_logged_in')?
													3
												: (
													engToBooleanArray($page, 'only_show_to_visitors_who_are_logged_out')?
														2
													:	0)));
									}
									saveMenuText($menuId, $langId, array('name' => arrayKey($page, 'menu_title')));
									
									$redundancy = 'secondary';
								}
								
								if (arrayKey($page, 'footer_menu_title')) {
									
									if ($menu = getMenuItemFromContent($cID, $cType, false, 'Footer', true)) {
										$menuId = $menu['id'];
									
									} else {
										$menuId = saveMenuDetails(array(
											'section_id' => 'Footer',
											'redundancy' => $redundancy,
											'name' => arrayKey($page, 'footer_menu_title'),
											'rel_tag' => arrayKey($page, 'menu_rel_tag'),
											'target_loc' => 'int',
											'content_id' => $cID,
											'content_type' => $cType,
											'ordinal' => $row[0],
											'hide_private_item' => 
												engToBooleanArray($page, 'only_show_to_visitors_who_are_logged_in')?
													3
												: (
													engToBooleanArray($page, 'only_show_to_visitors_who_are_logged_out')?
														2
													:	0)));
									}
									saveMenuText($menuId, $langId, array('name' => arrayKey($page, 'footer_menu_title')));
								}
							}
							
							//Publish the page straight away if requested
							if ($sp['publish']) {
								publishContent($cID, $cType);
							}
						}
					}
				}
			}
		}
	}
}
