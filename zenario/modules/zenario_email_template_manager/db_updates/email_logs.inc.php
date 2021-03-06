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
revision(1,
<<<_sql
	DROP TABLE IF EXISTS [[DB_NAME_PREFIX]][[ZENARIO_EMAIL_TEMPLATE_MANAGER_PREFIX]]email_template_sending_log
_sql

, <<<_sql
	CREATE TABLE [[DB_NAME_PREFIX]][[ZENARIO_EMAIL_TEMPLATE_MANAGER_PREFIX]]email_template_sending_log (
		id int(10) unsigned NOT NULL AUTO_INCREMENT,
		plugin_id int(10) ,
		plugin_instance_id int(10) ,
		content_id int(10) ,
		content_type varchar(25) ,
		content_version int(10),
		email_template_id int(10) NOT NULL,
		email_template_name varchar(255) NOT NULL,
		email_subject varchar(255),
		email_address_to varchar(255) NOT NULL,
		email_address_from varchar(255) NOT NULL,
		email_name_from varchar(255),
		email_body TEXT,
		sent_timestamp datetime NOT NULL,			
	PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8
_sql
);
revision(47,
<<<_sql
	ALTER TABLE [[DB_NAME_PREFIX]][[ZENARIO_EMAIL_TEMPLATE_MANAGER_PREFIX]]email_template_sending_log 
	CHANGE COLUMN sent_timestamp sent_datetime datetime NOT NULL
_sql
);
revision(50,
<<<_sql
	ALTER TABLE [[DB_NAME_PREFIX]][[ZENARIO_EMAIL_TEMPLATE_MANAGER_PREFIX]]email_template_sending_log 
	CHANGE COLUMN email_template_id email_template_id int(10) NULL
_sql
);
revision(51,
<<<_sql
	ALTER TABLE [[DB_NAME_PREFIX]][[ZENARIO_EMAIL_TEMPLATE_MANAGER_PREFIX]]email_template_sending_log 
	CHANGE COLUMN email_template_name email_template_name varchar(255) NULL
_sql
);
revision(54
, <<<_sql
	ALTER TABLE `[[DB_NAME_PREFIX]][[ZENARIO_EMAIL_TEMPLATE_MANAGER_PREFIX]]email_template_sending_log`
	CHANGE COLUMN `plugin_id` `module_id` int(10) unsigned NULL
_sql
, <<<_sql
	ALTER TABLE `[[DB_NAME_PREFIX]][[ZENARIO_EMAIL_TEMPLATE_MANAGER_PREFIX]]email_template_sending_log`
	CHANGE COLUMN `plugin_instance_id` `instance_id` int(10) unsigned NULL
_sql
);
revision(57
, <<<_sql
	ALTER TABLE [[DB_NAME_PREFIX]][[ZENARIO_EMAIL_TEMPLATE_MANAGER_PREFIX]]email_template_sending_log 
	ADD COLUMN email_address_to_overridden_by varchar(255) after email_address_to
_sql
);
revision(58
, <<<_sql
	ALTER TABLE [[DB_NAME_PREFIX]][[ZENARIO_EMAIL_TEMPLATE_MANAGER_PREFIX]]email_template_sending_log 
	ADD COLUMN attachment_present tinyint after `email_body`
_sql
);
revision(95
, <<<_sql
	ALTER TABLE [[DB_NAME_PREFIX]][[ZENARIO_EMAIL_TEMPLATE_MANAGER_PREFIX]]email_template_sending_log 
	ADD COLUMN `status` enum('success','failure') DEFAULT 'success' AFTER `sent_datetime` 
_sql
);

revision(98
, <<<_sql
	ALTER TABLE [[DB_NAME_PREFIX]][[ZENARIO_EMAIL_TEMPLATE_MANAGER_PREFIX]]email_template_sending_log
	ADD COLUMN email_address_replyto varchar(255) NOT NULL after `email_address_from`
_sql
);

revision(99
, <<<_sql
	ALTER TABLE [[DB_NAME_PREFIX]][[ZENARIO_EMAIL_TEMPLATE_MANAGER_PREFIX]]email_template_sending_log
	MODIFY COLUMN email_address_replyto varchar(255)
_sql
);

revision(100
, <<<_sql
	ALTER TABLE [[DB_NAME_PREFIX]][[ZENARIO_EMAIL_TEMPLATE_MANAGER_PREFIX]]email_template_sending_log
	ADD COLUMN email_name_replyto varchar(255)
_sql
);