<?php 
define('PATH_ROOT', dirname(dirname(__FILE__)));
define('SES_ORDERS', 'orders');
define('SES_ACCOUNT', 'account');
define('SES_CHECKOUT', 'checkOut');

define('PRD_CD', 'product_code');
define('PRD_NAME', 'product_name');
define('PRD_QTY', 'qty');
define('PRD_PRICE', 'price');
define('PRD_SW', 'shipping_weight');
define('PRD_COD', 'cash_on_delivery');

define('ORD_NM', 'order_name');
define('ORD_CG', 'order_charge');
define('ORD_ZIP', 'order_zipcode');
define('ORD_PREF', 'order_pref');
define('ORD_CITY', 'order_city');
define('ORD_TOWN', 'order_town');
define('ORD_BUILD', 'order_build_name');
define('ORD_PHONE', 'order_phone');
define('ORD_FAX', 'order_fax');
define('ORD_MAIL', 'order_mail');
define('ORD_SHIP_COND', 'order_ship_condition');
define('ORD_DELIV_DT', 'deliver_date');
define('ORD_DELIV_TM', 'deliver_time');
define('ORD_REM', 'order_remarks');
define('ORD_C_CD', 'order_campaign_code');
define('RCV_NM', 'reciever_name');
define('RCV_CG', 'reciever_charge');
define('RCV_ZIP', 'reciever_zipcode');
define('RCV_PREF', 'reciever_pref');
define('RCV_CITY', 'reciever_city');
define('RCV_TOWN', 'reciever_town');
define('RCV_BUILD', 'reciever_build_name');
define('RCV_PHONE', 'reciever_phone');
define('PAYMENT', 'payment');
define('CARD_BRAND', 'card_brand');
define('CARD_ACCOUNT', 'card_account');
define('CARD_NUMBER', 'card_number');
define('CARD_NUMBER_1', 'card_number[0]');
define('CARD_NUMBER_2', 'card_number[1]');
define('CARD_NUMBER_3', 'card_number[2]');
define('CARD_NUMBER_4', 'card_number[3]');
define('CARD_EXPIRE', 'card_expire');
define('CARD_MONTH', 'card_month');
define('CARD_YEAR', 'card_year');
define('CARD_PAYCOUNT', 'card_paycount');

define('CONFIG', PATH_ROOT . '/config/config.ini');
define('INI_PREF', 'prefectures');
define('INI_SC', 'shipConditions');
define('INI_DT', 'deliveryTimes');
define('INI_PAYMENT', 'payments');
define('INI_CARD_BRAND', 'card_brands');
define('INI_CARD_MONTH', 'card_months');
define('INI_CARD_YEAR', 'card_years');
define('INI_CARD_PAYCOUNT', 'card_paycounts');
define('INI_FREE_SHIPPING', 'free_shipping_base_price');
define('INI_FREE_SHIPPING_PRICE', 'price');
define('INI_CAMPAIGN', 'campaign');
define('INI_CAMPAIGN_CODE', 'code');
define('INI_CAMPAIGN_FREE_SHIPPING', 'free_shipping_base_price');
define('INI_CAMPAIGN_DISCOUNT', 'discount_price');

define('INI_SHIPPING_PRICES', 'shipping_prices');
define('INI_SHIPPING_OPTION', 'shipping_option');
define('INI_LIMIT_SHIPPING_OF_AREA', 'limit_shipping_of_area');
define('INI_HAS_SW_IS_ZERO', 'has_sw_is_zero');
define('INI_OVER_PRICE', 'over_price');
define('INI_UNI_PRICE', 'unification_price');


define('INI_SUBJECT', 'subject');
define('INI_SUBJECT_TO_USER', 'to_user');
define('INI_SUBJECT_TO_ADMIN', 'to_admin');


define('INI_N1', 'notice1');
define('INI_N2', 'notice2');
define('INI_N3', 'notice3');

define('PAYMENT_CARD', '0');
define('PAYMENT_EXCHANGE', '1');

?>