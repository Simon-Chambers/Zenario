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


if (is_array(arrayKey($box,'tabs'))) {
	foreach ($box['tabs'] as $tabName => &$tab) {
		if (!isInfoTag($tabName)) {
			foreach ($tab['fields'] as &$field) {
				if ($setting = arrayKey($field, 'site_setting', 'name')) {
					$field['value'] = setting($setting);
					
					if ($setting == 'default_template_family') {
						$field['values'] = array($field['value'] => $field['value']);
					
					} elseif ($setting == 'primary_domain') {
						$field['value'] = ifNull($field['value'], 'none');
					}
				}
			}
		}
	}
}

if (isset($box['tabs']['email']['fields']['email_address_system'])) {
	$box['tabs']['email']['fields']['email_address_system']['value'] = EMAIL_ADDRESS_GLOBAL_SUPPORT;
}

if (isset($box['tabs']['filesizes']['fields']['apache_max_filesize'])) {
	$box['tabs']['filesizes']['fields']['apache_max_filesize']['value'] = apacheMaxFilesize();
}

if (isset($box['tabs']['filesizes']['fields']['max_allowed_packet'])) {
	$box['tabs']['filesizes']['fields']['max_allowed_packet']['value'] = '?';
	
	if ($result = @sqlSelect("SHOW VARIABLES LIKE 'max_allowed_packet'")) {
		$settings = array();
		if ($row = sqlFetchRow($result)) {
			$box['tabs']['filesizes']['fields']['max_allowed_packet']['value'] = $row[1];
		}
	}
}

if (isset($box['tabs']['default_language']['fields']['default_language'])) {
	getLanguageSelectListOptions($box['tabs']['default_language']['fields']['default_language']);
}

if (isset($box['tabs']['urls']['fields']['mod_rewrite_suffix'])) {
	$box['tabs']['urls']['fields']['mod_rewrite_suffix']['values'] = array('.htm' => '.htm', '.html' => '.html');
}

if (isset($box['tabs']['dates']['fields']['vis_date_format_short'])) {
	formatDateFormatSelectList($box['tabs']['dates']['fields']['vis_date_format_short']);
	formatDateFormatSelectList($box['tabs']['dates']['fields']['vis_date_format_med']);
	formatDateFormatSelectList($box['tabs']['dates']['fields']['vis_date_format_long']);
	formatDateFormatSelectList($box['tabs']['dates']['fields']['vis_date_format_datepicker'], true);
	formatDateFormatSelectList($box['tabs']['dates']['fields']['storekeeper_date_format'], true);
}

if (isset($box['tabs']['primary_domain']['fields']['primary_domain'])) {
	if (setting('primary_domain')) {
		$box['tabs']['primary_domain']['fields']['primary_domain']['values'][setting('primary_domain')] = 'http:// or https://'. setting('primary_domain');
	}
	if ($_SERVER['HTTP_HOST'] != setting('primary_domain')) {
		$box['tabs']['primary_domain']['fields']['primary_domain']['values'][$_SERVER['HTTP_HOST']] = 'http:// or https://'. $_SERVER['HTTP_HOST'];
	}
}

if (isset($box['tabs']['speed']['fields']['have_query_cache'])) {
	if ($result = @sqlSelect("SHOW VARIABLES LIKE '%query_cache%'")) {
		$settings = array();
		while ($row = sqlFetchRow($result)) {
			$settings[$row[0]] = $row[1];
		}
		
		if (!$box['tabs']['speed']['fields']['query_cache_size']['hidden'] = !(
			$box['tabs']['speed']['fields']['have_query_cache']['value'] =
			$box['tabs']['speed']['fields']['have_query_cache']['current_value'] =
				engToBooleanArray($settings, 'have_query_cache') && engToBooleanArray($settings, 'query_cache_type')
		)) {
			$box['tabs']['speed']['fields']['query_cache_size']['value'] =
			$box['tabs']['speed']['fields']['query_cache_size']['current_value'] = formatFilesizeNicely((int) arrayKey($settings, 'query_cache_size'), $precision = 1, $adminMode = true);
		}
	
	} else {
		$box['tabs']['speed']['fields']['have_query_cache']['post_field_html'] = ' '. adminPhrase('(Could not check)');
		$box['tabs']['speed']['fields']['query_cache_size']['hidden'] = true;
	}
}

if (isset($box['tabs']['test']['fields']['test_send_email_address'])) {
	$adminDetails = getAdminDetails(adminId());
	$box['tabs']['test']['fields']['test_send_email_address']['value'] = $adminDetails['admin_email'];
}

//Working copy images store a number for enabled. But the UI is a checkbox for enabled, and then a number if enabled.
//Convert the format when displaying the fields
if (isset($box['tabs']['image_sizes']['fields']['thumbnail_wc'])) {
	if (setting('thumbnail_wc_image_size')) {
		$box['tabs']['image_sizes']['fields']['thumbnail_wc']['value'] = 1;
	} else {
		$box['tabs']['image_sizes']['fields']['thumbnail_wc']['value'] = '';
		$box['tabs']['image_sizes']['fields']['thumbnail_wc_image_size']['value'] = 300;
	}
}
if (isset($box['tabs']['image_sizes']['fields']['working_copy_image'])) {
	if (setting('working_copy_image_size')) {
		$box['tabs']['image_sizes']['fields']['working_copy_image']['value'] = 1;
	} else {
		$box['tabs']['image_sizes']['fields']['working_copy_image']['value'] = '';
		$box['tabs']['image_sizes']['fields']['working_copy_image_size']['value'] = 1000;
	}
}

//Set the value of the template directory
if (isset($box['tabs']['template_dir']['fields']['template_dir'])) {
	$box['tabs']['template_dir']['fields']['template_dir']['value'] = CMS_ROOT. 'zenario_custom/templates/grid_templates';
}

//On multisite sites, don't allow local Admins to change the directory paths
if (globalDBDefined() && !session('admin_global_id')) {
	foreach (array('backup_dir', 'docstore_dir') as $dir) {
		if (isset($box['tabs'][$dir]['edit_mode'])) {
			$box['tabs'][$dir]['edit_mode']['enabled'] = false;
		}
	}
}

//Load the value of the logo/favicon if it is set
if (isset($box['tabs']['logo']['fields']['logo'])) {
	$values['logo/logo'] = getRow('files', 'id', array('usage' => 'brand_logo'));
}
if (isset($box['tabs']['sk']['fields']['favicon'])) {
	$values['sk/favicon'] = getRow('files', 'id', array('usage' => 'organizer_favicon'));
}


return false;