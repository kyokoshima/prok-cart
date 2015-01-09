<?php
require_once 'app/constants.php';

class SettingConfig {

	function show(){
		$content = file_get_contents(CONFIG);
		include (PATH_ROOT.'/view/setting_config.html');
	}

	function confirm(){

		$org = file(CONFIG);
		// $new = $_REQUEST['content'];
		$new = explode("\n", $_REQUEST['content']);
		require_once 'Text/Diff.php';
		require_once 'Text/Diff/Renderer/inline.php';
		$diff = new Text_Diff('auto', array($org, $new));
		$renderer = new Text_Diff_Renderer_inline();

		$content_diff = $renderer->render($diff);
		echo '<pre>';
		var_dump($content_diff);
		echo '</pre>';
	}

	function complete(){

	}
}

$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : null;

$sc = new SettingConfig();
switch ($step) {
	case 'confirm':
		$sc->confirm();
		break;
	case 'complete':
		break;
	default:
		$sc->show();
		break;
}
