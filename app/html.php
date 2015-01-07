<?php

require_once 'utils.php';

class Html {

	var $u;

	function Html(){
		$this->u = new Utils();
	}

	function error($name, $errors){
		if (!empty($errors) && !empty($name)){
			if (isset($errors[$name])){
				echo '<p class="error">' . $errors[$name] . '</p>';
			}
		}
	}

	function textArea($name, $rows = null, $cols = null, $defaultValue = null){
		$out = '<textarea name="' . $name . '" ';

		if (!empty($rows)){
			$out .= 'rows="' . $rows . '" ';
		}
		if (!empty($cols)){
			$out .= 'cols="' . $cols . '" ';
		}

		$out .= '>';
		$req = $this->u->getRequest();
		$val = @$req[$name];
		if (!isset($val)){
			$val = $defaultValue;
		}
		$out .= $val;
		$out .= '</textarea>';
		echo $out;
	}

	function text($name, $size = null, $label = null, $id = null ,$class = null, $defaultValue = null, $maxSize = null){

		$args = func_get_args();
		$numargs = func_num_args();
		$out = '<input type="text" name="' . $name . '"';
		$defaultValue;
		switch($numargs){
			case 7:
				if (!empty($args[6])){
					$out .= ' maxlength="' . $args[6] . '"';
				}
			case 6:
				if (!empty($args[5])){
					$defaultValue = $args[5];
				}
			case 5:
				if (!empty($args[4])){
					$out .= ' class="' . $args[4] . '"';
				}
			case 4:
				if (!empty($args[3])){
					$out .= ' id="' . $args[3] . '"';
				}
			case 3:
				if (!empty($args[2])){
					$out .= ' placeholder="' . $args[2] . '"';
				}
			case 2:
				if (!empty($args[1])){
					$out .= ' size="' . $args[1] . '"';
				}
			default:
				if (isset($_REQUEST[$name])){
					if (mb_detect_encoding($_REQUEST[$name]) != false){
						//$val = mb_convert_encoding($_REQUEST[$name], 'SJIS', 'UTF-8');
						//$val = $_REQUEST[$name];
						$val = $_REQUEST[$name];
					}else{
						$val = $_REQUEST[$name];
					}
					//$val = $_REQUEST[$name];
					$out .= ' value="' . $val . '"';
				}else if(!empty($defaultValue)){
					$out .= ' value="' . $defaultValue . '"';
				}
			$out .= ' />';
		}
		/*
		if (!empty($size)){
			$out .= ' size="' . $size . '"';
		}
		if (!empty($class)){
			$out .= ' class="' . $class . '"';
		}
		if (!empty($id)){
			$out .= ' id="' . $id . '"';
		}
		if (!empty($label)){
			$out .= ' placeholder="' . $label . '"';
		}
		*/


		echo $out;
	}



	function radio($name, $options, $defaultValue = null){
		$out = '';
		$defaultCheckValue = @$_REQUEST[$name];
		if (!isset($defaultCheckValue)){
			if (!isset($defaultValue)){
				$defaultCheckValue = '0';
			}else{
				$defaultCheckValue = $defaultValue;
			}
		}
		foreach($options as $key => $value){
			$out .= '<input type="radio" name="' . $name . '" value="' . $key . '"';

			if ($defaultCheckValue == $key){
				$out .= ' checked="checked"';
			}

			$out .= ' /> ' . $value;
		}
		echo $out;
	}
	function select(/*$name, $options, $noSelectLabel, $id, $class*/){

		$args = func_get_args();
		$num_args = func_num_args();
		$name = $args[0];
		$options = $args[1];
		$select = array();
		switch($num_args){
			case 6:
				$select['defaultValue'] = $args[5];
			case 5:
				$select['clz'] = $args[4];
			case 4:
				$select['id'] = $args[3];
			case 3:
				$select['noselect'] = $args[2];
			case 2:
				$select['option'] = $args[1];
			default:
				$select['name'] = $args[0];
				break;
		}

		$out = '<select name="' . $select['name'] . '" ';
		if (!empty($select['id'])){
			$out .= 'id="' . $select['id'] . '" ';
		}
		if (!empty($select['clz'])){
			$out .= 'class="' . $select['clz'] . '" ';
		}
		$out .= '>';
		if (!empty($select['noselect'])){
			$out .= '<option value="0">' . $select['noselect'] . '</option>';
		}
		//ƒŠƒNƒGƒXƒg‚Ì’l
		$req = @$_REQUEST[$name];
		if (!isset($req)){
			$req = @$select['defaultValue'];
		}
		if (is_array($options)){
			foreach ($options as $key => $value){
				$out .= '<option value="' . $key . '"';
				if (isset($req) && $req == $key){
					$out .= ' selected="selected"';
				}
				$out .= '>' . $value . '</option>';
			}
		}else{
			print_r($options);
		}
		$out .= '</select>';
		echo $out;
	}

	function hidden(){
		//print_r(func_get_args());
		if (func_num_args() == 2){
			$args = func_get_args();
			$out = '<input type="hidden" ';
			$name = $args[0];
			$value = $args[1];
			$out .= 'name="' . $name . '" ';
			$out .= 'value="' . $value . '" />';
			echo $out;
		}

	}

	function price($price){
		$p = 0;
		$d = '0';
		if (!empty($price)){
			$p = $price;
			$d = number_format($p);
		}
		return '¥' . $d;
	}

	function echoPrice($price){
		echo $this->price($price);
	}
}
?>