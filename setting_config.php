<?php
require_once 'app/constants.php';

class SettingConfig {

	function show(){
		$content = file_get_contents(CONFIG);
		include (PATH_ROOT.'/view/setting_config.html');
	}

	function confirm(){

		$org = explode("\n", trim(file_get_contents(CONFIG)));
		// echo count($org);
		// $new = $_REQUEST['content'];
		$new = explode("\n", trim($_REQUEST['content']));
		require_once 'Text/Diff.php';
		require_once 'Text/Diff/Renderer/inline.php';
		$diff = new Text_Diff('auto', array($org, $new));

		// require_once 'Text/Diff/Renderer/unified.php';
		// $ur = new Text_Diff_Renderer_unified();
		// $urc = $ur->render($diff);
		// echo '<pre>';
		// var_dump ($urc);
		// echo '</pre>';
		if ($diff->isEmpty()){
			$message = '変更点がありません';
			$content = file_get_contents(CONFIG);
			include (PATH_ROOT.'/view/setting_config.html');
			return;
		}
		$renderer = new Text_Diff_Renderer_inline();

		$content_diff = $renderer->render($diff);
		$content = $_REQUEST['content'];
		// echo '<pre>';
		// var_dump($content_diff);
		// echo '</pre>';
		include (PATH_ROOT.'/view/setting_config_confirm.html');
	}

	function complete(){
		$content = html_entity_decode($_REQUEST['content']);
		$timestamp = date('Ymd-His');
		$org_file = CONFIG;
		$new_file = "${org_file}.${timestamp}";
		if (!copy($org_file, $new_file)){
			$message = 'ファイルのコピーに失敗しました';
		} else if (!file_put_contents($org_file, $content)){
			$message = 'config.iniの上書きに失敗しました';
		} else {
			$message[] = '更新されました。';
			$message[] = "変更前の内容は${new_file}へ保存されました。";
		}
		include (PATH_ROOT.'/view/setting_config.html');
	}
}

if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])
	or $_SERVER['PHP_AUTH_USER'] !== 'admin'
	or $_SERVER['PHP_AUTH_PW'] !== '7941'){
	header('WWW-Authenticate: Basic realm="Enter username and password."');
	header('Content-Type: text/plain; charset=utf-8');
	die();
}

$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : null;

$sc = new SettingConfig();
switch ($step) {
	case 'confirm':
		$sc->confirm();
		break;
	case 'complete':
		$sc->complete();
		break;
	default:
		$sc->show();
		break;
}
