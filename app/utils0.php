<?php
require_once 'constants.php';

class Utils {

	/**
	 * 手数料その他を計算する。
	 * @param $account アカウント情報
	 * @param $orders オーダー情報
	 */
	function calcFee($account, $orders){
		$totalPrice = 0;
		$totalShipping = 0;
		$totalQty = 0;
		$totalSw = 0;
		$CODFee = 0;
		$hasSWZero = false;
		$ini = $this->getIni();
		$shippingOption = @$ini[INI_SHIPPING_OPTION];

		//$hasCOD = false;
		$shippingTargetPrice = 0;
		//print_r($orders);
		foreach($orders as $key => $value){
			$qty = $value[PRD_QTY];
			$aPrice = $value[PRD_PRICE];
			$totalQty += $qty;
			$price = $qty * $aPrice;
			$totalPrice += $price;
			$sw = $qty * $value[PRD_SW];
			$totalSw += $sw;
			if ($sw == 0){
				$hasSWZero = true;
			}

			if ($value[PRD_COD] != 0){
				$shippingTargetPrice += $price;
			}
		}

		$iniOverPrice = $shippingOption[INI_OVER_PRICE];		//購入金額オーバー設定
		$iniHasSWZero = $shippingOption[INI_HAS_SW_IS_ZERO];	//シッピングウェイト0設定
		$uniPrice = $shippingOption[INI_UNI_PRICE];				//統一料金設定
		$free_shipping = @$ini[INI_FREE_SHIPPING];				//シッピングフリーセクション
		if (isset($free_shipping)){
			$limitPrice = $free_shipping[INI_FREE_SHIPPING_PRICE]; 	//シッピングフリー上限額
		}
		//送料無料設定
		//シッピングウェイトが0の物が一つでもあれば送料0円設定
		if ($iniHasSWZero && $hasSWZero){
			//echo "シッピングウェイト0のがあり";
			$totalShipping = 0;
		}else if($iniOverPrice && $limitPrice && $limitPrice <= $totalPrice){ //購入金額が指定金額以上であれば送料無料設定
			//echo "購入金額オーバーあり";
			//echo "limitPrice = $limitPrice";
			//if ($totalPrice >= $limitPrice){
				$totalShipping = 0;
			//}
		}else if($uniPrice){
			//全国統一料金
			$totalShipping = $this->getShipping($account[RCV_PREF],2);
		}else {
			$totalShipping = $this->getShipping($account[RCV_PREF], $totalSw);
		}


		//キャンペーンコード設定
		$campaign = @$ini[INI_CAMPAIGN];
		$discountPrice = 0;
		if (isset($campaign)){
			$inputCode = $account[ORD_C_CD];
			$defineCode = $campaign[INI_CAMPAIGN_CODE];

			if ($inputCode == $defineCode){
				$limitPrice = $campaign[INI_CAMPAIGN_FREE_SHIPPING];
				if ($totalPrice >= $limitPrice){
					$totalShipping = 0;
				}
				$discount = $campaign[INI_CAMPAIGN_DISCOUNT];
				$totalPrice -= $discount;
				$discountPrice = $discount;
				if ($totalPrice < 0){
					$totalPrice = 0;
				}
			}
		}

		//代引き手数料
		$cd = $account[PAYMENT];
		if ($cd == PAYMENT_EXCHANGE){
			//代引
			//if ($hasCOD){
			if ($shippingTargetPrice > 0 && $shippingTargetPrice < 10000){
				$CODFee = 324;
			}else if($shippingTargetPrice > 10000 && $shippingTargetPrice < 30000){
					$CODFee = 432;
			}else if($shippingTargetPrice > 30000){
					$CODFee = 648;
			}else{
				$CODFee = 0;
			}
			//}
		}

		//　消費税設定
		$taxRatio = $this->getTaxRatio();
		$tax = floor($totalPrice * ( $taxRatio / 100 ));

		$allCosts = $totalPrice + $totalShipping + $CODFee + $tax;
		$result['totalPrice'] = $totalPrice;
		$result['totalShipping'] = $totalShipping;
		$result['CODFee'] = $CODFee;
		$result['discountPrice'] = $discountPrice;
		$result['totalQty'] = $totalQty;
		$result['allCosts'] = $allCosts;
		$result['tax'] = $tax;
		//print_r($result);
		return $result;
	}

	function getTaxRatio(){
		$ini = $this->getIni();
		if (isset($ini['tax']['ratio'])){
			return $ini['tax']['ratio'];
		}
		return 0;
	}
	/**
	 * 指定県コードとShippingWeightから送料を計算する。
	 * @param unknown_type $prefCode 県コード
	 * @param unknown_type $sw ShippingWeight区分
	 * @return 送料
	 */
	function getShipping($prefCode, $sw){
		$ini = $this->getIni();
		$shippingPrices = $ini[INI_SHIPPING_PRICES];

		//青森〜四国までのマッピング
		//$swg[0] = array($630, 840, 1050, 1260, 1470);
		$swg[0] = explode(',', $shippingPrices[0]);

		//北海道・九州
		//$swg[1] = array(630, 840, 1050, 1260, 1470);
		$swg[1] = explode(',', $shippingPrices[1]);

		//沖縄・離島
		//$swg[2] = array(1260, 1790, 1790, 2210, 2210);
		$swg[2] = explode(',', $shippingPrices[2]);

		$swgIndex = $this->getAreaGroupIndex($prefCode);

		//最大送料のオプション

		$shippingOption = $ini[INI_SHIPPING_OPTION];
		$iniLimitShippingOfArea = $shippingOption[INI_LIMIT_SHIPPING_OF_AREA];
		if ($iniLimitShippingOfArea){
			return $swg[$swgIndex][4];
		}

		if ($sw == 0){
			return 0;
		}else if($sw == 1){
			return 630;
		}else{

			$swIndex = 0;
			if ($sw >= 2 && $sw <= 10){
				$swIndex = 0;
			}else if($sw >= 11 && $sw <= 30){
				$swIndex = 1;
			}else if($sw >= 31 && $sw <= 50){
				$swIndex = 2;
			}else if($sw >= 51 && $sw <= 70){
				$swIndex = 3;
			}else if($sw >= 71){
				$swIndex = 4;
			}

			return $swg[$swgIndex][$swIndex];
		}
	}

	function getAreaGroupIndex($prefCode){
		$swgIndex = 0;
			if ($prefCode == 1){
				//北海道
				$swgIndex = 1;
			}else if($prefCode >= 2 && $prefCode <= 7){
				//2="青森県"3="岩手県"4="秋田県"5="宮城県"6="山形県"7="福島県"
				$swgIndex = 0;
			}else if($prefCode >= 8 && $prefCode <= 14){
				//8="茨城県"9="栃木県"10="群馬県"11="千葉県"12="埼玉県"
				//13="東京都"14="神奈川県"
				$swgIndex = 0;
			}else if($prefCode >= 15 && $prefCode <= 23){
				//15="新潟県"16="富山県"17="石川県"18="福井県"19="山梨県"
				//20="長野県"21="岐阜県"22="静岡県"23="愛知県"
				$swgIndex = 0;
			}else if($prefCode >= 24 && $prefCode <= 30){
				//24="三重県"25="滋賀県"26="京都府"27="大阪府"
				//28="兵庫県"29="奈良県"30="和歌山県"
				$swgIndex = 0;
			}else if($prefCode >= 31 && $prefCode <= 35){
				//31="鳥取県"32="島根県"33="岡山県"34="広島県"35="山口県"
				$swgIndex = 0;
			}else if($prefCode >= 36 && $prefCode <= 39){
				//36="徳島県"37="香川県"38="愛媛県"39="高知県"
				$swgIndex = 0;
			}else if($prefCode >= 40 && $prefCode <= 46){
				//40="福岡県"41="佐賀県"42="長崎県"43="熊本県"
				//44="大分県"45="宮崎県"46="鹿児島県"
				$swgIndex = 1;
			}else if($prefCode >= 47 && $prefCode <= 48){
				//47="沖縄県"48="離島"
				$swgIndex = 2;
			}
		return $swgIndex;
	}

	/**
	 * 注文番号を発番する
	 * @return 注文番号
	 */
	function generateOrderNumber(){
		$file = 'config/order';

		@chmod ($file, 0666);
		$handle = fopen($file, 'r+');
		$number = 0;
		if ($handle){
			if (flock($handle, LOCK_EX)){
				$number = fgets($handle, 128);
				$number = trim($number);
				//var_dump($number);
				if (is_numeric($number)){
					$number++;
				}else{
					$number = 0;
				}

				rewind($handle);
				if(!fwrite($handle, $number)){
					//
				}

				flock($handle, LOCK_UN);
			}
		}

		fclose($handle);
		return sprintf('%07d', $number);
	}



	function getRequest(){
		global $req;
		$req = $_REQUEST;
		$convert = mb_convert_variables('UTF-8', 'sjis-win', $req);
		// print_r($req);
		//$convert = mb_convert_variables('SJIS', 'EUC-JP', $req);
		return $req;
	}

	function generateOrderDate(){
		$now = time();
		// $order_date = date('Y年m月d日', $now);
		// $order_date = preg_replace('/\//', '年', $order_date );
		// $order_time = date('H:i:s', $now);
		$week = date('w', $now);
		switch((string)$week){
			case '0':$week = '日';	break;
			case '1':$week = '月';break;
			case '2':$week = '火';break;
			case '3':$week = '水';	break;
			case '4':$week = '木';	break;
			case '5':$week = '金';	break;
			case '6':$week = '土';	break;
		}

		$order_date = date('Y', $now) . '年'
			. date('m', $now) . '月' . date('d') . '日'
			. "($week) "
			. date('H:i:s', $now);
		// $order_date = $order_date . '(' . $week . ')' . ' ' . $order_time;
		// $order_date = mb_convert_encoding($order_date, 'Shift_JIS', 'UTF-8');
		return $order_date;
	}


	/**
	 * config.iniファイルを取得する。
	 */
	function getIni(){
		return parse_ini_file(CONFIG, true);
	}

}

/*
$u = new Utils();
echo $u->getShipping(47, 50);
*/
?>