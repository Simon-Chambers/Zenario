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
		if ($values['show_headings'] != 1) {
			$fields['heading_if_items']['hidden'] = true;
		} else {
			$fields['heading_if_items']['hidden'] = false;
		}
		if ($values['show_headings_if_no_items'] != 1) {
			$fields['heading_if_no_items']['hidden'] = true;
		} else {
			$fields['heading_if_no_items']['hidden'] = false;
		}
		$box['tabs']['overall_list']['fields']['more_hyperlink_target']['hidden'] = 
			!$values['overall_list/show_more_link'];
		
		$box['tabs']['each_item']['fields']['canvas']['hidden'] = 
			!$values['each_item/show_sticky_images'];		

		$box['tabs']['each_item']['fields']['width']['hidden'] = 
			$box['tabs']['each_item']['fields']['canvas']['hidden']
		 || !in($values['each_item/canvas'], 'fixed_width', 'fixed_width_and_height', 'resize_and_crop');

		$box['tabs']['each_item']['fields']['height']['hidden'] = 
			$box['tabs']['each_item']['fields']['canvas']['hidden']
		 || !in($values['each_item/canvas'], 'fixed_height', 'fixed_width_and_height', 'resize_and_crop');

		$box['tabs']['each_item']['fields']['date_format']['hidden'] = 
		$box['tabs']['each_item']['fields']['show_times']['hidden'] = 
			!$values['each_item/show_dates'];
		
		$box['tabs']['each_item']['fields']['use_download_page']['hidden'] = 
			$values['first_tab/content_type'] != 'all'
		 && $values['first_tab/content_type'] != 'document';
		
		$box['tabs']['pagination']['fields']['page_limit']['hidden'] = 
		$box['tabs']['pagination']['fields']['pagination_style']['hidden'] = 
			!$values['pagination/show_pagination'];
		
		
		if (isset($box['tabs']['each_item']['fields']['canvas'])
		 && empty($box['tabs']['each_item']['fields']['canvas']['hidden'])) {
			if ($values['each_item/canvas'] == 'fixed_width') {
				$box['tabs']['each_item']['fields']['width']['note_below'] =
					adminPhrase('Images may be scaled down maintaining aspect ratio, but will never be scaled up.');
			
			} else {
				unset($box['tabs']['each_item']['fields']['width']['note_below']);
			}
			
			if ($values['each_item/canvas'] == 'fixed_height'
			 || $values['each_item/canvas'] == 'fixed_width_and_height') {
				$box['tabs']['each_item']['fields']['height']['note_below'] =
					adminPhrase('Images may be scaled down maintaining aspect ratio, but will never be scaled up.');
			
			} elseif ($values['each_item/canvas'] == 'resize_and_crop') {
				$box['tabs']['each_item']['fields']['height']['note_below'] =
					adminPhrase('Images may be scaled up or down maintaining aspect ratio.');
			
			} else {
				unset($box['tabs']['each_item']['fields']['height']['note_below']);
			}
		}
		
		break;
}