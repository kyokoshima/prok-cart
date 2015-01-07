<?php
require_once 'app/constants.php';
class Setting {

	function view(){
		$ini = parse_ini_file(CONFIG, true);
		include PATH_ROOT.'/view/setting.html';
	}

	function confirm(){
		$req = $_REQUEST;
		// var_dump($req);
		$ini_org = parse_ini_file(CONFIG, true);
		$new_ini = $ini_org;
		$changed = array();
		foreach($req as $k => $v) {
			if (isset($ini_org[$k])) {
				// echo '<pre>';
				// echo $k;
				// print_r (array_diff_assoc($v, $ini_org[$k]));
				// print_r ($ini_org[$k]);
				// print_r ($v);
				// echo '</pre>';
				if (array_diff_assoc($v, $ini_org[$k])) {
					$new_ini[$k] = $v;
					$changed[] = $k;
				}
			}
		}
		echo '変更されたキー';
		print_r ($new_ini);
		if (file_exists(CONFIG)) {
			$timestamp = date('Ymd-His');
			$new_filename = 'config/config.ini.' . $timestamp;
			copy('config/config.ini', $new_filename);
		}
	}
}

$step = isset($_REQUEST['step']) ? $_REQUEST['step'] : null;
$setting = new Setting();
if ($step == 'confirm') {
	$setting->confirm();
} else {
	$setting->view();
}