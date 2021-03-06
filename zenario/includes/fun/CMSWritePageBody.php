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


$bodyTag = '<body class="no_js '. browserBodyClass();

//Add the Admin Toolbar in Admin Mode
if (checkPriv()) {
	if ($includeAdminToolbar && cms_core::$cID) {
		//Set the current Admin Toolbar tab in Admin Mode
		$toolbars = array();
		CMSWritePageBodyAdminClass($bodyTag, $toolbars);
	} else {
		$includeAdminToolbar = false;
		$bodyTag .= ' zenario_adminMode zenario_pageMode_preview zenario_menuWand_off zenario_slotWand_off';
	}
} else {
	$includeAdminToolbar = false;
}

$toolbarAttr = '';
if ($includeAdminToolbar && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 6') === false) {
	//Make the Admin Toolbar float above the page, except in IE 6 which doesn't support the CSS.
	$attributes .= ' style="margin-top: 129px;"';
	$toolbarAttr = ' style="height: 129px; width: 100%; position: fixed; top: 0px; left: 0px; margin: 0px; z-index: 1000;"';
}

echo "\n", $bodyTag;

if ($showPreview) {
	echo ' zenario_showing_preview';
}

echo '"', $attributes, '>';

if (!$showPreview) {
	echo '
<script type="text/javascript">
	document.body.className = document.body.className.replace(\'no_\', \'\');
</script>';
}

if ($includeAdminToolbar) {
	CMSWritePageBodyAdminToolbar($toolbars, $toolbarAttr);
}