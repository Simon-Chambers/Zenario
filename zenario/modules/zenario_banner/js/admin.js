(function(zenario, zenarioA, zenario_banner) {

zenario_banner.open = function(containerId, editorId, delayed) {
	
	if (!zenarioAT.loaded && !delayed) {
		zenarioAT.runOnInit.push(function() {
			zenario_banner.open(containerId, editorId, true);
		});
		return;
	}
	
	var $input = $('<input type="text" id="banner_title_input_box" placeholder="'+zenario_banner.phrase('Title')+'"/>'), 
		$title = $('div#'+containerId+' div.banner_title h2'), 
		p, 
		props = ['color', 'background', 'font-family', 'text-size', 'font-weight', 'font-size', 'font-style']; 
		
	for (p in props) {
		$input.css(props[p], $title.css(props[p])); 
	}
	$input.val($title.text().replace(/^\s+/, '').replace(/\s+$/, '')); 
	$input.css('border', 'none'); $title.replaceWith($input);
	
	zenarioA.getSkinDesc();
	$('div#' + editorId).tinymce({
		script_url: URLBasePath + zenarioA.tinyMCEPath,

		plugins: ["advlist autolink lists link charmap hr anchor",
		"searchreplace code",
		"nonbreaking zenario_save contextmenu directionality",
		"paste autoresize"],
		
		browser_spellcheck: true,
		
		menu: {
			file: {title: 'File', items: ''},
			edit: {title: 'Edit', items: 'undo redo | cut copy paste | selectall'},
			insert: {title: 'Insert', items: 'link | anchor hr charmap'},
			view: {title: 'View', items: ''},
			format: {title: 'Format', items: 'bold italic underline strikethrough superscript subscript' + (zenarioA.skinDesc.style_formats? ' | formats' : '') + ' | removeformat'},
			tools: {title: 'Tools', items: 'searchreplace | code'}
		},
		
		toolbar: 'undo redo | link unlink | bold italic underline | removeformat' + (zenarioA.skinDesc.style_formats? ' | styleselect' : '') + ' | fontsizeselect | formatselect | numlist bullist | outdent indent | alignleft aligncenter alignright alignjustify | save save_and_close cancel',
		statusbar: false,
		
		autoresize_min_height: 100,
		paste_preprocess: zenarioA.tinyMCEPasteRreprocess,
		
		inline: true,
		zenario_inline_ui: true,
		
		inline_styles: false,
		allow_events: true,
		allow_script_urls: false,
		convert_urls: false,
		
		style_formats: zenarioA.skinDesc.style_formats,
		file_browser_callback: zenario_banner.SK
	});
	
	window.zenarioEditorSave = function(editor) {
		zenario_banner.saveViaAJAX(get(editor.id), true);
	};
	window.zenarioEditorSaveAndContinue = function(editor) {
		zenario_banner.saveViaAJAX(get(editor.id));
	};
	window.zenarioEditorCancel = function(editor) {
		zenario_banner.close(get(editor.id));
	};
}



zenario_banner.saveViaAJAX = function(el, close, confirm, confirmChoice) {
	var editorId;
	
	if (typeof el == 'string') {
		editorId = el;
		el = get(el);
	} else {
		editorId = el.id;
	}

	var containerId = zenario.getContainerIdFromEl(el);
	var slotName = zenario.getSlotnameFromEl(el);
	
	var saveLink = zenario.get(containerId + '_save_link').value;
	var content = $('div#' + editorId).tinymce().getContent();
	
	var error = zenario_banner.AJAX(
		saveLink,
		{
			_zenario_save_content_: 1,
			_sync_summary: zenario.engToBoolean(confirm && confirmChoice),
			content__content: content,
			content__title: $('div#'+containerId+' #banner_title_input_box').val()
		}, true);
	
	if (error) {
		zenario_banner.floatingMessage(error, true, 'error');
	} else if (close) {
		zenario_banner.doClose(slotName);
	} else {
		zenarioA.notification(zenarioA.phrase.contentSaved);
	}
}


zenario_banner.SK = function(field_name, url, type, win) {
	
	zenario_banner.win = win;
	zenario_banner.field_name = field_name;
	
	if (type == 'file') {
		zenarioA.SK('zenario_banner', 'setLinkURL', false,
						'zenario__content/nav/content/panel',
						'zenario__content/nav/content/panel',
						'zenario__content/nav/content/panel',
						'zenario__content/nav/content/panel',
						false, undefined, undefined, undefined, true);
	
	}
}

zenario_banner.lastFieldValue = '';
zenario_banner.setEditorField = function(value, el, onlyIfEmpty) {
	if (el === undefined) {
		el = zenario_banner.win.document.getElementById(zenario_banner.field_name);
	}
	
	if (onlyIfEmpty) {
		if (el.value !== '' && el.value != zenario_banner.lastFieldValue) {
			return;
		}
		zenario_banner.lastFieldValue = value;
	}
	
	el.value = value;
	zenario.fireChangeEvent(el);
}

zenario_banner.setLinkURL = function(path, key, row) {
	//Get the URL via an AJAX program
	key.getItemURL = 1;
	var URL = zenario.pluginClassAJAX('zenario_common_features', key, true);
	
	zenario_banner.setEditorField(row.title, $('.mce-panel input.mce-link_text_to_display')[0], true);
	zenario_banner.setEditorField(URL);
}



zenario_banner.close = function(el) {

	var containerId = zenario.getContainerIdFromEl(el);
	var slotName = zenario.getSlotnameFromEl(zenario.get(containerId));
	
	zenario_banner.floatingMessage(
		zenarioA.phrase.closeEditorWarning,
		'<input type="button" class="submit_selected" value="' + zenarioA.phrase.abandonChanges + '" onclick="zenarioA.closeFloatingBox(); zenario_banner.doClose(\'' + slotName + '\');" />' +
		'<input type="button" class="submit" value="' + zenarioA.phrase.cancel + '"/>',
		true);
}

zenario_banner.doClose = function(slotName) {
	$('#zenario_editor_toolbar').html('').hide();
	zenario_banner.refreshPluginSlot(slotName, undefined, false);
	
	//Reload the Admin Toolbar
	zenarioAT.init();
}



})(zenario, zenarioA, zenario_banner);