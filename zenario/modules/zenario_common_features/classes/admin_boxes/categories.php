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


class zenario_common_features__admin_boxes__categories extends module_base_class {
	
	protected function fillFieldValues(&$values, &$rec){
		foreach($rec as $k => $v){
			if (isset($values[$k])) {
				$values[$k] = $v;
			}
		}
	}
	
	public function fillAdminBox($path, $settingGroup, &$box, &$fields, &$values) {
		if ($id = arrayKey($box, 'key', 'id')) {
			$record = getRow('categories', true, $id);
			$box['key']['parent_id'] = $record['parent_id'];
			$this->fillFieldValues($values, $record);
			
			$box['title'] = adminPhrase('Editing the category "[[name]]"', $record);
		} else {
			$box['title'] = adminPhrase('Creating a category');
			$box['key']['parent_id'] = request('refiner__parent_category');
		}
	}
	
	public function validateAdminBox($path, $settingGroup, &$box, &$fields, &$values, $changes, $saving) {
		$catId = arrayKey($box, 'key', 'id');
	
		if(isset($values['name']) && !$values['name']) {
			$box['tabs']['details']['errors'][] = adminPhrase('_MSG_ENTER_ALL_FIELDS');
		}
		if (isset($values['name']) && checkIfCategoryExists($values['name'], $catId, $box['key']['parent_id'])) {
			$box['tabs']['details']['errors'][] = adminPhrase('_MSG_CATEGORY_ALREADY_EXISTS', array('name' => htmlspecialchars($values['name'])));
		}
	}
	
	public function saveAdminBox($path, $settingGroup, &$box, &$fields, &$values, $changes) {
		
		$row = array('name' => $values['name'], 'public' => $values['public']);
		
		if ($box['key']['id']) {
			updateRow('categories', $row, $box['key']['id']);
		} else {
			$row['parent_id'] = $box['key']['parent_id'];
			$box['key']['id'] = insertRow('categories', $row);
		}
		
		
		
		if ($values['public']) {
			foreach (getLanguages() as $lang) {
				if (!checkRowExists('visitor_phrases', array('language_id' => $lang['id'], 'code' => '_CATEGORY_'. (int) $box['key']['id'], 'module_class_name' => 'zenario_common_features'))) {
					$sql = "
				INSERT INTO ". DB_NAME_PREFIX. "visitor_phrases SET
					language_id = '". sqlEscape($lang['id']). "',
					local_text = '". sqlEscape($values['name']). "',
					code = '_CATEGORY_". (int) $box['key']['id']. "',
					module_class_name = 'zenario_common_features'";
					sqlQuery($sql);
				}
			}
		}
	}
}
