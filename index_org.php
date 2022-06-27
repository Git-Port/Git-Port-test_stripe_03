<?php

require_once( 'vendor/autoload.php' );

/*
 *  1. 顧客作成または既存顧客から cus_ から始まるIDを取得する
 *  2. Payment Intent(決済情報)を作成, cus_ と紐づける
 *  3. 決済(？)
 * */


$stripe = new \Stripe\StripeClient( 'sk_test_DPT5e5oBoRNZyswL9sJsjUYJ' );


# 商品値段×個数
$cart_items = array(
	array(
		'price'    => 10,
		'quantity' => 1,
	),
	array(
		'price'    => 100,
		'quantity' => 1,
	),
);

function calculate_order_amount( $cart_items )
{
	$amount = 0;
	foreach( $cart_items as $item ){
		$amount = $amount + $item['price'] * $item['quantity'];
	}
	
	return $amount;
}

try{
	
	# 1. 顧客作成または既存顧客から cus_ から始まるIDを取得する(※以下の例では新規顧客作成)
	$customer = $stripe->customers->create( [
		'name'        => 'Tsuru',
		'description' => 'Test User',
		'email'       => 'email@example.com',
	] );
	
	# 2. Payment Intent(決済情報)を作成, cus_ と紐づける
	$response = $stripe->paymentIntents->create( [
		'amount'               => calculate_order_amount( $cart_items ),
		'currency'             => 'jpy',
		'customer'             => $customer->id,
		'payment_method_types' => [ 'card' ],
		'receipt_email'        => "John@receipt.com",
		# 配送情報
		'shipping'             => array(
			'name'    => 'John Smith',
			'address' => array(
				'country' => '日本',
				'city'    => '東京都',
				'line1'   => '千代田区'
			)
		),
		'description'          => 'payment intent for cart items.',
		'metadata'             => array()
	] );
	
	//		echo "<pre>";
	//		var_dump( $response );
	//		echo "</pre>";
	
	//	$stripe->paymentMethods->attach([])
	
	//	$response = $stripe->paymentMethods->create( [
	//		'type'     => 'card',
	//		'customer' => $customer->id,
	//		'card'     => [
	//			'number'    => '4242424242424242',
	//			'exp_month' => 6,
	//			'exp_year'  => 2023,
	//			'cvc'       => '314',
	//		],
	//	] );
	//
	//	echo "<pre>";
	//	var_dump( $response );
	//	echo "</pre>";
	
} catch( Exception $e ){
	echo 'error : ' . $e->getMessage();
	echo '[an error occurred, unable to create payment intent]';
}
