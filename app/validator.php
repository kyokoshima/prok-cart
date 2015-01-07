<?php
class Validator {

	/**
	 * 注文者情報のバリデーションチェックを行う
	 * Enter description here ...
	 */
	function validate($params){

		//$params = @$_REQUEST;

		$errors = array();

		$this->checkRequired(array($params, ORD_NM, &$errors, 'お名前は必須入力です'));
		$this->checkRequired(array($params, ORD_ZIP, &$errors, '郵便番号は必須入力です'));
		$this->checkZipCode($params, ORD_ZIP, $errors, '郵便番号の入力に誤りがあります');

		$this->checkSelected(array($params, ORD_PREF, &$errors, '都道府県は必須入力です'));
		$this->checkRequired(array($params, ORD_CITY, &$errors, '市区町村は必須入力です'));
		$this->checkRequired(array($params, ORD_TOWN, &$errors, '町名地番は必須入力です'));
		$this->checkRequired(array($params, ORD_PHONE, &$errors, '連絡先電話番号は必須入力です'));
		$this->checkTelNumber($params, ORD_PHONE, $errors, '連絡先電話番号の入力に誤りがあります');
		if (!empty($params[ORD_FAX])){
			$this->checkTelNumber($params, ORD_FAX, $errors, '連絡先FAX番号の入力に誤りがあります');
		}
		$this->checkRequired(array($params, ORD_MAIL, &$errors, 'メールアドレスは必須入力です'));
		$this->checkMail($params[ORD_MAIL], ORD_MAIL, $errors, 'メールアドレスの入力に誤りがあります');
		$this->checkRequired(array($params, ORD_SHIP_COND, &$errors, '出荷条件は必須入力です'));

		$this->checkRequired(array($params, RCV_NM, &$errors, 'お名前は必須入力です'));
		$this->checkRequired(array($params, RCV_ZIP, &$errors, '郵便番号は必須入力です'));
		$this->checkZipCode($params, RCV_ZIP, $errors, '郵便番号の入力に誤りがあります');

		$this->checkSelected(array($params, RCV_PREF, &$errors, '都道府県は必須入力です'));
		$this->checkRequired(array($params, RCV_CITY, &$errors, '市区町村は必須入力です'));
		$this->checkRequired(array($params, RCV_TOWN, &$errors, '町名地番は必須入力です'));
		$this->checkRequired(array($params, RCV_PHONE, &$errors, '連絡先電話番号は必須入力です'));
		$this->checkTelNumber($params, RCV_PHONE, $errors, '連絡先電話番号の入力に誤りがあります。');

		$this->checkSelected(array($params, ORD_DELIV_DT, &$errors, '配送指定日は必須入力です'));


		if (isset($params[PAYMENT]) && @$params[PAYMENT] == PAYMENT_CARD){
			$this->checkSelected(array($params, CARD_BRAND, &$errors, 'カード支払いを指定した場合カード種別は必須入力です。'));
			//$this->_validateSelected(array($params, CARD_BRAND, &$errors
				//, 'カード支払いを指定した場合カード種別は必須入力です。'));
			$this->checkRequired(array($params, CARD_ACCOUNT, &$errors, 'カード支払いを指定した場合カード名義は必須入力です。'));
			//$this->_validateRequired(array($params, CARD_ACCOUNT, &$errors
				//, 'カード支払いを指定した場合カード名義は必須入力です。'));
			if (!$this->checkRequired(array($params, CARD_NUMBER))){
				$errors['card_number'] = 'カード支払いを指定した場合カード番号は必須入力です。';
			}else if(!$this->checkCardNumber($params[CARD_NUMBER])){
				$errors['card_number'] = 'カード番号の入力に誤りがあります。';
			}

			if(!$this->checkSelected(array($params, CARD_MONTH))
				|| !$this->checkSelected(array($params, CARD_YEAR))){
				$errors['card_expire_date'] = 'カード支払いを指定した場合カード有効期限は必須入力です。';
			}

			$this->checkSelected(array($params, CARD_PAYCOUNT, &$errors, 'カード支払いを指定した場合支払い回数は必須入力です。'));

		}

		// mb_convert_variables('Shift_JIS', 'UTF-8', $errors);
		return $errors;
	}

	function checkTelNumber($params, $name, &$errors, $message){
		return $this->checkRegex($params, $name, '/^[0-9\-]{9,14}$/', $errors, $message);
	}

	function checkZipCode($params, $name, &$errors, $message){
		return $this->checkRegex($params, $name, '/^\d{3}-?\d{4}$/', $errors, $message);
	}

	function checkRegex($params, $name, $regex, &$errors, $message){
		$result = false;
		if (empty($value)){
			$result = preg_match($regex, $params[$name]) > 0;
		}

		if (!$result){
			$errors[$name] = $message;
		}
		return $result;
	}
	function checkLength($value, $minRange = null, $maxRange = null, &$errors, $message){


	}


	/**
	 * 必須入力チェックを行う。
	 * パラメータがemptyの場合にはチェックNGとする。
	 * パラメータが配列の場合、要素に一つでもemptyがある場合はチェックNGとする。
	 * @param $args パラメータ配列、パラメータ名、蓄積エラーメッセージ、メッセージ内容
	 * @return チェックOKならtrue, NGならfalse
	 */
	function checkRequired($args/*$param, $name, &$errors, $message*/){

		//$args = func_get_args();
		//$numargs = func_num_args();
		$numargs = count($args);
		$param = $args[0];
		$name = $args[1];
		if ($numargs == 2){

		}else if($numargs == 4){
			$errors = $args[2];
			$message = $args[3];
		}
		$result = true;
		$hasError = false;
		if (is_array($param[$name])){
			$hasError = false;
			foreach($param[$name] as $key => $value){
				if (empty($value)){
					$hasError = true;
					break;
				}
			}

		}else{
			if (empty($param[$name])){
				if (isset($errors)){
					$hasError = true;
				}
			}
		}
		if ($hasError){
			if (isset($args[2])){
				$args[2][$name] = $message;
			}
			$result = false;
		}
		return $result;
	}

	/**
	 * 未選択情報のバリデートチェックを行う。
	 * 指定されたパラメータがemptyまたは0の場合はチェックNHとする。
	 * @param $args パラメータ、パラメータ名、エラーメッセージを蓄積する配列、メッセージ内容
	 * @return チェックOKならtrue, NGならfalse
	 */
	function checkSelected($args/*$param, $name, &$errors, $message*/){

		//$args = func_get_args();
		//$numargs = func_num_args();
		$numargs = count($args);
		$param = $args[0];
		$name = $args[1];

		if($numargs == 4){
			$errors = $args[2];
			$message = $args[3];
		}
		$result = true;
		if (empty($param[$name]) || $param[$name] == '0'){
			if (isset($errors)){
				//$errors[$name] = $message;
				$args[2][$name] = $message;
			}
			$result = false;
		}
		return $result;
	}

	function checkCardNumber($cardNumber){
		$result = false;
		if (count($cardNumber) != 4){
			return false;
		}else{
			foreach($cardNumber as $key => $value){
				if(empty($value)){
					return false;
				}else if(!is_numeric($value)){
					return false;
				}else if(strlen($value) != 4){
					return false;
				}
			}
		}
		return true;
	}

	function checkMail($address, $paramName, &$errors, $message){
		$result = false;

		if (!empty($address)){
			$result = preg_match('/[^@]+@[^@]+/', $address) > 0;
		}

		if (!$result){
			$errors[$paramName] = $message;
		}
		return $result;
	}
}
?>