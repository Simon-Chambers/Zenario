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
			case 'zenario__languages/nav/zenario__countries/panel':
			
			
				foreach ($panel['items'] as $K => &$item) {
					
					if ($item['country_status']=='active') {
						$item['traits'] = array('active' => true);
					} else {
						$item['traits'] = array('suspended' => true);
					}
					
					if ( $phraseId = getRow("visitor_phrases", 'id', array('code' => '_COUNTRY_NAME_' . $item['country_code']) )) {
						$item['traits']['show_localizations'] = "Yes";
					} 
				
					if (!checkRowExists(ZENARIO_COUNTRY_MANAGER_PREFIX . 'country_manager_regions', array('country_id' => $K))) {
						$item['traits']['can_delete_country'] = true;
					}
				}
				break;

			case 'zenario__languages/nav/zenario__countries/panel/regions/panel':
				foreach ($panel['items'] as $K => &$item) {
					if ( $refinerId && $phraseId = getRow("visitor_phrases", 'id', array('code' => '_REGION_NAME_' . $item['region_code']) )) {
						$item['traits']['show_localizations'] = "Yes";
					}

					if (!checkRowExists(ZENARIO_COUNTRY_MANAGER_PREFIX . 'country_manager_regions', array('parent_id' => $K))) {
						$item['traits']['can_delete_region'] = true;
					}
				}
				switch (get('refinerName')){
					case 'country_code_filter':
						if ($country=$this->getEnglishCountryName($refinerId)){
							$panel['title'] = 'Regions in the Country "'. $country . '"';
						}
						break;
					case 'parent_id':
						if ($region=$this->getEnglishRegionName($refinerId)){
							$panel['title'] = 'Sub-Regions of the Region "'. $region . '"';
						}
						break;
				}
				break;
		}

?>