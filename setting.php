<?php
require_once 'app/constants.php';
class Setting {

	function view(){
		// $ini = parse_ini_file(CONFIG, true);
		include PATH_ROOT.'/view/setting_menu.html';
	}

	function create_changed($parent, $child, $orginal, $new_value) {
		return array("${parent}.${child}" => 
				array(
					'org' => $original, 'new' => $new_value, 'key' => "${parent}[${child}]"));
	}

	function extract_changes($new_value) {
		$ini_org = parse_ini_file(CONFIG, true);
		$changed = array();
		foreach ($new_value as $k => $v) {
			if (isset($ini_org[$k])) {
				// 双方に存在しており値が変更されている場合
				foreach ($v as $kk => $vv) {
					if (!isset($ini_org[$k][$kk])) {
						$org_val = null;
					} else {
						$org_val = $ini_org[$k][$kk];
					}
					// echo '<pre>';
					// echo "${k}.${kk}";
					// print_r($org_val);
					// echo '</pre>';
					$new_val = $vv;
					if ($org_val != $new_val) {
						$changed["${k}.${kk}"] = array('org' => $org_val, 'new' => $new_val, 'key' => "${k}[$kk]");
					}
				}
				// 変更前のconfigに存在しており、新しい方に存在していない場合
				foreach ($ini_org[$k] as $kk => $vv) {
					if (!isset($v[$kk])) {
						// echo "新しい値： ${k}.${kk}";
						$org_val = $vv;
						$new_val = null;
						$changed["${k}.${kk}"] = array('org' => $org_val, 'new' => $new_val, 'key' => "${k}[$kk]");
					}
				}
			}
		}
		return $changed;
	}

	function confirm(){
		$req = $_REQUEST;

		// カード有効期限は最小と最大から値を形成する
		$card_years_min = $req['card_years_min'];
		$card_years_max = $req['card_years_max'];

		for($i=$card_years_min; $i<=$card_years_max; $i++) {
			$req['card_years'][$i] = $i;
		}

		// echo '<pre>';
		// var_dump($req);
		// echo '</pre>';
		$changed = $this->extract_changes($req);
		
		include PATH_ROOT.'/view/setting_confirm.html';
	}

	function complete(){
		$req = $_REQUEST;
		// echo '<pre>';
		// print_r($req);
		// echo '</pre>';
		$ini_org = parse_ini_file(CONFIG, true);

		// $changes = $this->extract_changes($req);
		// echo '<pre>';
		// print_r($changes);
		// echo '</pre>';

		// var_dump($ini_org[$changes['tax.ratio']['key']]);
		if (!empty($req) and file_exists(CONFIG)) {
			$new_ini = $ini_org;
			foreach ($req as $parent_key => $parent_v) {
				if (is_array($parent_v)) {
					foreach($parent_v as $child_key => $child_value) {
						// list($k1, $k2) = explode('.', $k);
						// echo '<pre>';
						// echo "${k1} ${k2}";
						// var_dump ($v);
						// var_dump ($ini_org[$k1][$k2]);
						// echo '</pre>';
						if (empty($child_value)) {
							unset($new_ini[$parent_key][$child_key]);
						} else {
							$new_ini[$parent_key][$child_key] = $child_value;
						}
					}
				}
			}
			// echo '<pre>';
			// var_dump ($new_ini);
			// echo '</pre>';
			$timestamp = date('Ymd-His');
			$new_filename = 'config/config.ini.' . $timestamp;
			copy('config/config.ini', $new_filename);
			$this->write_ini_file($new_ini, CONFIG);
		} else {
			echo '変更なし';
		}
		$message = '更新されました';
		$ini = parse_ini_file(CONFIG, true);
		include PATH_ROOT.'/view/setting.html';
	}

	function write_ini_file($array, $path) {
		$content = '';
		foreach($array as $k=>$v) {
			$content .= "[${k}]" .PHP_EOL;
			foreach ($v as $kk=>$vv) {
				if (is_string($vv)) {
					$vv = "\"${vv}\"";
				}
				$content .= "${kk}=${vv}".PHP_EOL;
			}
			$content .= PHP_EOL;
		}
		file_put_contents($path, $content);
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
$setting = new Setting();

if ($step == 'confirm') {
	$setting->confirm();
} else if ($step == 'complete') {
	$setting->complete();
} else {
	$setting->view();
}
