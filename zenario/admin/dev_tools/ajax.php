<?php

require '../../adminheader.inc.php';
if (!checkPriv('_PRIV_VIEW_DEV_TOOLS')) {
	exit;
}

switch (get('mode')) {
	case 'zenarioAB';
		$type = 'admin_boxes';
		break;
	case 'zenarioAT';
		$type = 'admin_toolbar';
		break;
	case 'zenarioO';
		$type = 'storekeeper';
		break;
	default:
		exit;
}

if (!empty($_GET['load_tuix_files']) && $data = json_decode($_GET['load_tuix_files'], true)) {
	if (!empty($data) && is_array($data)) {
		foreach ($data as $paths => &$dataForFile) {
			$paths = explode('.', $paths);
			
			
			if ((validateScreenName($module = arrayKey($paths, 0)))
			 && (validateScreenName($file = arrayKey($paths, 1)))
			 && (validateScreenName($ext = arrayKey($paths, 2)))
			 && ($path = moduleDir($module, 'tuix/'. $type. '/'. $file. '.'. $ext, true))) {
			
				if ($tags = zenarioReadTUIXFile($path)) {
					$dataForFile = array(
						'tags' => $tags,
						'source' => file_get_contents($path));
				}	
			}
		}
		
		header('Content-Type: text/javascript; charset=UTF-8');
		jsonEncodeForceObject($data);
	}
}
