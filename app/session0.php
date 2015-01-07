<?php
class Session {
/**
	 * リクエストの注文者情報をセッションに保存する。
	 * @return 保存した注文者情報
	 */
	function saveAccount(){

		$params = @$_REQUEST;
		//なぜかSJISの文字列がUTF-8→EUC-JPになっているので戻す
		if(mb_detect_encoding($params[ORD_NM]) != false){
			//mb_convert_variables('SJIS', 'EUC-JP,UTF-8', $params );
			//mb_convert_variables('SJIS', 'EUC-JP,UTF-8', $params );
		}
		$account[ORD_NM] = $params[ORD_NM];
		$account[ORD_CG] = $params[ORD_CG];
		$account[ORD_ZIP] = $params[ORD_ZIP];
		$account[ORD_PREF] = $params[ORD_PREF];
		$account[ORD_CITY] = $params[ORD_CITY];
		$account[ORD_TOWN] = $params[ORD_TOWN];
		$account[ORD_BUILD] = $params[ORD_BUILD];
		$account[ORD_PHONE] = $params[ORD_PHONE];
		$account[ORD_FAX] = $params[ORD_FAX];
		$account[ORD_MAIL] = $params[ORD_MAIL];
		$account[ORD_SHIP_COND] = $params[ORD_SHIP_COND];
		$account[ORD_DELIV_DT] = $params[ORD_DELIV_DT];
		$account[ORD_DELIV_TM] = $params[ORD_DELIV_TM];
		$account[ORD_REM] = $params[ORD_REM];
		$account[ORD_C_CD] = $params[ORD_C_CD];
		$account[RCV_NM] = $params[RCV_NM];
		$account[RCV_CG] = $params[RCV_CG];
		$account[RCV_ZIP] = $params[RCV_ZIP];
		$account[RCV_PREF] = $params[RCV_PREF];
		$account[RCV_CITY] = $params[RCV_CITY];
		$account[RCV_TOWN] = $params[RCV_TOWN];
		$account[RCV_BUILD] = $params[RCV_BUILD];
		$account[RCV_PHONE] = $params[RCV_PHONE];
		$account[PAYMENT] = $params[PAYMENT];
		$account[CARD_BRAND] = @$params[CARD_BRAND];
		$account[CARD_ACCOUNT] = $params[CARD_ACCOUNT];
		$cardNumber = '';
		if (!empty($params[CARD_NUMBER][0]) && !empty($params[CARD_NUMBER][1]) &&
			!empty($params[CARD_NUMBER][2]) && !empty($params[CARD_NUMBER][3]))
			{
			$cardNumber = $params[CARD_NUMBER][0] . '-' . $params[CARD_NUMBER][1] . '-' .
					$params[CARD_NUMBER][2] . '-' . $params[CARD_NUMBER][3];
		}
		$account[CARD_NUMBER] = $cardNumber;
		$account[CARD_NUMBER_1] = $params[CARD_NUMBER][0];
		$account[CARD_NUMBER_2] = $params[CARD_NUMBER][1];
		$account[CARD_NUMBER_3] = $params[CARD_NUMBER][2];
		$account[CARD_NUMBER_4] = $params[CARD_NUMBER][3];
		$cardExpire = $params[CARD_MONTH] . '/' . $params[CARD_YEAR];
		$account[CARD_EXPIRE] = $cardExpire;
		$account[CARD_MONTH] = $params[CARD_MONTH];
		$account[CARD_YEAR] = $params[CARD_YEAR];
		$account[CARD_PAYCOUNT] = $params[CARD_PAYCOUNT];

		$this->addSession(SES_ACCOUNT, $account);

		return $account;
	}
	/**
	 * 注文を追加する。
	 * @param $newOrder
	 */
	function addOrder($newOrder){
		$orders = $this->getOrders();
		$newOrders = array();
		if (isset($orders)){
			$newProductCode = $newOrder[PRD_CD];
			$hasProduct = false;
			foreach($orders as $order){
				if ($order[PRD_CD] == $newProductCode){
					$order[PRD_QTY] += $newOrder[PRD_QTY];
					$hasProduct = true;
				}
				$newOrders[] = $order;
			}
			if (!$hasProduct){
				$newOrders = $orders;
				$newOrders[] = $newOrder;
			}

		}else{
			$newOrders[] = $newOrder;
		}
		$this->addSession(SES_ORDERS, $newOrders);
	}
	/**
	 * 指定した番号の注文を削除する。
	 * @param $orderIndex
	 */
	function deleteOrder($orderIndex){
		$orders = $this->getOrders();
		$newOrders = array();
		if (isset($orders)){
			foreach($orders as $key => $order){
				if ($key != $orderIndex){
					$newOrders[] = $order;
				}
			}
		}
		$this->addSession(SES_ORDERS, $newOrders);
	}

	/**
	 * セッションにあるオブジェクトを取得する。
	 * @param $key
	 */
	function getSessionAttribute($key){
		$this->sessionStart();
		if (isset($_SESSION[$key])){
			return $_SESSION[$key];
		}else{
			return null;
		}
	}

	/**
	 * セッションに値を登録する。
	 * @param $key
	 * @param $value
	 */
	function addSession($key, $value){
		$this->sessionStart();
		$_SESSION[$key] = $value;
	}
	/**
	 * 呼び出し元ページを取得する。
	 * 呼び出し元ページがカートではない場合は呼び出し元ページとしてセッションに保持する。
	 */
	function getBackPage(){
		$ref = @$_SERVER['HTTP_REFERER'];
		if (isset($ref)){
			if (!strpos($ref, dirname($_SERVER['PHP_SELF']))){
				$this->addSession('backPage', $ref);
			}
		}
		return $this->getSessionAttribute('backPage');
	}

	/**
	 * セッションを開始する
	 */
	function sessionStart(){
		if (!isset($_SESSION)){
			session_start();
		}
	}

	function destroySession(){
		// セッション変数を全て解除する
		$_SESSION = array();

		// セッションを切断するにはセッションクッキーも削除する。
		// Note: セッション情報だけでなくセッションを破壊する。
		if (isset($_COOKIE[session_name()])) {
		    setcookie(session_name(), '', time()-42000, '/');
		}

		// 最終的に、セッションを破壊する
		session_destroy();
	}

	/**
	 * セッションから注文者情報を取得する。
	 * @return 注文者情報
	 */
	function getAccounts(){
		return $this->getSessionAttribute(SES_ACCOUNT);
	}

	/**
	 * セッションに存在するオーダーを取得する。
	 * @return オーダー
	 */
	function getOrders(){
		return $this->getSessionAttribute(SES_ORDERS);
	}

	function getCheckoutData(){
		return $this->getSessionAttribute(SES_CHECKOUT);
	}
	function editCheckoutData($paramName, $value){
		$checkData = $this->getCheckoutData();

		if (!isset($checkData)){
			$checkData = array();
		}
		$checkData[$paramName] = $value;
		$this->addSession(SES_CHECKOUT, $checkData);
	}

}