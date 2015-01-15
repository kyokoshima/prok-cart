<?php
require_once 'app/constants.php';
class SettingMail {

	function show_mail($target){
		if ($target == 'user') {
			$title = '購入者メール内容編集';
			$mail_content = file_get_contents('config/mail_for_user');
		} else if ($target == 'admin') {
			$title = '管理者メール内容編集';
			$mail_content = file_get_contents('config/mail_for_admin');			
		}
		include (PATH_ROOT.'/view/setting_mail.html');
	}

	function confirm_mail($target){
		// echo '<pre>';
		// var_dump($_REQUEST['mail_content']);
		// echo '</pre>';
		// die();


		if ($target == 'user') {
			$mail_file = 'config/mail_for_user';
			$org = file($mail_file);
			$title = '購入者メール変更確認';
		} else if ($target == 'admin') {
			$mail_file = 'config/mail_for_admin';
			$org = file($mail_file);
			$title = '管理者メール変更確認';
		}
		$new = explode("\n", $_REQUEST['mail_content']);
		// echo '<pre>';
		// var_dump($org);
		// var_dump($new);
		// echo '</pre>';
		require_once 'Text/Diff.php';
		require_once 'Text/Diff/Renderer/inline.php';
		$diff = new Text_Diff('auto', array($org, $new));

		if ($diff->isEmpty()){
			$message = '変更点がありません';
			$mail_content = file_get_contents($mail_file);
			include (PATH_ROOT.'/view/setting_mail.html');
			return;
		}
		$renderer = new Text_Diff_Renderer_inline();

		// echo '<pre>';
		// var_dump($renderer->render($diff));
		// echo '</pre>';
		$mail_content_diff = $renderer->render($diff);
		$mail_content = $_REQUEST['mail_content'];
		include (PATH_ROOT.'/view/setting_mail_confirm.html');
	}

	/**
	* メール本文を更新する
	*/
	function complete_mail($target){
		// echo '<pre>';
		// var_dump ($_REQUEST['mail_content']);
		// echo '</pre>';
		$mail_content = $_REQUEST['mail_content'];
		$timestamp = date('Ymd-His');
		if ($target == 'user') {
			$org_file = PATH_ROOT.'/config/mail_for_user';

		} else if ($target == 'admin') {
			$org_file = PATH_ROOT.'/config/mail_for_admin';
		}
		$new_file = $org_file . '.' . $timestamp;

		copy($org_file, $new_file);
		file_put_contents($org_file, $mail_content);
		$message[] = '更新されました。';
		$message[] = "変更前の内容は${new_file}へ保存されました。";
		include (PATH_ROOT.'/view/setting_mail.html');
	}
}

if (!isset($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])
	or $_SERVER['PHP_AUTH_USER'] !== 'admin'
	or $_SERVER['PHP_AUTH_PW'] !== '7941'){
	header('WWW-Authenticate: Basic realm="Enter username and password."');
	header('Content-Type: text/plain; charset=utf-8');
	die();
}

$setting = new SettingMail();
$target = isset($_REQUEST['t']) ? $_REQUEST['t'] : null;
$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : null;

if (!$target) {
	header('HTTP', true, 404);
	exit;
}
switch($step) {
	case 'confirm':
		$setting->confirm_mail($target);
		break;
	case 'complete':
		$setting->complete_mail($target);
		break;
	default:
		$setting->show_mail($target);
		break;
}