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


//Add a new Banner to the nest, placing it in the right-most tab
if (($moduleId = getModuleIdByClassName('zenario_banner'))
 && ($instance = getPluginInstanceDetails($instanceId))
 && ($image = getRow('files', array('id', 'filename', 'width', 'height'), array('usage' => 'inline', 'id' => $imageId)))) {
	
	if ($addTab) {
		self::addTab($instanceId);
	}
	
	$nestId = self::addPlugin($moduleId, $instanceId, false, adminPhrase('[[filename]] [[[width]] × [[height]]]', $image));
	
	setRow(
		'plugin_settings',
		array(
			'value' => '_CUSTOM_IMAGE',
			'is_content' => $instance['content_id']? 'version_controlled_setting' : 'synchronized_setting'),
		array(
			'instance_id' => $instanceId,
			'nest' => $nestId,
			'name' => 'image_source'));
	
	setRow(
		'plugin_settings',
		array(
			'value' => $image['id'],
			'is_content' => $instance['content_id']? 'version_controlled_setting' : 'synchronized_setting',
			'foreign_key_to' => 'file',
			'foreign_key_id' => $image['id']),
		array(
			'instance_id' => $instanceId,
			'nest' => $nestId,
			'name' => 'image'));
	
	setRow(
		'plugin_settings',
		array(
			'value' => $image['filename'],
			'is_content' => $instance['content_id']? 'version_controlled_setting' : 'synchronized_setting'),
		array(
			'instance_id' => $instanceId,
			'nest' => $nestId,
			'name' => 'alt_tag'));
	
	if ($instance['content_id']) {
		syncInlineFileContentLink($instance['content_id'], $instance['content_type'], $instance['content_version']);
	}
	
	return $nestId;
} else {
	return false;
}