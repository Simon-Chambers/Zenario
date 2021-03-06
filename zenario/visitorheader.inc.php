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


/*  
 *  This file can be used as a header file.
 *  It will include the full library of functions in the CMS, except for admin functions.
 *  It will then connect to the database.
 */


//Standard include file Header
if (!defined('NOT_ACCESSED_DIRECTLY')) {
	if (extension_loaded('mbstring')) mb_internal_encoding('UTF-8');
	
	//Attempt to set the working directory to the CMS Root, if it is not already
	//Note that I am using $_SERVER['SCRIPT_FILENAME'] and not __DIR__ to do this
	//because I want to resolve the relative URL when symlinks are used.
	$isFilelimit = 0;
	$dirname = $_SERVER['SCRIPT_FILENAME'];
	
	do {
		if (is_null($dirname) 
		 || ++$isFilelimit > 9) {
			echo
				'Could not resolve where the "zenario/" directory is.', "\n",
				'This could be a problem with a symlink, ',
				'or could be because you are trying to call this file from terminal ',
				'(which it is not designed to do).', "\n";
			exit;
		} else {
			chdir($dirname = dirname($dirname));
		}
	} while (!is_file('zenario/visitorheader.inc.php'));
	
	if (!defined('CMS_ROOT')) {
		define('CMS_ROOT', $dirname. '/');
	}
	
	//Define a constant to mark than any further include files have been legitamately included
	define('THIS_FILE_IS_BEING_DIRECTLY_ACCESSED', false);
	define('NOT_ACCESSED_DIRECTLY', true);
	unset($isFilelimit);
	unset($dirname);
}


//Display the content-type header
header('Content-Type: text/html; charset=UTF-8');

//Include the CMS' library of functions
require_once CMS_ROOT. 'zenario/liteheader.inc.php';
require CMS_ROOT. 'zenario/includes/cms.inc.php';

//Load site settings
loadSiteConfig();


//Check for any form submissions
if (!empty($_POST) && substr($_SERVER['HTTP_USER_AGENT'], 0, 10) != 'BlackBerry') {
	//Attempt to stop some XSS attacks by checking that the referer matches (except for old Blackberries which don't support this feature)
	if (substr(arrayKey($_SERVER, 'HTTP_REFERER'), 0, strlen(httpOrhttps(). arrayKey($_SERVER, 'HTTP_HOST'))) !== httpOrhttps(). arrayKey($_SERVER, 'HTTP_HOST')) {
		echo 'Sorry, but the referer for the form you just submitted does not seem to match this site';
		exit;
	}
}


//Include the Module Base Class
require CMS_ROOT. 'zenario/api/module_api.inc.php';
require CMS_ROOT. 'zenario/api/module_base_class/module_code.php';

//Include the currently running version of the Core CMS Module
foreach (cms_core::$editions as $className => $dirName) {
	if (inc($className)) {
		cms_core::$edition = $className;
		cms_core::$editionClass = new $className;
		break;
	}
}
unset($className);
unset($dirName);


//Attempt to get the host name
$host = httpHost();
$hostWOPort = httpHostWithoutPort();

//Check for a url redirect, and link to that item if one is found
if ($host != primaryDomain()
 && $hostWOPort != primaryDomain()
 && $redirect = getRow('spare_domain_names', array('content_id', 'content_type'), array('requested_url' => array($host, $hostWOPort)))) {
	header(
		'location:'. linkToItem($redirect['content_id'], $redirect['content_type'], true, '', false, false, false, primaryDomain()),
		true, 301);
	exit;
}


//Attempt to automatically log a User in if the cookie is set on the User's Machine
logUserInAutomatically();


//Set the user's language, if it is not set already
if (!empty($_SESSION) && empty($_SESSION['user_lang'])) {
	$_SESSION['user_lang'] = setting('default_language');
}

