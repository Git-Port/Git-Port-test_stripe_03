<?php

require_once( 'vendor/autoload.php' );


$stripe = new \Stripe\StripeClient( 'sk_test_DPT5e5oBoRNZyswL9sJsjUYJ' );

try{
	
	$setup_intent = $stripe->setupIntents->create( [
		'payment_method_types' => [ 'card' ],
	] );
	
	# jsonに変換して返す
	echo json_encode( $setup_intent );
	
} catch( Exception $e ){
	echo 'error : ' . $e->getMessage();
	echo '[an error occurred, unable to create payment intent]';
}
