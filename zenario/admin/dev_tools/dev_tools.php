<?php

require '../../adminheader.inc.php';
if (!checkPriv('_PRIV_VIEW_DEV_TOOLS')) {
	exit;
}

//require CMS_ROOT. 'zenario/admin/dev_tools/dev_tools.inc.php';

$gzf = setting('compress_web_pages')? '?gz=1' : '?gz=0';
$gz = setting('compress_web_pages')? '&amp;gz=1' : '&amp;gz=0';





echo
'<!DOCTYPE HTML>
<html>
<head>
	<title>';
	
		switch (get('mode')) {
			case 'zenarioAB';
				echo adminPhrase('Dev Tools: Specification for Admin Box');
				break;
			case 'zenarioAT';
				echo adminPhrase('Dev Tools: Specification for Admin Toolbar');
				break;
			case 'zenarioO';
				if (engToBoolean(get('skMap'))) {
					echo adminPhrase('Dev Tools: Specification for Storekeeper Map');
				} else {
					echo adminPhrase('Dev Tools: Specification for Storekeeper Panel');
				}
				break;
			default:
				exit;
		}

echo '
	</title>';

$prefix = '../../';
CMSWritePageHead($prefix, false, false);


getCSSJSCodeHash();
$v = ifNull(setting('css_js_version'), ZENARIO_CMS_VERSION);

echo '
	<link rel="stylesheet" type="text/css" href="../../styles/dev_tools.css?v=', $v, '" media="screen"/>';


echo '</head>';
CMSWritePageBody('', false);
CMSWritePageFoot($prefix, false, false, false);

echo '
<script type="text/javascript" src="../../libraries/mit/js-yaml/js-yaml.js?v=', $v, '"></script>
<script type="text/javascript" src="../../libraries/public_domain/tv4/tv4.js?v=', $v, '"></script>
<script type="text/javascript" src="../../libraries/public_domain/tv4/customised_messages.js?v=', $v, '"></script>';

/*
echo '
<script type="text/javascript" src="../../libraries/mit/toastr/toastr.min.js?v=', $v, '"></script>
<script type="text/javascript">
	window.zenarioAdminHasZipPerms = ', engToBoolean(checkPriv('_PRIV_VIEW_TEMPLATE_FAMILY')), ';
	window.zenarioAdminHasSavePerms = ', engToBoolean(checkPriv('_PRIV_EDIT_TEMPLATE_FAMILY')), ';
</script>';
*/

?>


<div id="toolbar"></div>
<div id="editor"></div>
<div id="sidebar" class="sidebarEmpty">
	<div class="vscroll" id="sidebar_inner">
	</div>
</div>


<?php




switch (get('mode')) {
	case 'zenarioAB':
		$schemaName = 'admin_box_schema';
		break;
	
	case 'zenarioAT':
		$schemaName = 'admin_toolbar_schema';
		break;
	
	case 'zenarioO':
		$schemaName = 'organizer_schema';
		break;
	
	default:
		exit;
}


$schema = zenarioReadTUIXFile(CMS_ROOT. 'zenario/api/'. $schemaName. '.yaml');
unset($schema['common_definitions']);


echo '
<script type="text/javascript" src="doc_tools.js?v=', $v, '"></script>
<script type="text/javascript" src="../../js/dev_tools.min.js?v=', $v, '"></script>
<script type="text/javascript">';

if (checkPriv('_PRIV_SAVE_DEV_TOOLS')) {
	echo '
	zenario.saveDevTools = true;';
}

echo '
	var schema = ', json_encode($schema), ';
	devTools.init(\'', jsEscape(get('mode')), '\', \'', jsEscape($schemaName), '\', schema, ', engToBoolean(get('skMap')), ');
</script>
</body>
</html>';