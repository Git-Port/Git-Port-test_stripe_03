const publicKey = 'pk_test_fCAZxCdH4sliECV5AsY3FM9s';

const stripe = Stripe(publicKey, { betas : [ 'payment_intent_beta_3' ] });
const elements = stripe.elements();

// スタイルのカスタマイズ
const style = {
	base       : {
		color : '#32325d', fontFamily : '"Helvetica Neue", Helvetica, sans-serif', fontSmoothing : 'antialiased', fontSize : '16px', '::placeholder' : { color : '#aab7c4' }
	}, invalid : {
		color : '#fa755a', iconColor : '#fa755a'
	}
};

// クレジットカード情報入力欄の構築
const card = elements.create('card', { style : style, hidePostalCode : true });
card.mount('#card-element');

// 入力変更時のリスナー
// バリデーションメッセージを表示する
card.addEventListener('change', (event) => {
	let displayError = document.getElementById('card-errors');
	if( event.error ){
		displayError.textContent = event.error.message;
	} else {
		displayError.textContent = '';
	}
});

// submit時のリスナー
let setupIntent = {};
const form = document.getElementById('form');
form.addEventListener('submit', async (event) => {
	event.preventDefault();
	
	// setupIntentObj がからの場合は  Setup Intents に接続して情報を取得
	setupIntent = await getSetupIntent();
	
	if( setupIntent !== undefined ){
		
		stripe.confirmCardSetup(setupIntent.client_secret, {
			payment_method : { card : card }
		}).then((result) => {
			if( result.error ){
				// error
				stripeDisplayErrorMessage(result.error.message);
			} else {
				// success
				stripePaymentMethodHandler(result.setupIntent.payment_method);
			}
		});
	}
	
});

// サーバー側に送るパラメーター作成し、GET送信
const stripePaymentMethodHandler = (payment_method) => {
	
	let hiddenInputToken = document.createElement('input');
	hiddenInputToken.setAttribute('type', 'hidden');
	hiddenInputToken.setAttribute('name', 'stripe_payment_method_id');
	hiddenInputToken.setAttribute('value', payment_method);
	
	form.appendChild(hiddenInputToken);
	form.submit();
}

// Setup Intents に接続
const getSetupIntent = () => {
	return axios.post('./setupintents.php')
							.then((response) => response.data)
							.then((setupIntentObj) => {
								if( setupIntentObj.error ){
									stripeDisplayErrorMessage(setupIntentObj.error.message);
								} else {
									return setupIntentObj;
								}
							});
};

const stripeDisplayErrorMessage = (message) => {
	const displayError = document.getElementById('card-errors');
	displayError.textContent = message;
}

// オブジェクトの空判定
const isEmpty = (obj) => {
	for( let prop in obj ){
		if( Object.prototype.hasOwnProperty.call(obj, prop) ){
			return false;
		}
	}
	return JSON.stringify(obj) === JSON.stringify({});
}

