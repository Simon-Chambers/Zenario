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


if (needRevision(156)) {
	
	//Add or update a record in the custom_datasets table with the correct details
	//(Note if you upgrade from version 7 or earlier this will have been done manually
	// by the migration script, but it's safe to call again.)
	$datasetId = registerDataset(
		'Documents',
		'documents_custom_data',
		'documents',
		'zenario_document_properties',
		'zenario__content/nav/hierarchical_files/panel',
		'',
		'');
	//registerDataset($label, $table, $system_table = '', $extends_admin_box = '', $extends_organizer_panel = '', $view_priv = '', $edit_priv = '')
	
	
	//Register system fields
	//(System fields are registered automatically when an admin views the datasets panel in Organizer, so this step
	// is optional, but when they are registered automatically they default to the "other_system_field" type and are
	// not selectable in things such as User Forms. Specifically registering them like this will ensure they are
	// usable.)
	//(Again, if you upgrade from version 7 or earlier these will have also been done manually
	// by the migration script, but they're also safe to call again.)
	//registerDatasetSystemField($datasetId, $type, $tabName, $fieldName, $dbColumn = false, $validation = 'none', $valuesSource = '')
	
	revision(156);
}