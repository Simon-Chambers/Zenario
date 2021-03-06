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
	This file contains JavaScript source code.
	The code here is not the code you see in your browser. Before this file is downloaded:
	
		1. Compilation macros are applied (e.g. "foreach" is a macro for "for .. in ... hasOwnProperty").
		2. It is minified (e.g. using Google Closure Compiler).
		3. It may be wrapped togther with other files (this is to reduce the number of http requests on a page).
	
	For more information, see js_minify.shell.php for steps (1) and (2), and "inc.js.php" files for step (3).
*/


(function(
	URLBasePath,
	window, document,
	zenario, zenarioA, zenarioTab, zenarioAT,
	get, engToBoolean, htmlspecialchars, ifNull, jsEscape, phrase,
	undefined) {
		"use strict";


zenarioA.init = true;
zenarioA.menuWandOn = true;
zenarioA.slotWandOn = false;
zenarioA.showGridOn = false;
zenarioA.storekeeperInitTime = 5000;

zenarioA.tooltipLengthThresholds = {
	adminBoxTitle: 120,
	adminToolbarTitle: 60,
	organizerBackButton: 70,
	organizerPanelTitle: 100
};





zenarioA.generateAutoPasswords = function() {
var password_characters='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
var password_length = '8';
var password='';
var len=0;
for(var i=0;i<password_length;i++){ 
password+=password_characters.charAt(Math.floor(Math.random()*password_characters.length))
}
	return password;
};




zenarioA.microTemplates = {};




//Code to add the "zenario_slotParent" class to the elements just above slots
	//When you hover over a slot, the control box for that slot, or the zenario_slotControlsWrap for that slot,
	//the CSS class zenario_slotParent should be added just above that slot.
	//When you move your mouse to another slot, the CSS class should be immediately removed from that slot, and its Plugin Options dropdown should be immediately closed.
	//When you move your mouse away from the slot, but not over another slot, it should be removed after a short delay.
zenarioA.slotParentMouseOverLastId = false;
zenarioA.slotParentMouseOver = function(event) {
	if (zenarioA.slotControlHide) {
		clearTimeout(zenarioA.slotControlHide);
	}
	
	$('.zenario_slotParent').removeClass('zenario_slotParent');
	
	var id;
	if (event) {
		if (this.className != 'zenario_slotControlsWrap') {
			id = $(this).attr('id').replace('-control_box', '');
			$(this).parent().addClass('zenario_slotParent');
		} else {
			id = 'plgslt_' + (this.id + '').replace('zenario_fbAdminPluginOptionsWrap-', '');
			$('#' + id).parent().addClass('zenario_slotParent');
		}
		
		if (zenarioA.slotParentMouseOverLastId && zenarioA.slotParentMouseOverLastId != id) {
			zenarioA.closeSlotControls();
		}
		zenarioA.slotParentMouseOverLastId = id;
	} else {
		zenarioA.closeSlotControls();
		zenarioA.slotParentMouseOverLastId = false;
	}
};

zenarioA.slotParentMouseOut = function(a) {
	if (zenarioA.slotControlHide) {
		clearTimeout(zenarioA.slotControlHide);
	}
	
	zenarioA.slotControlHide = setTimeout(zenarioA.slotParentMouseOver, zenarioA.slotControlHoverInterval);
};

zenarioA.setSlotParents = function() {
	$('.zenario_slotAdminControlBox').parent().children().mouseenter(zenarioA.slotParentMouseOver);
	$('.zenario_slotAdminControlBox').parent().children().mouseleave(zenarioA.slotParentMouseOut);
	$('#zenario_afb_container .zenario_slotControlsWrap').mouseenter(zenarioA.slotParentMouseOver);
	$('#zenario_afb_container .zenario_slotControlsWrap').mouseleave(zenarioA.slotParentMouseOut);
};

zenarioA.slotControlHide = false;
zenarioA.slotControlHoverInterval = 1500;
zenarioA.slotControlCloseInterval = 100;
$(document).ready(zenarioA.setSlotParents);



/*  Functions for managing plugin slots  */

zenarioA.openSlotControlsBox = false;
zenarioA.openSlotControls = function(el, e, slotName) {
	
	var closeAsIsAlreadyOpen = $('#zenario_fbAdminSlotControls-' + slotName).is(':visible');
	
	el.blur();
	zenarioA.closeSlotControls();
	
	if (!closeAsIsAlreadyOpen && zenarioA.checkForEdits()) {
		var width,
			section,
			sections = {info: false, notes: false, actions: false, overridden_info: false, overridden_actions: false},
			instanceId = zenario.slots[slotName].instanceId,
			
			//Get the grid span from the slot name
			$gridspan = $('.' + slotName + '.span.slot'),
			gridCSSClass = false,
			gridWidth = false;
		
		if ($gridspan.length) {
			//Attempt to get the CSS class names of the wrapper of the slot
			//(it's easier to look this up using JavaScript than it is to work it out in fillAllAdminSlotControls() in php).
			gridCSSClass = $gridspan.attr('class'),
			gridWidth = zenarioA.getSlotWidth($gridspan);
		}
		
		if (get('zenario_fbAdminSlotControls-' + slotName).innerHTML.indexOf('zenario_long_option') == -1) {
			width = 255;
		} else {
			width = 280;
		}
		
		
		var left = -width + 34;
		var top = 32;
		
		zenarioA.openBox(
			undefined,
			get('plgslt_' + slotName + '-wrap').className + ' zenario_fbAdminSlotControls',
			zenarioA.openSlotControlsBox = 'AdminSlotControls-' + slotName,
			e, width, left, top, false, false, false, false);
		
		//Check that each section has at least one label or button in it. If not, hide that section
		foreach (sections as section) {
			$('#zenario_fbAdminSlotControls-' + slotName + ' .zenario_slotControlsWrap_' + section).show();
			
			$('#zenario_fbAdminSlotControls-' + slotName + ' .zenario_slotControlsWrap_' + section + ' .zenario_sc:visible').each(function(i, el) {
				sections[section] = true;
			});
		
			if (!sections[section]) {
				$('#zenario_fbAdminSlotControls-' + slotName + ' .zenario_slotControlsWrap_' + section).hide();
			}
		}
		
		//Set the CSS class that the grid is using
		if (gridCSSClass) {
			//Strip out some technical class-names that make the grid work but designers don't need to see
			gridCSSClass = gridCSSClass.replace(' alpha ', ' ').replace(' omega ', ' ').replace(/\bspan\d*_?\d*\s/g, '');
			
			$('#zenario_slot_control__' + slotName + '__info__' + 'grid_css_class').show();
			$('#zenario_slot_control__' + slotName + '__info__' + 'grid_css_class > span').text(gridCSSClass);
		} else {
			$('#zenario_slot_control__' + slotName + '__info__' + 'grid_css_class').hide();
		}
		
		//Set the width of this slot
		if (gridWidth) {
			$('#zenario_slot_control__' + slotName + '__info__' + 'grid_width').show();
			$('#zenario_slot_control__' + slotName + '__info__' + 'grid_width > span').text(gridWidth);
		} else {
			$('#zenario_slot_control__' + slotName + '__info__' + 'grid_width').hide();
		}
		
		$('#plgslt_' + slotName + '-control_box').addClass('zenario_adminSlotControlsOpen');
	}
	
	return false;
};

zenarioA.dontCloseSlotControls = function() {
	if (zenarioA.slotControlClose) {
		clearTimeout(zenarioA.slotControlClose);
	}
};

zenarioA.closeSlotControlsAfterDelay = function() {
	zenarioA.dontCloseSlotControls();
	zenarioA.slotControlClose = setTimeout(zenarioA.closeSlotControls, zenarioA.slotControlCloseInterval);
};

zenarioA.closeSlotControls = function() {
	zenarioA.dontCloseSlotControls();
	if (zenarioA.openSlotControlsBox) {
		zenarioA.closeBox(zenarioA.openSlotControlsBox, true, {effect: 'fade', duration: 200});
		$('.zenario_slotAdminControlBox').removeClass('zenario_adminSlotControlsOpen');
	}
};

//Gets display information on the width of a slot.
zenarioA.getSlotWidth = function($gridspan) {
		
	//Get the actual width of the slot
	var fluidWidth = false,
		cssWidth = '',
		pxWidth = $gridspan.width(),
		
		//Try and read the number of columns from the css class names, e.g. "span3"
		css = $gridspan.attr('class') || '',
		columns = css.match(/\bspan\d+\b/);
	
	if (columns) {
		columns = 1 * columns[0].match(/\d+/);
	}
	
	try {
		//Loop through each stylesheet/rule, checking to see if there is a grid and a "span" rule that matches this span
		//Adapted from http://stackoverflow.com/questions/324486/how-do-you-read-css-rule-values-with-javascript
		outerLoop:
		foreach (document.styleSheets as var i => var s) {
			var rules = s.rules || s.cssRules;
		
			innerLoop:
			foreach (rules as var j => var rule) {
				if (rule.selectorText
				 && rule.style.width
				 && ('' + rule.selectorText).match(/\.span/)
				 && $gridspan.is(rule.selectorText)) {
					cssWidth = rule.style.width;
					break outerLoop;
				}
			}
		}
	} catch (e) {
		cssWidth = '';
	}
	
	if (!cssWidth) {
		cssWidth = pxWidth + 'px';
	} else if (!cssWidth.match(/\d+px/)) {
		fluidWidth = true;
	}
	
	if (fluidWidth) {
		cssWidth += ' (' + pxWidth + ' px ' + phrase.atCurrentSize + ')';
	}
	
	if (columns) {
		if (columns == 1) {
			cssWidth = '1 column, ' + cssWidth;
		} else {
			cssWidth = columns + ' columns, ' + cssWidth;
		}
	}
	
	return cssWidth;
};




zenarioA.pickNewPluginSlotName = false;
zenarioA.pickNewPlugin = function(el, slotName, level) {
	el.blur();
	
	zenarioA.pickNewPluginSlotName = slotName;
	zenarioA.pickNewPluginLevel = level;
	
	zenarioA.SK('zenarioA', 'addNewReusablePlugin', false, 'zenario__modules/nav/modules/panel/refiners/slotable_only////', 'zenario__modules/nav/instances/panel', 'zenario__modules/nav/modules/panel', 'zenario__modules/nav/instances/panel', true, true, phrase.insertReusablePlugin);
	
	return false;
};

zenarioA.addNewReusablePlugin = function(path, key, row) {
	var instanceId = key.id, slotName = zenarioA.pickNewPluginSlotName, level = zenarioA.pickNewPluginLevel;
	
	var error = 
		zenario.pluginClassAJAX('zenario_common_features', {addPluginInstance: instanceId, slotName: slotName, level: level, cID: zenario.cID, cType: zenario.cType, cVersion: zenario.cVersion}, true);
	
	if (error) {
		zenarioA.showMessage(error);
	} else {
		zenario.refreshPluginSlot(slotName, '');
	}
};


zenarioA.addNewWireframePlugin = function(el, slotName, moduleId) {
	el.blur();
	
	var req = {
			addPlugin: moduleId,
			slotName: slotName,
			level: 2,
			cID: zenario.cID,
			cType: zenario.cType,
			cVersion: zenario.cVersion
		},
		html = zenario.pluginClassAJAX('zenario_common_features', req, false);
	
	if (zenarioA.loggedOut(html)) {
		return;
	}
	
	zenarioA.floatingBox(html, $(el).text(), 'warning', false, false, function() {
	
		var error = zenario.pluginClassAJAX('zenario_common_features', req, true);
	
		if (error) {
			zenarioA.showMessage(error);
		} else {
			zenario.refreshPluginSlot(slotName, '');
		}
	});
	
	return false;
};


//Show the thickbox for editing the instance in the slot, if there is one
zenarioA.pluginSlotEditSettings = function(el, slotName, fabPath) {
	el.blur();
	var instanceId = zenario.slots[slotName].instanceId;
	
	if (!get('zenario_theme_name_' + slotName + '__0') && instanceId) {
		zenarioAB.open(fabPath || 'plugin_settings', {cID: zenario.cID, cType: zenario.cType, cVersion: zenario.cVersion, slotName: slotName, instanceId: instanceId, frontEnd: 1});
	}
	
	return false;
};

//Moving modules
zenarioA.movePlugin = function(el, slotName) {
	el.blur();
	
	zenarioA.floatingBox(phrase.movePluginDesc, true, 'question', true, true, function() {
		zenarioA.moveSource = slotName;
		$('.zenario_slotAdminControlBox').addClass('zenario_moveDestination');
		$('#plgslt_' + slotName + '-control_box').removeClass('zenario_moveDestination').addClass('zenario_moveSource');
	});
	
	return false;
};

zenarioA.doMovePlugin = function(el, moveDestination) {
	el.blur();
	
	var moveSource = zenarioA.moveSource;
	zenarioA.cancelMovePlugin(el);
	
	if (moveSource && moveDestination) {
		if (zenarioA.toolbar == 'edit') {
			zenarioA.doMovePlugin2(moveSource, moveDestination, 1);
		} else if (zenarioA.toolbar == 'template') {
			var html = zenario.pluginClassAJAX('zenario_common_features', {movePlugin: 1, level: 2, cID: zenario.cID, cType: zenario.cType, cVersion: zenario.cVersion}, false);
			
			if (zenarioA.loggedOut(html)) {
				return;
			}
			
			zenarioA.floatingBox(html, phrase.movePlugin, 'warning', false, false, function() {
				zenarioA.doMovePlugin2(moveSource, moveDestination, 2);
			});
		}
	}
	
	return false;
};

zenarioA.doMovePlugin2 = function(moveSource, moveDestination, level) {
	var error = 
		zenario.pluginClassAJAX('zenario_common_features', {movePlugin: 1, level: level, slotNameSource: moveSource, slotNameDestination: moveDestination, cID: zenario.cID, cType: zenario.cType, cVersion: zenario.cVersion}, true);
	
	if (error) {
		zenarioA.showMessage(error);
	} else {
		zenario.refreshPluginSlot(moveSource, '');
		zenario.refreshPluginSlot(moveDestination, '');
	}
};

zenarioA.cancelMovePlugin = function(el) {
	if (el) el.blur();
	
	delete zenarioA.moveSource;
	$('.zenario_slotAdminControlBox').removeClass('zenario_moveDestination').removeClass('zenario_moveSource');
	
	return false;
};




zenarioA.refreshAllSlotsWithCutCopyPaste = function(allowedModules) {
	
	//Try to get a list of every type of plugin affected
	var slotName, m, module, modules = {};
	
	allowedModules = allowedModules.split(',');
	
	foreach (allowedModules as m => module) {
		if (module) {
			modules[module] = true;
		}
	}
	
	modules.zenario_banner = true;
	modules.zenario_html_snippet = true;
	modules.zenario_wysiwyg_editor = true;
	
	//Reload the contents of every slot these plugins are in (version controlled plugins only)
	foreach (modules as module) {
		if (window[module]
		 && window[module].slots) {
			foreach (window[module].slots as slotName) {
				if (zenario.slots[slotName]
				 && zenario.slots[slotName].isVersionControlled) {
					window[module].refreshPluginSlot(slotName, false, false, false);
				}
			}	
		}
	};
};
	
zenarioA.copyContents = function(el, slotName, allowedModules) {
	el.blur();
	
	var error = 
		zenario.pluginClassAJAX('zenario_common_features', {copyContents: 1, allowedModules: allowedModules, slotName: slotName, cID: zenario.cID, cType: zenario.cType, cVersion: zenario.cVersion}, true);
	
	if (error) {
		zenarioA.showMessage(error);
	} else {
		zenarioA.refreshAllSlotsWithCutCopyPaste(allowedModules);
	}
};

zenarioA.cutContents = function(el, slotName, allowedModules) {
	el.blur();
	
	var error = 
		zenario.pluginClassAJAX('zenario_common_features', {cutContents: 1, allowedModules: allowedModules, slotName: slotName, cID: zenario.cID, cType: zenario.cType, cVersion: zenario.cVersion}, true);
	
	if (error) {
		zenarioA.showMessage(error);
	} else {
		zenarioA.refreshAllSlotsWithCutCopyPaste(allowedModules);
	}
};

zenarioA.pasteContents = function(el, slotName) {
	el.blur();
	
	var error = 
		zenario.pluginClassAJAX('zenario_common_features', {pasteContents: 1, slotName: slotName, cID: zenario.cID, cType: zenario.cType, cVersion: zenario.cVersion}, true);
	
	if (error) {
		zenarioA.showMessage(error);
	} else {
		zenario.refreshPluginSlot(slotName, '', undefined, false, false, false, false);
	}
};

zenarioA.overwriteContents = function(el, slotName) {
	el.blur();
	
	zenarioA.floatingBox(phrase.overwriteContentsConfirm, $(el).text(), 'warning', false, false, function() {
		var error = 
			zenario.pluginClassAJAX('zenario_common_features', {overwriteContents: 1, slotName: slotName, cID: zenario.cID, cType: zenario.cType, cVersion: zenario.cVersion}, true);
	
		if (error) {
			zenarioA.showMessage(error);
		} else {
			zenario.refreshPluginSlot(slotName, '', undefined, false, false, false, false);
		}
	});
	
	return false;
};

zenarioA.swapContents = function(el, slotName) {
	el.blur();
	
	zenarioA.floatingBox(phrase.swapContentsConfirm, $(el).text(), 'warning', false, false, function() {
		var error = 
			zenario.pluginClassAJAX('zenario_common_features', {swapContents: 1, slotName: slotName, cID: zenario.cID, cType: zenario.cType, cVersion: zenario.cVersion}, true);
	
		if (error) {
			zenarioA.showMessage(error);
		} else {
			zenario.refreshPluginSlot(slotName, '', undefined, false, false, false, false);
		}
	});
	
	return false;
};




zenarioA.removePlugin = function(el, slotName, level) {
	el.blur();
	
	var req = {
			removePlugin: 1,
			level: level,
			slotName: slotName,
			cID: zenario.cID,
			cType: zenario.cType,
			cVersion: zenario.cVersion
		},
		html = zenario.pluginClassAJAX('zenario_common_features', req, false);
	
	if (zenarioA.loggedOut(html)) {
		return;
	}
	
	zenarioA.floatingBox(html, phrase.remove, true, false, false, function() {
		var error = 
			zenario.pluginClassAJAX('zenario_common_features', req, true);
	
		if (error) {
			zenarioA.showMessage(error);
		} else {
			zenario.refreshPluginSlot(slotName, '', undefined, false, false, false, false);
		}
	});
	
	return false;
};

	
zenarioA.hidePlugin = function(el, slotName) {
	el.blur();
	
	var error = 
		zenario.pluginClassAJAX('zenario_common_features', {hidePlugin: 1, slotName: slotName, cID: zenario.cID, cType: zenario.cType, cVersion: zenario.cVersion}, true);
	
	if (error) {
		zenarioA.showMessage(error);
	} else {
		zenario.refreshPluginSlot(slotName, '', undefined, false, false, false, false);
	}
};

	
zenarioA.showPlugin = function(el, slotName) {
	el.blur();
	
	var error = 
		zenario.pluginClassAJAX('zenario_common_features', {showPlugin: 1, slotName: slotName, cID: zenario.cID, cType: zenario.cType, cVersion: zenario.cVersion}, true);
	
	if (error) {
		zenarioA.showMessage(error);
	} else {
		zenario.refreshPluginSlot(slotName, '', undefined, false, false, false, false);
	}
};




/*  Reloading Slots  */

//Callback function for refreshPluginSlot()
zenarioA.replacePluginSlot = function(slotName, instanceId, level, tabId, contents, info, scriptsToRunBefore) {
	
	var moduleId = false,
		isVersionControlled = false,
		beingEdited = false,
		slotControls = '',
		slotControlsCSSClass = '';
	
	//Look through the info at the top of the AJAX return
	if (info) {
		foreach (info as var i) {
			var details = info[i].split('--');
			
			//Watch out for the "In Edit Mode" tag from modules in their edit modes
			if (details[0] == 'IN_EDIT_MODE') {
				beingEdited = true;
			
			//Watch out for the Plugin id
			} else if (details[0] == 'MODULE_ID') {
				moduleId = 1*details[1];
			
			} else if (details[0] == 'WIREFRAME') {
				isVersionControlled = true;
			
			//Change the Class name of the slot
			} else if (details[0] == 'CSS_CLASS') {
				get('plgslt_' + slotName).className = 'zenario_slot ' + zenario.uneschyp(details[1])
			
			//Add a JavaScript namespace for a Plugin if one is not already present
			//Also watch out for modules requesting JavaScript files to be added
			} else if (details[0] == 'NAMESPACE') {
				if (moduleId && !window[zenario.uneschyp(details[1])]) {
					zenario.addPluginJavaScript(moduleId);
				}
			
			} else if (details[0] == 'SLOT_CONTROLS') {
				slotControls = zenario.uneschyp(details[1]);
			
			} else if (details[0] == 'SLOT_CONTROLS_CSS_CLASS') {
				slotControlsCSSClass = zenario.uneschyp(details[1]);
			}
		}
	}
	
	if (!moduleId) {
		instanceId = 0;
	}
	
	//Set the current instance id
	zenario.slot([[slotName, instanceId, moduleId, level, tabId, undefined, beingEdited, isVersionControlled]]);
	
	//Add a css class around slots that are being edited using the WYSIWYG Editor
	if (beingEdited) {
		slotControlsCSSClass += ' zenario_slot_being_edited';
	}
	
	//Set the CSS class for the slot's admin wrapper/slot controls
	get('plgslt_' + slotName + '-wrap').className = slotControlsCSSClass;
	
	//If any slots are being edited, set a warning message for if an admin tries to leave the page 
	window.onbeforeunload = zenarioA.onbeforeunload;
	
	//Remember that this slot is being edited
	zenario.slots[slotName].beingEdited = beingEdited;
	
	foreach (scriptsToRunBefore as var script) {
		if (zenario.slots[slotName]) {
			zenario.callScript(scriptsToRunBefore[script], zenario.slots[slotName].moduleClassName);
		}
	}
	
	//Refresh the slot's innerHTML
	get('plgslt_' + slotName).innerHTML = contents;
	get('zenario_fbAdminSlotControlsContents-' + slotName).innerHTML = slotControls;
	
	
	//Set tooltips for the area, if we are using tooltips
	zenario.tooltips('#plgslt_' + slotName + ' a');
	zenario.tooltips('#plgslt_' + slotName + ' img');
	zenario.tooltips('#plgslt_' + slotName + ' input');
};


zenarioA.checkSlotsBeingEdited = function() {
	if (zenario.slots) {
		foreach (zenario.slots as var slotName => var slot) {
			if (slot.beingEdited) {
				$('body').addClass('zenario_being_edited');
				return true;
			}
		}
	}
	
	$('body').removeClass('zenario_being_edited');
	return false
};

zenarioA.checkForEdits = function() {
	//Don't allow something to be changed whilst in edit mode
	if (zenarioA.toolbar == 'edit' && zenarioA.checkSlotsBeingEdited()) {
		zenarioA.showMessage(phrase.editorOpen);
		return false;
	} else {
		return true;
	}
};

zenarioA.onbeforeunload = function() {
	//If any Admin Boxes are open, and look like they might have been changed, set a warning message for if an admin tries to leave the page 
	if (zenarioAB.isOpen && zenarioAB.editModeOnBox() && (zenarioAB.changes() || zenarioAB.callFunctionOnEditors('isDirty'))) {
		return phrase.leaveAdminBoxWarning;
	
	//Set a warning if any slots are being edited
	} else if (zenarioA.checkSlotsBeingEdited()) {
		return phrase.leavePageWarning;
	
	} else {
		return undefined;
	}
};





//Pop up messages/boxes


zenarioA.boxesOpen = {};
zenarioA.openBox = function(html, className, n, e, width, left, top, disablePageBelow, overlay, draggable, resizable, padding, maxHeight, rightCornerOfElement, bottomCornerOfElement) {
	
	if (!n) {
		n = 1;
	}
	
	if (className === undefined) {
		className = '';
	}
	
	var $box,
		$overlay,
		zIndex;
	
	if (disablePageBelow || overlay) {
		if (!get('zenario_fb' + n + '__overlay')) {
			var s, split, plit, overlayClassName = 'zenario_overlay';
			
			if (className) {
				split = className.split(' ');
				foreach (split as s => plit) {
					if (plit) {
						overlayClassName += ' ' + plit + '__overlay';
					}
				}
			}
			
			$('body').append('<div class="' + overlayClassName + '" id="zenario_fb' + n + '__overlay" style="display: none;"></div>');
		}
		$overlay = $('#zenario_fb' + n + '__overlay');
	}
	
	if (!get('zenario_fb' + n)) {
		$('body').append('<div id="zenario_fb' + n + '"></div>');
	}
	get('zenario_fb' + n).className = className;
	$box = $('#zenario_fb' + n);
	
	if (draggable === true) {
		$box.draggable({handle: '.zenario_drag', cancel: '.zenario_no_drag', containment: 'window'});
	
	} else if (typeof draggable == 'string') {
		$box.draggable({handle: draggable, cancel: '.zenario_no_drag', containment: 'window'});
	}
	
	
	zenarioA.adjustBox(n, e, width, left, top, html, padding, maxHeight, rightCornerOfElement, bottomCornerOfElement);
	
	
	if (disablePageBelow || overlay) {
		zIndex = $box.css('z-index');
		$overlay.css('z-index', ifNull(1 * zIndex - 1, 0, 0)).show().unbind('click');
		
		if (!disablePageBelow) {
			$overlay.click(function() {
				zenarioA.closeBox(n);
			});
		}
		
		$overlay.show();
	}
	
	if (resizable) {
		if (resizable === true) {
			resizable = {};
		}
		
		if (resizable.containment === undefined) {
			resizable.containment = 'document';
		}
		if (resizable.minWidth === undefined) {
			resizable.minWidth = width;
		}
		
		$box.resizable(resizable);
	}
	
	$box.show();
};

zenarioA.adjustBox = function(n, e, width, left, top, html, padding, maxHeight, rightCornerOfElement, bottomCornerOfElement) {
	
	var wrapper = get('zenario_fb' + n);
	
	
	//var fromElement = false;
	
	if (!n) {
		n = 1;
	}
	
	if (top === undefined) {
		top = 15;
	}
	
	if (left === undefined) {
		left = 50;
	}
	
	if (padding === undefined) {
		padding = 15;
	}
	
	if (!width) {
		left = 0;
		padding = 0;
		wrapper.style.left = '0';
		wrapper.style.width = '100%';
	} else {
		wrapper.style.width = width + 'px';
	}
	
	
	if (html !== false && html !== undefined) {
		wrapper.innerHTML = html;
		zenario.addJQueryElements('#zenario_fb' + n + ' ', true);
	}
	
	if (maxHeight === undefined) {
		maxHeight = ifNull($('#zenario_fb' + n).height(), 50);
	}
	
	
	//Position the floating box
	//e can be a mouse event, or an object that was clicked on
	//If e is provided, then position it relative to the mouse/object. Otherwise position it relative to the screen.
	if (e) {
		var y;
		if (e.clientY !== undefined || e.pageY !== undefined) {
			y = zenario.getMouseY(e) + top;
		} else {
			y = $(e).offset().top + top;
			
			if (bottomCornerOfElement) {
				y += $(e).height();
			}
		}
		
		//Check the box is not off the screen, and move it back if so
		var minY = 1 + zenario.scrollTop();
		if (y < minY) {
			y = minY;
		} else {
			
			var windowHeight = $(window).height();
			
			if (zenarioA.pageEl) {
				windowHeight = Math.min(windowHeight, $(zenarioA.pageEl).height());
			}
			
			var maxY = windowHeight - maxHeight - padding + zenario.scrollTop();
			if (y > maxY) {
				y = maxY;
			}
		}
		
		wrapper.style.bottom = null;
		wrapper.style.top = y + 'px';
		wrapper.style.position = 'absolute';
	
	} else {
		if (top < 1) {
			top = 0;
		} else if (top > 100) {
			top = 100;
		}
		
		if (zenario.browserIsIE(6)) {
			wrapper.style.position = 'absolute';
			
			var className = wrapper.className;
			className = className.replace(/zenario_fbIE6Hack\d+/, '');
			className += ' zenario_fbIE6Hack' + (top < 4? top : 5 * Math.round(top / 5));
			
			wrapper.className = className;
			
		} else {
			wrapper.style.position = 'fixed';
			
			if (top <= 50) {
				wrapper.style.bottom = null;
				wrapper.style.top = top + '%';
			} else {
				wrapper.style.top = null;
				wrapper.style.bottom = (100 - top) + '%';
			}
		}
	}
	
	if (width) {
		if (e) {
			var x;
			if (e.clientX !== undefined || e.pageX !== undefined) {
				x = zenario.getMouseX(e) + top;
			} else {
				x = $(e).offset().left + left;
				
				if (rightCornerOfElement) {
					x += $(e).width();
				}
			}
			
			//Check the box is not off the screen, and move it back if so
			var minX = 1 + zenario.scrollLeft();
			if (x < minX) {
				x = minX;
			} else {
				var maxX = $(window).width() - width - padding + zenario.scrollLeft();
				if (x > maxX) {
					x = maxX;
				}
			}
			
			wrapper.style.right = null;
			wrapper.style.marginRight = 0;
			wrapper.style.marginLeft = 0;
			wrapper.style.left = x + 'px';
		} else {
			if (left < 1) {
				left = 0;
			} else if (left > 100) {
				left = 100;
			}
			
			var space = Math.max(0, $(window).width() - width);
			
			wrapper.style.right = null;
			wrapper.style.left = Math.round(space * left / 100) + 'px';
		}
	}
};

zenarioA.checkIfBoxIsOpen = function(n) {
	return $('#zenario_fb' + n).is(':visible');
};

zenarioA.closeBox = function(n, keepHTML, options) {
	if (!n) {
		n = 1;
	}
	
	if (keepHTML) {
		$('#zenario_fb' + n).hide(options);
	} else {
		$('#zenario_fb' + n).remove();
	}
	
	$('#zenario_fb' + n + '__overlay').remove();
	
	if (zenarioA.checkIfBoxIsOpen(n)) {
		document.body.focus();
	}
	
	return false;
};

zenarioA.closeBoxHandler = function(box) {
	if (box.w) {
		delete zenarioA.boxesOpen[box.w.attr('id')];
		box.w.hide();
	}
	
	if (box.o) {
		box.o.remove();
	}
	
	foreach (zenarioA.boxesOpen as var n) {
		return;
	}
	
	setTimeout(
		function() {
			zenarioA.floatingBoxOpen = false;
			foreach (zenarioA.boxesOpen as var n) {
				zenarioA.floatingBoxOpen = true;
			}
		}, 100);
};

zenarioA.infoBox2 = function(html) {
	zenarioA.openBox(html, 'zenario_fbAdminInfoBox', 'AdminInfoBox', undefined, 405, undefined, undefined, false, true, '.zenario_infoBoxHead', false);
	
	$('#zenario_fbAdminInfoBox .zenario_infoBoxDirectory').each(function(i, el) {
		el.innerHTML = zenarioA.forcePathWrap(el.innerHTML);
	});
};


//Fix a bug where the directory path does not wrap in Webkit/IE
zenarioA.forcePathWrap = function(html, pattern, replacement) {
	
	if (zenario.browserIsFirefox() && pattern === undefined) {
		return html;
	
	} else {
		if (pattern === undefined) {
			pattern = /\//g;
		}
		if (replacement === undefined) {
			replacement = '/';
		}
		
		return html.replace(pattern, replacement + '<span style="font-size: 1px;"> </span>');
	}
};



zenarioA.loggedOut = function(message) {
	if (message.substr(0, 17) == '<!--Logged_Out-->') {
		zenarioA.showMessage(message);
		return true;
	} else {
		return false;
	}
};

//If the admin has been logged out, check to see whether this window is in an iframe, and show the login window in the iframe if possible.
zenarioA.loggedOutIframeCheck = function(message, messageType) {
	var parent = (self && self != self.parent && self.parent);
	
	try {
		if (parent
		 && parent.zenarioA
		 && parent.zenarioA.showMessage) {
			parent.zenarioA.closeBox('AdminOrganizer');
			parent.zenarioA.showMessage(message, true, messageType);
			
			return true;
		}
	} catch (e) {
		return false;
	}
	
	return false;
};

zenarioA.floatingBox = function(message, buttonsHTML, messageType, modal, htmlEscapeMessage, onOkay) {
	var defaultModalValue = false,
		html = '';
	
	
	if (htmlEscapeMessage) {
		message = htmlspecialchars(message, true);
	}
	
	if (buttonsHTML === true) {
		buttonsHTML =
			'<input type="button" class="submit" value="' + phrase.OK + '"/>';
	
	} else if (buttonsHTML && buttonsHTML.indexOf('<input ') === -1) {
		buttonsHTML =
			'<input class="submit_selected" type="button" value="' + htmlspecialchars(buttonsHTML) + '"/>' +
			'<input type="button" class="submit" value="' + phrase.cancel + '"/>';
	}
	
	if (messageType == 'success' || messageType == 4) {
		messageType = 'zenario_fbSuccess';
	
	} else if (messageType == 'question' || messageType == 3) {
		messageType = 'zenario_fbQuestion';
	
	} else if (messageType == 'error' || messageType == 2) {
		messageType = 'zenario_fbError';
	
	} else if (messageType && messageType != 'none') {
		messageType = 'zenario_fbWarning';
		defaultModalValue = true;
	
	} else {
		messageType = '';
	}
	
	if (modal === undefined) {
		modal = defaultModalValue;
	}
	
	html =
		'<div class="zenario_jqmBor"><div class="zenario_jqmBor"><div class="zenario_jqmBor"><div class="zenario_jqmBor">' +
			'<div class="zenario_jqmHead"><div></div></div>' +
			'<div class="zenario_jqmMain"><div class="zenario_jqmMainBor"><div class="zenario_jqmMainBor">' +
				'<div class="zenario_fbMain">' +
					'<div class="zenario_jqmWinBor"><div class="zenario_jqmWinBor">' +
						'<div id="zenario_fbWarning" class="' + messageType + '">' +
							'<div id="zenario_fbInner">' + message + '</div>' +
						'</div>' +
					'</div></div>' +
					'<div class="zenario_fbButtons" id="zenario_fbMessageButtons" onclick="zenarioA.closeFloatingBox();" onkeyup="' +
						'if (event.keyCode == 27) {' +
							'zenarioA.closeFloatingBox();' +
							'return false;' +
					'}">' + buttonsHTML + '</div>' +
				'</div>' +
			'</div></div></div>' +
			'<div class="zenario_jqmFoot"></div>' +
		'</div></div></div></div>';
	

	zenarioA.openBox(html, 'zenario_fbAdmin zenario_prompt', 'AdminMessage', undefined, 550, 50, 17, modal, true, false, false);
	
	zenario.addJQueryElements('#zenario_fbAdminMessage ', true);
	
	if (onOkay) {
		var $button = $('#zenario_fbMessageButtons .submit_selected');
		
		if (!$button.length) {
			$button = $('#zenario_fbMessageButtons .submit');
		}
		
		$button.click(
			function() {
				setTimeout(onOkay, 1);
			}
		);
	}
};

zenarioA.currentlyClosingFloatingBox = false;
zenarioA.closeFloatingBox = function(stopBoxClosingTwice) {
	if (zenarioA.currentlyClosingFloatingBox) {
		zenarioA.currentlyClosingFloatingBox = false;
	} else {
		if (zenarioA.checkIfBoxIsOpen('AdminMessage')) {
			zenarioA.closeBox('AdminMessage');
			
			if (stopBoxClosingTwice) {
				zenarioA.currentlyClosingFloatingBox = true;
			}
		}
	}
};



//Add jQuery elements automatically by class name
zenarioA.addJQueryElements = function(path) {
	
	var onchange_callback = undefined;
	
	if (zenarioAB.isOpen) {
		onchange_callback = function(inst) {
			zenarioAB.fieldChange(inst.id);
		}
	}
	
	var	content_css = undefined;
	
	//if (zenario.skinId) {
	//	content_css = URLBasePath + 'zenario/styles/skin.css.php?v=' + encodeURIComponent(window.zenarioCSSJSVersionNumber) + '&id=' + zenario.skinId + '&editor=1' + (zenario.useGZ? '' : '&gz=1');
	//}

	//Add TinyMCE editors to the page
	zenarioA.getSkinDesc();
	var options = {
		script_url: URLBasePath + zenarioA.tinyMCEPath,
		
		inline: false,
		menubar: false,
		statusbar: false,
		plugins: "autoresize",
		convert_urls: false,
		readonly: true,
		
		inline_styles: false,
		allow_events: true,
		allow_script_urls: true,
		
		autoresize_max_height: Math.max(Math.floor(($(window).height()) * 0.5), 300),
		
		onchange_callback: onchange_callback,
		init_instance_callback: function(instance) {
			zenarioA.enableDragDropUploadInTinyMCE(false, undefined, get('row__' + ifNull(instance.editorId, instance.id)));
			var el;
			if ((el = instance.editorContainer)
			 && (el = $('#' + instance.editorContainer.id + ' iframe'))
			 && (el = el[0])
			 && (el = el.contentWindow)) {
				zenarioA.enableDragDropUploadInTinyMCE(false, undefined, el);
			}
		}
	}
	$(path + 'textarea.tinymce_readonly').tinymce(options);
	
	
	var options2 = $.extend(true, {}, options, {
		plugins: [
			"advlist autolink lists link image charmap hr anchor",
			"searchreplace code",
			"nonbreaking table contextmenu directionality",
			"paste autoresize"],
		
		image_advtab: true,
        browser_spellcheck: true,
		
		paste_preprocess: zenarioA.tinyMCEPasteRreprocess,

		readonly: false,
		
		relative_urls: true,
		document_base_url: URLBasePath,
		
		content_css: content_css,
		toolbar: 'undo redo | bold italic | removeformat | fontsizeselect | formatselect | numlist bullist | outdent indent | code',
		style_formats: zenarioA.skinDesc.style_formats,
		oninit: undefined
	});
	$(path + 'textarea.tinymce').tinymce(options2);
	
	var options3 = $.extend(true, {}, options2, {
		relative_urls: false,
		document_base_url: undefined,
		toolbar: 'undo redo | image link unlink | bold italic | removeformat | fontsizeselect | formatselect | numlist bullist | outdent indent | code',
		
		file_browser_callback: zenarioA.fileBrowser,
		init_instance_callback: function(instance) {
			zenarioA.enableDragDropUploadInTinyMCE(true, URLBasePath, get('row__' + ifNull(instance.editorId, instance.id)));
			var el;
			if ((el = instance.editorContainer)
			 && (el = $('#' + instance.editorContainer.id + ' iframe'))
			 && (el = el[0])
			 && (el = el.contentWindow)) {
				zenarioA.enableDragDropUploadInTinyMCE(true, URLBasePath, el);
			}
		}
	});
	$(path + 'textarea.tinymce_with_images_and_links').tinymce(options3);
	
	var options4 = $.extend(true, {}, options3, {
		toolbar: 'undo redo | image | bold italic | removeformat | fontsizeselect | formatselect | numlist bullist | outdent indent | code'
	});
	$(path + 'textarea.tinymce_with_images').tinymce(options4);
	
	var options5 = $.extend(true, {}, options4, {
		toolbar: 'undo redo | link unlink | bold italic | removeformat | fontsizeselect | formatselect | numlist bullist | outdent indent | code'
	});
	$(path + 'textarea.tinymce_with_links').tinymce(options5);
	
	
	
	
	//Look for fields that IDs from Storekeeper, and look up what the names should be
	$(path + '.look_value_from_sk').each(function(i, el) {
		//Requires three fields with ids in the format: "id", "name_for_id" and "target_path_for_id" to work.
		if (el.id && el.id.substr(0, 9) == 'name_for_') {
			var id = el.id.substr(9);
			var pathEl, valueEl, panel;
			if ((valueEl = get(id)) && (pathEl = get('target_path_for_' + id))) {
				if (valueEl.value && valueEl.value != 0) {
					if (panel = zenarioA.getSKItem(pathEl.value, valueEl.value)) {
						var labelTag = ifNull(panel.default_sort_column, 'name');
						if (panel.items && panel.items[valueEl.value] && panel.items[valueEl.value][labelTag]) {
							if (el.type && el.type == 'text') {
								el.value = (panel.items[valueEl.value][labelTag] + '').replace(/\s/g, ' ');
							} else {
								el.innerHTML = htmlspecialchars(panel.items[valueEl.value][labelTag]);
							}
						}
					}
				}
			}
		}
	});
	
	//jQuery datepickers (Admin mode version)
	$(path + 'input.zenario_datepicker').each(function(i, el) {
		if (el.id && zenarioA.siteSettings && get('_value_for__' + el.id)) {
			var changeMonthAndYear = $(el).hasClass('zenario_datepicker_change_month_and_year');
			$(el).datepicker({
				changeMonth: changeMonthAndYear,
				changeYear: changeMonthAndYear,
				dateFormat: zenarioA.siteSettings.storekeeper_date_format,
				altField: '#_value_for__' + el.id,
				altFormat: 'yy-mm-dd',
				showOn: 'focus',
				onSelect: function(dateText, inst) { zenarioAB.fieldChange(this.name); }
			});
		}
	});
	
	//Admin mode tooltips
	zenarioA.tooltips(path + 'span[title]');
	zenarioA.tooltips(path + '.pluginAdminMenuButton[title]', {position: {my: 'left top+2', at: 'left bottom', collision: 'flipfit'}});
};

zenarioA.tooltips = function(target, options) {
	if (!options) {
		options = {};
	}
	
	if (options.tooltipClass === undefined) {
		options.tooltipClass = 'zenario_admin_tooltip';
	}
	
	zenario.tooltips(target, options);
};

zenarioA.setTooltipIfTooLarge = function(target, title, sizeThreshold) {
	
	$(target).each(function(i, el) {
		var tooltip;
		
		if (title === undefined) {
			tooltip = el.innerHTML;
		} else {
			tooltip = title;
		}
		
		try {
			$(el).jQueryTooltip('destroy');
		} catch (e) {
		}
		
		if (tooltip.replace(/\&\w+;/g, '-').length > sizeThreshold) {
			zenarioA.tooltips(el, {content: tooltip, items: '*'});
		} else {
			$(el).attr('title', '');
		}
	});
};



//Functions for TinyMCE

zenarioA.tinyMCEPasteRreprocess = function(pl, o) {
	o.content = o.content.replace(
		/<\/?font\b[^>]*?>/gi, '').replace(
		/<b\b[^>]*?>/gi, '<strong>').replace(
		/<\/b>/gi, '</strong>').replace(
		/<i\b[^>]*?>/gi, '<em>').replace(
		/<\/i>/gi, '</em>').replace(
		/<u\b[^>]*?>/gi, '<u>').replace(
		/<\/u>/gi, '</u>').replace(
		/<u>\s*<p\b[^>]*?>/gi, '<p><u>').replace(
		/<\/p>\s*<\/u>/gi, '</u></p>').replace(
		/<em>\s*<p\b[^>]*?>/gi, '<p><em>').replace(
		/<\/p>\s*<\/em>/gi, '</em></p>').replace(
		/<strong>\s*<p\b[^>]*?>/gi, '<p><strong>').replace(
		/<\/p>\s*<\/strong>/gi, '</strong></p>').replace(
		/<u\b[^>]*?>/gi, '<span style="text-decoration: underline;">').replace(
		/<\/u>/gi, '</span>');
};

//Enable an Admin to upload an image or an animation by draging and dropping it onto the WYSIWYG Editor
//The file will be uploaded using a call to the handleOrganizerPanelAJAX() function of the Common Features Module
zenarioA.enableDragDropUploadInTinyMCE = function(enableImages, prefix, el) {
	
	if (typeof el == 'string') {
		el = get(el);
	}
	
	if (el) {
		zenarioA.disableFileDragDrop(el);
		
		if (enableImages && zenarioA.canDoHTML5Upload()) {
			var url = URLBasePath + 'zenario/ajax.php',
				request = {
					method_call: 'handleOrganizerPanelAJAX',
					__pluginClassName__: 'zenario_common_features',
					__path__: 'editor_temp_file',
					upload: 1};
			
			zenarioA.setHTML5UploadFromDragDrop(url, request, function() { zenarioA.addMediaToTinyMCE(prefix); }, el);
		}
	}
};

//Add a file or files uploaded above into the editor
zenarioA.addMediaToTinyMCE = function(prefix) {
	var files,
		html = '';
	
	if (files = zenario.nonAsyncAJAX(URLBasePath + 'zenario/ajax.php?method_call=getNewEditorTempFiles', true, true)) {
		foreach (files as var f => var file) {
			if (file && file.checksum && file.filename) {
				
				var url = prefix + 'zenario/file.php?c=' + file.checksum + '&usage=editor_temp_file&filename=' + encodeURIComponent(file.filename);
				
				html += '\n' +
					'<img src="' + htmlspecialchars(url) + '" alt="' + htmlspecialchars(file.filename) + '"' +
					' width="' + file.width + '" height="' + file.height + '"/>';
			}
		}
	}
	tinyMCE.execCommand('mceInsertContent', false, html);
};


zenarioA.skinDesc = undefined;
zenarioA.getSkinDesc = function() {
	if (zenarioA.skinDesc === undefined) {
		zenarioA.skinDesc = {};
		
		var desc;
		if (zenario.skinId
		 && (desc = zenario.pluginClassAJAX('zenario_common_features', {skinId: zenario.skinId}, false, true))
		 && (typeof desc == 'object')) {
			zenarioA.skinDesc = desc;
		}
	}
	
	return zenarioA.skinDesc;
};

zenarioA.fileBrowser = function(field_name, url, type, win) {
	zenarioA.win = win;
	zenarioA.field_name = field_name;
	
	if (type == 'file') {
		var id = tinyMCE.activeEditor.id,
			pick_items = {
				path: 'zenario__content/nav/content/panel',
				target_path: 'zenario__content/nav/content/panel',
				min_path: 'zenario__content/nav/content/panel',
				max_path: 'zenario__content/nav/content/panel',
				disallow_refiners_looping_on_min_path: false};
		
		if (id
		 && zenarioAB.focus
		 && zenarioAB.focus.tab
		 && zenarioAB.focus.tabs
		 && zenarioAB.focus.tabs[zenarioAB.focus.tab]
		 && zenarioAB.focus.tabs[zenarioAB.focus.tab].fields
		 && zenarioAB.focus.tabs[zenarioAB.focus.tab].fields[id]
		 && zenarioAB.focus.tabs[zenarioAB.focus.tab].fields[id].insert_link_button
		 && zenarioAB.focus.tabs[zenarioAB.focus.tab].fields[id].insert_link_button.pick_items) {
			pick_items = zenarioAB.focus.tabs[zenarioAB.focus.tab].fields[id].insert_link_button.pick_items;
		}

		zenarioA.SK('zenarioA', 'setLinkURL', false,
						pick_items.path,
						pick_items.target_path,
						pick_items.min_path,
						pick_items.max_path,
						pick_items.disallow_refiners_looping_on_min_path,
						undefined,
						pick_items.one_to_one_choose_phrase,
						undefined,
						true);
	
	} else if (type == 'image') {
		var id = tinyMCE.activeEditor.id,
			pick_items = {
				path: 'zenario__content/hidden_nav/media/panel/hidden_nav/inline_images_shared/panel',
				target_path: 'zenario__content/hidden_nav/media/panel/hidden_nav/inline_images_shared/panel',
				min_path: 'zenario__content/hidden_nav/media/panel/hidden_nav/inline_images_shared/panel',
				max_path: false,
				disallow_refiners_looping_on_min_path: false};
		
		if (id
		 && zenarioAB.focus
		 && zenarioAB.focus.tab
		 && zenarioAB.focus.tabs
		 && zenarioAB.focus.tabs[zenarioAB.focus.tab]
		 && zenarioAB.focus.tabs[zenarioAB.focus.tab].fields
		 && zenarioAB.focus.tabs[zenarioAB.focus.tab].fields[id]
		 && zenarioAB.focus.tabs[zenarioAB.focus.tab].fields[id].insert_image_button
		 && zenarioAB.focus.tabs[zenarioAB.focus.tab].fields[id].insert_image_button.pick_items) {
			pick_items = zenarioAB.focus.tabs[zenarioAB.focus.tab].fields[id].insert_image_button.pick_items;
		}

		zenarioA.SK('zenarioA', 'setImageURL', false,
						pick_items.path,
						pick_items.target_path,
						pick_items.min_path,
						pick_items.max_path,
						pick_items.disallow_refiners_looping_on_min_path,
						undefined,
						pick_items.one_to_one_choose_phrase,
						undefined,
						true);
	}
};



zenarioA.lastFieldValue = '';
zenarioA.setEditorField = function(value, el, onlyIfEmpty) {
	if (el === undefined) {
		el = zenarioA.win.document.getElementById(zenarioA.field_name);
	}
	
	if (onlyIfEmpty) {
		if (el.value !== '' && el.value != zenarioA.lastFieldValue) {
			return;
		}
		zenarioA.lastFieldValue = value;
	}
	
	el.value = value;
	zenario.fireChangeEvent(el);
};

zenarioA.setLinkURL = function(path, key, row) {
	
	//Get the URL via an AJAX program
	key.getItemURL = true;
	var URL = zenario.pluginClassAJAX('zenario_common_features', key, true);
	
	if (zenarioA.loggedOut(URL)) {
		return;
	}
	
	zenarioA.setEditorField(row.title, $('.mce-panel input.mce-link_text_to_display')[0], true);
	zenarioA.setEditorField(URLBasePath + URL);
};

zenarioA.setImageURL = function(path, key, row) {

	var imageURL = URLBasePath + 'zenario/file.php?c=' + row.checksum;
	
	if (key.usage && key.usage != 'inline') {
		imageURL += '&usage=' + encodeURIComponent(key.usage);
	}
	
	imageURL += '&filename=' + encodeURIComponent(row.filename);
	
	zenarioA.setEditorField(row.alt_tag, $('.mce-panel input.mce-image_alt')[0]);
	zenarioA.setEditorField(imageURL);
};

zenarioA.setFlashURL = function(path, key, row) {
	var flashURL = URLBasePath + 'zenario/file.php?c=' + row.checksum + '&filename=' + encodeURIComponent(row.filename);
	
	zenarioA.setEditorField(flashURL);
};



zenarioA.formatFilesizeNicely = function(size, precision) {
	
	//Return 0 without formating if the size is 0.
	if (size <= 0) {
		return '0';
	}
	
	//Define labels to use
	var labels = ['_BYTES', '_KBYTES', '_MBYTES', '_GBYTES', '_TBYTES'];
	
	//Work out which of the labels to use, based on how many powers of 1024 go into the size, and
	//how many labels we have
	var order = Math.min(4, 
					Math.floor(
						Math.log(size) / Math.log(1024)
					));
	
	precision = Math.pow(10, precision);
	
	return (Math.round(precision * size / Math.pow(1024, order)) / precision) + phrase[labels[order]];

};



zenarioA.makeTimeFromParts = function(hours,minutes,seconds) {
	var outputTime;
	
	if (seconds == undefined) {
		seconds = "00";
	}
	
	outputTime = hours + ":" + minutes + ":" + seconds;
	
	return outputTime;
};



/* Storekeeper launch functions */

//Format the name of an item from Storekeeper appropriately
zenarioA.formatSKItemName = function(panel, i) {
	var string = undefined,
		string2,
		value;
	
	if (panel.items
	 && panel.items[i]) {
	
		if (string = string2 = ifNull(panel.label_format_for_picked_items, panel.label_format_for_grid_view)) {
			foreach (panel.items[i] as var c) {
				if (string.indexOf('[[' + c + ']]') != -1) {
					value = panel.items[i][c];
					
					if (panel.columns
					 && panel.columns[c]
					 && (panel.columns[c].format || panel.columns[c].empty_value)) {
						value = zenarioA.formatSKItemField(value, panel.columns[c]);
					}
					
					while (string != (string2 = string.replace('[[' + c + ']]', value))) {
						string = string2;
					}
				}
			}
		
		} else {
			string = panel.items[i][ifNull(panel.default_sort_column, 'name')];
		}
	}
	
	if (string === undefined) {
		return i;
	} else {
		return string.replace(/\s+/g, ' ');
	}
};

zenarioA.formatSKItemField = function(value, column) {

	if (column && column.format) {
		var format = column.format,
			//Most formats allow additional text seperated by a space
			extra = '';
		
		if (value && (format != 'date' && format != 'datetime' && format != 'datetime_with_seconds' && format != 'remove_zero_padding')) {
			var pos = value.indexOf(' ');
			if (pos != -1) {
				extra = value.substr(pos);
				value = value.substr(0, pos);
			}
		}
		
		if (format == 'true_or_false') {
			value = engToBoolean(value)? phrase.tru : phrase.fal;
			
		} else if (format == 'yes_or_no') {
			if (engToBoolean(value)) {
				if (column.yes_phrase !== undefined) {
					value = column.yes_phrase
				} else {
					value = phrase.yes;
				}
			} else {
				if (column.no_phrase !== undefined) {
					value = column.no_phrase
				} else {
					value = phrase.no;
				}
			}
			
		} else if (format == 'remove_zero_padding') {
			value = value.replace(/\b0+/g, '');
			
		} else if (format == 'enum' && column.values && column.values[value] !== undefined) {
			value = column.values[value];
			
		} else if ((format == 'module_name' || format == 'module_class_name') && zenarioO.init) {
			if (!value) {
				value = phrase.core;
			} else if (zenarioA.pluginNames[value]) {
				value = zenarioA.pluginNames[value];
			}
			
		} else if (format == 'filesize' && value == 1*value) {
			value = zenarioA.formatFilesizeNicely(value, 1);
			
		} else if ((format == 'language_english_name' || format == 'language_local_name') && zenarioA.lang[value]) {
			value = zenarioA.lang[value].name;
			
		} else if ((format == 'language_english_name_with_id' || format == 'language_local_name_with_id') && zenarioA.lang[value]) {
			value = zenarioA.lang[value].name + ' (' + value + ')';
			
		} else if (format == 'date' || format == 'datetime' || format == 'datetime_with_seconds') {
			value = zenario.formatDate(value, format == 'date'? false : format);
		}
		
		value += extra;
	}
	
	if (!value && column && column.empty_value) {
		value = column.empty_value;
	}
	
	return value;
};

//Open the storekeeper in quick mode
zenarioA.SKQ = function(path, targetPath, minPath, maxPath, disallowRefinersLoopingOnMinPath, slotName, instanceId, reloadOnChanges, wrapperCSSClass) {
	
	zenarioA.SK(
		false, false, false,
		path, false, minPath, maxPath, disallowRefinersLoopingOnMinPath,
		false, false, false,
		false, false,
		true, targetPath, instanceId,
		undefined,
		undefined, undefined,
		reloadOnChanges, wrapperCSSClass);
};

//Get the correct CSS class name to put around Storekeeper
zenarioA.getSKBodyClass = function(win) {
	if (win === undefined) {
		win = window;
	}
	
	return 'zenario_sk ' + (
		win.zenarioONotFull || win.zenarioA.openedInIframe?
			(
				(win.zenarioOQuickMode? 'zenario_organizer_quick' : 'zenario_organizer_select') +
				' ' + 
				(win.zenarioOOpenOverAdminBox? 'zenario_organizer_over_admin_box' : 'zenario_organizer_under_admin_box')
			)
		  : 'zenario_organizer_full'
	) + ' ' + (win.zenarioOWrapperCSSClass || '');
};

//Open the storekeeper in select mode
zenarioA.SK = function(
	callbackObject, callbackFunction, enableMultipleSelect,
	path, targetPath, minPath, maxPath, disallowRefinersLoopingOnMinPath,
	chooseButtonActiveClass, choosePhrase, chooseMultiplePhrase,
	tinyMCE,
	openOverAdminBox,
	skQuick, openingPath, openingInstance,
	combineItem,
	allowNoSelection, noSelectionChoosePhrase,
	reloadOnChanges, wrapperCSSClass,
	object
) {
	
	var win,
		useIframe = !skQuick || zenarioA.storekeeperWindow || zenarioA.checkIfBoxIsOpen('sk');
	
	if (!object) {
		object = {};
	}
	
	if (!useIframe) {
		win = window;
	
	} else {
		//If we've already got a Storekeeper, we'll need to load this one new in an iFrame
		
		//Initialise it if it's not been preloaded yet
		zenarioA.SKInit();
		
		win = get('zenario_sk_iframe').contentWindow;
		
		//The "openOverAdminBox" variable should be false if we're opening an iframe,
		//as this new Storekeeper will be the first on the page and won't need to open over anything.
		openOverAdminBox = false;
	}
	
	//Close the tooltip if it is open
	zenario.closeTooltip();
	
	var overlayOpacity;
	if (skQuick) {
		overlayOpacity = 0;
	} else if (tinyMCE) {
		overlayOpacity = 10;
	} else if (!zenarioA.openedInIframe) {
		overlayOpacity = 75;
	} else {
		overlayOpacity = 35;
	}

	win.zenarioOQueue = [{path: path, branch: -1, selectedItems: {}}];
	
	//minPath and targetPath should default to path, as long as it is not a navigation path
	if (path.indexOf('//') === -1) {
		if (!minPath) {
			minPath = path;
		}
		if (!targetPath) {
			targetPath = path;
		}
	}
	
	//Max path should default to the target path if not set
	if (maxPath === undefined) {
		if (targetPath && targetPath.indexOf('//') === -1) {
			maxPath = targetPath;
		}
		
	//The max path variable can be set to false to turn those features off.
	//Convert strings such as "No" or "False" to false.
	} else if (!engToBoolean(maxPath)) {
		maxPath = false;
	}
	
	
	win.zenarioOTargetPath = targetPath;
	win.zenarioOMinPath = minPath;
	win.zenarioOMaxPath = maxPath;
	win.zenarioODisallowRefinersLoopingOnMinPath = engToBoolean(disallowRefinersLoopingOnMinPath);
	
	win.zenarioOCallbackObject = callbackObject;
	win.zenarioOCallbackFunction = callbackFunction;
	win.zenarioOChoosePhrase = choosePhrase;
	win.zenarioOChooseButtonActiveClass = chooseButtonActiveClass;
	win.zenarioOChooseMultiplePhrase = chooseMultiplePhrase;
	win.zenarioOMultipleSelect = engToBoolean(enableMultipleSelect);
	win.zenarioOAllowNoSelection = engToBoolean(allowNoSelection);
	win.zenarioONoSelectionChoosePhrase = noSelectionChoosePhrase;
	
	win.zenarioONotFull = true;
	win.zenarioOQuickMode = skQuick;
	win.zenarioOSelectMode = !skQuick;
	win.zenarioOSelectObject = object;
	
	win.zenarioOOpeningPath = openingPath;
	win.zenarioOOpeningInstance = openingInstance;
	win.zenarioOCombineItem = combineItem;
	win.zenarioOReloadOnChanges = reloadOnChanges;
	win.zenarioOWrapperCSSClass = wrapperCSSClass || '';
	win.zenarioOOpenOverAdminBox = openOverAdminBox;
	win.zenarioOFirstLoad = true;
	
	if (!useIframe) {
		//If we've not currently got an existing full-Storekeeper instance in this frame, set Storekeeper up in a <div>
		
		//openOverAdminBox
		
		if (!zenarioO.init) {
			zenarioO = new zenarioNewO('', undefined);
		}
		
		zenarioO.open(zenarioA.getSKBodyClass(win), undefined, $(window).width() * 0.8, 50, 10, !skQuick, true, true, {minWidth: 550});
		//zenarioO.open = function(className, e, width, left, top, disablePageBelow, overlay, draggable, resizable, padding, maxHeight, rightCornerOfElement, bottomCornerOfElement) {
		
		zenarioO.init();
	}
	
	//If Storekeeper has been already pre-loaded, we can use the navigation functions to go to the correct path
	if (win.zenarioO) {
		win.zenarioO.go(path, -1);
	}
	//Otherwise store the requested path in zenarioOGoToPathWhenLoaded and wait for the it to catch up
		
	if (useIframe) {
		//Show the storekeeper window
		zenarioA.openBox(false, zenarioA.getSKBodyClass(win), 'AdminOrganizer', false, false, 50, 2, !skQuick, true, false, false);
		
		get('zenario_sk_iframe').style.width = zenario.browserIsIE(6)? '600px' : '96%';
		if (skQuick) {
			get('zenario_sk_iframe').style.height = '100%';
		} else {
			get('zenario_sk_iframe').style.height = '96%';
		}
		
		if (get('zenario_sk_iframe').contentWindow.zenarioO) {
			get('zenario_sk_iframe').contentWindow.zenarioO.lastSize = false;
		}
		
		get('zenario_fbAdminOrganizer').style.left = '0px';
	}
};

zenarioA.SKInitted = false;
zenarioA.SKInit = function() {
	if (zenarioA.SKInitted) {
		return;
	}
	
	get('zenario_fbAdminOrganizer').innerHTML =
		'<iframe id="zenario_sk_iframe" src="' + URLBasePath + 'zenario/admin/organizer.php?openedInIframe=1&amp;rand=' + (new Date).getTime() + '"></iframe>';
	
	zenarioA.SKInitted = true;
};

//Attempt to preload the Storekeeper ten seconds after the current page has finished loading
zenarioA.SKStartInit = function() {
	
	//Don't do anything for IE 6 and 7
	if (zenario.browserIsIE(7)) {
		return false;
	}
	
	if (!window.zenario_inIframe && !zenarioA.openedInIframe) {
		setTimeout(zenarioA.SKInit, zenarioA.storekeeperInitTime);
	}
};


zenarioA.getCustomFromSK = function(field, path, targetPath, minPath, maxPath, disallowRefinersLoopingOnMinPath) {
	zenarioA.SKTarget = field;
	zenarioA.SK('zenarioA', 'setCustom', false, path, targetPath, minPath, maxPath, disallowRefinersLoopingOnMinPath);
};

//Handle callback for custom things
zenarioA.setCustom = function(path, key, row) {
	get(zenarioA.SKTarget).value = key.id;
	get('name_for_' + zenarioA.SKTarget).value = row[row.__label_tag__];
};

//Handle callback for a Group
zenarioA.setGroupNameAndId = function(path, key, row) {
	get(zenarioA.SKTarget).value = key.id;
	get('name_for_' + zenarioA.SKTarget).value = row.name;
};

//Handle callback for a user
zenarioA.setUserNameAndId = function(path, key, row) {
	get(zenarioA.SKTarget).value = key.id;
	get('name_for_' + zenarioA.SKTarget).value = row.username;
};

zenarioA.setContentIdAndCType = function(path, key, row, panel) {
	get(zenarioA.SKTarget).value = key.id;
	get('name_for_' + zenarioA.SKTarget).value = zenarioA.formatSKItemName(panel, key.id);
};

zenarioA.removeNameAndId = function(field) {
	get(field).value = '';
	get('name_for_' + field).value = get('nothing_selected_phrase_for_' + field).value;
};



zenarioA.multipleLanguagesEnabled = function() {
	var lang,
		langs = 0;
	
	if (zenarioA.lang) {
		foreach (zenarioA.lang as lang) {
			if (zenarioA.lang[lang].enabled) {
				if (langs++) {
					return true;
				}
			}
		}
	}
	
	return false;
};



//Functions for enabling HTML 5 file-uploads in WYSIWYG Editors and in Storekeeper Panels

zenarioA.uploading = false;
zenarioA.canDoHTML5Upload = function() {
	return window.FileReader;
};

zenarioA.clearHTML5UploadFromDragDrop = function() {
	delete zenarioA.uploadPathFromDragDrop;
	delete zenarioA.uploadRequestFromDragDrop;
	delete zenarioA.uploadCallBackFromDragDrop;
};

zenarioA.setHTML5UploadFromDragDrop = function(path, request, callBack, el) {
	if (!zenarioA.canDoHTML5Upload()) {
		return false;
	
	} else {
		zenarioA.uploadPathFromDragDrop = path;
		zenarioA.uploadRequestFromDragDrop = request;
		zenarioA.uploadCallBackFromDragDrop = callBack;
		ifNull(el, document.body).addEventListener('drop', zenarioA.doHTML5UploadFromDragDrop, false);
		return true;
	}
};

zenarioA.doHTML5UploadFromDragDrop = function(e) {
	zenarioA.stopFileDragDrop(e);
	zenarioA.doHTML5Upload(
		e.target.files || e.dataTransfer.files,
		zenarioA.uploadPathFromDragDrop,
		zenarioA.uploadRequestFromDragDrop,
		zenarioA.uploadCallBackFromDragDrop);
};

zenarioA.doHTML5Upload = function(files, path, request, callBack) {
	if (!path || !request || zenarioA.uploading) {
		return;
	}
	
	zenarioA.uploadPath = path;
	zenarioA.uploadRequest = request;
	zenarioA.uploadCallBack = callBack;
	zenarioA.uploadResponses = [];
	
	zenarioA.uploadFile = -1;
	zenarioA.uploadFiles = files;
	zenarioA.doNextUpload();
};

zenarioA.doNextUpload = function() {
	
	var $zenario_progress_wrap = $('#zenario_progress_wrap'),
		$zenario_progress_name = $('#zenario_progress_name'),
		$zenario_progress_stop = $('#zenario_progress_stop');
	
	$zenario_progress_name.text('');
	$zenario_progress_stop.unbind('click');
	
	zenarioA.uploading = !!zenarioA.uploadFiles[++zenarioA.uploadFile];
	
	if (zenarioO.init) {
		zenarioO.setWrapperClass('uploading', zenarioA.uploading);
	}
	
	if (zenarioA.uploading) {
		
		$zenario_progress_wrap.show().removeClass('zenario_progress_cancelled');
		
		get('zenario_progressbar').style.width = '0%';
		
		if (!zenarioA.uploadFiles[zenarioA.uploadFile].size || !zenarioA.uploadFiles[zenarioA.uploadFile].name) {
			zenarioA.doNextUpload();
		
		} else if (zenarioA.uploadFiles[zenarioA.uploadFile].size > zenarioA.maxUpload) {
			zenarioA.showMessage(phrase.uploadTooLarge.replace('[[maxUploadF]]', zenarioA.maxUploadF), true, 'error');
			zenarioA.doNextUpload();
		
		} else {
			$zenario_progress_name.text(zenarioA.uploadFiles[zenarioA.uploadFile].name);
			
			zenarioA.uploader = new XMLHttpRequest();  
			zenarioA.uploader.open('POST', zenarioA.uploadPath, true);
			
			try {
				//Try to add a header for the filename
				zenarioA.uploader.setRequestHeader('X_FILENAME', zenarioA.uploadFiles[zenarioA.uploadFile].name);
			} catch (e) {
				//Don't worry if it coudln't be added, it's optional
			}
			
			var data = new FormData();
			foreach (zenarioA.uploadRequest as var k) {
				data.append(k, '' + zenarioA.uploadRequest[k]);
			}
			
			data.append('Filedata', zenarioA.uploadFiles[zenarioA.uploadFile]);
			
			zenarioA.uploader.upload.addEventListener('progress', zenarioA.uploadProgress, false);
			
			zenarioA.uploader.addEventListener('load', zenarioA.uploadDone, false);
			zenarioA.uploader.addEventListener('error', zenarioA.uploadDone, false);
			zenarioA.uploader.addEventListener('abort', zenarioA.uploadDone, false);
			
			$zenario_progress_stop.click(function() {
				$zenario_progress_wrap.show().removeClass('zenario_progress_cancelled');
				zenarioA.uploader.abort();
			});
			
			zenarioA.uploader.send(data);
		}
	
	} else {
		$zenario_progress_wrap.hide();
		
		if (zenarioA.uploadCallBack) {
			zenarioA.uploadCallBack(zenarioA.uploadResponses);
		}
	}
};

zenarioA.uploadProgress = function(e) {
	var completion = 0;
	if (e.lengthComputable) {  
		completion = 100 * e.loaded / e.total;  
	}
	
	get('zenario_progress_wrap').style.display = 'block';
	get('zenario_progressbar').style.width = completion + '%';
};

zenarioA.uploadDone = function(e) {
	
	if (zenarioA.uploader.responseText && zenarioA.uploader.responseText != 1) {
		try {
			data = JSON.parse(zenarioA.uploader.responseText);
		
			if (typeof data != 'object') {
				throw 0;
			}
			
			zenarioA.uploadResponses.push(data);
		
		} catch (e) {
			zenarioA.showMessage(zenarioA.uploader.responseText, true, 'error', false, true);
		}
	}
	
	zenarioA.doNextUpload();
};



zenarioA.debug = function(mode, skMap) {
	window.open(URLBasePath + 'zenario/admin/dev_tools/dev_tools.php?mode=' + encodeURIComponent(mode) + (skMap? '&skMap=1' : '')); return false;
};

//Open the current admin's profile
zenarioA.openProfile = function() {
	//zenarioAB.open = function(path, key, tab, values, callBack, templatePrefix)
	zenarioAB.open(
		'zenario_admin',
		{id: zenario.adminId},
		undefined, undefined,
		function() {
			document.location = document.location;
		}
	);
};


//Functionality for clicking on Menu Nodes. They should:
	//Follow their hyperlinks in preview mode
	//Open Storekeeper Quick in menu mode if the menu wand is on
		//Or show the create a menu node admin box for the create buttons
	//Do nothing otherwise
zenarioA.openMenuAdminBox = function(key, alwaysOpen) {
	if (!key) {
		key = {};
	}
	
	if (alwaysOpen) {
		//continue
	
	} else if (zenarioA.pageMode == 'preview') {
		return true;
	
	} else if (zenarioA.pageMode == 'menu' && zenarioA.menuWandOn) {
		//continue
	
	} else {
		return false;
	}
	
	if (zenario.cID) {
		key.cID = zenario.cID;
		key.cType = zenario.cType;
		key.languageId = zenario.langId;
	}
	
	if (key.id) {
		//If this is an existing Menu Item, open it in Storekeeper Quick
		var path = zenario.pluginClassAJAX('zenario_common_features', {getMenuItemStorekeeperDeepLink: key.id, languageId: key.languageId}, true);
		
		if (zenarioA.loggedOut(path)) {
			return;
		}
		
		var object = {
			organizer_quick: {
				path: path,
				target_path: 'zenario__menu/hidden_nav/menu_nodes/panel',
				min_path: 'zenario__menu/hidden_nav/menu_nodes/panel',
				max_path: 'zenario__menu/hidden_nav/menu_nodes/panel',
				disallow_refiners_looping_on_min_path: false,
				reload_menu_slots: true,
				reload_admin_toolbar: true}};
		
		zenarioAT.action(object);
		
	} else {
		//Otherwise open an Admin Box
		zenarioAB.open('zenario_menu', key);
	}
	
	return false;
};


//If there is an entry (e.g. "Edit Content") in the actions dropdown that needs to be on a draft,
//this function will create a draft of a published page (after a confirm prompt),
//reload the page, then click the entry again.
zenarioA.draft = function(aId, justView) {
	
	if (zenarioAT.focus
	 && zenarioAT.focus.sections
	 && zenarioAT.focus.sections.edit
	 && zenarioAT.focus.sections.edit.buttons
	 && zenarioAT.focus.sections.edit.buttons.start_editing
	 && !zenarioA.hidden(zenarioAT.focus.sections.edit.buttons.start_editing)) {
		
		if (justView && zenarioA.draftJustView) {
			delete zenarioA.draftJustView;
			return true;
		
		} else {
			delete zenarioA.draftJustView;
		}
		
		
		var buttonsHTML = '',
			object = $.extend(true, {}, zenarioAT.focus.sections.edit.buttons.start_editing);
		
		buttonsHTML =
			'<input type="button" class="submit_selected" value="' + htmlspecialchars(object.ajax.confirm.button_message) + '" onclick="zenarioA.draftSetCallback(\'' + htmlspecialchars(aId) + '\'); zenarioAT.action2();"/>';
		
		if (justView) {
			buttonsHTML +=
				'<input type="button" class="submit" value="' + htmlspecialchars(object.ajax.confirm.button_message__just_view) + '" onclick="zenarioA.draftDoCallback(\'' + htmlspecialchars(aId) + '\');"/>';
		}
		
		buttonsHTML +=
			'<input type="button" class="submit" value="' + htmlspecialchars(object.ajax.confirm.cancel_button_message) + '"/>';
		
		delete object.ajax.request.switch_to_edit_mode;
		object.ajax.confirm.message =
			'<!--Button_HTML:' + buttonsHTML + '-->' +
			object.ajax.confirm.message__editing_published;
		
		zenarioA.action(zenarioAT, object);
		return false;
	}
	
	return true;
};

zenarioA.savePageMode = function(async, data) {
	if (!data) {
		data = {};
	}
	data._save_page_mode = zenarioA.pageMode;
	data._save_page_toolbar = zenarioA.toolbar;
	data._save_page_slot_wand = zenarioA.slotWandOn? 1 : '';
	data._save_page_show_grid = zenarioA.showGridOn? 1 : '';
	
	$.ajax({
		type: 'POST',
		url: URLBasePath + 'zenario/admin/quick_ajax.php',
		data: data,
		async: async
	});
};

zenarioA.draftSetCallback = function(aId) {
	zenarioA.savePageMode(false, {_draft_set_callback: aId});
};

zenarioA.draftDoCallback = function(aId) {
	zenarioA.draftJustView = true;
	$('#' + aId).click();
	delete zenarioA.draftJustView;
};



//Admin Actions


zenarioA.hidden = function(tuixObject, checkJsFunction) {
	delete tuixObject._h_js;
	var c;
	
	if (engToBoolean(tuixObject.hidden)) {
		return true;
	
	//Don't show a <call_js_function> button or toolbar that calls a function that does not exist
	} else if (checkJsFunction && (tuixObject.call_js_function && !zenarioA.checkFunctionExists(tuixObject.call_js_function['function'], tuixObject.call_js_function.encapsulated_object))) {
		tuixObject._h_js = true;
		return true;
	
	//Check a JavaScript condition (which used to be called "js_condition" but is now called "visible_if")
	} else if (c = tuixObject.visible_if || tuixObject.js_condition) {
		//try {
			if (!zenarioA.eval(c, tuixObject)) {
				tuixObject._h_js = true;
				return true;
			}
		//} catch (e) {
		//	alert(tuixObject.visible_if + '\n\nwas not a valid JavaScript expression.'); 
		//	tuixObject.visible_if = false;
		//}
	}
	return false;
};



zenarioA.checkActionUnique = function(object) {
	var actions = [],
		test,
		tests = [
			'admin_box',
			'ajax',
			'call_js_function',
			'combine_items',
			'navigation_path',
			'do_csv_export',
			'frontend_link',
			'help',
			'link',
			'panel',
			'pick_items',
			'popout',
			'organizer_quick',
			'upload'];
	
	foreach (tests as test) {
		if (object[tests[test]]) {
			actions.push(tests[test]);
		}
	}
	
	switch (actions.length) {
		case 1:
			return true;
		
		case 0:
			return false;
		
		default:
			console.log(object);
			alert('This navigation or button has multiple actions associated with it:\n\n' + actions + '\n\n(See the console log for the faulty definition.)');
			return false;
	}
};

//Handle what happens when an admin clicks on something that will cause an action; e.g. a button on the admin toolbar or a button on a Storekeeper Panel's Toolbar
zenarioA.action = function(zenarioCallingLibrary, object, itemLevel, branch, link, extraRequests, specificItemRequested) {
	if (zenarioCallingLibrary.uploading) {
		return false;
	}
	
	//Check to see if there is a unique action on this button
		//(But skip this if we're overriding the functionality with a link)
	if (!link) {
		if (!zenarioA.checkActionUnique(object)) {
			return false;
		}
	}
	
	var ajaxMethodCall;
	if (zenarioCallingLibrary.encapName == 'zenarioO') {
		ajaxMethodCall = 'handleOrganizerPanelAJAX';
	} else if (zenarioCallingLibrary.encapName == 'zenarioAT') {
		ajaxMethodCall = 'handleAdminToolbarAJAX';
	} else {
		ajaxMethodCall = 'handleAdminBoxAJAX';
	}
	
	if (!link && object.link) {
		link = object.link;
	
	} else if (!link && object.panel && object.panel._path_here) {
		link = {path: object.panel._path_here};
	}
	
	//In select mode, don't let an admin navigate past a certain point using double-click actions on items
	if (link && zenarioO.pathNotAllowed(link)) {
		return;
	}
	
	//Clear the yourWorkInProgressLastUpdated flag so that the list will be immediately updated after any change
	zenarioO.yourWorkInProgressLastUpdated = 0;
	
	zenarioCallingLibrary.pickItemsItemLevel = itemLevel;
	zenarioCallingLibrary.postPickItemsObject = false;
	
	
	//Uploads and AJAX requests need a path and a requests object
	var url, requests, thing;
	if ((thing = object.upload)
	 || (thing = object.ajax)) {
		
		url =
			'zenario/ajax.php?' +
				'__pluginClassName__=' + thing.class_name +
				'&__path__=' + zenarioCallingLibrary.path +
				'&method_call=' + ajaxMethodCall;
		
		requests = zenarioCallingLibrary.getKey(itemLevel);
		
		if (thing.request) {
			$.extend(requests, thing.request);
		}
		
		if (extraRequests) {
			$.extend(requests, extraRequests);
		}
	}
	
	//Handle uploads first, as they need converting to ajax pop-ups for browsers without html5
	if (object.upload) {
		var fallback = !zenarioA.canDoHTML5Upload(),
			html = '<input type="file" name="Filedata"';
		
		if (object.upload.accept) {
			html += ' accept="' + htmlspecialchars(object.upload.accept) + '"';
		
		//Backwards compatibility with old versions
		} else if (object.upload.fileDesc == 'Images') {
			html += ' accept="image/*"';
		}
		
		if (fallback) {
			html += ' id="zenario_fallback_fileupload"';
		}
		
		if (!fallback && engToBoolean(object.upload.multi)) {
			html += ' multiple';
		}
		html += '/>';
		
		if (!fallback) {
			var $input = $(html);
			$input.change(function() {
				zenarioA.doHTML5Upload(this.files, URLBasePath + url, requests, function(responses) {
					zenarioCallingLibrary.uploadComplete(responses);
				});
				
			});
			$input.click();
			return;
			
		} else {
			//For backwards compatability for browsers without flash, attempt to convert file upload tags into ajax->confirm->form tags
			object = $.extend(true, {}, object);
			requests._html5_backwards_compatibility_hack = 1;
			
			object.ajax = {
				class_name: object.upload.class_name,
				confirm: {
					message: html,
					html: true,
					button_message: phrase.upload,
					cancel_button_message: phrase.cancel,
					form: true
				},
				request: requests
			};
		}
	}
	
	
	if (object.call_js_function) {
		if (object.call_js_function.encapsulated_object) {
			window[object.call_js_function.encapsulated_object][object.call_js_function['function']](object, zenarioCallingLibrary.getKeyId());
		} else {
			window[object.call_js_function['function']](object, zenarioCallingLibrary.getKeyId());
		}
	
	} else if (link) {
		//If a link is set, go to the link
		if (engToBoolean(link.unselect_items)) {
			zenarioO.selectedItems = {};
			zenarioO.saveSelection();
		}
		
		zenarioO.go(
			link.path,
			branch,
			link.refiner? {'id': itemLevel? zenarioO.getKeyId() : '', 'name': link.refiner} : undefined,
			undefined, undefined, undefined, undefined, specificItemRequested);
	
	} else if (object.frontend_link) {
		var id, item,
			frontend_link = object.frontend_link,
			sameWindow = false;
		
		if (itemLevel && (id = zenarioCallingLibrary.getKeyId(true)) && (item = zenarioCallingLibrary.focus.items[id]) && item.frontend_link) {
			frontend_link = item.frontend_link;
		}
		
		//For Storekeeper, attempt to insert the return link,
		//and then open it in the same window if we successfully inserted it
		if (zenarioCallingLibrary.encapName == 'zenarioO') {
			frontend_link = zenarioO.parseReturnLink(frontend_link);
			
			if (frontend_link.indexOf('&zenario_sk_return=') != -1) {
				sameWindow = true;
			}
		}
		
		if (sameWindow || frontend_link.substr(0, 25) == 'zenario/admin/welcome.php') {
			zenario.goToURL(zenario.addBasePath(frontend_link));
		
		} else if (frontend_link.indexOf('://') !== -1) {
			window.open(frontend_link);
		
		} else if (zenarioCallingLibrary.encapName == 'zenarioAT') {
			zenario.goToURL(zenario.addBasePath(frontend_link));
		
		} else if (window.opener && !window.opener.zenarioO) {
			window.opener.location = zenario.addBasePath(frontend_link);
		
		} else if (window.storekeeperChildWindow && !window.storekeeperChildWindow.closed) {
			window.storekeeperChildWindow.location = zenario.addBasePath(frontend_link);
		
		} else {
			window.storekeeperChildWindow = window.open(zenario.addBasePath(frontend_link));
		}
	
	} else if (object.navigation_path) {
		var id, item, pos,
			navigation_path = object.navigation_path;
		
		if (itemLevel && (id = zenarioCallingLibrary.getKeyId(true)) && (item = zenarioCallingLibrary.focus.items[id]) && item.navigation_path) {
			navigation_path = item.navigation_path;
		}
		
		if (zenarioO.init) {
			zenarioO.go(navigation_path, -1);
		} else {
			zenario.goToURL(zenario.addBasePath('zenario/admin/organizer.php#' + navigation_path));
		}
	
	} else if (object.admin_box) {
		zenarioA.nowDoingSomething('loading');
		var key = ifNull(zenarioCallingLibrary.getKey(itemLevel), {});
		
		if (object.admin_box.key) {
			foreach (object.admin_box.key as var r) {
				key[r] = object.admin_box.key[r];
			}
		}
		
		zenarioAB.open(object.admin_box.path, key, object.admin_box.tab, object.admin_box.values);
	
	} else if (object.popout) {
		var id, item, title, usage;
		var popout = $.extend(true, {}, object.popout);
		
		if (itemLevel && (id = zenarioCallingLibrary.getKeyId(true)) && (item = zenarioCallingLibrary.focus.items[id])) {
			if (item.popout) {
				popout = $.extend(popout, item.popout);
			}
			
			if (!popout.title && zenarioCallingLibrary.popoutLabelFormat) {
				popout.title = zenarioCallingLibrary.applyMergeFields(zenarioCallingLibrary.popoutLabelFormat, false, id);
			}
		}
			
		if (item && item.href && popout.href === undefined) {
			popout.href = item.href;
		
		} else if (item && item.frontend_link && popout.href === undefined) {
			popout.href = zenarioO.parseReturnLink(item.frontend_link, '_sk_preview=1');
		
		} else if (item && popout.href) {
			popout.href += popout.href.indexOf('?') === -1? '?' : '&';
			
			if (item.checksum) {
				popout.href += 'c=' + encodeURIComponent(item.checksum);
				
				if (id == 1*id) {
					popout.href += '&id=' + id;
				}
			
			} else if (id == 1*id) {
				popout.href += 'id=' + id;
			
			} else {
				popout.href += 'c=' + encodeURIComponent(id);
			}
			
			if (usage = ifNull(zenarioCallingLibrary.getKey().usage, item.usage)) {
				popout.href += '&usage=' + encodeURIComponent(usage);
			}
			
			if (item.filename) {
				popout.href += '&filename=' + encodeURIComponent(item.filename);
			}
		}
		
		if (popout.href) {
			popout.href = zenario.addBasePath(popout.href);
		}
		
		if (item && item.width) {
			popout.initialWidth = item.width;
			//popout.innerWidth = item.width;
			//popout.initialWidth = 1*item.width + (zenarioA.colorboxInterfaceWidth = 72)
		}
		if (item && item.height) {
			popout.initialHeight = item.height;
			//popout.innerHeight = item.height;
			//popout.initialHeight = 1*item.height + (zenarioA.colorboxInterfaceHeight = 72)
		}
		
		if (popout.css_class) {
			var cssClasses = ('' + popout.css_class).split(' ');
			popout.onOpen = function() { zenario.addClassesToColorbox(cssClasses); };
			popout.onClosed = function() { zenario.removeClassesToColorbox(cssClasses); };
		}
		
		if (popout.iframe && popout.width === undefined) {
			popout.width = Math.min(Math.floor($(window).width() * 0.93), 1200);
		}
		if (popout.iframe && popout.height === undefined) {
			popout.height = Math.floor($(window).height() * 0.9);
		}
		
		if (popout.preloading === undefined) {
			popout.preloading = false;
		}
		
		$.colorbox(popout);
	
	} else if (object.organizer_quick) {
		var id,
			path = object.organizer_quick.path;
		
		if (itemLevel && (id = zenarioCallingLibrary.getKeyId(true))) {
			path = path.replace(/\[\[id\]\]/g, id);
		}
		
		//zenarioA.SKQ = function(path, targetPath, minPath, maxPath, disallowRefinersLoopingOnMinPath, slotName, instanceId, reloadOnChanges) {
		zenarioA.SKQ(
			path,
			
			//We don't need the target path variable when opening like this, but as a little hack I'll
			//fill it with something anyway.
			object.organizer_quick.min_path || object.organizer_quick.max_path || object.organizer_quick.path,
			
			object.organizer_quick.min_path, object.organizer_quick.max_path,
			engToBoolean(object.organizer_quick.disallow_refiners_looping_on_min_path),
			undefined, undefined, zenarioCallingLibrary.encapName);
	
	} else if (object.pick_items && !itemLevel) {
		
		if (object.pick_items.ajax) {
			zenarioCallingLibrary.postPickItemsObject = object.pick_items;
		}
		
		//Use storekeeper in select mode to combine two items
		zenarioCallingLibrary.actionTarget =
			'zenario/ajax.php?' +
				'__pluginClassName__=' + object.pick_items.class_name +
				'&__path__=' + zenarioCallingLibrary.path +
				'&method_call=' + ajaxMethodCall;
		zenarioCallingLibrary.actionRequests = zenarioCallingLibrary.getKey(itemLevel);
		
		if (object.pick_items.request) {
			$.extend(zenarioCallingLibrary.actionRequests, object.pick_items.request);
		}
		
		zenarioA.SK(
			zenarioCallingLibrary.encapName, 'pickItems',
			object.pick_items.multiple_select,
			object.pick_items.path,
			object.pick_items.target_path,
			object.pick_items.min_path,
			object.pick_items.max_path,
			object.pick_items.disallow_refiners_looping_on_min_path,
			true,
			object.pick_items.one_to_one_choose_phrase,
			object.pick_items.one_to_many_choose_phrase,
			undefined, undefined, undefined, undefined, undefined,
			zenarioCallingLibrary.getLastKeyId(true),
			object.pick_items.allow_no_selection,
			object.pick_items.one_to_no_selection_choose_phrase,
			undefined, undefined,
			object.pick_items);
	
	} else if (object.combine_items && itemLevel) {
		
		if (object.combine_items.ajax) {
			zenarioCallingLibrary.postPickItemsObject = object.combine_items;
		}
		
		//Use storekeeper in select mode to combine two items
		zenarioCallingLibrary.actionTarget =
			'zenario/ajax.php?' +
				'__pluginClassName__=' + object.combine_items.class_name +
				'&__path__=' + zenarioCallingLibrary.path +
				'&method_call=' + ajaxMethodCall;
		zenarioCallingLibrary.actionRequests = zenarioCallingLibrary.getKey(itemLevel);
		
		if (object.combine_items.request) {
			$.extend(zenarioCallingLibrary.actionRequests, object.combine_items.request);
		}
		
		zenarioA.SK(
			zenarioCallingLibrary.encapName, 'pickItems',
			object.combine_items.multiple_select,
			object.combine_items.path,
			object.combine_items.target_path,
			object.combine_items.min_path,
			object.combine_items.max_path,
			object.combine_items.disallow_refiners_looping_on_min_path,
			true,
			zenarioCallingLibrary.itemsSelected > 1 && object.combine_items.many_to_one_choose_phrase?
				object.combine_items.many_to_one_choose_phrase
			  : object.combine_items.one_to_one_choose_phrase,
			zenarioCallingLibrary.itemsSelected > 1 && object.combine_items.many_to_many_choose_phrase?
				object.combine_items.many_to_many_choose_phrase
			  : object.combine_items.one_to_many_choose_phrase,
			undefined, undefined, undefined, undefined, undefined,
			zenarioCallingLibrary.getKeyId(false),
			object.combine_items.allow_no_selection,
			zenarioCallingLibrary.itemsSelected > 1 && object.combine_items.many_to_no_selection_choose_phrase?
				object.combine_items.many_to_no_selection_choose_phrase
			  : object.combine_items.one_to_no_selection_choose_phrase,
			undefined, undefined,
			object.combine_items);
	
	} else if (object.ajax) {
		
		//Run an AJAX function
		zenarioCallingLibrary.actionTarget = url;
		zenarioCallingLibrary.actionRequests = requests;
		
		//If a confirm is set, set up a pop-up floating box box to ask the admin before carrying out the action
		if (object.ajax.confirm) {
			
			var isHTML = engToBoolean(object.ajax.confirm.html);
			var isDownload = engToBoolean(object.ajax.confirm.download);
			
			//Get the message/html for this box
			var message;
			
			//Part of the backwards compatability hack for browsers without HTML 5 uploads above
			if (object.upload) {
				message = object.ajax.confirm.message;
			
			//Use the multiple select message if more than one item is selected
			} else if (itemLevel && zenarioCallingLibrary.itemsSelected > 1) {
				message = ifNull(object.ajax.confirm.multiple_select_message, object.ajax.confirm.message);
			
			//Apply mergefields to populate the message with information from the selected row
			//Note that if they're not going to be escaped below then they need to be here.
			} else if (object.ajax.confirm.message) {
				message = zenarioCallingLibrary.applyMergeFields(object.ajax.confirm.message, isHTML);
			}
			
			//If no message is set, try and get one using an AJAX call
			if (!message) {
				message = zenario.nonAsyncAJAX(URLBasePath + zenarioCallingLibrary.actionTarget + zenario.urlRequest(zenarioCallingLibrary.actionRequests), false);
			}
			
			//If this is a download, add the current search/sorting information in,
			//just in case the download should differ depending on the current view
			if (isDownload) {
				zenarioCallingLibrary.actionRequests._download = 1;
				
				if (zenarioCallingLibrary.searchTerm !== undefined) {
					zenarioCallingLibrary.actionRequests._search = zenarioCallingLibrary.searchTerm;
				}
				
				if (zenarioCallingLibrary.prefs[zenarioCallingLibrary.path] && zenarioCallingLibrary.prefs[zenarioCallingLibrary.path].sortBy) {
					zenarioCallingLibrary.actionRequests._sort_col = zenarioCallingLibrary.prefs[zenarioCallingLibrary.path].sortBy;
					zenarioCallingLibrary.actionRequests._sort_desc = zenarioCallingLibrary.prefs[zenarioCallingLibrary.path].sortDesc? 1 : 0;
				} else {
					zenarioCallingLibrary.actionRequests._sort_col = zenarioCallingLibrary.labelTag;
				}
			}
			
			//Start generating the box.
			//If there is a form, the message should be surrounded by <form></form> tags.
			var html = '';
			var buttonsHTML = '';
			if (isDownload || object.upload) {
				html += 
					'<form id="jqmodal_form" action="' + htmlspecialchars(URLBasePath + zenarioCallingLibrary.actionTarget + '&_sk_form_submission=1') + '"' +
						' onsubmit="get(\'preloader_circle\').style.visibility = \'visible\';"' +
						' target="zenario_iframe" method="post"' + (object.upload? ' enctype="multipart/form-data"' : '') + '>';
				
				foreach (zenarioCallingLibrary.actionRequests as var r) {
					html += '<input type="hidden" value="' + htmlspecialchars(zenarioCallingLibrary.actionRequests[r]) + '" name="' + htmlspecialchars(r) + '"/>';
				}
				
				if (!isHTML) {
					message = htmlspecialchars(message, true);
					isHTML = true;
				}
			}
			
			html += message;
			
			//If there is a form, the confirm button should submit the form...
			if (isDownload || object.upload) {
				html += '</form>';
				
				buttonsHTML =
					'<input type="button" class="submit_selected" value="' + htmlspecialchars(object.ajax.confirm.button_message) + '" onclick="get(\'jqmodal_form\').submit();"/>';
			//...otherwise it should launch a syncronous AJAX request.
			} else {
				buttonsHTML =
					'<input type="button" class="submit_selected" value="' + htmlspecialchars(object.ajax.confirm.button_message) + '" onclick="' + zenarioCallingLibrary.encapName + '.action2();"/>';
			}
			
			buttonsHTML +=
				'<input type="button" class="submit" value="' + htmlspecialchars(object.ajax.confirm.cancel_button_message) + '"/>';
			
			
			zenarioA.showMessage(html, buttonsHTML, object.ajax.confirm.message_type, undefined, !isHTML);
			
			//If this was a fileupload in fallback mode, try to click the fileupload prompt straight away...
			if (object.upload) {
				//...except on IE, where this causes an error :(
				if (!zenario.browserIsIE()) {
					$('#zenario_fallback_fileupload').click();
				}
			}
		
		//If there is no confirmation then do the action straight away
		} else {
			zenarioCallingLibrary.action2();
		}
		
	
	} else if (object.help) {
		var messageType = ifNull(object.help.message_type, 'question'),
			htmlEscapeMessage = !engToBoolean(object.help.html);
		
		if (object.help.message) {
			zenarioA.showMessage(object.help.message, true, messageType, false, htmlEscapeMessage);
		}
		
	
	} else if (engToBoolean(object.do_csv_export)) {
		zenarioCallingLibrary.doCSVExport();
	}
};

zenarioA.isHtaccessWorking = function() {
	return zenario.nonAsyncAJAX(URLBasePath + 'zenario/includes/test_files/is_htaccess_working.css', true) == 'Yes it is';
};

zenarioA.checkFunctionExists = function(functionName, encapName) {
	if (encapName) {
		return window[encapName] && typeof window[encapName][functionName] == 'function';
	} else {
		return typeof window[functionName] == 'function';
	}
};



//Utility function for the zenarioXX.sortYYY() series of function
//Given two arrays (each of which represents an element), say which should be first
zenarioA.sortArray = function(a, b) {
	
	//Check to see if they're identical
	if (a[1] === b[1]) {
		return 0;
	
	} else {
		var aNumeric = a[1] == 1*a[1],
			bNumeric = b[1] == 1*b[1];
	
		//Try a numeric comparision
		if (aNumeric && bNumeric) {
			return 1*a[1] < 1*b[1]? -1 : 1;
	
		//Put any numeric values before strings
		} else if (aNumeric || bNumeric) {
			return aNumeric? -1 : 1;
		
		//Otherwise try a string comparision
		} else {
			return ('' + a[1]).toUpperCase() < ('' + b[1]).toUpperCase()? -1 : 1;
		}
	}
};



zenarioA.readData = function(data, setSessionStorageURL, setSessionStorageRequest) {
	try {
		data = JSON.parse(data);
		
		if (typeof data != 'object') {
			throw 0;
		}
	} catch (e) {
		//Display an error message if the data couldn't be parsed
		zenarioA.showMessage(data, true, 'error', false, true);
		//zenarioA.showMessage(message, buttonsHTML, messageType, modal, htmlEscapeMessage)
		
		//Close the AJAX loader if it was open
		zenarioA.hideAJAXLoader();
		
		return false;
	}
	
	if (setSessionStorageURL) {
		zenario.setSessionStorage(data, setSessionStorageURL, setSessionStorageRequest, true);
	}
	
	return data;
};


//Default error handler for lost AJAX requests
	//Note that there have been some issues with AJAX request submitted just before a page navigation being counted as timeouts
	//To get round this, firstly we try to detect page navigation and then don't show a message after this has happened
	//Secondly there's a slight time delay inserted on the 404 errors using setTimeout(), as a work around to try and prevent any race-conditions

zenarioA.AJAXErrorHandler = function(resp, statusType, statusText) {
	if (!zenarioA.unloaded) {
		var msg = '<!--Button_HTML:-->';
		
		if (statusText) {
			msg += '<h1><b>' + htmlspecialchars(resp.status + ' ' + statusText) + '</b></h1>';
		}
		
		if (resp.status == 404) {
			msg += '<p>' +  (zenario.showDevTools? phrase.error404Dev : phrase.error404) + '</p>';
		
		} else if (resp.status == 500) {
			msg += '<p>' +  (zenario.showDevTools? phrase.error500Dev : phrase.error500) + '</p>';
		
		} else if (resp.status == 0 || statusType == 'timeout') {
			msg += '<p>' +  (zenario.showDevTools? phrase.errorTimedOutDev : phrase.errorTimedOut) + '</p>';
		}
		
		if (resp.responseText) {
			msg += '<div>' + resp.responseText + '</div>';
		}
		
		msg +=
			'<p style="display: none;">' +
				'Last URL accessed: ' +
				htmlspecialchars(zenario.checkLastUrl) +
			'</p>';
		
		if (resp.status == 0 || statusType == 'timeout') {
			setTimeout(function() { zenarioA.showMessage(msg, true, 'error'); }, 750);
		} else {
			zenarioA.showMessage(msg, true, 'error');
		}
	}
};
$.ajaxSetup({error: zenarioA.AJAXErrorHandler});

zenarioA.attempts = {};
zenarioA.attemptNum = 0;
zenarioA.keepTrying = function(fun, attempt) {
	if (attempt === undefined) {
		attempt = ++zenarioA.attemptNum;
		zenarioA.attempts[attempt] = true;
	
	} else if (!zenarioA.attempts[attempt]) {
		return;
	}
	
	fun(attempt);
	
	setTimeout(function() {
		zenarioA.keepTrying(fun, attempt);
	}, 30000);
};

zenarioA.stopTrying = function(attempt) {
	if (!zenarioA.attempts[attempt]) {
		return false;
	
	} else {
		delete zenarioA.attempts[attempt];
		return true;
	}
};



zenarioA.onunload = function(e) {
	if (!window.onpagehide || !e.persisted) {
		zenarioA.unloaded = true;
		zenarioA.closeFloatingBox();
	}
};

if (window.onpagehide) {
	window.addEventListener("pagehide", zenarioA.onunload, false);
} else {
	window.onunload = zenarioA.onunload;
}


//Stop event propigation
zenarioA.stop = function(e) {
	e = (e || event);
	
	if (e && e.stopPropagation) {
		e.stopPropagation();
	}
	
	return false;
};


zenarioA.stopDefault = function(e) {
	e = (e || event);
	
	if (e && e.stopPropagation) {
		e.stopPropagation();
	}
	if (e && e.preventDefault) {
		e.preventDefault();
	}
	
	return false;
};


//Try to disable the ability to navigate away from the page by dragging a file or an image into the browser from the filesystem
zenarioA.stopFileDragDrop = function(e) {
	var fileUpload = false;
	if (e.dataTransfer && e.dataTransfer.types) {
		if (typeof e.dataTransfer.types.contains == 'function') {
			fileUpload = e.dataTransfer.types.contains('Files');
		
		} else {
			fileUpload = (('' + e.dataTransfer.types) == 'Files') || (e.dataTransfer.types[0] && ('' + e.dataTransfer.types[0]) == 'Files');
		}
	}
	
	if (fileUpload) {
		e.stopPropagation();
		e.preventDefault();
	}
};

zenarioA.disableFileDragDrop = function(el) {
	if (zenarioA.canDoHTML5Upload()) {
		el.addEventListener('drop', zenarioA.stopDefault, false);
		el.addEventListener('dragenter', zenarioA.stopDefault, false);
		el.addEventListener('dragover', zenarioA.stopDefault, false);
		el.addEventListener('dragexit', zenarioA.stopDefault, false);
	}
};

zenarioA.checkCookiesEnabled = function() {
	return zenario.nonAsyncAJAX(URLBasePath + 'zenario/admin/quick_ajax.php?_check_cookies_enabled=1&no_cache=1')
		|| zenario.nonAsyncAJAX(URLBasePath + 'zenario/admin/quick_ajax.php?_check_cookies_enabled=1&no_cache=1');
};

$(document).ready(function() {
	zenarioA.disableFileDragDrop(document.body);
});

//If any slots are being edited, set a warning message for if an admin tries to leave the page 
window.onbeforeunload = zenarioA.onbeforeunload;




})(
	URLBasePath,
	window, document,
	zenario, zenarioA, zenarioTab, zenarioAT,
	zenario.get, zenario.engToBoolean, zenario.htmlspecialchars, zenario.ifNull, zenario.jsEscape, zenarioA.phrase);
