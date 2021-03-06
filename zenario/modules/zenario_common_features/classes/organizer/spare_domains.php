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


class zenario_common_features__organizer__spare_domains extends module_base_class {
	
	public function preFillOrganizerPanel($path, &$panel, $refinerName, $refinerId, $mode) {
		if (getRow('site_settings', 'value', 'primary_domain')) {
			$panel['no_items_message'] .= ' '.
				adminPhrase('No Spare Domain Names have been defined.');
		} else {
			$panel['no_items_message'] .= ' '.
				adminPhrase('You currently cannot use Spare Domain Names on your site as you have not set a Primary Domain. Please go to Configuration -> Site settings -> Primary Domain to set a domain.');
			
			unset($panel['db_items']);
			unset($panel['collection_buttons']['create']);
		}
	}
	
	public function fillOrganizerPanel($path, &$panel, $refinerName, $refinerId, $mode) {
		if (!empty($panel['items']) && is_array($panel['items'])) {
			foreach ($panel['items'] as &$item) {
				if ($item['frontend_link']) {
					$item['frontend_link'] = 'http://'. $item['frontend_link'];
				}
			}
		}
	}
	
	public function lineStorekeeperCSV($path, &$columns, $refinerName, $refinerId) {
		if (getRow('site_settings', 'value', 'primary_domain')) {
			$panel['no_items_message'] .= ' '.
				adminPhrase('No Spare Domain Names have been defined.');
		} else {
			$panel['no_items_message'] .= ' '.
				adminPhrase('You currently cannot use Spare Domain Names on your site as you have not set a Primary Domain. Please go to Configuration -> Site settings -> Primary Domain to set a domain.');
				
			unset($panel['db_items']);
			unset($panel['collection_buttons']['create']);
		}
	}
	
	public function handleOrganizerPanelAJAX($path, $ids, $ids2, $refinerName, $refinerId) {
		if (post('delete') && checkPriv('_PRIV_MANAGE_SPARE_DOMAIN_NAME')) {
			$sql = "
				DELETE FROM ". DB_NAME_PREFIX . "spare_domain_names
				WHERE requested_url = '". sqlEscape(decodeItemIdForStorekeeper($ids)). "'";
			sqlQuery($sql);
		}
	}
}