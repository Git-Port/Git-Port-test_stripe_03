<?php

$base_page_url     = "https://olupono-test-stripe:8890";
$thanks_page_url   = $base_page_url . "/thanks.html";
$failed_page_url   = $base_page_url . "/failed.html";
$payment_method_id = '';

# stripe_payment_method_id に値がない場合は処理中止
if( $_POST['stripe_payment_method_id'] != '' && isset( $_POST['stripe_payment_method_id'] ) ){
	$payment_method_id = $_POST['stripe_payment_method_id'];
} else {
	header( 'Location: ' . $base_page_url );
	exit;
}

require_once( 'vendor/autoload.php' );

/*
 *  1. 顧客作成または既存顧客から cus_ から始まるIDを取得する
 *  2. Payment Intent(決済情報)を作成, cus_ と紐づける
 *  3. 決済(？)
 *
 * Stripe Checkoutを利用した場合の、Customer作成タイミングについて
 * https://qiita.com/hideokamoto/items/4e1c64da1dd227b09733
 * */

$stripe = new \Stripe\StripeClient( 'sk_test_DPT5e5oBoRNZyswL9sJsjUYJ' );

$cart_items = array(
	array(
		'price'    => 20,
		'quantity' => 1,
	),
	array(
		'price'    => 100,
		'quantity' => 1,
	),
);

# 商品値段×個数
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
	
	// customer を paymentMethod へアタッチ
	$payment_method = $stripe->paymentMethods->retrieve( $payment_method_id );
	$payment_method->attach( [ 'customer' => $customer->id ] );
	
	# 2. Payment Intent(決済情報)を作成, cus_ と紐づける
	$response = $stripe->paymentIntents->create( [
		'amount'         => calculate_order_amount( $cart_items ),
		'currency'       => 'jpy',
		'customer'       => $customer->id,
		'payment_method' => $payment_method_id,
		//		'payment_method_types' => [ 'card' ], // 不明
		'off_session'    => true, // 支払い情報を保存, ECサイトでの２回目以降の購入処理や、「手付金を請求し、後で残額を請求する」ビジネスモデルなどへの対応が可能
		'confirm'        => true, // true にすることで決済完了
		'receipt_email'  => "John@receipt.com",
		# 配送情報
		'shipping'       => array(
			'name'    => 'John Smith',
			'address' => array(
				'country' => '日本',
				'city'    => '東京都',
				'line1'   => '千代田区'
			)
		),
		'description'    => 'payment intent for cart items.',
		'metadata'       => array()
	] );
	
	echo $_POST['name'];
	
	echo "<pre>";
	var_dump( $response );
	echo "</pre>";
	
	//	// 本番では削除する, リロード対策
	//	$payment_method_id = '';
	
	# 成功時は thanks.page
	if( $response->status == 'succeeded' ){
		header( 'Location: ' . $thanks_page_url );
		exit;
	}
	
	//	#  thanks.page
	//	header( 'Location: ' . $failed_page_url );
	//	exit;
	
} catch( Exception $e ){
	echo 'error : ' . $e->getMessage() . '<br/>';
	echo '[an error occurred, unable to create payment intent]<br/>';
}
