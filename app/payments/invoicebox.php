<?php

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {
	if (isset($_REQUEST['participantId'])) {
			$participantId 		= IntVal($_REQUEST['participantId']);
	}
	if (isset($_REQUEST['OrderId'])) {
			$participantOrderId 	= IntVal($_REQUEST['participantOrderId']);
	}

	if ( !($participantId && $participantOrderId )){
			die( "Данные запроса не переданы" );
	}
	$order_id = $participantOrderId;
    if ($mode == 'notify') {
		
        $order_id = $participantOrderId;
		$order_info = fn_get_order_info($order_id);
		$ucode 		= trim($_REQUEST["ucode"]);
		$timetype 	= trim($_REQUEST["timetype"]);
		$time 		= str_replace(' ','+',trim($_REQUEST["time"]));
		$amount 	= trim($_REQUEST["amount"]);
		$currency 	= trim($_REQUEST["currency"]);
		$agentName 	= trim(html_entity_decode($_REQUEST["agentName"], ENT_QUOTES, 'UTF-8'));
		$agentPointName = trim(html_entity_decode($_REQUEST["agentPointName"], ENT_QUOTES, 'UTF-8'));
		$testMode 	= trim($_REQUEST["testMode"]);
		$sign	 	= trim($_REQUEST["sign"]);
		$processor_data = $order_info['payment_method'];
		$participant_apikey 	=  $processor_data['processor_params']['invoicebox_api_key'];
		$sign_strA = 
			$participantId .
			$participantOrderId .
			$ucode .
			$timetype .
			$time .
			$amount .
			$currency .
			$agentName .
			$agentPointName .
			$testMode .
			$participant_apikey;
		$sign_crcA = md5( $sign_strA ); 
		
		if ( strtolower($sign_crcA) != strtolower($sign) )
		{
			die( "Подпись запроса неверна" );
		}; 
			
            
            
           $order_status = 'P';
            fn_change_order_status($order_id, $order_status);
            
                
            die('OK');
   
       
    }elseif ($mode == 'return') {
		$order_status = 'P';
		$pp_response = array();
        $pp_response['order_status'] = $order_status;
        $pp_response['reason_text'] = 'Success payment';
        fn_finish_payment($order_id, $pp_response);
		fn_order_placement_routines('route', $order_id, false);
    }elseif ($mode == 'cancel') {
		$order_status = 'I';
        fn_change_order_status($order_id, $order_status);
		$order_id = $_REQUEST['order_id'];
        fn_order_placement_routines('route', $order_id, false);
    }

} else {
	
    $testmode = ($processor_data['processor_params']['invoicebox_testmode'] == 'test') ? 'test' : '';
    $order_id = $order_info['order_id'] ;
    $sh_cost = fn_order_shipping_cost($order_info);
    $need_shipping = $order_info['need_shipping'];
	$total_amount=0;
    $it = 0;
	$payment_desc = 'Order №'.$order_id.' - '.$order_info['total'] .' '. $order_info['secondary_currency'];
    if (is_array($order_info['products'])) {
        foreach ($order_info['products'] as $v) {
			$total_amount+=$v['amount'];
        }
    }
	if(count($order_info['payment_method']['tax_ids']) > 0){
		$vat = round($order_info['taxes'][$order_info['payment_method']['tax_ids'][0]]['rate_value']);
	} else {
		$vat = '0';
	}
    
    $notify_url = fn_url("payment_notification.notify?payment=invoicebox", AREA, 'current');
	$return = fn_url("payment_notification.return?payment=invoicebox", AREA, 'current');
	$returncancel = fn_url("payment_notification.cancel?payment=invoicebox", AREA, 'current');
	$signatureValue = md5(
			$processor_data['processor_params']['invoicebox_participant_id'].
			$order_id.
			$order_info['total'].
			$order_info['secondary_currency'].
			$processor_data['processor_params']['invoicebox_api_key']
			); 
	$form_data["itransfer_participant_sign"] = $signatureValue;
	$form_data["itransfer_participant_id"] = $processor_data['processor_params']['invoicebox_participant_id'];
    $form_data["itransfer_participant_ident"] = $processor_data['processor_params']['invoicebox_participant_ident'];
    $form_data["itransfer_participant_sign"] = $signatureValue;
    $form_data["itransfer_order_id"] = $order_id;
    $form_data["itransfer_order_amount"] =$order_info['total'];
    $form_data["itransfer_order_quantity"] = $total_amount;
    $form_data["itransfer_testmode"] = $testmode;
    $form_data["itransfer_order_currency_ident"] = $order_info['secondary_currency'];
    $form_data["itransfer_order_description"] = $payment_desc;
    $form_data["itransfer_person_name"] = $order_info['b_firstname'] . ' ' . $order_info['b_lastname'];
    $form_data["itransfer_person_email"] = $order_info['email'];
    $form_data["itransfer_person_phone"] = $order_info['phone'];
    $form_data["itransfer_body_type"] ="PRIVATE";
    $form_data["itransfer_url_return"] = $return;
    $form_data["itransfer_url_returnsuccess"] = $return;
    $form_data["itransfer_url_notify"] = $notify_url;
    $form_data["itransfer_url_cancel"] = $returncancel;
    $form_data["itransfer_cms_name"] ="CS-Cart";
	if (!empty($order_info['products'])) {
        foreach ($order_info['products'] as $k => $v) {
            $it++;
            $price = fn_format_price($v['price'] - (fn_external_discounts($v) / $v['amount']));
			$form_data["itransfer_item{$it}_name"] = $v['product'];
            $form_data["itransfer_item{$it}_quantity"] = $v['amount'];
            $form_data["itransfer_item{$it}_price"] = $price;
            $form_data["itransfer_item{$it}_vatrate"] = $vat;
			$form_data["itransfer_item{$it}_measure"] = 'шт.';
        }
    }
	if ($sh_cost > 0) {
		$it++;
		$form_data["itransfer_item{$it}_name"] = $order_info['shipping'][0]['shipping'];
        $form_data["itransfer_item{$it}_quantity"] = "1";
        $form_data["itransfer_item{$it}_price"] = $sh_cost;
		$form_data["itransfer_item{$it}_measure"] = 'шт.';
        //$form_data["itransfer_item{$suffix}_vatrate"] = $vat;
	}
    fn_create_payment_form('https://go.invoicebox.ru/module_inbox_auto.u', $form_data, 'Invoicebox', false);
}

exit;



