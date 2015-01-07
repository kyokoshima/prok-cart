<?php

require_once 'app/cart.php';

define('DOC_ROOT', dirname($_SERVER['PHP_SELF']));

$req = $_REQUEST;
$step = @$_REQUEST['step'];
$cart =  new Cart();
// ini_set( 'display_errors', 1 );
// error_reporting(E_ALL);
if ($step == 'inputAccount'){
	$cart->inputAccount();
}elseif($step == 'confirm'){
	$cart->confirm();
}elseif($step == 'complete'){
	$cart->complete();
}elseif($step == 'addCart'){
	$cart->addCart();
}elseif($step == 'deleteCart'){
	$cart->deleteCart();
}elseif($step == 'recalcOrder'){
	$cart->recalcOrder();
}else{
	$cart->listProducts();
}