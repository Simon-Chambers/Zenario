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
 *  It will not connect to the database.
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


if (!is_file(CMS_ROOT. 'zenario/visitorheader.inc.php')) {
	echo 'Your CMS_ROOT value is not correctly set. \''. CMS_ROOT. '\' is not the correct path to the CMS.';
	exit;
}


//Attempt to calculate the SUBDIRECTORY, if not set already
//(Note that similar logic is used to validate the SUBDIRECTORY at the top of admin/welcome.php)
if (!defined('SUBDIRECTORY')) {
	//Get the original included filepath
	$file = substr($_SERVER['SCRIPT_FILENAME'], strlen(CMS_ROOT));
	
	//Get the included location
	$self = $_SERVER['PHP_SELF'];
	
	//If the two don't match up, try chopping the filenames off the ends of the path
	if (substr($self, -strlen($file)) != $file) {
		$pos = max(strrpos($file, '/'), strrpos($file, '\\'), -1);
		$file = substr($file, 0, $pos? $pos + 1 : 0);
		
		$pos = max(strrpos($self, '/'), strrpos($self, '\\'), -1);
		$self = substr($self, 0, $pos? $pos + 1 : 0);
		
		unset($pos);
	}
	
	//Trim the included location by the filepath to get the current SUBDIRECTORY
	if (strlen($file)) {
		$subdir = substr($self, 0, -strlen($file));
	} elseif ($self) {
		$subdir = $self;
	} else {
		$subdir = '/';
	}
	
	define('SUBDIRECTORY', $subdir);
	unset($backtrace);
	unset($file);
	unset($self);
	unset($subdir);
}


//Add includes from the CMS
require_once CMS_ROOT. 'zenario/cacheheader.inc.php';
require CMS_ROOT. 'zenario/api/admin_functions.inc.php';
require CMS_ROOT. 'zenario/api/array_and_object_functions.inc.php';
require CMS_ROOT. 'zenario/api/content_item_functions.inc.php';
require_once CMS_ROOT. 'zenario/api/database_functions.inc.php';
require CMS_ROOT. 'zenario/api/file_functions.inc.php';
require CMS_ROOT. 'zenario/api/link_path_and_url_core_functions.inc.php';
require CMS_ROOT. 'zenario/api/string_functions.inc.php';
require CMS_ROOT. 'zenario/api/system_functions.inc.php';
require CMS_ROOT. 'zenario/api/user_functions.inc.php';


//Get the latest revision number from latest_revision_no.inc.php
function getLatestRevisionNumber() {
	return LATEST_REVISION_NO;
}

//Get the CMS version number from latest_revision_no.inc.php
//Also attempt to guess whether this is a dev site or an on-demand site
function getCMSVersionNumber() {
	
	if (file_exists(CMS_ROOT. 'developer.txt')) {
		return ZENARIO_CMS_VERSION. ' (dev)';
	
	} elseif (is_dir(CMS_ROOT. 'zenario/libraries/not_to_redistribute')) {
		return ZENARIO_CMS_VERSION. ' On-Demand';
	
	} else {
		return ZENARIO_CMS_VERSION;
	}
}




//Write the URLBasePath, and other related JavaScript variables, to the page
function CMSWritePageHead($prefix, $mode = false, $includeOrganizer = true) {
	require funIncPath(__FILE__, __FUNCTION__);
}

function CMSWritePageBody($attributes = '', $includeAdminToolbar = true, $showPreview = false) {
	require funIncPath(__FILE__, __FUNCTION__);
}

function CMSWritePageBodyAdminClass(&$class, &$toolbars) {
	require funIncPath(__FILE__, __FUNCTION__);
}

function CMSWritePageBodyAdminToolbar(&$toolbars, $toolbarAttr = '') {
	require funIncPath(__FILE__, __FUNCTION__);
}

//Write the URLBasePath, and other related JavaScript variables, to the page
function CMSWritePageFoot($prefix, $mode = false, $includeOrganizer = true, $includeAdminToolbar = true) {
	require funIncPath(__FILE__, __FUNCTION__);
}




function showCookieConsentBox() {
		
	//Add the login link for admins if this looks like a logged out admin
	if (isset($_COOKIE['COOKIE_LAST_ADMIN_USER']) && !checkPriv()) { 
		$url = getGlobalURL(true). SUBDIRECTORY. 'zenario/admin/welcome.php?cID='. rawurlencode(request('cID')). '&cType='. rawurlencode(request('cType'));
		
		echo '
			<div class="admin_link">
				<a href="', $url, '">', adminPhrase('Login'), '</a>
			</div>';
		
		//Never allow a page with an "Admin" link to be cached...
		cms_core::$canCache = false;
		
		//Note that this should override showing the cookie consent box, no matter the settings
		return;
	}

	switch (setting('cookie_require_consent')) {
		case 'implied':
			//Implied consent - show the cookie message, just once. Continuing to use the site counts as acceptance.
			if (!empty($_COOKIE['cookies_accepted']) || session('cookies_accepted')) {
				return;
			}
			
			echo '
<!--googleoff: all-->
	<div class="zenario_cookie_consent">
		<div class="zenario_cookie_consent_wrap">
			<div class="zenario_cc_message">', phrase('_COOKIE_CONSENT_IMPLIED_MESSAGE'), '</div>
			<div class="zenario_cc_buttons">
				<div class="zenario_cc_continue">
					<a href="" onclick="$(\'div.zenario_cookie_consent\').slideUp(\'slow\'); return false;">', phrase('_COOKIE_CONSENT_CONTINUE'), '</a>
				</div>
			</div>
		</div>
	</div>
<!--googleon: all-->';
			
			$_SESSION['cookies_accepted'] = true;
			break;
			
			
		case 'explicit':
			//Explicit consent - show the cookie message until it is accepted or rejected, if the reject button is enabled.
			if (cms_core::$cookieConsent == 'hide'
			 || canSetCookie()
			 || (cms_core::$cookieConsent != 'require') && session('cookies_rejected')) {
				return;
			}
			
			echo '
<!--googleoff: all-->
	<div class="zenario_cookie_consent">
		<div class="zenario_cookie_consent_wrap">
		<div class="zenario_cc_message">', phrase('_COOKIE_CONSENT_MESSAGE'), '</div>
		<div class="zenario_cc_close">
			<a href="#" onclick="$(\'div.zenario_cookie_consent\').fadeOut(\'slow\'); return false;">', phrase('_COOKIE_CONSENT_CLOSE'), '</a>
		</div>
		<div class="zenario_cc_buttons">';
	
	if (setting('cookie_consent_type') == 'message_accept_reject' && cms_core::$cookieConsent != 'require') {
		echo '
			<div class="zenario_cc_reject">
				<a href="zenario/quick_ajax.php?accept_cookies=0">', phrase('_COOKIE_CONSENT_REJECT'), '</a>
			</div>';
	}
	
	echo '
			<div class="zenario_cc_accept">
				<a href="zenario/quick_ajax.php?accept_cookies=1">', phrase('_COOKIE_CONSENT_ACCEPT'), '</a>
			</div>
		</div>
		</div>
	</div>
<!--googleon: all-->';
			break;
	}
}


//Display a Plugin in a slot
function slot($slotName, $mode = false) {
	//echo '<div style="border: 1px black solid;">'. $slotName. '</div>'; return;
	
	//Replacing anything non-alphanumeric with an underscore
	$slotName = HTMLId($slotName);
	
	//Start the plugin if it is there, then return it to the Layout
	if (!empty(cms_core::$slotContents[$slotName])
	 && !empty(cms_core::$slotContents[$slotName]['class'])
	 && empty(cms_core::$slotContents[$slotName]['error'])) {
		cms_core::$slotContents[$slotName]['used'] = true;
		cms_core::$slotContents[$slotName]['found'] = true;
		
		cms_core::$slotContents[$slotName]['class']->start();
		
		$slot = cms_core::$slotContents[$slotName]['class'];
	
	//If we didn't find a plugin, but we're in admin mode, 
	//return an "empty" plugin derrived from the base class so that the controls are still displayed to the admin
	} elseif (checkPriv()) {
		//Mark that we've found this slot
		setupNewBaseClassPlugin($slotName);
		cms_core::$slotContents[$slotName]['found'] = true;
		
		cms_core::$slotContents[$slotName]['class']->start();
		
		$slot = cms_core::$slotContents[$slotName]['class'];
	
	} else {
		$slot = false;
	}
	
	if ($mode == 'grid' || $mode == 'outside_of_grid') {
		//New functionality for grids - output the whole slot, don't use a return value
		if ($slot) {
			$slot->show();
			$slot->end();
		}
		//Add some padding for empty grid slots so they don't disappear and break the grid
		if ($mode == 'grid' && (!$slot || checkPriv())) {
			echo '<span class="pad_slot pad_tribiq_slot">&nbsp;</span>';
			//Note: "pad_tribiq_slot" was the old class name.
			//I'm leaving it in for a while as any old Grid Layouts might still be using that name
			//and they won't be updated until the next time someone edits them.
		}
		
	} else {
		//Old functionality - return the class object
		return $slot;
	}
}

function setupNewBaseClassPlugin($slotName) {
	if (!isset(cms_core::$slotContents[$slotName])) {
		cms_core::$slotContents[$slotName] = array();
	}
	
	if (!isset(cms_core::$slotContents[$slotName]['class']) || empty(cms_core::$slotContents[$slotName]['class'])) {
		cms_core::$slotContents[$slotName]['class'] = new module_base_class;
		cms_core::$slotContents[$slotName]['class']->setInstance(
			array(cms_core::$cID, cms_core::$cType, cms_core::$cVersion, $slotName, false, false, false, false, false, false, false, false, false, false));
	}
}

function showPluginError($slotName) {
	echo ifNull(arrayKey(cms_core::$slotContents, $slotName, 'error'), adminPhrase('[Empty Slot]'));
}

//Did we use all of our slots..?
function checkSlotsWereUsed() {
	//Only run this in admin mode
	if (checkPriv()) {
		require funIncPath(__FILE__, __FUNCTION__);
	}
}






//Return the a name of a CSS class to use, depending on whether a field is set or not
function mandatory($filled) {
	return $filled? 'class="mandatory_correct"' : 'class="mandatory"';
}

//There is no way to target another frame with a header('location: www.blah.com'); call.
//This function is as close as we're going to get, alas
function topHeaderLocation($url, $prefix = 'top') {
	echo 
'<html>
	<body>
		<script type="text/javascript">
			', $prefix, '.location.href = "', $url, '";
		</script>
		<a href="', $url, '" target="_main">Please click here to continue to the correct location</a>
	</body>
</html>';
	exit;
}


/*
 *   function fillOptions
 *	 returns the rows of a table
 *   as a string ready for use
 *   inside a select tag
 */
function fillOptions( $table, $value, $text, $default = '', $refiner = '', $useGetPhrase = true ) {
	$options = '';
	if ( ! empty( $refiner ) ) {
		$refiner = 'where ' . $refiner;
	}
	$sql = '
		select ' . $value . ', ' . $text . '
		from ' . $table . '
		' . $refiner . ' 
		order by ' . $text;
	$result = sqlQuery($sql);
	while ( list( $id, $text ) = sqlFetchRow( $result ) ) {
		if ( $id == $default ) {
			$selected = ' selected = "selected"';
		} else {
			$selected = '';
		}
		if ( $useGetPhrase ) {
			$options .= '<option value="' . $id . '"' . $selected.'>' . adminPhrase( $text ) . '</option>';
		} else {
			$options .= '<option value="' . $id . '"' . $selected.'>' .  $text . '</option>';
		}
	}
	return $options;
}
/*
 *   function enumToOption
 *	 returns the options of enum field
 *   as a string ready for use
 *   inside a select tag
 */

function enumToOption( $table, $field, $defaultValue = '') {
	$result = sqlSelect( 'describe ' . $table . ' ' . $field );
	$row =  sqlFetchArray( $result );
	#removes starting 'enum(' and the closing ')'
	$enumValues = substr ( $row['Type'] , 5, -1 );
	#removes any single quotes from the string
	$enumValues = str_replace( '\'', '', $enumValues );
	#makes an array for processing options
	$enumValues = explode ( ',', $enumValues );
	$options = '';
	foreach ( $enumValues as $value ) {
		if ( $value == $defaultValue ) {
			$options .= '<option value="' . $value .'" selected="selected">' . adminPhrase( '_' . $value ) . '</option>';
		} else { 
			$options .= '<option value="' . $value .'">' . adminPhrase( '_' . $value ) . '</option>';
		}
	}
	return $options;
}


function maskString($str,$char_mask="*") {
	$mask = "";
	for ($i=0; $i < strlen($str); $i++) {
		$mask .= $char_mask;
	}
	return $mask;
}


//Handle error pages

/* Special case for an zenario/admin/Author creating a new site */
function showStartSitePageIfNeeded($reportDBOutOfDate = false) {
	return require funIncPath(__FILE__, __FUNCTION__);
}



//Deprecated function that has since been renamed
function getSiteConfig($settingName) {
	return setting($settingName);
}

function cookieFreeDomain() {
	if (httpOrhttps() == 'http://' && setting('use_cookie_free_domain') && setting('cookie_free_domain') && !checkPriv()) {
		return 'http://'. setting('cookie_free_domain'). SUBDIRECTORY;
	} else {
		return false;
	}
}

function getGlobalURL($includeProtocol = false) {
	if ($includeProtocol) {
		return (setting('admin_use_ssl')? 'https://' : 'http://'). primaryDomain(). (setting('admin_use_ssl_port')? ':'. setting('admin_use_ssl_port') : '');
	} else {
		return primaryDomain();
	}
}


//Permission functions

//Check to see if an Admin has a certain privilege
function checkPriv($action = false, $editCID = false, $editCType = false, $editCVersion = 'latest', $welcomePage = false) {
	if (isset($_SESSION['admin_logged_into_site'])) {
		//If the Admin is not logged in to this site, then they shouldn't have Admin rights here
		if ($_SESSION['admin_logged_into_site'] != httpHost(). SUBDIRECTORY. setting('site_id')) {
			return false;
		}
		
		//If the Admin hasn't passed the welcome page, then they shouldn't be able to use their Admin rights anywhere except the welcome page
		if (!$welcomePage && !session('admin_logged_in')) {
			return false;
		}
		
		//If this is a check to edit a Content Item, also check to see if it is unlocked to the current admin and able to be edited
		if (!$welcomePage && $editCID && $editCType) {
			//If this is a check on the current content item, there's no need to query the database for info we already have
			if ($editCID === cms_core::$cID && $editCType === cms_core::$cType) {
				if (cms_core::$status == 'deleted' || cms_core::$locked) {
					return false;
			
				//If a specific version is given, also check that version is a draft
				} elseif ($editCVersion !== 'latest') {
					if (($editCVersion !== true && $editCVersion != cms_core::$adminVersion)
					 || cms_core::$status == 'published'
					 || cms_core::$status == 'hidden'
					 || cms_core::$status == 'trashed') {
						return false;
					}
				}
			
			//Otherwise look the current status, version and checkout-owner up from the database
			} else {
				$content = getRow('content', array('status', 'admin_version', 'lock_owner_id'), array('id' => $editCID, 'type' => $editCType));
				if (!$content
				 || $content['status'] == 'deleted'
				 || ($content['lock_owner_id'] && $content['lock_owner_id'] != session('admin_userid'))) {
					return false;
				
				//If a specific version is given, also check that version is a draft
				} elseif ($editCVersion !== 'latest') {
					if (($editCVersion !== true && $editCVersion != $content['admin_version'])
					 || $content['status'] == 'published'
					 || $content['status'] == 'hidden'
					 || $content['status'] == 'trashed') {
						return false;
					}
				}
			}
		}
		
		//No action specified? Just checking if the admin is logged in will do.
		if ($action === false) {
			return true;
		
		//Otherwise check if the Admin has this specific privilege
		} else {
			return !empty($_SESSION['privs']['_ALL']) || !empty($_SESSION['privs'][$action]);
		}
	} else {
		return false;
	}
}

//A more agressive version of check priv that stops all execution if an admin does not have the requested privilege
function exitIfNotCheckPriv($action = false, $editCID = false, $editCType = false, $editCVersion = 'latest', $welcomePage = false) {
	if (!checkPriv($action, $editCID, $editCType, $editCVersion, $welcomePage)) {
		exit;
	} else {
		return true;
	}
}


//Set an admin's session
function setAdminSession($adminIdL, $adminIdG = false) {
	return require funIncPath(__FILE__, __FUNCTION__);
}


//Log an Admin Out
function unsetAdminSession($destorySession = true) {
	
	unset($_SESSION['admin_first_name']);
	unset($_SESSION['admin_last_name']);
	unset($_SESSION['admin_logged_in']);
	unset($_SESSION['admin_logged_into_site']);
	unset($_SESSION['admin_server_host']);
	unset($_SESSION['admin_userid']);
	unset($_SESSION['admin_global_id']);
	unset($_SESSION['admin_username']);
	unset($_SESSION['admin_box_sync']);
	unset($_SESSION['admin_copied_contents']);
	unset($_SESSION['privs']);
	
	if ($destorySession) {
		if (session('admin_logged_into_site') == httpHost(). SUBDIRECTORY. setting('site_id')) {
			if (isset($_COOKIE[session_name()])) {
				setcookie(session_name(), '', time() - 42000, '/');
			}
		}
		
		session_destroy();
	}
}




/*
	Functions to help make dynamic forms
*/


//Get a description of a table
function getFields($prefix, $tableName, $addPasswordConfirm = false) {
	$fields = array();
	$sql = "DESC ". sqlEscape($prefix. $tableName);
	$result = sqlQuery($sql);

	while($field = sqlFetchAssoc($result)) {
		$field['Table'] = $tableName;
		
		$field['Date'] = strpos($field['Type'], 'date') !== false;
		
		$field['Numeric'] = strpos($field['Type'], 'enum') === false
						&& (strpos($field['Type'], 'int') !== false
						 || strpos($field['Type'], 'double') !== false
						 || strpos($field['Type'], 'float') !== false);
		
		$fields[$field['Field']] = $field;
		
		if ($field['Field'] == 'password' && $addPasswordConfirm) {
			$fields[$field['Field']]['Type'] = 'password';
			$fields['password_reconfirm'] = $fields[$field['Field']];
			$fields['password_reconfirm']['Table'] = '';
			$fields['password_reconfirm']['Type'] = 'password_reconfirm';
		}
	}
	
	return $fields;
}

	
//A function to help with saving the returns from these fields
function addFieldToSQL(&$sql, $table, $field, $values, $editing, $details = array()) {
	
	if ($sql) {
		$sql .= ",";
	} elseif ($editing) {
		$sql = "
		UPDATE ". $table. " SET";
	} else {
		$sql = "
		REPLACE INTO ". $table. " SET";
	}
	
	$sql .= "
			". $field. " = ";
	
	//Attempt to save empty dates correctly in strict mode
	if ($details['Date'] && strlen((string) $values[$field] < 8)) {
		if ($details['Null'] == 'Yes') {
			$values[$field] = '';
		} else {
			$values[$field] = '0000-00-00';
		}
	}
	
	//Convert empty strings to NULLs if possible
	if ($values[$field] === '' && $details['Null'] == 'Yes') {
		$sql .= "NULL";
	
	//Otherwise convert empty strings to 0s for non-string fields
	} elseif (!$values[$field] && $details['Numeric']) {
		$sql .= "0";
	
	//Make sure Numeric values are actually numeric
	} elseif ($details['Numeric']) {
		$sql .= (int) $values[$field];
	
	//Otherwise use sqlEscape
	} else {
		$sql .= "'". sqlEscape($values[$field]). "'";
	}
}




function getAllContentTypes() {
	$sql = "SELECT content_type_id
			FROM ".DB_NAME_PREFIX."content_types
			ORDER BY content_type_id";
	$result = sqlQuery($sql);

	while ($row = sqlFetchArray ($result)) {
		$types[] = $row['content_type_id'];
	}
	return $types;
}

function getContentTypes($contentType=0) {
	$sql = "SELECT content_type_id,
				content_type_name_en,
				default_layout_id
		FROM ".DB_NAME_PREFIX."content_types";
	
	if ($contentType) {
		$sql .= " WHERE content_type_id = '" . sqlEscape($contentType) . "'";
	}
	
	$sql .= " ORDER BY content_type_id";
	
	$result = sqlQuery($sql);
	$contentTypes = array();
	while ($row = sqlFetchArray ($result)) {
		array_push( $contentTypes, $row ) ;
	}
	return $contentTypes;
}

function getContentTypeName($cType) {
	return getRow('content_types', 'content_type_name_en', $cType);
}

function getContentTypeDetails($cType) {
	return getRow('content_types', true, $cType);
}

function getTemplateDetails($layoutId) {
	$sql = "
		SELECT
			layout_id,
			family_name,
			file_base_name,
			CONCAT(file_base_name, '.tpl.php') AS filename,
			CONCAT('L', IF (layout_id < 10, LPAD(CAST(layout_id AS CHAR), 2, '0'), CAST(layout_id AS CHAR)), ' ', name) AS id_and_name,
			name,
			content_type,
			status,
			skin_id,
			css_class
		FROM ". DB_NAME_PREFIX. "layouts
		WHERE layout_id = ". (int) $layoutId;
	$result = sqlQuery($sql);
	return sqlFetchAssoc($result);
}


/*	Content Functions	*/

function isSpecialPage($cID, $cType) {
	$specialPage = array_search($cType. '_'. $cID, cms_core::$specialPages);
	
	if ($specialPage !== false) {
		$specialPage = explode('`', $specialPage, 2);
		return $specialPage[0];
	} else {
		return false;
	}
}

function getContentLang($cID, $cType = false) {
	return getRow('content', 'language_id', array('id' => $cID, 'type' => ifNull($cType, 'html')));
}

function resolveContentItemFromRequest(&$cID, &$cType, &$redirectNeeded) {
	$equivId = $cID = $cType = $alias = $reqLangId = $redirectNeeded = false;

	//If there is a menu id in the request, try to get the Content Item from that
	if (!empty($_REQUEST['mID']) && ($menu = getContentFromMenu($_REQUEST['mID'], 2))) {
		$cID = $menu['equiv_id'];
		$cType = $menu['content_type'];
		langEquivalentItem($cID, $cType);
		
		//Visitors shouldn't see this type of link, so redirect them to the correct URL
		if (!checkPriv()) {
			$redirectNeeded = 301;
		}
		return;
	}
	
	
	//Check for a requested page
	if (empty($_REQUEST['cID'])) {
		//Show one of the home pages if there's nothing in the request
		$equivId = cms_core::$homeCID;
		$cType = cms_core::$homeCType;
	
	} else {
		//Check the request for a numeric cID, a string alias, and a language code separated by a comma.
		$alias = explode(',', $_REQUEST['cID']);
		
		if (!empty($alias[1])) {
			$reqLangId = $alias[1];
		}
		$alias = $alias[0];
		
		//Allow language codes with no alias as a shortcut to the homepage in that language
		if ($reqLangId && !$alias) {
			$cID = cms_core::$homeCID;
			$cType = cms_core::$homeCType;
			
			langEquivalentItem($cID, $cType, $reqLangId);
			
			$redirectNeeded = 301;
			return;
		
		//Link by numeric cID
		} elseif (is_numeric($alias)) {
			$cID = (int) $alias;
			
			if (!empty($_REQUEST['cType'])) {
				$cType = $_REQUEST['cType'];
			} else {
				$cType = 'html';
			}
			
			//Allow numeric cIDs with language codes, but redirect them to the correct URL
			if ($reqLangId) {
				langEquivalentItem($cID, $cType, $reqLangId);
				$redirectNeeded = 301;
			}
			
			//We know both the Content Item and language from the numeric id,
			//so we can return straight away without looking up anything else.
			return;
		
		//Link by tag id
		} elseif (getCIDAndCTypeFromTagId($cID, $cType, $alias)) {
			//Allow numeric cIDs with language codes, but redirect them to the correct URL
			if ($reqLangId) {
				langEquivalentItem($cID, $cType, $reqLangId);
				$redirectNeeded = 301;
			}
			
			//Again we can return straight away as we know the specific Content Item
			return;
		
		//Link by an alias
		} else {
			//Attempt to look up a page with this alias
			$sql = "
				SELECT id, type, equiv_id, language_id
				FROM ". DB_NAME_PREFIX. "content
				WHERE alias = '". sqlEscape($alias). "'
				  AND status NOT IN ('trashed', 'deleted')
				ORDER BY language_id";
			
			//If there was a language code in the URL, focus on the language that we're looking for
			if ($reqLangId) {
				$sql .= " = '". sqlEscape($reqLangId). "' DESC, language_id";
			}
			
			//Get two rows, so we can tell if this alias was unique
			$sql .= "
				LIMIT 2";
			
			$result = sqlQuery($sql);
			if ($row = sqlFetchAssoc($result)) {
				$row2 = sqlFetchAssoc($result);
			}
			
			//If the alias was not found, then we can't resolve it to a Content Item
			if (!$row) {
				$cID = false;
				$cType = false;
				return;
			
			//If there was only one result for that alias, we can use this straight away
			} elseif (!$row2) {
				$cID = $row['id'];
				$cType = $row['type'];
				
				//Redirect the case where we resolved a match, but the alias didn't actually match the language code
				if ($reqLangId && $reqLangId != $row['language_id']) {
					langEquivalentItem($cID, $cType, $reqLangId);
					$redirectNeeded = 301;
				}
				
				return;
			
			//If there was a language specified and there was only one match for that language, we're also good to go
			} elseif ($reqLangId && $reqLangId == $row['language_id'] && $reqLangId != $row2['language_id']) {
				$cID = $row['id'];
				$cType = $row['type'];
				return;
			
			} else {
				//Otherwise, just note down which translation chain was found, and resolve to the correct language below
				$cID = false;
				$equivId = $row['equiv_id'];
				$cType = $row['type'];
			}
		}
	}
	
	
	//If we reach this point, we've found a translation to show, but don't know which language to show it in.
	
	//Get the Visitor's preferred languae from their browser.
		//Note: as of 6.1 we only look at the first choice.
	$acptLangId = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'], 2);
	$acptLangId = explode(';', $acptLangId[0], 2);
	$acptLangId = strtolower(trim($acptLangId[0]));
	
	//Also look for the first part of the language code (before the hyphen) as a fallback
	$acptLangId2 = explode('-', $acptLangId, 2);
	$acptLangId2 = $acptLangId2[0];
	
	//Look at the languages that we have for the requested translation.
	$sql = "
		SELECT c.id, c.type, c.equiv_id, c.language_id, c.alias, l.detect, l.detect_lang_codes
		FROM ". DB_NAME_PREFIX. "content AS c
		INNER JOIN ". DB_NAME_PREFIX. "languages AS l
		   ON c.language_id = l.id
		WHERE c.equiv_id = ". (int) $equivId. "
		  AND c.type = '". sqlEscape($cType). "'
		  AND c.status NOT IN ('trashed', 'deleted')
		ORDER BY
			c.language_id = '". sqlEscape(setting('default_language')). "' DESC,
			c.language_id";
	
	$match = false;
	$result = sqlQuery($sql);
	while ($row = sqlFetchAssoc($result)) {
		//If this language should be auto-detected, get a list of language codes that it matches to
		if ($row['detect']) {
			$langCodes = array_flip(explode(',', str_replace(' ', '', $row['language_id']. ','. $row['detect_lang_codes'])));
		}
		
		//If there is a match, use that and stop here
		if ($row['detect'] && !empty($langCodes[$acptLangId])) {
			$match = $row;
			break;
		
		//If there is a match on the first part of the language code, remember this one as a fallback
		} elseif ($row['detect'] && !empty($langCodes[$acptLangId2])) {
			$match = $row;
		
		//If nothing else matches, make sure we go to the default language
		//(or the first found language if there was no default) as a fallback.
		} elseif (!$match) {
			$match = $row;
		}
	}
	
	if ($match) {
		$cID = $match['id'];
		
		//If there was a requested alias, which was different than the resolved alias, we should do a redirect.
		if ($alias && $alias != $match['alias']) {
			$redirectNeeded = 301;
		
		//If there was a requested language, which was different than the resolved language, we should do a redirect.
		} elseif ($reqLangId && $reqLangId != $match['language_id']) {
			$redirectNeeded = 301;
		
		//For multilingual sites, if the language code was not in the URL and we had an ambiguous link, we should do a redirect.
		//But make it a 302 redirect, as we don't want to discourage Search Engines from listing the URLs of landing pages.
		} elseif (!$reqLangId && getNumLanguages() > 1) {
			$redirectNeeded = 302;
		}
	
	} else {
		$cID = false;
		$cType = false;
	}
}

//Check to see if a Content Item exists, and the current visitor/user/admin can see a Content Item
//(Admins can see all Content Items that exist)
function checkPerm($cID, $cType = 'html', $requestVersion = false) {
	$content = false;
	return (bool) checkPermAndGetShowableContent($content, $cID, $cType, $requestVersion, $return_permissions_error = false);
}

//Gets the correct version of a Content Item to show someone, or false if the do not have any access.
//(Works exactly like checkPerm() above, except it will return a version number.)
function getShowableVersion($cID, $cType = 'html') {
	$content = false;
	return checkPermAndGetShowableContent($content, $cID, $cType, $requestVersion = false, $return_permissions_error = false);
}

//Check to see if a Content Item exists, and the current visitor/user/admin can see a Content Item
//Works like checkPerm() above, except that it will return a permissions error code
//It also looks up some details on the Content Item
function getShowableContent(&$content, &$version, $cID, $cType = 'html', $requestVersion = false) {
	$return_permissions_error = true;
	
	$versionNumber = checkPermAndGetShowableContent($content, $cID, $cType, $requestVersion, $return_permissions_error);
	
	if ($versionNumber && is_numeric($versionNumber)) {
		$versionColumns = array(
			'version',
			'title', 'description', 'keywords',
			'layout_id', 'css_class',
			'publication_date', 'published_datetime', 'created_datetime',
			'rss_slot_name', 'rss_nest');
		
		$version = getRow('versions', $versionColumns, array('id' => $content['id'], 'type' => $content['type'], 'version' => $versionNumber));
		$versionNumber = true;
	}
	
	return $versionNumber;
}

function checkPermAndGetShowableContent(&$content, $cID, $cType, $requestVersion, $return_permissions_error) {
	// Returns the version of this content item which should normally be returned
	if (!$cID) {
		return false;
	} else {
		
		$translationChainColumns = array(
			'equiv_id', 'type', 'privacy', 'log_user_access');
		$contentColumns = array(
			'equiv_id', 'id', 'type', 'language_id', 'alias',
			'visitor_version', 'admin_version', 'status', 'lock_owner_id');
		
		if ((!$content = getRow('content', $contentColumns, array('id' => $cID, 'type' => ifNull($cType, 'html'))))
		 || (!$chain = getRow('translation_chains', $translationChainColumns, array('equiv_id' => $content['equiv_id'], 'type' => $content['type'])))) {
			return false;
		}
		
		if (!checkPriv()) {
			// We are in visitor mode, show only a published version
			if (!isPublished($content['status'])) {
				return false;
			
			} elseif (!$content['visitor_version']) {
				return false;
			}
			
			
			if ($chain['privacy'] == 'public') {
				// Item is published and non-private, so return the published version number
				return $content['visitor_version'];
			
			} elseif ($chain['privacy'] == 'no_access') {
				$grantAccess = false;
				$status = false;
				
				if ($result = sendSignal("eventAccessDenied",array("userId" => session("extranetUserID"), "contentId" => $content['id'], "contentType" => $content['type']))) {
					foreach ($result as $resultModule) {
						if ($resultModule) {
							if (is_array($resultModule)) {							
								if (isset($resultModule['grant_access'])) {
									if ($resultModule['grant_access']==true) {
										$grantAccess = true;
										break;
									} elseif ($resultModule['grant_access']==false) {
										$grantAccess = false;
										$status = $resultModule['status'];
										break;
									}
								}
							}
						}
					}
				}
				
				if ($grantAccess) {
					return $content['visitor_version'];
				} else {
					if ($status) {
						return $status;
					} else {
						return false;
					}
				}
			} else {
				// Published Item exists in this version but is private; check permissions
				if (!session('extranetUserID')) {
					// Deny it; user is not logged in as a valid extranet user
					return $return_permissions_error? 'notLoggedIn' : false;
				
				} elseif ($chain['privacy'] == 'all_extranet_users') {
					return $content['visitor_version'];
				
				} elseif ($chain['privacy'] == 'group_members') {
					$groups_id_list = array_keys(getUserGroups(session('extranetUserID')));
					
					if (!empty($groups_id_list)) {
						$sql = "
							SELECT 1
							FROM ". DB_NAME_PREFIX. "group_content_link AS gcl
							WHERE equiv_id = ". (int) $chain['equiv_id']. "
							  AND content_type = '". sqlEscape($chain['type']). "'
							  AND group_id IN(". inEscape($groups_id_list, true). ")
							LIMIT 1";
								
						if (($result = sqlQuery($sql)) && (sqlFetchRow($result))) {
							return $content['visitor_version'];
						}		
					}
					
					return $return_permissions_error? 'no_permission' : false;
				
				} elseif ($chain['privacy'] == 'specific_users') {
					if (checkRowExists(
						'user_content_link',
						array(
							'user_id' => session('extranetUserID'),
							'equiv_id' => $chain['equiv_id'],
							'content_type' => $chain['type']))
					) {
						return $content['visitor_version'];
					} else {
						return $return_permissions_error? 'no_permission' : false;
					}
				}
			}
		
		} else {
			//Version number was specified in the URL; but first check to see if it exists
			if ($requestVersion) {
				if (checkRowExists('versions', array('id' => $content['id'], 'type' => $content['type'], 'version' => $requestVersion))) {
					return (int) $requestVersion;
				}
			
			} else {
				if (checkRowExists('versions', array('id' => $content['id'], 'type' => $content['type'], 'version' => $content['admin_version']))) {
					return $content['admin_version'];
				}
			}
		}
	}
	
	return false;
}

function setShowableContent(&$content, &$version) {
	cms_core::$equivId = $content['equiv_id'];
	cms_core::$cID = $content['id'];
	cms_core::$cType = $content['type'];
	cms_core::$alias = $content['alias'];
	cms_core::$status = $content['status'];
	cms_core::$langId = $_SESSION['user_lang'] = $content['language_id'];
	
	cms_core::$cVersion = $version['version'];
	cms_core::$adminVersion = $content['admin_version'];
	cms_core::$visitorVersion = $content['visitor_version'];
	cms_core::$pageTitle = $version['title'];
	cms_core::$description = $version['description'];
	cms_core::$keywords = $version['keywords'];
	cms_core::$itemCSS = $version['css_class'];
	cms_core::$date = ifNull($version['publication_date'], $version['published_datetime'], $version['created_datetime']);
	cms_core::$rss = $version['rss_nest']. '_'. $version['rss_slot_name'];
	
	cms_core::$isDraft =
		$version['version'] == $content['admin_version'] && (
			$content['status'] == 'first_draft'
		 || $content['status'] == 'published_with_draft'
		 || $content['status'] == 'hidden_with_draft'
		 || $content['status'] == 'trashed_with_draft');
	
	cms_core::$locked = $content['lock_owner_id'] && $content['lock_owner_id'] != session('admin_userid');

	//Given what we know, find a Layout and a Template Family as best we can.
	//Give priority to matching Layout ids, matching family names, active Layouts,
	//and then html type Layouts in that order
	$sql = "
		SELECT family_name, layout_id, file_base_name, skin_id, css_class
		FROM ". DB_NAME_PREFIX. "layouts
		ORDER BY
			content_type = '". sqlEscape(cms_core::$cType). "' DESC";
	
	if (($layoutId = $version['layout_id']) || ($layoutId = getRow('content_types', 'default_layout_id', array('content_type_id' => cms_core::$cType)))) {
		$sql .= ",
			layout_id = ". (int) $layoutId. " DESC";
	}
	
	$sql .= ",
			layout_id";
	
	$result = sqlQuery($sql);
	$template = sqlFetchAssoc($result);
	
	cms_core::$layoutId = $template['layout_id'];
	cms_core::$templateCSS = $template['css_class'];
	cms_core::$templateFamily = $template['family_name'];
	cms_core::$templateFileBaseName = $template['file_base_name'];
	cms_core::$templateFilename = $template['file_base_name']. '.tpl.php';
	cms_core::$templatePath = zenarioTemplatePath(cms_core::$templateFamily);
	
	//This constant was used in some old Template Files.
	define('TEMPLATE_PATH', cms_core::$templateFamily);
	
	if ((cms_core::$skinId = templateSkinId($template, true))
	 && ($skin = getSkinFromId(cms_core::$skinId))) {
		cms_core::$skinName = $skin['name'];
		cms_core::$skinCSS = $skin['css_class'];
	}
}

function contentItemAlias($cID, $cType) {
	return getRow('content', 'alias', array('id' => $cID, 'type' => $cType));
}

function contentItemTemplateId($cID, $cType, $cVersion = false) {
	
	if (!$cVersion) {
		$cVersion = getLatestVersion($cID, $cType);
	}
	
	return getRow('versions', 'layout_id', array('id' => $cID, 'type' => $cType, 'version' => $cVersion));
}

function templateSkinId($template, $fallback = false) {
	
	if (!is_array($template)) {
		$template = getRow('layouts', array('family_name', 'skin_id'), $template);
	}
	
	if ($template) {
		if ($template['skin_id']) {
			return $template['skin_id'];
		
		} elseif ($skinId = getRow('template_families', 'skin_id', array('family_name' => $template['family_name']))) {
			return $skinId;
		
		} elseif ($fallback) {
			return getRow('skins', 'id', array('family_name' => $template['family_name'], 'type' => 'usable', 'missing' => 0));
		}
	}
	return false;
}


function getLatestContentID($cType) {
	$sql = "
		SELECT MAX(id)
		FROM ". DB_NAME_PREFIX. "content
		WHERE type = '". sqlEscape($cType). "'";

	$result = sqlQuery($sql);
	$row = sqlFetchRow($result);
	return arrayKey($row, 0);
}

function getPublishedVersion($cID, $cType) {
	return getRow('content', 'visitor_version', array('id' => $cID, 'type' => $cType));
}

function getLatestVersion($cID, $cType) {
	return getRow('content', 'admin_version', array('id' => $cID, 'type' => $cType));
}

function getAppropriateVersion($cID, $cType) {
	return getRow('content', checkPriv()? 'admin_version' : 'visitor_version', array('id' => $cID, 'type' => $cType));
}


function getItemTitle($cID, $cType, $cVersion = false) {
	
	if (!$cVersion) {
		$cVersion = getLatestVersion($cID, $cType);
	}
	
	if ($cID == cms_core::$cID && $cType == cms_core::$cType && $cVersion == cms_core::$cVersion) {
		return cms_core::$pageTitle;
	} else {
		return getRow('versions', 'title', array('id' => $cID, 'type' => $cType, 'version' => $cVersion));
	}
}

function getItemDescription($cID, $cType, $cVersion) {
	return getRow('versions', 'description', array('id' => $cID, 'type' => $cType, 'version' => $cVersion));
}

function formatTagFromTagId($tagId) {
	$cID = $cType = false;
	if (getCIDAndCTypeFromTagId($cID, $cType, $tagId)) {
		return formatTag($cID, $cType);
	} else {
		return false;
	}
}

function formatTag($cID, $cType, $alias = -1, $langId = false, $neverAddLanguage = false) {
	$content = false;
	$friendlyURL = '';
	
	if ($alias === -1) {
		$content = getRow('content', array('alias', 'language_id'), array('id' => $cID, 'type' => $cType));
		$alias = $content['alias'];
	}
	
	if ($alias) {
		$friendlyURL = '/'. $alias;
	}
	
	if (!$neverAddLanguage
	 && getNumLanguages() > 1) {
		if (!$langId) {
			if (!$content) {
				$content = getRow('content', array('alias', 'language_id'), array('id' => $cID, 'type' => $cType));
			}
			$langId = $content['language_id'];
		}
		
		$friendlyURL .= ','. $langId;
	}
	
	return $cType. '_'. $cID. $friendlyURL;
}


function cutTitle($title, $max_title_length = 20, $cutText = '...') {
	if (strlen($title) > $max_title_length) {
		return mb_substr($title, 0, floor($max_title_length/2)). $cutText. mb_substr($title, -floor($max_title_length/2));
	} else {
		return $title;
	}
}

//Read a line from admin_phrase_codes/en.txt
//Either read the next full line after a given position, or if $pos is not specified, the next line from the last position
function adminPhraseLine($lang, &$f, &$code, &$text, $pos = false) {
	$code = $text = '';
	
	if ($pos !== false) {
		fseek($f, $pos);
		fgets($f);
	}
	
	if ($line = fgets($f)) {
		$line = explode('|', $line, 2);
		$code = $line[0];
		$text = trim($line[1], "\t\n\r\0\x0B");
		
		return true;
	} else {
		return false;
	}
}

function getAdminPhraseCode($target, $lang) {
	return require funIncPath(__FILE__, __FUNCTION__);
}

function getAdminDetails($admin_id) {
	
	if ($details = getRow('admins', true, $admin_id)) {
		//Old key/value format for backwards compatability with old code
		foreach ($details as $key => $value) {
			$details['admin_'. $key] = $value;
		}
		$details['disable'] = (int) empty($row['perm_manage']);
	}
	
	return $details;
}


//Old name for adminPhrase()
function getPhrase($code, $replace = false) {
	return adminPhrase($code, $replace);
}

function adminPhrase($code, $replace = false) {
	
	//No support for a multilingual admin interface yet.
	$lang = 'en';
	
	if ($replace === false) {
		$replace = array();
	}
	
	//Some phrases are now being simply hard-coded. However we still wish to call adminPhrase on these
	//hardcoded phrases for tracking purposes
	//Ignore anything that does not start with an underscore
	if ($lang == 'en' && substr($code, 0, 1) != '_') {
		$phrase = $code;
	} else {
		$phrase = getAdminPhraseCode($code, $lang);
	}
	
	if (empty($replace)) {
		return $phrase;
	} else {
		foreach ($replace as $key => &$replacement) {
			if (!is_array($replacement)) {
				$phrase = str_replace('[['. $key. ']]', $replacement, $phrase);
			}
		}
		return $phrase;
	}
}

//Deprecated, please use phrase() instead
function getVLPPhrase($code, $replace = false, $languageId = false, $returnFalseOnFailure = false, $moduleClass = '', $phrase = false, $altCode = false) {
	return phrase($code, $replace, $moduleClass, $languageId, 2);
}

//Replacement function for gettext()/ngettext() in our Twig frameworks
function phrase($code, $replace = array(), $moduleClass = 'lookup', $languageId = false, $backtraceOffset = 1) {
	
	//The twig frameworks don't pass in the phrase class name, so we need to rememeber it using a static class variable
	if ($moduleClass === 'lookup') {
		$moduleClass = cms_core::$moduleClassNameForPhrases;
	}
	if (!$moduleClass) {
		$moduleClass = 'zenario_common_features';
	}
	
	
	//Use $languageId === true as a shortcut to the site default language
	//Otherwise if $languageId is not set, try to get language from session, or the site default if that is not set
	if ($languageId === true) {
		$languageId = setting('default_language');
	
	} elseif (!$languageId) {
		if (!empty($_SESSION['user_lang'])) {
			$languageId = $_SESSION['user_lang'];
	
		} else {		
			$languageId = setting('default_language');
		}
	}
	
	$multiLingal = getNumLanguages() > 1;
	$inDefaultLanguage = !$multiLingal || $languageId == setting('default_language');
	$isCode = substr($code, 0, 1) == '_';
	$needsUpdate = false;
	$phrase = $code;
	$needsTranslating = $isCode || isset(cms_core::$translateLanguages[$languageId]);
	
	//Phrase codes (which start with an underscore) always need to be looked up
	//Otherwise we only need to look up phrases on multi-lingual sites
	if ($multiLingal || $isCode) {
		
		//Attempt to find a record of the phrase in the database
		$sql = "
			SELECT local_text, seen_in_visitor_mode
			FROM ". DB_NAME_PREFIX. "visitor_phrases
			WHERE language_id = '". sqlEscape($languageId). "'
			  AND module_class_name = '". sqlEscape($moduleClass). "'
			  AND code = '". sqlEscape($code). "'
			LIMIT 1";
	
		$result = sqlQuery($sql);
		if ($row = sqlFetchRow($result)) {
			//If we found a translation, replace the code/default text with the translation
				//Note that phrases in the default language are never actually translated,
				//we're just checking if they are there!
			if ($isCode || !$inDefaultLanguage) {
				$phrase = $row[0];
			}
			
			//If this is the first time we've seen this phrase in visitor mode, note it down
			if (!$row[1] && !checkPriv()) {
				$needsUpdate = true;
			}
		
		} else {
			//If we didn't find a translation that we needed, complain about it
			if ($isCode || !$inDefaultLanguage) {
				$phrase = $code. ' (untranslated)';
			}
			
			//For multilingal sites, any phrases that are not in the database need to be noted down
			if (!$isCode
			 && $multiLingal
			 && ($inDefaultLanguage
				 || !checkRowExists(
						'visitor_phrases',
						array(
							'language_id' => setting('default_language'),
							'module_class_name' => $moduleClass,
							'code' => $code))
			)) {
				$needsUpdate = true;
			}
		}
		
		//Make sure that this phrase is registered in the database
		if ($needsUpdate) {
			//Attempt to log the filename that this phrase appeared in by checking debug backtrace
			if (is_string($backtraceOffset)) {
				$filename = $backtraceOffset;
			
			} else {
				$filename = '';
			
				if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
					$back = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
				} elseif (version_compare(PHP_VERSION, '5.3.6', '>=')) {
					$back = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
				} else {
					$back = debug_backtrace(false);
				}
			
				if (!empty($back[$backtraceOffset]['file'])) {
					//Strip off the CMS root
					$filename = str_replace('$'. CMS_ROOT, '', '$'. $back[$backtraceOffset]['file']);
				
					//If this looks like it was in a framework, try to overwrite this with the path to the
					//source of the framework file
					if (cms_core::$frameworkFile
					 && ($filename == 'zenario/api/module_api.inc.php'
					  || substr($filename, 0, 17) == 'cache/frameworks/'
					)) {
						$filename = cms_core::$frameworkFile;
					}
				}
			}
			
			setRow(
				'visitor_phrases',
				array(
					'seen_in_visitor_mode' => checkPriv()? 0 : 1,
					'seen_in_file' => substr($filename, 0, 255)),
				array(
					'language_id' => setting('default_language'),
					'module_class_name' => $moduleClass,
					'code' => $code));
		}
	}
	
	
	//Replace merge fields in the phrase
	if (!empty($replace) && is_array($replace)) {
		foreach ($replace as $key => $value) {
			$phrase = str_replace(array('[['. $key. ']]', '{{'. $key. '}}'), $value, $phrase);
		}
	}
	
	return $phrase;
}

function nphrase($text, $pluralText = false, $n = 1, $replace = array(), $moduleClass = 'lookup', $languageId = false) {
	
	if ($pluralText !== false && $n !== 1) {
		return phrase($pluralText, $replace, $moduleClass, $languageId, 2);
	} else {
		return phrase($text, $replace, $moduleClass, $languageId, 2);
	}
}

//Returns a Content Item that points to a Menu Node
//Note that this will probably be the Content Item in the Primary Language
function getContentFromMenu($mID, $recurseLimit = 0) {
	
	if ($menu = getRow('menu_nodes', array('id', 'equiv_id', 'content_type', 'parent_id'), $mID)) {
		if ($menu['equiv_id'] && $menu['content_type']) {
			$menu['content_id'] = $menu['equiv_id'];
			return $menu;
		
		} elseif ($recurseLimit) {
			return getContentFromMenu($menu['parent_id'], --$recurseLimit);
		}
	}
	
	return false;
}

function getMenuItemFromContent($cID, $cType, $fetchSecondaries = false, $sectionId = false, $allowGhosts = false) {
	if ($cID && $cType) {
		$sql = "
			SELECT
				m.id,
				m.id AS mID,
				m.section_id,
				c.language_id,
				t.name,
				m.redundancy, 
				m.parent_id,
				t.ext_url, 
				m.ordinal,
				m.hide_private_item,
				m.invisible,
				m.rel_tag,
				m.css_class
			FROM ". DB_NAME_PREFIX. "content AS c
			INNER JOIN ". DB_NAME_PREFIX. "menu_nodes AS m
			   ON m.equiv_id = c.equiv_id
			  AND m.content_type = c.type
			  AND m.target_loc = 'int'
			". ($allowGhosts? "LEFT" : "INNER"). " JOIN ". DB_NAME_PREFIX. "menu_text AS t
			   ON t.menu_id = m.id
			  AND t.language_id = c.language_id
			WHERE c.id = ". (int) $cID. "
			  AND c.type = '" . sqlEscape($cType) . "'";
		
		if ($sectionId) {
			$sql .= "
			  AND m.section_id = ". (int) menuSectionId($sectionId);
		}
		
		$sql .= "
			ORDER BY m.redundancy = 'primary' DESC";
		
		if (!$fetchSecondaries) {
			$sql .= "
				LIMIT 1";
		}
		
		$result = sqlQuery($sql);
		
		if ($fetchSecondaries) {
			$rows = array();
			while($row = sqlFetchAssoc($result)) {
				$rows[] = $row;
			}
			return $rows;
		
		} elseif ($row = sqlFetchAssoc($result)) {
			return $row;
			
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function menuSectionId($sectionIdOrName, $checkExists = false) {
	if (!is_numeric($sectionIdOrName)) {
		return getRow('menu_sections', 'id', array('section_name' => $sectionIdOrName));
	
	} elseif ($checkExists) {
		return getRow('menu_sections', 'id', array('id' => $sectionIdOrName));
	
	} else {
		return $sectionIdOrName;
	}
}

function menuSectionName($sectionIdOrName) {
	if (is_numeric($sectionIdOrName)) {
		return getRow('menu_sections', 'section_name', array('id' => $sectionIdOrName));
	} else {
		return $sectionIdOrName;
	}
}

function getMenuInLanguage($mID, $langId) {
	$sql = "
		SELECT
			m.id AS mID,
			t.name,
			m.target_loc,
			m.open_in_new_window,
			c.equiv_id,
			c.id AS cID,
			c.type AS cType,
			c.alias,
			m.use_download_page,
			m.hide_private_item,
			t.ext_url,
			c.visitor_version,
			m.invisible,
			m.accesskey,
			m.ordinal,
			m.rel_tag,
			m.css_class
		FROM ". DB_NAME_PREFIX. "menu_text AS t
		INNER JOIN ". DB_NAME_PREFIX. "menu_nodes AS m
		   ON m.id = t.menu_id
		LEFT JOIN ". DB_NAME_PREFIX. "content AS c
		   ON m.equiv_id = c.id
		  AND m.content_type = c.type
		  AND m.target_loc = 'int'
		  AND t.language_id = c.language_id
		WHERE t.language_id = '" . sqlEscape($langId) . "'
		  AND t.menu_id = ". (int) $mID;
	
	$result = sqlQuery($sql);
	return sqlFetchAssoc($result);
}

function getSectionMenuItemFromContent($equivId, $cType, $section, $mustBePrimary = false) {

	$sql = "
		SELECT id
		FROM ". DB_NAME_PREFIX. "menu_nodes
		WHERE equiv_id = ". (int) $equivId. "
		  AND content_type = '". sqlEscape($cType). "'
		  AND section_id = ". (int) menuSectionId($section). "
		  AND target_loc = 'int'";
	
	if ($mustBePrimary) {
		$sql .= "
		  AND redundancy = 'primary'";
	
	} else {
		$sql .= "
		ORDER BY redundancy = 'primary' DESC";
	}
	
	$sql .= "
		LIMIT 1";
	
	$result = sqlQuery($sql);
	if ($row = sqlFetchAssoc($result)) {
		return $row['id'];
	} else {
		return false;
	}
}

function getMenuNodeDetails($mID, $langId = false) {
	$row = getRow('menu_nodes', true, $mID);
	
	if ($row && $langId) {
		$row['mID'] = $row['id'];
		$row['name'] = null;
		$row['descriptive_text'] = null;
		$row['ext_url'] = null;
		
		if ($text = getRow('menu_text', array('name', 'descriptive_text', 'ext_url'), array('menu_id' => $mID, 'language_id' => $langId))) {
			$row['name'] = $text['name'];
			$row['descriptive_text'] = $text['descriptive_text'];
			$row['ext_url'] = $text['ext_url'];
		}
		
		if ($row['equiv_id'] && $row['content_type']) {
			$row['content_id'] = $row['equiv_id'];
			langEquivalentItem($row['content_id'], $row['content_type'], $langId);
		}
	}
	
	return $row;
}

function getMenuName($mID, $langId = false) {
	
	$markFrom = false;
	if ($langId === false) {
		$langId = ifNull(session('user_lang'), setting('default_language'));
	
	} elseif ($langId === true) {
		$langId = setting('default_language');
	
	} elseif (checkPriv()) {
		$markFrom = true;
	}
	
	$sql = "
		SELECT name, language_id
		FROM ". DB_NAME_PREFIX. "menu_text AS mt
		WHERE menu_id = ". (int) $mID. "
		ORDER BY
			language_id = '". sqlEscape($langId). "' DESC,
			language_id = '". sqlEscape(setting('default_language')). "' DESC
		LIMIT 1";
	
	$result = sqlQuery($sql);
	if ($row = sqlFetchAssoc($result)) {
		
		if ($markFrom && $row['language_id'] != $langId) {
			$row['name'] = adminPhrase('[[name]] ([[language_id]])', array('name' => $row['name'], 'language_id' => $row['language_id']));
		}
		
		return $row['name'];
	} else {
		return false;
	}
}

function isMenuItemAncestor($childId, $ancestorId) {
	return checkRowExists('menu_hierarchy', array('child_id' => $childId, 'ancestor_id' => $ancestorId));
}

function getNumMenuChildren($mID) {
	$sql = "
		SELECT COUNT(*)
		FROM ". DB_NAME_PREFIX. "menu_hierarchy
		WHERE ancestor_id = " . (int) $mID;
	$result = sqlQuery($sql);
	$row = sqlFetchRow($result);
	return $row[0];
}

function getMenuParent($mID) {
	return getRow('menu_nodes', 'parent_id', array('id' => $mID));
}


function shouldShowMenuItem(&$row, &$cachingRestrictions) {
	
	// Hide menu node if static method is set and returns false
	if (!empty($row['module_class_name'])) {
		$cachingRestrictions = 'staticFunctionCalled';
		if (!(inc($row['module_class_name']))
		 || !(method_exists($row['module_class_name'], $row['method_name']))
		 || !(call_user_func(
				array($row['module_class_name'], $row['method_name']),
					$row['param_1'], $row['param_2'])
		)) {
			return false;
		}
	}
	
	if ($row['target_loc'] == 'int') {
		//Check to see if there is a published version
		if (!$row['visitor_version']) {
			//If a Menu Node is translated, but a Content Item does not have a translation, try to link to a Content Item in the default Language
			if (!$row['cID'] && $row['cType'] && $row['equiv_id']) {
				if ($content = getRow('content', array('alias', 'visitor_version'), array('id' => $row['equiv_id'], 'type' => $row['cType']))) {
					$row['cID'] = $row['equiv_id'];
					$row['alias'] = $content['alias'];
					$row['visitor_version'] = $content['visitor_version'];
					
					return shouldShowMenuItem($row, $cachingRestrictions);
				}
			}
			
			return false;
		
		//Check for Menu Nodes that are only shown if logged in/out as an Extranet User
		//But note that this logic is not used in Admin Mode; Admins always see every Menu Node, and don't see any special markings for these Menu Nodes
		} elseif ($row['hide_private_item'] == 3) {
			$cachingRestrictions = 'privateItemsExist';
			return session('extranetUserID') || checkPriv();
		
		} elseif ($row['hide_private_item'] == 2) {
			$cachingRestrictions = 'privateItemsExist';
			return !session('extranetUserID') || checkPriv();
		
		} elseif ($row['hide_private_item']) {
			$cachingRestrictions = 'privateItemsExist';
			return checkPerm($row['cID'], $row['cType']);
		}
	}
	return $row['target_loc'] != 'none';
}


function getMenuStructure(
	&$cachingRestrictions,
	$sectionId,
	$currentMenuId = false,
	$parentMenuId = 0,
	$numLevels = 0,
	$maxLevel1MenuItems = 100,
	$language = false,
	$onlyFollowOnLinks = false,
	$onlyIncludeOnLinks = false,
	$showAdminAddMenuItem = true,
	$showInvisibleMenuItems = false,
	$showMissingMenuNodes = false,
	$recurseCount = 0
) {
	if ($language === false) {
		$language = !empty($_SESSION['user_lang'])? $_SESSION['user_lang'] : setting('default_language');
	}
	
	if (++$recurseCount == 1) {
		$level1counter = 0;
	}
	
	//Look up all of the Menu Items on this level
	$rows = array();
	if ($showMissingMenuNodes && $language != setting('default_language')) {
		$result = cms_core::lookForMenuItems($parentMenuId, setting('default_language'), $sectionId, $currentMenuId, $recurseCount, $showInvisibleMenuItems);
		while ($row = sqlFetchAssoc($result)) {
			if (empty($row['css_class'])) {
				$row['css_class'] = 'missing';
			} else {
				$row['css_class'] .= ' missing';
			}
			
			$rows[$row['mID']] = $row;
		}
	}
	
	$result = cms_core::lookForMenuItems($parentMenuId, $language, $sectionId, $currentMenuId, $recurseCount, $showInvisibleMenuItems);
	while ($row = sqlFetchAssoc($result)) {
		$rows[$row['mID']] = $row;
	}
	
	
	if (!empty($rows)) {
		$menuIds = '';
		$unsets = array();
		foreach ($rows as &$row) {
			$row['on'] = false;
			$row['children'] = false;
			$unsets[$row['mID']] = true;
			$menuIds .= ($menuIds? ',' : ''). $row['mID'];
		}
		unset($row);
		
		//Look for children of the Menu Nodes we will be displaying, so we know which Menu Nodes have no children
		$sql = "
			SELECT DISTINCT ancestor_id
			FROM ". DB_NAME_PREFIX. "menu_hierarchy
			WHERE ancestor_id IN (". $menuIds. ")
			  AND separation = 1";
		
		$result = sqlQuery($sql);
		while ($row = sqlFetchRow($result)) {
			$rows[$row[0]]['children'] = true;
		}
		
		//Look for Menu Nodes that are ancestors of the current Menu Node
		if ($currentMenuId) {
			$sql = "
				SELECT ancestor_id
				FROM ". DB_NAME_PREFIX. "menu_hierarchy
				WHERE ancestor_id IN (". $menuIds. ")
				  AND child_id = ". (int) $currentMenuId;
			
			$result = sqlQuery($sql);
			while ($row = sqlFetchRow($result)) {
				$rows[$row[0]]['on'] = true;
			}
		}
		
		//Loop through each found Menu Item
		foreach ($rows as $menuId => &$row) {
			
			if ($recurseCount == 1) {
				$level1counter++;
			}
			
			if ($onlyIncludeOnLinks && !$row['on']) {
				//Have a "breadcrumbs" option to only show the chain to the current content item
				continue;
			
			} else {
				$row['active'] = $showMenuItem = shouldShowMenuItem($row, $cachingRestrictions);
				
				if (checkPriv()) {
					//Always show an Admin a Menu Node
					$showMenuItem = true;
					$row['onclick'] = "if (!window.zenarioA) return true; return zenarioA.openMenuAdminBox({id: ". (int)  $row['mID']. "});";
					if (empty($row['css_class'])) {
						$row['css_class'] = 'zenario_menu_node';
					} else {
						$row['css_class'] .= ' zenario_menu_node';
					}
				}
			}
		
			if ($showMenuItem) {
				if ($row['target_loc'] == 'ext' && $row['ext_url']) {
					$row['url'] = $row['ext_url'];
						
				} else if ($row['target_loc'] == 'int' && $row['cID']) {
					$request = '';
					if ($row['cType'] == 'document' && !$row['use_download_page']) {
						$request = '&download=1';
						
						if (inc('zenario_google_analytics_tracker')) {
							$row['onclick'] = zenario_google_analytics_tracker::trackDownloadNow($row['cID'], $row['cType'], $row['alias']);
						}
					}
					
					$row['url'] = linkToItem($row['cID'], $row['cType'], false, $request, $row['alias'], true);
					if (!empty($row['anchor'])) {
						$row['url'] .= '#'.$row['anchor'];
					}
					
					if (cms_core::$cID == $row['cID'] && cms_core::$cType == $row['cType'] && cms_core::$menuTitle !== false) {
						$row['name'] = cms_core::$menuTitle;
					}
			
				} else {
					$row['url'] = '';
				}
				
				if ($row['accesskey']) {
					$row['title'] = adminPhrase('_ACCESS_KEY_EQUALS', array('key' => $row['accesskey']));
				}
		
				if ($row['open_in_new_window']) {
					$row['target'] = '_blank';
				}
			
			} else {
				$row['url'] = '';
				unset($row['onclick']);
			}
			
			if ($showMenuItem || $row['name']) {
				$goFurther = !$numLevels || $recurseCount < $numLevels;
				$followLink = !$onlyFollowOnLinks || $row['on'];
				
				//If this row has children...
				if ($row['children']) {
					//Recurse down into the child levels and display them, if needed
					if ($goFurther && $followLink) {
						$row['children'] = getMenuStructure(
												$cachingRestrictions,
												$sectionId, $currentMenuId, $row['mID'],
												$numLevels, $maxLevel1MenuItems, $language,
												$onlyFollowOnLinks, $onlyIncludeOnLinks,
												$showAdminAddMenuItem, $showInvisibleMenuItems, $showMissingMenuNodes,
												$recurseCount);
						
						if ($row['target_loc'] == 'none' && checkPriv()) {
							//Publishing a Content Item under an unlinked Menu Node will cause that to appear - mark this as so in Admin Mode
							foreach ($row['children'] as &$child) {
								if (!empty($child['active'])) {
									$row['active'] = true;
									break;
								}
							}
						}
					
					//Otherwise if we're not recursing, check that at least one of the children are in fact visible to the current Visitor
					} else {
						$row['children'] = false;
						$result2 = cms_core::lookForMenuItems($row['mID'], $language, $sectionId, $currentMenuId, $recurseCount, $showInvisibleMenuItems);
						while ($row2 = sqlFetchAssoc($result2)) {
							if ($row2['target_loc'] != 'none' && empty($row2['invisible']) && shouldShowMenuItem($row2, $cachingRestrictions)) {
								$row['children'] = true;
								break;
							}
						}
					}
					
					//Unlinked Menu Items with visible children should still be shown to Visitors.
					//Unlinked Menu Items with no visible children should be shown but marked as inactive to Admins.
					if (checkPriv()) {
						$showMenuItem = true;
					
					} elseif (!empty($row['children']) && $followLink && $goFurther) {
						$showMenuItem = true;
						$row['active'] = true;
					}
				}
				
				//Even if a Menu Node had no children, Admins should still have an "add menu" button if appropriate
				if ($showAdminAddMenuItem && $goFurther && $followLink && checkPriv('_PRIV_ADD_MENU_ITEM') && empty($row['children'])) {
					$row['children'] = array('add' => showAdminAddMenuItem($sectionId, $language, $row['mID']));
				}
			}
			
			if ($showMenuItem) {
				//Don't show unlinked Menu Nodes that have no immediate children to Visitors
				if ($row['target_loc'] != 'none' || $row['children'] || checkPriv()) {
					//Ensure that we show this Menu Node!
					unset($unsets[$menuId]);
				}
				
				if ($recurseCount == 1) {
					if ($level1counter >= $maxLevel1MenuItems) {
						break;
					}
				}
			}
		}
		
		//Remove any Menu Items that we should not display
		foreach ($unsets as $menuId => $dummy) {
			unset($rows[$menuId]);
		}
	}
	
	if (
		checkPriv('_PRIV_ADD_MENU_ITEM') && $showAdminAddMenuItem
		&& ($recurseCount > 1 || $level1counter < $maxLevel1MenuItems)
	) {
		$rows['add'] = showAdminAddMenuItem($sectionId, $language, $parentMenuId);
	}
	
	if ($recurseCount>1000) {
		echo "Aborting; Menu Generation seems to be in an infinite recursion loop!";
		exit;
	}
	
	return $rows;
}


function showAdminAddMenuItem($sectionId, $language, $parentMenuId) {
	
	$mrg = array('section' => htmlspecialchars(menuSectionName($sectionId)));
	if ($parentMenuId) {
		$mrg['menuitem'] = htmlspecialchars(getMenuName($parentMenuId));
		$tooltip = adminPhrase('Create a Menu Node here|Section: [[section]]<br />Parent: [[menuitem]]', $mrg);
	} else {
		$tooltip = adminPhrase('Create a Menu Node here|Section: [[section]]<br />Level 1', $mrg);
	}
	
	return array(
			'url' => '#',
			'is_admin_add_menu_item' => true,
			'onclick' =>
				checkPriv('_PRIV_CREATE_FIRST_DRAFT')?
					"zenarioAB.open('zenario_quick_create', {quick_create_menu: 1, target_menu_parent: ". (int) $parentMenuId. ", target_menu_section: ". (int) $sectionId. ", target_language_id: '". jsEscape($language). "'});"
				 :	"zenarioA.openMenuAdminBox({quick_create_menu: 1, target_menu_parent: ". (int) $parentMenuId. ", target_menu_section: ". (int) $sectionId. ", target_language_id: '". jsEscape($language). "'}, true);",
			'img' => '<img src="zenario/admin/images/slots/create_menu_icon.png" class="pluginAdminMenuButton" alt="'. adminPhrase('_ADD'). '" border="0" 
						title="'. $tooltip. '" />'
			);
}





//Build a link to a content item
function linkToItem(
			$cID, $cType = 'html', $fullPath = false, $request = '', $alias = false,
			$autoAddImportantRequests = false, $useAliasInAdminMode = false,
			$httpHost = false, $returnAliasAndLanguageCode = false) {
	
	if (!is_numeric($cID)) {
		$tagId = $cID;
		getCIDAndCTypeFromTagId($cID, $cType, $tagId);
	}
	
	//In 6.1, we're no longer allowing this function to be called with a non-numeric alias or other bad data
	if (!$cID || !is_numeric($cID) || !$cType) {
		return false;
	}
	
	$useAlias = ($useAliasInAdminMode || empty($_SESSION['admin_logged_into_site']) || !checkPriv());
	$usingAlias = false;
	
	//Attempt to look up the alias if it wasn't provided
	$content = false;
	if ($alias === false) {
		if ($cID == cms_core::$cID && $cType == cms_core::$cType) {
			$alias = cms_core::$alias;
		
		} elseif ($useAlias) {
			$result = sqlSelect("
				SELECT lang_code_in_url, language_id, alias
				FROM ". DB_NAME_PREFIX. "content
				WHERE id = ". (int) $cID. "
				  AND type = '". sqlEscape($cType). "'"
			);
			if ($content = sqlFetchRow($result)) {
				$alias = $content[2];
			}
		}
	}
	
	//Link to the item using either the cID or the alias.
	if ($alias && $useAlias) {
		$aliasOrCID = $alias;
		$usingAlias = true;
		
		//If multiple languages are enabled on this site, check to see if we need to add the language code to the alias.
		if (getNumLanguages() > 1) {
			//We will need to add the language code if the alias is used more than once,
			//the settings for that Content Item say so, or if the settings for the Content Item are left on
			//default and the Site Settings say so.
			//Note for efficiency, use slightly different code depending on if we already have the details looked up
			if ($content) {
				if ($content[0] == 'show' || ($content[0] == 'default' && !setting('translations_hide_language_code'))) {
					$needToUseLangCode = true;
				} else {
					$sql = "
						SELECT 1
						FROM ". DB_NAME_PREFIX. "content
						WHERE alias = '". sqlEscape($alias). "'
						LIMIT 2";
					$result = sqlQuery($sql);
					$needToUseLangCode = sqlFetchRow($result) && sqlFetchRow($result);
				}
					
			} else {
				$sql = "
					SELECT lang_code_in_url, language_id
					FROM ". DB_NAME_PREFIX. "content
					WHERE alias = '". sqlEscape($alias). "'
					ORDER BY
						id = ". (int) $cID. " DESC,
						type = '". sqlEscape($cType). "' DESC
					LIMIT 2";
				
				$result = sqlQuery($sql);
				if (!$content = sqlFetchRow($result)) {
					return false;
				
				} else {
					$needToUseLangCode =
						sqlFetchRow($result)
					 || ($content[0] == 'show' || ($content[0] == 'default' && !setting('translations_hide_language_code')));
				}
			}
			
			if ($needToUseLangCode) {
				$aliasOrCID .= ','. $content[1];
			}
		}
		
	} else {
		$aliasOrCID = $cType. '_'. $cID;
	}
	
	if ($returnAliasAndLanguageCode) {
		if (!$usingAlias) {
			return false;
		} else {
			return $aliasOrCID;
		}
	}
	
	
	//Add important requests to the URL, if the content item being linked to is the current content item
	$request = addAmp($request);
	if ($autoAddImportantRequests
	 && !empty(cms_core::$importantGetRequests)
	 && is_array(cms_core::$importantGetRequests)
	 && ($alias == request('cID')
	  || $aliasOrCID == request('cID')
	  || ($cID == request('cID') && $cType == ifNull(request('cType'), 'html')))) {
		foreach(cms_core::$importantGetRequests as $getRequest => $defaultValue) {
			if (isset($_GET[$getRequest]) && $_GET[$getRequest] != $defaultValue) {
				$request .= '&'. urlencode($getRequest). '='. urlencode($_GET[$getRequest]);
			}
		}
	}
	
	if (!$fullPath) {
		$fullPath = '';
	} elseif (!$httpHost) {
		$fullPath = httpOrHttps(). httpHost(). SUBDIRECTORY;
	} else {
		$fullPath = httpOrHttps(). $httpHost. SUBDIRECTORY;
	}
	
	if ($useAlias
	 && $cType == 'document'
	 && ($request === '&download=1'
	  || $request === '&download=true'
	  || $request === '&download=1&cType=document'
	  || $request === '&download=true&cType=document'
	  || $request === '&cType=document&download=1'
	  || $request === '&cType=document&download=true')
	 && setting('mod_rewrite_enabled')) {
		return $fullPath. $aliasOrCID. '.download';
	
	} elseif ($useAlias && $request === '&method_call=showRSS' && setting('mod_rewrite_enabled')) {
		return $fullPath. $aliasOrCID. '.rss';
	
	} elseif ($request) {
		return $fullPath. indexDotPHP(!$fullPath). "?cID=". $aliasOrCID. addAmp($request);
	
	} elseif ($useAlias && setting('mod_rewrite_enabled')) {
		return $fullPath. $aliasOrCID. setting('mod_rewrite_suffix');
	
	} else {
		return $fullPath. indexDotPHP(!$fullPath). "?cID=". $aliasOrCID;
	}
}

function showAdminTitle($titleAdmin, $info="") {
	$text = "<tr style=\"background-color:#CCCCCC;\"><td class=\"panel_title\" style=\"text-align: left;\"><h5 style=\"text-align:left;\">";
	$text .= $titleAdmin;
	$text .="</h5>" . $info . "</td></tr>";
	return $text;
}

function getLanguages($includeAllLanguages = false, $onlyIncludeLangId = false, $orderByEnglishName = false, $defaultLangFirst = false) {
	$sql = "
		SELECT
			l.id,
			IFNULL(en.local_text, lo.local_text) AS english_name,
			IFNULL(lo.local_text, en.local_text) AS language_local_name,
			IFNULL(f.local_text, 'white') as flag
		FROM ";
		
	if ($onlyIncludeLangId !== false) {
		$sql .= "
			(SELECT '". sqlEscape($onlyIncludeLangId). "' AS id)";
	
	} elseif ($includeAllLanguages) {
		$sql .= "
			(
				SELECT DISTINCT language_id AS id
				FROM ". DB_NAME_PREFIX. "visitor_phrases
			)";
	
	} else {
		$sql .= DB_NAME_PREFIX. "languages";
	}
	
	$sql .= " AS l
		LEFT JOIN ". DB_NAME_PREFIX. "visitor_phrases AS en
		   ON en.module_class_name = 'zenario_common_features'
		  AND en.language_id = l.id
		  AND en.code = '__LANGUAGE_ENGLISH_NAME__'
		LEFT JOIN ". DB_NAME_PREFIX. "visitor_phrases AS lo
		   ON lo.module_class_name = 'zenario_common_features'
		  AND lo.language_id = l.id
		  AND lo.code = '__LANGUAGE_LOCAL_NAME__'
		LEFT JOIN ". DB_NAME_PREFIX. "visitor_phrases AS f
		   ON f.module_class_name = 'zenario_common_features'
		  AND f.language_id = l.id
		  AND f.code = '__LANGUAGE_FLAG_FILENAME__'
		ORDER BY ";
	
	if ($defaultLangFirst) {
		$sql .= "l.id = '". sqlEscape(setting('default_language')). "' DESC, ";
	}
	
	if ($orderByEnglishName) {
		$sql .= "IFNULL(en.local_text, lo.local_text)";
	} else {
		$sql .= "l.id";
	}
	
	$result = sqlQuery($sql);
	$langs = array();
	while ($row = sqlFetchAssoc($result)) {
		if ($onlyIncludeLangId) {
			return $row;
		} else {
			$langs[$row['id']] = $row;
		}
	}
	
	if ($onlyIncludeLangId) {
		return false;
	} else {
		return $langs;
	}
}

function getNumLanguages() {
	if (!defined('ZENARIO_NUM_LANGUAGES')) {
		$result = sqlSelect("SELECT COUNT(*) FROM " . DB_NAME_PREFIX . "languages");
		$row = sqlFetchRow($result);
		define('ZENARIO_NUM_LANGUAGES', $row[0]);
	}
	return ZENARIO_NUM_LANGUAGES;
}

function getLanguage($langId = false) {
	return getLanguages(true, ifNull($langId, session('user_lang'), setting('default_language')));
}

function getLanguageName($languageId = false, $addIdInBracketsToEnd = true, $returnIdOnFailure = true) {
	
	$name = getRow('visitor_phrases', 'local_text', array('code' => '__LANGUAGE_ENGLISH_NAME__', 'language_id' => $languageId, 'module_class_name' => 'zenario_common_features'));
	
	if ($name !== false) {
		if ($addIdInBracketsToEnd) {
			return $name. ' ('. $languageId. ')';
		} else {
			return $name;
		}
	} elseif ($returnIdOnFailure) {
		return $languageId;
	} else {
		return false;
	}

}

function getLanguageFlag($langId = false) {
	if ($lang = getLanguage($langId)) {
		return $lang['flag'];
	} else {
		return 'white';
	}
}

function isEmtpyVisitorLanguagePack( $languageId ) {
	$sql = "
		SELECT COUNT( code )
		FROM " . DB_NAME_PREFIX . "visitor_phrases
		WHERE language_id = '" . sqlEscape($languageId) . "'";
	$result = sqlQuery($sql);
	list( $quantity ) = sqlFetchRow($result);
	if ( $quantity > 0 ) { 
		return false;
	} else {
		return true;
	}
}









function getDatasetDetails($dataset) {
	if (is_array($dataset)) {
		return $dataset;
	} elseif (is_numeric($dataset)) {
		return getRow('custom_datasets', true, $dataset);
	} else {
		return getRow('custom_datasets', true, array('system_table' => $dataset));
	}
}

function getDatasetTabDetails($datasetId, $tabName) {
	return getRow('custom_dataset_tabs', true, array('dataset_id' => $datasetId, 'name' => $tabName));
}

function getDatasetFieldDetails($field, $dataset = false) {
	if (is_numeric($field)) {
		return getRow('custom_dataset_fields', true, $field);
	} else {
		if (!is_numeric($dataset) && !is_array($dataset)) {
			$dataset = getDatasetDetails($dataset);
		}
		if (!empty($dataset['id'])) {
			return getRow('custom_dataset_fields', true, array('dataset_id' => $dataset['id'], 'db_column' => $field));
		}
	}
}

function getDatasetFieldsDetails($dataset) {
	if (is_string($dataset)) {
		$dataset = getDatasetDetails($dataset);
	}
	if (is_array($dataset)) {
		$dataset = $dataset['id'];
	}
	
	$out = array();
	if ($fields = getRowsArray('custom_dataset_fields', true, array('dataset_id' => $dataset, 'type' => array('!' => 'other_system_field')))) {
		foreach ($fields as $field) {
			$out[$field['db_column']] = $field;
		}
	}
	
	return $out;
}

function getDatasetFieldValue($recordId, $field, $dataset = false) {
	if (($field = getDatasetFieldDetails($field))
	 && ($dataset = getDatasetDetails(ifNull($dataset, $field['dataset_id'])))) {
		if ($field['is_system_field']) {
			if ($field['db_column']) {
				return getRow($dataset['system_table'], $field['db_column'], $recordId);
			}
		} else {
			if ($field['type'] == 'checkboxes') {
				$values = getDatasetFieldLOV($cfield, false);
				return inEscape(
						getRowsArray(
							'custom_dataset_values_link',
							'value_id',
							array(
								'linking_id' => $tags['key']['id'],
								'value_id' => array_keys($cfield['values']))),
						'numeric');
			} else {
				return getRow($dataset['table'], $field['db_column'], $recordId);
			}
		}
	}
	
	return false;
}

//Checkboxes are stored in the custom_dataset_values_link table as there could be more than one of them.
//Given an array or comma-seperated list of the checked values, this function will set the value in the
//database.
function updateDatasetCheckboxField($datasetId, $fieldId, $linkingId, $values) {
	if (!is_array($values)) {
		$values = explode(',', $values);
	}
	
	//Loop through making sure that the selected values are in the database.
	$selectedIds = array();
	foreach ($values as $id) {
		if ($id) {
			$selectedIds[$id] = $id;
			setRow(
				'custom_dataset_values_link',
				array(),
				array('dataset_id' => $datasetId, 'value_id' => $id, 'linking_id' => $linkingId));
		}
	}
	
	//Remove any values from the database that *weren't* selected
	$sql = "
		DELETE cdvl.*
		FROM ". DB_NAME_PREFIX. "custom_dataset_field_values AS cdfv
		INNER JOIN ". DB_NAME_PREFIX. "custom_dataset_values_link AS cdvl
		   ON cdvl.value_id = cdfv.id
		  AND cdvl.linking_id = ". (int) $linkingId. "
		WHERE cdfv.field_id = ". (int) $fieldId;
	
	if (!empty($selectedIds)) {
		$sql .= "
		  AND cdfv.id NOT IN (". inEscape($selectedIds, 'numeric'). ")";
	}
	
	sqlQuery($sql);
}


function datasetFieldDBColumn($fieldId) {
	return getRow('custom_dataset_fields', 'db_column', $fieldId);
}

function datasetFieldId($fieldDbColumn) {
	return getRow('custom_dataset_fields', 'id', array('db_column' => $fieldDbColumn));
}

function getDatasetSystemFieldDetails($datasetId, $tabName, $fieldName) {
	return getRow('custom_dataset_fields', true, array('dataset_id' => $datasetId, 'tab_name' => $tabName, 'field_name' => $fieldName, 'is_system_field' => 1));
}

function getDatasetFieldValueLabel($valueId) {
	return getRow('custom_dataset_field_values', 'label', $valueId);
}

function getDatasetFieldLOVFlatArrayToLabeled(&$value, $key) {
	if (!is_array($value)) {
		$value = array('label' => $value);
	}
	
	++cms_core::$dbupCurrentRevision;
	
	if (empty($value['ord'])) {
		$value['ord'] = cms_core::$dbupCurrentRevision;
	}
}

function getDatasetFieldLOV($field, $flat = true) {
	if (!is_array($field)) {
		$field = getDatasetFieldDetails($field);
	}
	
	$lov = array();
	if (chopPrefixOffOfString($field['type'], 'centralised_')) {
		if ($field['values_source']
		 && ($source = explode('::', $field['values_source'], 3))
		 && (!empty($source[0]))
		 && (!empty($source[1]))
		 && (!isset($source[2]))
		 && (inc($source[0]))) {
			$lov = call_user_func($source);
			
			if (!$flat) {
				cms_core::$dbupCurrentRevision = 0;
				array_walk($lov, 'getDatasetFieldLOVFlatArrayToLabeled');
				cms_core::$dbupCurrentRevision = false;
			}
		}
	} else {
		if ($flat) {
			$cols = 'label';
		} else {
			$cols = array('ord', 'label', 'note_below');
		}
		
		$lov = getRowsArray('custom_dataset_field_values', $cols, array('field_id' => $field['id']), array('ord'));
	}
	
	return $lov;
}

function countDatasetFieldRecords($field, $dataset = false) {
	if (!is_array($field)) {
		$field = getDatasetFieldDetails($field);
	}
	if ($field && !is_array($dataset)) {
		$dataset = getDatasetDetails($field['dataset_id']);
	}
	
	if ($field && $dataset) {
		
		if ($field['type'] == 'checkboxes') {
			$sql = "
				SELECT COUNT(DISTINCT vl.linking_id)
				FROM ". DB_NAME_PREFIX. "custom_dataset_field_values AS fv
				INNER JOIN ". DB_NAME_PREFIX. "custom_dataset_values_link AS vl
				ON vl.value_id = fv.id
				WHERE fv.field_id = ". (int) $field['id'];
		
		} elseif (in($field['type'], 'checkbox', 'group', 'radios', 'select')) {
			$sql = "
				SELECT COUNT(*)
				FROM `". DB_NAME_PREFIX. sqlEscape($field['is_system_field']? $dataset['system_table'] : $dataset['table']). "`
				WHERE `". sqlEscape($field['db_column']). "` != 0";
		
		} else {
			$sql = "
				SELECT COUNT(*)
				FROM `". DB_NAME_PREFIX. sqlEscape($field['is_system_field']? $dataset['system_table'] : $dataset['table']). "`
				WHERE `". sqlEscape($field['db_column']). "` IS NOT NULL
				  AND `". sqlEscape($field['db_column']). "` != ''";
		}
		
		$result = sqlQuery($sql);
		$row = sqlFetchRow($result);
		
		return $row[0];
	} else {
		return false;
	}
}


function getUserGroups($user_id) {
	$groups = array();
	
	//Look up a list of group names on the system
	if (!is_array(cms_core::$groups)) {
		cms_core::$groups = getRowsArray('custom_dataset_fields', 'db_column', array('type' => 'group', 'is_system_field' => 0));
	}
	
	if (!empty(cms_core::$groups)) {
		//Get the row from the users_custom_data table for this user
		//(Note that the group names stored in cms_core::$groups are the column names)
		$inGroups = getRow('users_custom_data', cms_core::$groups, $user_id);
		
		//Come up with a subsection of the groups that this user is in
		foreach (cms_core::$groups as $groupId => $groupName) {
			if (!empty($inGroups[$groupName])) {
				$groups[$groupId] = $groupName;
			}
		}
	}
	
	return $groups;
}

function checkUserInGroup($groupId, $userId = 'session') {
	
	if ($userId === 'session') {
		$userId = $_SESSION['extranetUserID'];
	}
	
	if (!$userId || !((int) $groupId)) {
		return false;
	}
	
	$group_name = datasetFieldDBColumn($groupId);
	
	if(!$group_name) {
		return false;
	}
	
	$sql = "
		SELECT 1
		FROM ". DB_NAME_PREFIX. "users_custom_data
		WHERE `" .$group_name . "` = 1
		  AND user_id = ". (int) $userId;
	
	$result = sqlQuery($sql);
	
	return (bool) sqlFetchRow($result);
}

function getUserGroupsNames( $userId ) {
	$groups = getUserGroups($userId);
	
	if (empty($groups)) {
		return adminPhrase('_NO_GROUP_MEMBERSHIPS');
	} else {
		return implode(', ', $groups);
	}
}

function getGroupLabel($group_id) {
	if ($group_id) {
		if(is_numeric($group_id)) {
			return getRow('custom_dataset_fields', 'label', $group_id);
		} else {
			return getRow('custom_dataset_fields', 'label', array('db_column' => $group_id));
		}
	} else {
		return adminPhrase("_ALL_EXTRANET_USERS");
	}

}

function getGroupPrivateName($group_id) {
	$sql = "
		SELECT name
		FROM ". DB_NAME_PREFIX."groups
		WHERE id = ". (int) $group_id;
	
	$result = sqlQuery($sql);
	if ($row = sqlFetchArray($result)) {
		return $row['name'];
	}
	return false;
}

//CONTENT TYPE
function getContentStatus($cID, $cType) {
	return getRow('content', 'status', array('id' => $cID, 'type' => $cType));
}


//Given an image size and a target size, resize the image (maintaining aspect ratio).
function resizeImage($image_width, $image_height, $constraint_width, $constraint_height, &$width_out, &$height_out, $allowUpscale = false) {
	$width_out = $image_width;
	$height_out = $image_height;
	
	if ($image_width == $constraint_width && $image_height == $constraint_height) {
		return;
	}
	
	if (!$allowUpscale && ($image_width <= $constraint_width) && ($image_height <= $constraint_height)) {
		return;
	}

	if (($constraint_width / $image_width) < ($constraint_height / $image_height)) {
		$width_out = $constraint_width;
		$height_out = (int) ($image_height * $constraint_width / $image_width);
	} else {
		$height_out = $constraint_height;
		$width_out = (int) ($image_width * $constraint_height / $image_height);
	}

	return;
}

//Given an image size and a target size, resize the image by different conditions and return the values used in the calculations
function resizeImageByMode(&$mode, $width, $height, $maxWidth, $maxHeight, &$newWidth, &$newHeight, &$cropWidth, &$cropHeight, &$cropNewWidth, &$cropNewHeight) {
	
	if ($mode == 'unlimited') {
		$cropNewWidth = $cropWidth = $newWidth = $width;
		$cropNewHeight = $cropHeight = $newHeight = $height;
	
	} elseif ($mode == 'stretch') {
		$cropWidth = $width;
		$cropHeight = $height;
		$cropNewWidth = $newWidth = $maxWidth;
		$cropNewHeight = $newHeight = $maxHeight;
	
	} elseif ($mode == 'resize_and_crop') {
		
		if (($maxWidth / $width) < ($maxHeight / $height)) {
			$newWidth = (int) ($width * $maxHeight / $height);
			$newHeight = $maxHeight;
			$cropWidth = (int) ($maxWidth * $height / $maxHeight);
			$cropHeight = $height;
			$cropNewWidth = $maxWidth;
			$cropNewHeight = $maxHeight;
		
		} else {
			$newWidth = $maxWidth;
			$newHeight = (int) ($height * $maxWidth / $width);
			$cropWidth = $width;
			$cropHeight = (int) ($maxHeight * $width / $maxWidth);
			$cropNewWidth = $maxWidth;
			$cropNewHeight = $maxHeight;
		}
	
	} elseif ($mode == 'fixed_width') {
		$maxHeight = $height;
		$mode = 'resize';
	
	} elseif ($mode == 'fixed_height') {
		$maxWidth = $width;
		$mode = 'resize';
	
	} else {
		$mode = 'resize';
	}
	
	if ($mode == 'resize') {
		$newWidth = false;
		$newHeight = false;
		resizeImage($width, $height, $maxWidth, $maxHeight, $newWidth, $newHeight);
		$cropWidth = $width;
		$cropHeight = $height;
		$cropNewWidth = $newWidth;
		$cropNewHeight = $newHeight;
	}
}

function resizeImageString(&$image, $mime_type, &$width, &$height, $maxWidth, $maxHeight, $mode = 'resize', $offset = 0) {
	require funIncPath(__FILE__, __FUNCTION__);
}




/* Content specific functions */


/* Documents */


function createRandomDir($length, $type = 'downloads', $onlyForCurrentVisitor = true, $ip = -1, $prefix = '') {
	mt_srand((double) microtime() * 100000);
	
	$dir = '';
	for ($r = 0; $r < $length; $r += 3) {
		$dir .= base_convert(mt_rand(0, 46655), 10, 36);
	}
	
	return createCacheDir($prefix. $dir, $type, $onlyForCurrentVisitor, $ip);
}


function createCacheDir($dir, $type = 'downloads', $onlyForCurrentVisitor = true, $ip = -1, $mainDir = false) {
	
	if (!$mainDir) {
		if ($type == 'downloads' || $type == 'images' || $type == 'files') {
			$mainDir = 'private';
		} else {
			$mainDir = 'cache';
		}
	}
	
	$fullPath = CMS_ROOT. ($path = $mainDir. '/'. $type. '/');
	
	if (!is_dir($fullPath) || !is_writable($fullPath)) {
		return false;
	
	} else {
		$path .= $dir. '/';
		$fullPath .= $dir. '/';
		
		if (is_dir($fullPath)) {
			touch($fullPath. 'accessed');
			return $path;
		
		} else {
			mkdir($fullPath, 0777);
			chmod($fullPath, 0777);
			
			if ($onlyForCurrentVisitor) {
				htaccessFileForCurrentVisitor($fullPath, $ip);
			}
			
			touch($fullPath. 'accessed');
			chmod($fullPath. 'accessed', 0666);
			return $path;
		}
	}
}

function htaccessFileForCurrentVisitor($path, $ip = -1) {
	if ($ip === -1) {
		$ip = visitorIP();
	}
	
	if (!$ip) {
		$file  = "deny from all\n";
	
	} elseif (defined('USE_FORWARDED_IP') && constant('USE_FORWARDED_IP')) {
		$file  = 'RewriteEngine on'. "\n";
		$file .= 'RewriteCond %{HTTP:X-Forwarded-For} !^'. str_replace(',', '\\,', str_replace(' ', '\\ ', preg_quote($ip))). '$'. "\n";
		$file .= 'RewriteRule . - [F,NC]'. "\n";
	
	} else {
		$file  = "deny from all\n";
		$file .= "allow from ". $ip. "\n";
	}
	
	$file .= "RemoveType .php\n";
	
	if (file_put_contents($path. '/.htaccess', $file)) {
		chmod($path. '/.htaccess', 0666);
		return true;
	} else {
		return false;
	}
}


function cleanDownloads() {
	//Only allow this function to run at most once per page-load
	if (defined('ZENARIO_CLEANED_DOWNLOADS')) {
		return ZENARIO_CLEANED_DOWNLOADS;
	
	} else {
		$time = time();
		
		//Check if this function was last run within the last 5 minutes
		$lifetime = 5 * 60;
		if (file_exists($accessed = 'cache/stats/clean_downloads/accessed')) {
			$timeA = fileatime($accessed);
			$timeM = filemtime($accessed);
			
			if (!$timeA || $timeA < $timeM) {
				$timeA = $timeM;
			}
			
			$empty = $timeA < $time - $lifetime;
		} else {
			$empty = true;
		}
		
		if (!$empty) {
			//If it was run in the last 5 minutes, don't run it again now
			define('ZENARIO_CLEANED_DOWNLOADS', true);
			return true;
		
		} else {
			//Otherwise, continue running cleanDownloads(), and call the createCacheDir() function to create/touch
			//the cache/stats/clean_downloads/accessed file so we know that we last ran cleanDownloads() at this current time
			createCacheDir('clean_downloads', 'stats', true, false);
			
			return require funIncPath(__FILE__, __FUNCTION__);
		}
	}
}



function getSearchtermParts($searchString) {
	//Remove everything from the search terms except for word characters, single quotes (which can be part of words) and double quotes
	//Attempt to validate allowing UTF-8 characters through
	if (!function_exists('mb_ereg_replace')
	 || !$searchString = mb_ereg_replace('[^\w\s_\'"]', ' ', $searchString)) {
		//Fall back to traditional pattern matching if that fails
		$searchString = preg_replace('/[^\w\s_\'"]/', ' ', $searchString);
	}
	
	//Limit the search results to 100 chars
	$searchString = substr($searchString, 0, 100);
	
	//Break the search string up into tokens.
	//Normally we break by spaces, but you can use a pattern in double quotes to override this
	preg_match_all('/"([^"]+)"|(\S+)/', trim($searchString), $searchStrings, PREG_SET_ORDER);
	
	$quotesUsed = false;
	$searchWordsAndPhrases = array();
	
	foreach($searchStrings as $i => $string) {
		//Have a limit of 10 words
		if ($i >= 10) {
			break;
		}
		
		//Remove any double-quotes that might still be in the text
		$string = str_replace('"', '', $string);
		
		if (isset($string[2])) {
			$searchWordsAndPhrases[$string[2]] = 'word';
		} else {
			$searchWordsAndPhrases[$string[1]] = 'phrase';
			$quotesUsed = true;
		}
	}
	
	//Just in case the user doesn't know about the "using quotes to group words together" feature,
	//add the whole phrase in as a search term
	if (!$quotesUsed && count($searchStrings > 1)) {
		$searchWordsAndPhrases[$searchString] = 'whole phrase';
	}
	
	return $searchWordsAndPhrases;
}




/* Categories */

function getCategoryName($id) {
	return getRow('categories', 'name', array('id' => $id));
}

function getContentItemLayout($cID, $cType, $cVersion) {
	return getRow('versions', 'layout_id', array('id' => $cID, 'type' => $cType, 'version' => $cVersion));
}

function getContentItemCategories($cID, $cType, $publicOnly = false, $langId = false, $sql = false) {
	
	$equivId = equivId($cID, $cType);

	if ($sql === false) {
		$sql = "
			SELECT 
				c.id,
				c.parent_id,
				c.name,
				c.public,
				c.landing_page_equiv_id,
				c.landing_page_content_type
			FROM " . DB_NAME_PREFIX . "categories AS c
			INNER JOIN " . DB_NAME_PREFIX . "category_item_link AS cil
				ON c.id = cil.category_id
			WHERE cil.equiv_id = " . (int) $equivId . "
				AND cil.content_type = '" . sqlEscape($cType) . "'";
	}
	
	if ($publicOnly) {
		$sql .= "
			AND c.public = 1";
	}
				
	$result = sqlQuery($sql);
	
	if (sqlNumRows($result)>0) {
		while ($row = sqlFetchArray($result)) {
			if (!$row['public']) {
				$row['public_name'] = false;
				$row['landing_page_equiv_id'] = false;
				$row['landing_page_content_type'] = false;
			} else {
				$row['public_name'] = phrase('_CATEGORY_'. $row['id'], false, '', $langId);
			}
			
			$categories[] = $row;
		}
		
		return $categories;
	} else {
		return false;
	}
}

function setContentItemCategories($cID, $cType, $categories) {
	$equivId = equivId($cID, $cType);
	
	deleteRow('category_item_link', array('equiv_id' => $equivId, 'content_type' => $cType));
	
	if (is_array($categories)) {
		foreach ($categories as $value) {
			if ($value) {
				insertRow('category_item_link', array('category_id' => $value, 'equiv_id' => $equivId, 'content_type' => $cType));
			}
		}
	}
}
function addSingleContentItemToCategories($cID, $cType, $categories) {
	$equivId = equivId($cID, $cType);
	
	if (!is_array($categories)) {
		$categories = explode(',', $categories);
	}
	
	foreach ($categories as $id) {
		if ($id) {
			setRow('category_item_link', array(), array('category_id' => $id, 'equiv_id' => $equivId, 'content_type' => $cType));
		}
	}
}
function addContentItemToCategories($cID, $cType, $categories) {
	$equivId = equivId($cID, $cType);
	
	if (!is_array($categories)) {
		$categories = explode(',', $categories);
	}
	
	foreach ($categories as $id) {
		if ($id) {
			setRow('category_item_link', array(), array('category_id' => $id, 'equiv_id' => $equivId, 'content_type' => $cType));
		}
	}
}

function removeContentItemCategories($cID, $cType, $categories) {
	$equivId = equivId($cID, $cType);
	foreach ($categories as $value) {
		if ($value) {
			deleteRow('category_item_link', array('category_id' => $value, 'equiv_id' => $equivId, 'content_type' => $cType));
		}
	}		
}


/* Miscellaneous */

function formatNicely($text,$limit) {
	
	$text = trim(strip_tags($text));
	
	if (!$text || mb_strlen($text) < $limit) {
		return $text;
	} else {
		$new_content = mb_substr($text,0,$limit);
		
		$new_content = mb_ereg_replace('[^\s,]+$','',$new_content);
		$new_content = mb_ereg_replace('[\s\t\n\r]+$','',$new_content);	
		$new_content = mb_ereg_replace(',+$','',$new_content);	
			
		if (preg_match('/[\.]$/',$new_content) || !$text) {
			return $new_content;
		} else {
			return $new_content. '...';
		}
		
		return $new_content;
	}
}

function formatDateNicely($date, $format_type = false, $languageId = false, $time_format = '', $rss = false) {
	
	//Use $languageId === true as a shortcut to the site default language
	//Otherwise if $languageId is not set, try to get language from session, or the site default if that is not set
	if ($languageId === true) {
		$languageId = setting('default_language');
	
	} elseif (!$languageId) {
		if (!empty($_SESSION['user_lang'])) {
			$languageId = $_SESSION['user_lang'];
	
		} else {		
			$languageId = setting('default_language');
		}
	}
	
	if ($time_format === true) {
		$time_format = ' %H:%i';
	}
	
	if ($rss) {
		$format_type = '%a, %d %b %Y';
		$time_format = ' %H:%i:%s ';
	
	} elseif (!$format_type || $format_type == 'vis_date_format_long' || $format_type == '_LONG') {
		$format_type = setting('vis_date_format_long');
	
	} elseif ($format_type == 'vis_date_format_med' || $format_type == '_MEDIUM') {
		$format_type = setting('vis_date_format_med');
	
	} elseif ($format_type == 'vis_date_format_short' || $format_type == '_SHORT') {
		$format_type = setting('vis_date_format_short');
	}
	
	//If this language is not English, do not show "1st/2nd/3rd
	if ($languageId != 'en' && substr($languageId, 0, 3) != 'en-') {
		$format_type = str_replace('%D', '%e', $format_type);
	}
	
	
	$sql = "SELECT DATE_FORMAT('" . sqlEscape($date) . "', '" . sqlEscape($format_type. $time_format) . "')";
	
	$result = sqlQuery($sql);
	list($formattedDate) = sqlFetchRow($result);
	
	$returnDate = '';
	if ($rss) {
		$returnDate = $formattedDate;
		
		if ($time_format) {
			$sql = "SELECT TIME_FORMAT(NOW() - UTC_TIMESTAMP(), '%H%i') ";
			$result = sqlQuery($sql);
			list($timezone) = sqlFetchRow($result);
			
			if (substr($timezone, 0, 1) != '-') {
				$timezone = '+'. $timezone;
			}
			
			$returnDate .= $timezone;
		}
		
	} else {
		foreach (preg_split('/\[\[([^\[\]]+)\]\]/', $formattedDate, -1,  PREG_SPLIT_DELIM_CAPTURE) as $i => $part) {
			if ($i % 2) {
				$returnDate .= phrase($part, false, '', $languageId);
			} else {
				$returnDate .= $part;
			}
		}
	}
	
	return $returnDate;
}

function formatDateTimeNicely($date, $format_type = false, $languageId = false, $rss = false) {
	return formatDateNicely($date, $format_type, $languageId, true, $rss);
}

function formatTimeNicely($time,$format_type) {
	$sql = "SELECT TIME_FORMAT('" . sqlEscape($time) . "', '" . sqlEscape($format_type) . "') as new_time";
	$result = sqlQuery($sql);
	$row = sqlFetchArray($result);
	if($row) {
		$new_time = $row['new_time'];
	} 
	return $new_time;
}

function configFileSize($size) {
	//Define labels to use
	$labels = array('', 'K', 'M', 'G', 'T');
	$precision = 0;
	
	//Work out which of the labels to use, based on how many powers of 1024 go into the size, and
	//how many labels we have
	$order = min(
				floor(
					log($size) / log(1024)
				),
			  count($labels)-1);
	
	return round($size / pow(1024, $order), $precision). $labels[$order];
}

function formatFilesizeNicely($size, $precision = 0, $adminMode = false, $vlpClass = '') {
	
	if (is_array($size)) {
		$size = $size['size'];
	}
	
	//Return 0 without formating if the size is 0.
	if ($size <= 0) {
		return '0';
	}
	
	//Define labels to use
	$labels = array('_BYTES', '_KBYTES', '_MBYTES', '_GBYTES', '_TBYTES');
	
	//Work out which of the labels to use, based on how many powers of 1024 go into the size, and
	//how many labels we have
	$order = min(
				floor(
					log($size) / log(1024)
				),
			  count($labels)-1);
	
	$mrg = 
		array('size' => 
			round($size / pow(1024, $order), $precision)
		);
	
	if ($adminMode) {
		return adminPhrase($labels[$order], $mrg);
	} else {
		return phrase($labels[$order], $mrg, $vlpClass);
	}
}

function formatFileTypeNicely($type, $vlpClass = '') {
	switch($type) {
	case 'image/jpeg': 
		$new_type = phrase('_JPEG_file', false, $vlpClass);
		break;
	case 'image/pjpeg': 
		$new_type = phrase('_JPEG_file', false, $vlpClass);
		break;
	case 'image/jpg': 
		$new_type = phrase('_JPG_file', false, $vlpClass);
		break;
	case 'image/gif': 
		$new_type = phrase('_GIF_file', false, $vlpClass);
		break;
	case 'image/png': 
		$new_type = phrase('_PNG_file', false, $vlpClass);
		break;
	default:
		$new_type = phrase('_UNDEFINED', false, $vlpClass);
	}
	return $new_type;
}



//Check if the random number generator has been seeded yet, and seed it if not
function seedRandomNumberGeneratorIfNeeded() {
	//Use whether the RANDOM_NUMBER_GENERATOR_IS_SEEDED constant is defined to remember
	//if we've seeded yet
	if (!defined('RANDOM_NUMBER_GENERATOR_IS_SEEDED')) {
		//If we need to seed the random number generator, get the number of microseconds
		//past the current second, and use that to seed
		$x = explode(' ', microtime());
		mt_srand((int) (1000000 * $x[0]));
		
		//Define the RANDOM_NUMBER_GENERATOR_IS_SEEDED constant so that we don't seed again
		define('RANDOM_NUMBER_GENERATOR_IS_SEEDED', true);
	}
}

//Generate a random string of numbers and letter
function randomString($requiredLength = 12) {
	
	seedRandomNumberGeneratorIfNeeded();
	
	//Only use certain characters. (I've stripped out vowels, just in case a swearword
	//is randomly generated. Also 1s look too much like ls and 0s look too much like os
	$lettersToUse = str_split('BCDFGHJKLMNPQRSTVWXYZbcdfghjklmnpqrstvwxyz23456789');
	$stringOut = '';
	
	//Loop while our output string is still too short
	while (strlen($stringOut) < $requiredLength) {
		//Generate a random number, convert it into lots of little numbers between 0 and
		//49 (our string of characters above is 50 long), convert each to one of the characters
		//from our string of characters, then append each to the output string
		foreach(str_split((string) mt_rand(), 2) as $chunk) {
			$stringOut .= $lettersToUse[((int) $chunk) % 50];
		}
	}
	
	//We might have made out string too long, chop it if needed and return it.
	return substr($stringOut, 0, $requiredLength);
}


//Returns an array of password strength names to scores
//Defined as a function so that it is only hardcoded here, rather
//than everywhere it is used.
function passwordStrengthsToValues($strength = false) {
	$a = array('_WEAK' => 0, '_MEDIUM' => 35, '_STRONG' => 70, '_VERY_STRONG' => 100);
	//Either return an array, or if $strength is set, return the score for that strength
	if ($strength) {
		return $a[$strength];
	} else {
		return $a;
	}
}

function passwordValuesToStrengths($value) {
	if ($value < 35) {
		return '_WEAK';
	} elseif ($value < 70) {
		return '_MEDIUM';
	} elseif ($value < 100) {
		return '_STRONG';
	} else {
		return '_VERY_STRONG';
	}
}


//Calculate the bonus for using different sets of characters
	//e.g. lower case, upper case, numeric, non-alphanumeric...
function calculateBonusFromUsage($n) {
	return min(2, $n);
}

//Check if a given password meets the strength requirements.
function checkPasswordStrength($pass, $passwordStrengthRequired = false) {
	return require funIncPath(__FILE__, __FUNCTION__);
}





function getEmailTemplate ($name) {
	$sql = "SELECT subject,
				body
			FROM " . DB_NAME_PREFIX . "email_templates
			WHERE template_name = '" . sqlEscape($name) . "'";
	
	$result = sqlQuery($sql);
	
	if (sqlNumRows($result)>0) {
		$row = sqlFetchArray($result);
		return $row;
	} else {
		return false;
	}
}

/* Contenttype specific functions */

function updateUserForumEntries($user_id,$new_user_id) {
	$user_id = (int) $user_id;
	$new_user_id = (int) $new_user_id;
	
	$sql = "UPDATE " . DB_NAME_PREFIX . "forumthread
			SET start_user_id = " . $new_user_id . "
			WHERE start_user_id = " . $user_id;
	$result = sqlQuery($sql);

	$sql = "UPDATE " . DB_NAME_PREFIX . "forumpost
			SET writer_user_id = " . $new_user_id . "
			WHERE writer_user_id = " . $user_id;
	$result = sqlQuery($sql);

}



/*  modules  */

function getModuleDetails($idOrName, $fetchBy = 'id') {
	$sql = "
		SELECT
			id,
			id AS module_id,
			class_name,
			class_name AS name,
			display_name,
			vlp_class,
			status,
			default_framework,
			css_class_name,
			is_pluggable,
			can_be_version_controlled
		FROM " . DB_NAME_PREFIX . "modules";
	
	if ($fetchBy == 'class' || $fetchBy == 'name') {
		$sql .= "
			WHERE class_name = '" . sqlEscape($idOrName) . "'";
	
	} else {
		$sql .= "
			WHERE id = " . (int) $idOrName;
	}
	
	$result = sqlQuery($sql);
	
	if (!$module = sqlFetchAssoc($result)) {
		return false;
	} else {
		return $module;
	}
}

function getModuleName($id) {
	return getRow('modules', 'class_name', array('id' => $id));
}

function getModuleDisplayName($id) {
	return getRow('modules', 'display_name', array('id' => $id));
}

function getModuleDisplayNameByClassName($name) {
	return getRow('modules', 'display_name', array('class_name' => $name));
}

function getNestedPluginName($id) {
	return getRow('nested_plugins', 'name_or_title', $id);
}

function getModuleClassName($id) {
	return getRow('modules', 'class_name', array('id' => $id));
}

function getModuleId($name) {
	return getRow('modules', 'id', array('class_name' => $name));
}

function getModuleStatus($id) {
	return getRow('modules', 'status', array('id' => $id));
}

function getModuleStatusByName($name) {
	return getRow('modules', 'status', array('class_name' => $name));
}

function getModuleStatusByClassName($name) {
	return getRow('modules', 'status', array('class_name' => $name));
}

function getModuleIdByClassName($name) {
	return getRow('modules', 'id', array('class_name' => $name));
}

function getModuleClassNameByName($name) {
	return $name;
}

function moduleDirs($tests = 'module_code.php') {
	$dirs = array();
	
	if (!is_array($tests)) {
		$tests = array($tests);
	}
	
	foreach (array(
		'zenario/modules/',
		'zenario_extra_modules/',
		'zenario_custom/modules/'
	) as $path) {
		if (is_dir($path)) {
			foreach (scandir($path) as $dir) {
				if (substr($dir, 0, 1) != '.') {
					foreach ($tests as $test) {
						if (file_exists(CMS_ROOT. $path. $dir. '/'. $test)) {
							$dirs[$dir] = $path. $dir. '/'. $test;
						}
					}
				}
			}
		}
	}
	
	return $dirs;
}


function canActivateModule($name, $fetchBy = 'name', $activate = false) {
	
	$error = array();
	$missingPlugin = false;
	
	if ($module = getModuleDetails($name, $fetchBy)) {
		if ($module['status'] != 'module_running') {
			return false;
		
		} elseif ($activate && !includeModuleAndDependencies($module['class_name'], $missingPlugin)) {
			return false;
		}
	
	} else {
		return false;
	}
	
	
	if ($activate) {
		setModulePrefix($module);
		$useThisClassInstance = false;
		
		$class = new $module['class_name'];
		
		$class->setInstance(array(false, false, false, false, false, false, $module['class_name'], $module['vlp_class'], $module['id'], false, false, false, false, false));

		return $class;
	} else {
		return true;
	}
}

function activateModule($name) {
	return canActivateModule($name, 'name', true);
}

function activateModuleClass($name) {
	return canActivateModule($name, 'class', true);
}


function inc($moduleClass) {
	$missingPlugin = array();
	if (($module = getModuleDetails($moduleClass, 'class'))
	 && ($module['status'] == 'module_running')
	 && (includeModuleAndDependencies($module['class_name'], $missingPlugin))) {
		setModulePrefix($module);
		return true;
	} else {
		return false;
	}
}

function getModuleDependencies($moduleName) {
	$sql = "
		SELECT
			d.dependency_class_name,
			d.`type`,
			m.class_name,
			m.id AS module_id,
			m.vlp_class
		FROM ". DB_NAME_PREFIX. "module_dependencies AS d
		LEFT OUTER JOIN ". DB_NAME_PREFIX. "modules AS m
		   ON d.dependency_class_name = m.class_name
		  AND m.status = 'module_running'
		WHERE d.module_class_name = '". sqlEscape($moduleName). "'
		  AND `type` = 'dependency'";
	
	$depends = array();
	$result = sqlQuery($sql);
	while($row = sqlFetchAssoc($result)) {
		$depends[] = $row;
	}
	
	return $depends;
}


//Include all of a Module's Dependency files, then include the Module
//Note: you need to check to see if a Module is running first, before calling this
function includeModuleAndDependencies($moduleName, &$missingPlugin, $recurseCount = 9) {
	
	if (!$recurseCount) {
		return false;
	}
	
	//Check that this has not been included already - if so, our job has already been done
	$file = moduleDir($moduleName, 'module_code.php');
	if (in_array($file, get_included_files())) {
		return true;
	}

	//Check for dependencies
	foreach (getModuleDependencies($moduleName) as $row) {
		//For each dependency, check if it is running and try to include it
		if ($row['module_id'] && includeModuleAndDependencies($row['class_name'], $missingPlugin, $recurseCount-1)) {
			setModulePrefix($row);
		} else {
			//Otherwise report a dependancy as missing, then stop
			$missingPlugin = array('module' => $row['dependency_class_name']);
			return false;
		}
	}
	
	$missingPlugin = false;
	if (file_exists($file)) {
		require_once $file;
		return true;
	} else {
		return false;
	}
}


//Try to check the tuix_file_contents table to see which files we need to include
function modulesAndTUIXFiles(
	$type, $requestedPath = false, $settingGroup = '',
	$getIndividualFiles = true, $includeBaseFunctionalityWithSettingGroups = true,
	$compatibilityClassNames = false
) {
	return require funIncPath(__FILE__, __FUNCTION__);
}

function advancedSearchSQL(&$whereStatement, &$joins, $path, $json, $tablePrefix = '') {
	return require funIncPath(__FILE__, __FUNCTION__);
}



/* Instances and plugin settings */

function getModuleInheritance($moduleClassName, $type) {
	$sql = "
		SELECT dependency_class_name
		FROM ".  DB_NAME_PREFIX. "module_dependencies
		WHERE module_class_name = '". sqlEscape($moduleClassName). "'
		  AND `type` = '". sqlEscape($type). "'
		LIMIT 1";
	
	$result = sqlQuery($sql);
	if ($row = sqlFetchAssoc($result)) {
		return $row['dependency_class_name'];
	} else {
		return false;
	}
}

function getModuleInheritances($moduleClassName, $type, $includeCurrent = true, $recurseLimit = 9) {
	$inheritances = array();
	
	if ($includeCurrent) {
		$inheritances[] = $moduleClassName;
	}
	
	while (--$recurseLimit && ($moduleClassName = getModuleInheritance($moduleClassName, $type))) {
		$inheritances[] = $moduleClassName;
	}
	
	return $inheritances;
}



//Returns the name of the currently running plugin, in upper-case.
//Must be called from code within the plugin's own folder with the __FILE__ Magic Constant
//The main reason for this function is to use in the latest_revision_no.inc.php files, to keep them
//nice and tidy.
function moduleName($file) {
	//Take the current path
		//Match up to and including the modules directory with .*[/\\]modules[/\\]
		//The next sequence of chars will be the modules directory
		//There may be another slash, or to the path - match with [/\\]?.*
	return strtoupper(preg_replace('#.*modules[/\\\\](\w*)[/\\\\]?\w*[/\\\\]?#', '\1', dirname($file)));
}


/* Plugin Slots */

//Get a list of every plugin instance currently running on a page
function getSlotContents(
	&$slotContents,
	$cID, $cType, $cVersion,
	$layoutId = false, $templateFamily = false, $templateFileBaseName = false,
	$specificInstanceId = false, $specificSlotName = false, $ajaxReload = false,
	$runPlugins = true, $exactMatch = false
) {
	
	if ($layoutId === false) {
		$layoutId = contentItemTemplateId($cID, $cType, $cVersion);
	}
	
	if ($templateFamily === false) {
		$templateFamily = getRow('layouts', 'family_name', $layoutId);
	}
	
	if ($templateFileBaseName === false) {
		$templateFileBaseName = getRow('layouts', 'file_base_name', $layoutId);
	}
	
	
	$slots = array();
	$slotContents = array();
	$modules = getRunningModules();
	
	$whereSlotName = '';
	if ($specificSlotName && !$specificInstanceId) {
		$whereSlotName = "
			  AND slot_name = '". sqlEscape($specificSlotName). "'";
	}
	
	//Look for every plugin instance on the current page, prioritising item level
	//over Layout level, and Layout level over Template Family level.
	$sql = "
		SELECT
			pi.slot_name,
			pi.module_id,
			pi.instance_id,
			vcpi.id AS vcpi_id,
			tsl.slot_name IS NOT NULL as `exists`,
			pi.level
		FROM (
			SELECT slot_name, module_id, instance_id, id, 'template' AS type, 2 AS level
			FROM ". DB_NAME_PREFIX. "plugin_layout_link
			WHERE family_name = '". sqlEscape($templateFamily). "'
			  AND layout_id = ". (int) $layoutId.
			  $whereSlotName;
	
	if ($cID) {
		$sql .= "
		  UNION
			SELECT slot_name, module_id, instance_id, id, 'item' AS type, 1 AS level
			FROM ". DB_NAME_PREFIX. "plugin_item_link
			WHERE content_id = ". (int) $cID. "
			  AND content_type = '". sqlEscape($cType). "'
			  AND content_version = ". (int) $cVersion.
			  $whereSlotName;
	}
	
	$sql .= "
		) AS pi";
	
	//Don't show missing slots, except for Admins with the correct permissions
	if (!(checkPriv('_PRIV_MANAGE_ITEM_SLOT') || checkPriv('_PRIV_MANAGE_ITEM_SLOT')) && !($specificInstanceId || $specificSlotName)) {
		$sql .= "
		INNER JOIN ". DB_NAME_PREFIX. "template_slot_link AS tsl";
	} else {
		$sql .= "
		LEFT JOIN ". DB_NAME_PREFIX. "template_slot_link AS tsl";
	}
	
	$sql .= "
		   ON tsl.family_name = '". sqlEscape($templateFamily). "'
		  AND tsl.file_base_name = '". sqlEscape($templateFileBaseName). "'
		  AND tsl.slot_name = pi.slot_name";
	
	$sql .= "
		LEFT JOIN ". DB_NAME_PREFIX. "plugin_instances AS vcpi
		   ON vcpi.module_id = pi.module_id
		  AND vcpi.content_id = ". (int) $cID. "
		  AND vcpi.content_type = '". sqlEscape($cType). "'
		  AND vcpi.content_version = ". (int) $cVersion. "
		  AND vcpi.slot_name = pi.slot_name
		WHERE TRUE";
	
	if ($exactMatch && $specificInstanceId) {
		$sql .= "
		  AND IFNULL(vcpi.id, pi.instance_id) = ". (int) $specificInstanceId. "";
	}
	if ($exactMatch && $specificSlotName) {
		$sql .= "
		  AND pi.slot_name = '". sqlEscape($specificSlotName). "'";
	}
	
	$sql .= "
		ORDER BY";
	
	if (!$exactMatch && $specificInstanceId) {
		$sql .= "
			IFNULL(vcpi.id, pi.instance_id) = ". (int) $specificInstanceId. " DESC,";
	}
	if (!$exactMatch && $specificSlotName) {
		$sql .= "
			pi.slot_name = '". sqlEscape($specificSlotName). "' DESC,";
	}
	
	if ($specificInstanceId || $specificSlotName) {
		$sql .= "
			pi.level ASC,
			pi.slot_name
		LIMIT 1";
		
		$checkOpaqueRulesAreValid = false;
	
	} else {
		$sql .= "
			pi.level DESC,
			pi.slot_name";
		
		$checkOpaqueRulesAreValid = true;
	}
	
	
	$result = sqlQuery($sql);
	while($row = sqlFetchAssoc($result)) {
		
		//Don't allow Opaque missing slots to count as missing slots
		if (empty($row['module_id']) && !$row['exists']) {
			continue;
		}
		
		//Check if this is a version-controlled Plugin instance
		$isVersionControlled = false;
		if ($row['module_id'] != 0 && $row['instance_id'] == 0) {
			$isVersionControlled = true;
			
			//Check if an instance has been inserted for this Content Item
			if ($row['vcpi_id']) {
				$row['instance_id'] = $row['vcpi_id'];
			
			} elseif ($runPlugins) {
				//If not, insert one
				$row['instance_id'] =
					getVersionControlledPluginInstanceId($cID, $cType, $cVersion, $row['slot_name'], $row['module_id']);
			}
		}
		
		//The "Opaque" option is a special case; let it through without an "is running" check
		if ($row['module_id'] == 0) {
			//The "Opaque" option is used to hide plugins on the layout layer on specific pages.
			//It's not valid if it's not actually covering anything up!
			if ($checkOpaqueRulesAreValid && empty($slotContents[$row['slot_name']])) {
				continue;
			}
			
			$slotContents[$row['slot_name']] = array('instance_id' => 0, 'module_id' => 0);
			$slotContents[$row['slot_name']]['error'] = adminPhrase('[Plugin hidden on this content item]');
			$slotContents[$row['slot_name']]['level'] = $row['level'];
			$slots[$row['slot_name']] = true;
		
		//Otherwise, if the instance is running, allow it to be added to the page
		} elseif (!empty($modules[$row['module_id']])) {
			$slotContents[$row['slot_name']] = $modules[$row['module_id']];
			$slotContents[$row['slot_name']]['level'] = $row['level'];
			$slotContents[$row['slot_name']]['module_id'] = $row['module_id'];
			$slotContents[$row['slot_name']]['instance_id'] = $row['instance_id'];
			$slotContents[$row['slot_name']]['css_class'] = $modules[$row['module_id']]['css_class_name'];
			
			if ($isVersionControlled) {
				$slotContents[$row['slot_name']]['content_id'] = $cID;
				$slotContents[$row['slot_name']]['content_type'] = $cType;
				$slotContents[$row['slot_name']]['content_version'] = $cVersion;
				$slotContents[$row['slot_name']]['slot_name'] = $row['slot_name'];
			}
			
			$slotContents[$row['slot_name']]['cache_if'] = array();
			$slotContents[$row['slot_name']]['clear_cache_by'] = array();
			
			$slots[$row['slot_name']] = true;
		}
	}
	
	
	//Attempt to initialise each plugin on the page
	foreach ($slots as $slotName => $dummy) {
		//Attempt to check to see if it is in the filesystem, then include it in the list of running instances
		if (!empty($slotContents[$slotName]['class_name']) && !empty($slotContents[$slotName]['instance_id'])) {
			if ($runPlugins) {
				cms_core::initInstance(
					$slotContents, $slotName,
					$cID, $cType, $cVersion,
					$layoutId, $templateFamily, $templateFileBaseName,
					$specificInstanceId, $specificSlotName, $ajaxReload,
					$runPlugins);
			}
		
		} elseif (!empty($slotContents[$slotName]['level'])) {
			if ($runPlugins) {
				setupNewBaseClassPlugin($slotName);
				
				//Treat the case of hidden (item layer) and empty (layout layer) as just empty,
				//but if there is something hidden at the item layer and there is a plugin
				//at the layout layer, show a special message
				if (!$checkOpaqueRulesAreValid
				 && $slotContents[$slotName]['level'] == 1
				 && $layoutId
				 && checkRowExists('plugin_layout_link', array('slot_name' => $slotName, 'layout_id' => $layoutId))) {
					$slotContents[$slotName]['error'] = adminPhrase('[Plugin hidden on this content item]');
				}
			}
		}
	}
}

function getVersionControlledPluginInstanceId($cID, $cType, $cVersion, $slotName, $moduleId) {
	
	
	if ($cID == 0 || $cID == -1) {
		return $cID;
	}
	
	$ids = array('module_id' => $moduleId, 'content_id' => $cID, 'content_type' => $cType, 'content_version' => $cVersion, 'slot_name' => $slotName);

	if (!$instanceId = getRow('plugin_instances', 'id', $ids)) {
		$instanceId = insertRow('plugin_instances', $ids);
	}
	
	return $instanceId;
}



//Activate and setup a plugin
//Note that the function canActivateModule() or equivalent should be called on the plugin's name before calling setInstance() or initInstance()
function setInstance(&$instance, $cID, $cType, $cVersion, $slotName, $checkForErrorPages = false, $nest = 0, $tab = 0) {
	
	$missingPlugin = false;
	if (!includeModuleAndDependencies($instance['class_name'], $missingPlugin)) {
		$instance['class'] = false;
		return false;
	}
	
	$instance['class'] = new $instance['class_name'];
	
	$instance['class']->setInstance(array($cID, $cType, $cVersion, $slotName,
										  arrayKey($instance, 'instance_name'), $instance['instance_id'],
										  $instance['class_name'], $instance['vlp_class'],
										  $instance['module_id'],
										  $instance['default_framework'], $instance['framework'],
										  $instance['css_class'],
										  arrayKey($instance, 'level'), !empty($instance['content_id'])), $nest, $tab);
}

//Work out whether we are displaying this Plugin.
//Run the plugin's own initalisation routine. If it returns true, then display the plugin.
//(But note that modules are always displayed in admin mode.)
function initInstance(&$instance) {
	if (!($instance['init'] = (bool) $instance['class']->init()) && !(checkPriv())) {
		$instance['class'] = false;
		return false;
	} else {
		return true;
	}
}

function getPluginInstanceDetails($instanceIdOrName, $fetchBy = 'id') {
	
	if (!$instanceIdOrName) {
		return false;
	}
	
	$sql = "
		SELECT
			i.id AS instance_id,
			i.name AS instance_name,
			i.content_id,
			i.content_type,
			i.content_version,
			i.slot_name,
			IF(i.framework = '', m.default_framework, i.framework) AS framework,
			m.default_framework,
			m.css_class_name,
			i.css_class,
			i.module_id,
			m.class_name,
			m.display_name,
			m.vlp_class,
			m.status
		FROM ". DB_NAME_PREFIX. "plugin_instances AS i
		INNER JOIN ". DB_NAME_PREFIX. "modules AS m
		   ON m.id = i.module_id";
	
	if ($fetchBy == 'id') {
		$sql .= "
		WHERE i.id = ". (int) $instanceIdOrName;
	
	} elseif ($fetchBy == 'name') {
		$sql .= "
		WHERE i.name = '". sqlEscape($instanceIdOrName). "'";
	
	} else {
		return false;
	}
	
	$result = sqlQuery($sql);
	$instance = sqlFetchAssoc($result);
	
	if ($instance['content_id'] && checkPriv()) {
		$instance['instance_name'] = $instance['display_name'];
	}
	
	unset($instance['display_name']);
	return $instance;
}

function getPluginInstanceName($instanceId) {
	$instanceDetails = getPluginInstanceDetails($instanceId);
	return $instanceDetails['instance_name'];
}

function getPluginInItemSlot($slotName, $cID, $cType = 'html', $cVersion = false) {
	return getPluginInstanceInItemSlot($slotName, $cID, $cType, $cVersion, true);
}

function getPluginInstanceInItemSlot($slotName, $cID, $cType = 'html', $cVersion = false, $getModuleId = false) {
	
	if (!$cVersion) {
		$cVersion = getLatestVersion($cID, $cType);
	}
	
	$sql = "
		SELECT ". ($getModuleId? 'module_id' : 'instance_id'). "
		FROM ". DB_NAME_PREFIX. "plugin_item_link
		WHERE slot_name = '". sqlEscape($slotName). "'
		  AND content_id = ". (int) $cID. "
		  AND content_type = '". sqlEscape($cType). "'
		  AND content_version = ". (int) $cVersion;
	
	$result = sqlQuery($sql);
	if ($row = sqlFetchRow($result)) {
		return $row[0];
	} else {
		return false;
	}
}

function checkInstanceIsWireframeOnItemLayer($instanceId) {
	return
		($plugin = getRow('plugin_instances', array('content_id', 'content_type', 'content_version', 'slot_name', 'module_id'), $instanceId))
	 && (!($plugin['instance_id'] = 0))
	 && (checkRowExists('plugin_item_link', $plugin));
}

function getPluginInTemplateSlot($slotName, $templateFamily, $layoutId) {
	return getPluginInstanceInTemplateSlot($slotName, $templateFamily, $layoutId, true);
}

function getPluginInstanceInTemplateSlot($slotName, $templateFamily, $layoutId, $getModuleId = false) {
	
	$sql = "
		SELECT ". ($getModuleId? 'module_id' : 'instance_id'). "
		FROM ". DB_NAME_PREFIX. "plugin_layout_link
		WHERE slot_name = '". sqlEscape($slotName). "'
		  AND family_name = '". sqlEscape($templateFamily). "'
		  AND layout_id = ". (int) $layoutId;
	
	$result = sqlQuery($sql);
	if ($row = sqlFetchRow($result)) {
		return $row[0];
	} else {
		return false;
	}
}

function zenarioInitialiseTwig() {
	require CMS_ROOT. 'zenario/libraries/bsd/twig/lib/Twig/Autoloader.php';
	Twig_Autoloader::register();
	
	require CMS_ROOT. 'zenario/libraries/mit/twig-extensions/lib/Twig/Extensions/Autoloader.php';
	Twig_Extensions_Autoloader::register();
	
	cleanDownloads();
	//$loader = new Twig_Loader_Filesystem(/*'/path/to/templates'*/);
	//cms_core::$twig = new Twig_Environment($loader, array(
	//	'cache' => CMS_ROOT. 'cache/frameworks/',
	//));
}

//Attempt to find the path to a Framework
function frameworkPath($framework, $className, $includeFilename = false, $limit = 10) {
	if (!--$limit) {
		return false;
	}
	
	if ($path = moduleDir($className, 'frameworks/'. $framework. '/framework.twig.html', true, true)) {
		if ($includeFilename) {
			return $path;
		} else {
			return substr($path, 0, -19);
		}
	} elseif ($path = moduleDir($className, 'frameworks/'. $framework. '/framework.html', true, true)) {
		if ($includeFilename) {
			return $path;
		} else {
			return substr($path, 0, -14);
		}
	}
	
	$sql = "
		SELECT dependency_class_name
		FROM ". DB_NAME_PREFIX. "module_dependencies
		WHERE type = 'inherit_frameworks'
		  AND module_class_name = '". sqlEscape($className). "'
		LIMIT 1";
	
	if (($result = sqlQuery($sql))
	 && ($row = sqlFetchRow($result))) {
		return frameworkPath($framework, $row[0], $includeFilename, $limit);
	} else {
		return false;
	}
}

function frameworkCheckArrayOfArrays(&$mergeFields) {
	//Check if we have an array or an array of arrays
	$arrayOfArrays = false;
	if (!is_array($mergeFields)) {
		$mergeFields = array();
	
	} else {
		foreach($mergeFields as &$mergeFieldsRow) {
			$arrayOfArrays = is_array($mergeFieldsRow);
			break;
		}
	}
	
	//Allow an array of arrays to be passed as mergeFields. If this happens, then we'll
	//display the template once for each mergeField.
	if (!$arrayOfArrays) {
		//Otherwise just display the template once by turning the array into an
		//array of a single array
		$mergeFields = array($mergeFields);
	}
}





function sendSignal($signalName, $signalParams) {
	//Don't try to send a signal if we are in the Admin Login Screen applying Database Updates
	if (!class_exists('module_base_class')) {
		return false;
	}
	
	if (!empty(cms_core::$signalsCurrentlyTriggered[$signalName])) {
		return false;
	}
	
	cms_core::$signalsCurrentlyTriggered[$signalName] = true;
	
		$sql = "
			SELECT module_id, module_class_name, module_class_name AS class_name, static_method
			FROM ". DB_NAME_PREFIX. "signals
			WHERE signal_name = '". sqlEscape($signalName). "'
			  AND module_class_name NOT IN (
				SELECT suppresses_module_class_name
				FROM ". DB_NAME_PREFIX. "signals AS e
				INNER JOIN ". DB_NAME_PREFIX. "modules AS m
				   ON e.module_id = m.id
				WHERE e.signal_name = '". sqlEscape($signalName). "'
				  AND e.suppresses_module_class_name != ''
				  AND m.status = 'module_running'
			  )
			ORDER BY signal_name, module_class_name";
	
		$returns = array();
		$result = sqlQuery($sql);
		while($row = sqlFetchAssoc($result)) {
			if (inc($row['class_name'])) {
				if ($row['static_method']) {
					$returns[$row['class_name']] = call_user_func_array(array($row['class_name'], $signalName), $signalParams);
				} else {
					$module = new $row['class_name'];
					$returns[$row['class_name']] = call_user_func_array(array($module, $signalName), $signalParams);
				}
			}
		}
	
	unset(cms_core::$signalsCurrentlyTriggered[$signalName]);
	return $returns;
}



/* Skins */

function getSkinFromId($skinId) {
	return getRow('skins', array('id', 'family_name', 'name', 'display_name', 'type', 'extension_of_skin', 'css_class', 'missing'), array('id' => $skinId));
}

function getSkinFromName($familyName, $skinName) {
	return getRow('skins', array('id', 'family_name', 'name', 'display_name', 'type', 'extension_of_skin', 'css_class', 'missing'), array('family_name' => $familyName, 'name' => $skinName));
}

function getSkinPath($templateFamily = false, $skinName = false) {
	return zenarioTemplatePath(ifNull($templateFamily, cms_core::$templateFamily)). 'skins/'. ifNull($skinName, cms_core::$skinName). '/';
}

function getSkinPathURL($templateFamily = false, $skinName = false) {
	return zenarioTemplatePath(ifNull($templateFamily, cms_core::$templateFamily)). 'skins/'. rawurlencode(ifNull($skinName, cms_core::$skinName)). '/';
}

function zenarioTemplatePath($templateFamily = false, $fileBaseName = false, $css = false) {
	return 'zenario_custom/templates/'. ($templateFamily? $templateFamily. '/'. ($fileBaseName? $fileBaseName. ($css? '.css' : '.tpl.php') : '') : '');
}



function stripBadCharsForXMLParser ($input) {
	return str_replace(chr(12),"",$input);
}




//Open a file and attempt to turn it into a SimpleXMLElement
function SimpleXML($file) {
	
	if (!(is_file($file)) || (!$string = file_get_contents($file))) {
		return false;
	}
	
	$xml = new SimpleXMLElement($string);
	
	return $xml;
}
	


function assocArrayToXML ($data) {
	$xml = new XmlWriter();
	$xml->openMemory();
	$xml->startDocument('1.0', 'UTF-8');
	$xml->startElement('root');
	
	function write(XMLWriter $xml, $data, $recurseCounter){
		$recurseCounter++;
		
		foreach($data as $key => $value){
			if ($recurseCounter==1) {
				$key = "row";
			}
		
			if (!is_numeric($key)) {
				if(is_array($value)){
					$xml->startElement($key);
					write($xml, $value, $recurseCounter);
					$xml->endElement();
					continue;
				}
				
				$xml->writeElement($key, $value);
			}
		}
	}
	
	write($xml, $data,$recurseCounter);
	$xml->endElement();
	return $xml->outputMemory(true);	
}





//
// Translation functionality
//


function equivId($cID, $cType) {
	return getRow('content', 'equiv_id', array('id' => $cID, 'type' => $cType));
}

function langEquivalentItem(&$cID, &$cType, $langId = false) {
	
	if (!$cID) {
		return false;
	
	} elseif (!$cType) {
		if (!getCIDAndCTypeFromTagId($cID, $cType, $cID)) {
			return false;
		}
	}
	
	if ($langId === false) {
		if (empty($_SESSION['user_lang'])) {
			$langId = setting('default_language');
		} else {
			$langId = $_SESSION['user_lang'];
		}
	
	} elseif ($langId === true) {
		$langId = setting('default_language');
	}
	
	$sql = "
		SELECT id, equiv_id, language_id
		FROM ". DB_NAME_PREFIX. "content
		WHERE id = ". (int) $cID. "
		  AND type = '". sqlEscape($cType). "'";
	$result = sqlQuery($sql);
	
	if ($row = sqlFetchAssoc($result)) {
		if ($langId != $row['language_id']) {
			$sql = "
				SELECT id
				FROM ". DB_NAME_PREFIX. "content
				WHERE equiv_id = ". (int) $row['equiv_id']. "
				  AND type = '". sqlEscape($cType). "'
				  AND language_id = '". sqlEscape($langId). "'";
			$result = sqlQuery($sql);
			
			if ($row = sqlFetchAssoc($result)) {
				$cID = $row['id'];
			}
		}
		
		return true;
	} else {
		return false;
	}
}

function equivalences($cID, $cType, $includeCurrent = true, $equivId = false) {
	if ($equivId === false) {
		$equivId = equivId($cID, $cType);
	}
	
	$result = getRows(
		'content',
		array('id', 'type', 'language_id', 'equiv_id', 'status'),
		array('equiv_id' => $equivId, 'type' => $cType),
		'language_id');
	
	$equivs = array();
	while($equiv = sqlFetchAssoc($result)) {
		if ($includeCurrent || $equiv['id'] != $cID) {
			$equivs[$equiv['language_id']] = $equiv;
		}
	}
	
	return $equivs;
}

