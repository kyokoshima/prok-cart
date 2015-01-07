<?php

require_once 'constants.php';
require_once 'html.php';
require_once 'utils.php';
require_once 'session.php';

class Cart {
	var $h, $u, $s;

	var $debug;
	/**
	 * コンストラクタ
	 */
	function Cart(){
		$this->h = new Html();
		$this->u = new Utils();
		$this->s = new Session();
	}

	/**
	 * カート追加処理を行う
	 */
	function addCart(){
		$req = $this->u->getRequest();
		$qty = trim($req[PRD_QTY]);
		// $productCode = trim(mb_convert_encoding($req[PRD_CD], 'Shift_JIS', 'UTF-8'));
		// $productName = trim(mb_convert_encoding($req[PRD_NAME], 'Shift_JIS', 'Shift_JIS'));
		$productCode = trim($req[PRD_CD]);
		$productName = trim($req[PRD_NAME]);
		$price = trim($req[PRD_PRICE]);
		$shippingWeight = trim($req[PRD_SW]);
		$cashOnDelivery = trim($req[PRD_COD]);
		// print_r($req);
		//echo ('QTY=>' . $productCode);

		if (!empty($qty) && isset($productCode) &&
			!empty($productName) && isset($price) &&
			isset($shippingWeight) && isset($cashOnDelivery)){
			$newOrder = array(
				PRD_CD => $productCode,
				PRD_NAME => $productName,
				PRD_PRICE => $price,
				PRD_SW => $shippingWeight,
				PRD_COD => $cashOnDelivery,
				PRD_QTY => $qty
			);

			// print_r ($newOrder);
			$this->s->addOrder($newOrder);
		}
		$this->listProducts();
	}

	/**
	 * カートから商品を除去する
	 */
	function deleteCart(){
		$orderIndex = @$_REQUEST['orderIndex'];
		if (isset($orderIndex)){
			$this->s->deleteOrder($orderIndex);
		}
		$this->listProducts();
	}

	/**
	 * カートリストページを出力する。
	 */
	function listProducts(){
		$title = 'STEP1';
		$orders = $this->s->getOrders();
		$this->debug($orders);
		$ref = $this->s->getBackPage();
		// 税率
		// $ini = $this->u->getIni();
		$taxRatio = $this->u->getTaxRatio();
		// print_r($taxRatio);
		include 'view/productList.html';
	}

	function recalc(){
		$orders = $this->s->getOrders();
		$param = $this->u->getRequest();
		$qty = $param[PRD_QTY];

		$recalcOrders = array();
		foreach($orders as $key => $value){
			//$orderedQty = $qty[$key];
			$orderedQty = mb_convert_kana($qty[$key], 'anr', 'Shift_JIS');
			//var_dump($orderedQty);
			if (!preg_match('/^[\d]+$/', $orderedQty)){
				$recalcOrders[] = $orders[$key];
				continue;
			}
			if (!empty($orderedQty) || $orderedQty > 0){
				$orders[$key][PRD_QTY] = $orderedQty;
				$recalcOrders[] = $orders[$key];
			}

		}

		$this->s->addSession(SES_ORDERS, $recalcOrders);
	}

	function recalcOrder(){
		$this->recalc();
		$this->listProducts();
	}

	/**
	 * 注文者情報入力画面を出力する。
	 * @param $errors
	 */
	function inputAccount($errors = null){
		$orders = $this->s->getOrders();

		if (!isset($orders)){
			$this->listProducts();
			return;
		}
		$title = 'STEP2';
		$ini = $this->u->getIni();

		$prefs = $ini[INI_PREF];
		$shipConditions = $ini[INI_SC];
		$dv_times = $ini[INI_DT];
		$payments = $ini[INI_PAYMENT];
		$card_brands = $ini[INI_CARD_BRAND];
		$card_months = $ini[INI_CARD_MONTH];
		$card_years = $ini[INI_CARD_YEAR];
		$card_paycounts = $ini[INI_CARD_PAYCOUNT];

		$deliv_date = array();
		$today = date('Y,m,d', time());
		list($y, $m, $d) = explode(',', $today);
		$d += 7;
		$deliv_begin_date = mktime(0, 0, 0, $m, $d, $y );

		$deliv_date[1] = $this->_getLabelDeliverDate(1);
		for ($i = 1; $i <= 14; $i++){
			$deliv_begin_date += 24 * 60 * 60;
			$date = date('Y/m/d', $deliv_begin_date);
			$deliv_date[$date] = $date;
		}

		$notice1 = @$ini[INI_N1];
		$notice2 = @$ini[INI_N2];
		$notice3 = @$ini[INI_N3];
		$accounts = $this->s->getAccounts();
		$this->debug($accounts);
		// header('Content-Type: text/html; charset=Shift_JIS');
		include 'view/inputAccount.html';
	}
	/**
	 * 指定したラベル定義グループの指定したコードのラベルを取得する。
	 * @param $group ラベル定義グループ
	 * @param $code コード値
	 */
	function _getLabel($group, $code){
		$ini = $this->u->getIni();
		//echo '★★' . $group . ':' . $code;
		if (isset($ini[$group])){
			if (isset($ini[$group][$code])){

				return $ini[$group][$code];
			}
		}
	}

	function _getLabelDeliverDate($code){
		$result;
		if(!empty($code)){
			if ($code == 1){
				// $result = mb_convert_encoding('最短時間で送って下さい', 'Shift_JIS', 'UTF-8');
				$result = '指定なし＜最短発送＞';
			}else{
				$result = $code;
			}
		}
		return $result;
	}

	/**
	 *　注文者情報確認処理を行う
	 */
	function confirm(){

		$orders = $this->s->getOrders();

		if (!isset($orders)){
			$this->listProducts();
			return;
		}
		//バリデーションチェック
		require_once 'validator.php';
		$v = new Validator();
		$req = $this->u->getRequest();

		$errors = $v->validate($req);
		if (!empty($errors)){
			$this->inputAccount($errors);
			return;
		}

		$account = $this->s->saveAccount();
		$orders = $this->s->getOrders();
		if (!isset($orders)){
			return;
		}
		$fee = $this->u->calcFee($account, $orders);
		$totalPrice = $fee['totalPrice'];
		$totalShipping = $fee['totalShipping'];
		$CODFee = $fee['CODFee'];
		$totalQty = $fee['totalQty'];
		$discountPrice = $fee['discountPrice'];
		// $ini = $this->u->getIni();
		$taxRatio = $this->u->getTaxRatio();
		$tax = floor($totalPrice * ($taxRatio / 100));

		$title = 'STEP3';

		include 'view/confirm.html';
	}

	function echoPayment($payment, $value){
		if (isset($payment) && $payment == PAYMENT_CARD){
			echo $value;
		}
	}

	function replace_values_user($matches){
		return $this->replace_values($matches, true);
	}

	function replace_values_admin($matches){
		return $this->replace_values($matches, false);
	}

	/**
	 * preg_replace_callback処理用置き換えを行う
	 * $matches[0] = ${var_name}
	 * $mathces[1] = var_name
	 * の想定
	 * 注文情報、注文者情報から該当する値がある場合には置き換え結果を返す
	 * なければ空白を返す。
	 * @param $matches
	 * @return 置き換え結果文字列
	 */
	function replace_values($matches, $isUser){
		$account = $this->s->getAccounts();
		$orders = $this->s->getOrders();
		$checkoutData = $this->s->getCheckoutData();
		$varName = $matches[1];
		if ($varName == 'orders'){
			$order_list = '';
			foreach($orders as $key => $value){
				$order_list .= mb_convert_encoding('■ ', 'Shift_JIS', 'UTF-8');
				$order_list .= $value[PRD_CD] . ' , ';
				$order_list .= $value[PRD_NAME] . ' , ';
				$order_list .= $this->price($value[PRD_PRICE]) . '円 X ';
				$order_list .= $value[PRD_QTY] . ' = ';
				$order_list .= $this->price($value[PRD_QTY] * $value[PRD_PRICE]) . '円 ';
				$order_list .= "\r\n";
			}
			return $order_list;
		}else if(array_key_exists($varName, $checkoutData)){
			return $checkoutData[$varName];
		}else if (array_key_exists($varName, $account)){
			if ($varName == ORD_PREF || $varName == RCV_PREF){
				return $this->_getLabel(INI_PREF, $account[$varName]);
			}else if($varName == ORD_SHIP_COND){
				return $this->_getLabel(INI_SC, $account[$varName]);
			}else if($varName == ORD_DELIV_TM){
				return $this->_getLabel(INI_DT, $account[$varName]);
			}else if($varName == PAYMENT){
				return $this->_getLabel(INI_PAYMENT, $account[$varName]);
			}else if($varName == CARD_BRAND){
				if ($account[PAYMENT] == PAYMENT_CARD){
					return $this->_getLabel(INI_CARD_BRAND, $account[$varName]);
				}else{
					return '';
				}
			}else if($varName == CARD_ACCOUNT){
				if ($account[PAYMENT] == PAYMENT_CARD){
					return $account[$varName];
				}else{
					return '';
				}
			}else if($varName == 'card_number'){
				if ($account[PAYMENT] == PAYMENT_CARD){
					$cardNumber = '';
					if ($isUser){
						$cardNumber = '****-****-****-' . $account[CARD_NUMBER_4];
					}else{
						$cardNumber = $account[CARD_NUMBER_1] . '-'
							. $account[CARD_NUMBER_2] . '-'
							. $account[CARD_NUMBER_3] . '-'
							. $account[CARD_NUMBER_4];
					}
					return $cardNumber;
				}else{
					return '';
				}
			}else if($varName == CARD_EXPIRE){
				if ($account[PAYMENT] == PAYMENT_CARD){
					return $account[$varName];
				}else{
					return '';
				}
			}else if($varName == CARD_PAYCOUNT){
				if ($account[PAYMENT] == PAYMENT_CARD){
					return $this->_getLabel(INI_CARD_PAYCOUNT, $account[$varName]);
				}else{
					return '';
				}
			}else if($varName == ORD_DELIV_DT){
				return $this->_getLabelDeliverDate($account[$varName]);
			}else{
				return $account[$varName];
			}
		}else{
			return '';
		}
	}

	function price($price){
		$p = 0;
		$d = '0';
		if (!empty($price)){
			$p = $price;
			$d = number_format($p);
		}
		// return mb_convert_encoding($d . '円', 'Shift_JIS', 'UTF-8');
		return $d;
		// return $d . '円';
	}


	/**
	 * 注文完了画面を表示する。
	 */
	function complete(){
		$orders = $this->s->getOrders();
		$this->debug($orders);
		if (!isset($orders)){
			$this->listProducts();
			return;
		}
		//注文情報生成
		$order_number = $this->u->generateOrderNumber();
		$this->s->editCheckoutData('order_number', $order_number);
		$order_date = $this->u->generateOrderDate();
		$this->s->editCheckoutData('order_date', $order_date);
		//$orders = $this->getOrders();
		$account = $this->s->getAccounts();
		$fee = $this->u->calcFee($account, $orders);
		//print_r($fee);
		$totalPrice = $fee['totalPrice'];
		$totalShipping = $fee['totalShipping'];
		$CODFee = $fee['CODFee'];
		$totalQty = $fee['totalQty'];
		$allCosts = $fee['allCosts'];
		$discountPrice = $fee['discountPrice'];
		$tax = $fee['tax'];
		$total_taxin_price = $totalPrice + $tax;

		$this->s->editCheckoutData('total_price', $this->price($totalPrice));
		$this->s->editCheckoutData('total_shipping', $this->price($totalShipping));
		$this->s->editCheckoutData('cod_fee', $this->price($CODFee));
		$this->s->editCheckoutData('order_count', $totalQty);
		$this->s->editCheckoutData('all_costs', $this->price($allCosts));
		$this->s->editCheckoutData('discount_price', $this->price($discountPrice));
		$this->s->editCheckoutData('tax', $this->price($tax));
		$this->s->editCheckoutData('total_taxin_price', $this->price($total_taxin_price));

		$tpl_admin = 'config/mail_for_admin';
		$handle = fopen($tpl_admin, 'r');
		$body_admin = fread($handle, filesize($tpl_admin));
		fclose($handle);

		$body_admin = preg_replace_callback('/\$\{([a-z0-9_]+)\}/'
			,array($this, 'replace_values_admin'), $body_admin );

		$tpl_user = 'config/mail_for_user';
		$handle = fopen($tpl_user, 'r');
		$body_user = fread($handle, filesize($tpl_user));
		fclose($handle);
		$body_user = preg_replace_callback('/\$\{([a-z0-9_]+)\}/'
			,array($this, 'replace_values_user'), $body_user );

		$this->debug ($body_user);
		$this->debug ($body_admin);

		$accounts = $this->s->getAccounts();
		$to_user = $accounts[ORD_MAIL];
		$from = $this->getFromAddress();
		//$subject = '【GIFTEX】ご注文を頂きありがとうございました。';
		$ini = @$this->u->getIni();
		$subjectTmpl = $ini[INI_SUBJECT];
		$subject = $subjectTmpl[INI_SUBJECT_TO_USER];
		$add_header = "";
		// $add_header = "Content-Type: text/plain; charset=ISO-2022-JP \r\n";
		// $add_header .= "Content-Transfer-Encoding: 7bit \r\n";
		mb_language('ja');
		mb_internal_encoding('UTF-8');
		if (!empty($to_user)){
			// $body_user = mb_convert_encoding($body_user, 'ISO-2022-JP', 'AUTO');
			// $subject = mb_convert_encoding($subject, 'ISO-2022-JP', 'AUTO');
			// $subject = $this->encodeMIMEHeader($subject);

			mb_send_mail($to_user, $subject, $body_user, $from, $add_header);
		}
		$to_admin = $this->getAdminMailAddress();
		$from = $this->createFromHeader($accounts[ORD_NM], $accounts[ORD_MAIL]);
		//$subject = '【GIFTEX】' . $accounts[ORD_NM] . '様から注文を頂きました。';
		$subject = $subjectTmpl[INI_SUBJECT_TO_ADMIN];
		$subject = str_replace('${name}', $accounts[ORD_NM], $subject);
		if (!empty($to_admin)){
			// $body_admin = mb_convert_encoding($body_admin, 'ISO-2022-JP', 'AUTO');
			// $subject = mb_convert_encoding($subject, 'ISO-2022-JP', 'AUTO');
			// $subject = $this->encodeMIMEHeader($subject);
			foreach ($to_admin as $key => $value){
				mb_send_mail($value, $subject, $body_admin, $from, $add_header);
			}
		}

		$this->s->destroySession();

		$title = 'STEP4';
		include 'view/complete.html';
	}



	/**
	 * 管理者メールアドレスを取得する。
	 * @return array 管理者メールアドレス
	 */
	function getAdminMailAddress(){
		$ini = $this->u->getIni();
		return $ini['order_address'];
	}

	/**
	 * メール送信元アドレスを取得する。
	 * @return メール送信元
	 */
	function getFromAddress(){
		$ini = $this->u->getIni();
		$from_address = $ini['from_address'];
		return $this->createFromHeader($from_address['label'], $from_address['address']);
	}

	/**
	 * mb_send_mailで指定するFromヘッダを生成する。
	 * @param $label ラベル部分
	 * @param $address メールアドレス部分
	 * @return String Fromヘッダ文字列
	 */
	function createFromHeader($label, $address){
		//echo mb_internal_encoding();
		mb_internal_encoding("UTF-8");
		$from = 'From:';
		if (!empty($label)){
			// $from .= mb_encode_mimeheader($label, 'ISO-2022-JP') . '<' . $address . '>';
			$from .= mb_encode_mimeheader($label) . '<' . $address . '>';
		}else{
			$from .= $address;
		}
		return $from;
	}

	function encodeMIMEHeader($str){
		$org = mb_internal_encoding();	// 元のエンコーディングを保存
		mb_internal_encoding("ISO-2022-JP");// 変換したい文字列のエンコーディングをセット
		$result = mb_encode_mimeheader(
				mb_convert_encoding($str, "ISO-2022-JP", "UTF-8"),"ISO-2022-JP","B","\r\n");
		mb_internal_encoding($org);// エンコーディングを戻す
		return $result;
	}




	function hidden(){
		//print_r(func_get_args());
		$this->h->hidden(func_get_args());
	}

	function debug($var){
		//$this->debug[] = $var;
	}
}