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
	case 'zenario__social/nav/comments/panel':
	
		require_once CMS_ROOT. 'zenario/libraries/mit/markitup/bbcode2html.inc.php';
		
		foreach ($panel['items'] as $id => &$item) {
			$item['comment_on_page'] =
				adminPhrase(
					'Pending Comment on "[[page_title]]", left on [[comment_last_edit]]', 
					array(
						'page_title' => linkToItem($item['content_id'], $item['content_type'], $fullPath = false, $request = '', $alias = false, $autoAddImportantRequests = false, $useAliasInAdminMode = true),
						'comment_last_edit' => formatDateTimeNicely($item['comment_last_edit'])));
	
			BBCode2Html($item['summary'], $enableColours = false, $enableImages = false, $enableLinks = false, $enableEmoticons = false);
	
			$item['frontend_link'] = linkToItem($item['content_id'], $item['content_type'], false, 'zenario_sk_return=navigation_path#zenario_comments_start_here');
		}
		
		break;
}
