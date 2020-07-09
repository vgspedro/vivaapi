
# Viva Wallet Native Checkout V2 API PHP Wrapper Library by Aleksey Kuleshov

## This package is based on Aleksey Kuleshov work.

## Has been modded to suit my needs.

This is a wrapper for Native Checkout V2 API of Viva Wallet: https://developer.vivawallet.com/online-checkouts/native-checkout-v2/

## How to use

This library is installed via [Composer](http://getcomposer.org/). You will need to require `vgspedro/vivaapi`:

```
composer require vgspedro/vivaapi
```

#### Symfony framework

#### Create the Controler

# src/Controler/Payment.php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use App\Service\VivaWallet;

class PaymentController extends AbstractController
{
    private $environment;
    private $amount = 36812;

    public function __construct(ParameterBagInterface $environment)
    {
        $this->environment = $environment;
        $this->amount = 36812;
    }

    public function index(VivaWallet $viva)
    {

        return $this->render('admin/payment/native.html', [
            'amount' => $this->amount,
            'viva_token' => $viva->getCardChargeToken(),
            'sf_v' => \Symfony\Component\HttpKernel\Kernel::VERSION,
            'payment_url' => $this->environment->get("kernel.environment") == 'prod' ? 'https://www.vivapayments.com' : 'https://demo-api.vivapayments.com',
        ]);
    }


    /**
    * Make some transations accordind the "action" value from the form
    **/
    public function submit(Request $request, VivaWallet $viva)
    {       
        $pre_auth = $request->request->get('action') == 'authorization' ? false : true;

        $client = [
            'email' => $request->request->get('email'),
            'phone' => $request->request->get('phone'),
            'full_name' => $request->request->get('name'),
            'request_lang' => 'pt',
            'country_code' => 'PT'
        ];

        $transaction = [
            'amount' => $this->amount,
            'installments' => 1,
            'charge_token' => $request->request->get('token'),
            'merchant_trans' => 'Information to the Merchant',
            'customer_trans' => 'Information to the Client ' .$request->request->get('action'),
            'tip_amount' => 0,
            'pre_auth' => $pre_auth,
            'currency_code' => 978// https://pt.iban.com/currency-codes
        ];

        if($request->request->get('action') == 'charge'){
            $charge = $viva->setCharge($client, $transaction);
            //Something went wrong send info to user
            if ($charge['status'] == 0)
                return new JsonResponse([
                    'status' => 0,
                    'message' => $charge['data'],
                    'data' => $charge
                ]);

            return new JsonResponse([
                'status' => 1,
                'message' => $charge['data'],
                'data' => $trans
            ]);

        }
        
        else if($request->request->get('action') == 'authorization'){    
            $charge = $viva->setAutorization($client, $transaction);
            //Something went wrong send info to user
            if ($charge['status'] == 0)
                return new JsonResponse([
                    'status' => 0,
                    'message' => $charge['data'],
                    'data' => $charge
                ]);
            
            return new JsonResponse([
                'status' => 1,
                'message' => $charge['data'],
                'data' => $charge
            ]);

        }

        else if($request->request->get('action') == 'charge_capture'){
            $charge = $viva->setCharge($client, $transaction);
            //Something went wrong send info to user
            if ($charge['status'] == 0)
                return new JsonResponse([
                    'status' => 0,
                    'message' => $charge['data'],
                    'data' => $charge
                ]);
            
            $capture = $viva->setCapture($charge['data']->transactionId, $transaction['amount']);
            
            return new JsonResponse([
                'status' => 1,
                'message' => $capture['data'],
                'data' => $capture
            ]);
        }

        else if($request->request->get('action') == 'charge_cancel'){
            $charge = $viva->setCharge($client, $transaction);

            //Something went wrong send info to user
            if ($charge['status'] == 0)
                return new JsonResponse([
                    'status' => 0,
                    'message' => $charge['data'],
                    'data' => $charge
                ]);
            
            //
            $cancel = $viva->setCancel($charge['data']->transactionId, $transaction['amount']);
            
            return new JsonResponse([
                'status' => 1,
                'message' => $cancel['data'],
                'data' => $cancel
            ]);

        }

        //Something went wrong send info to user
            return new JsonResponse([
                'status' => 0,
                'message' => 'Not Processed',
                'data' => null
            ]);
    }

}


#### Create the Service

# src/Service/VivaWallet.php

namespace App\Service;

use \VgsPedro\VivaApi\Transaction\Authorization;
use \VgsPedro\VivaApi\Transaction\Url;
use \VgsPedro\VivaApi\Transaction\Customer;
use \VgsPedro\VivaApi\Transaction\Charge;
use \VgsPedro\VivaApi\Transaction\Capture;
use \VgsPedro\VivaApi\Transaction\Cancel;
use \VgsPedro\VivaApi\Transaction\ChargeToken;
use \VgsPedro\VivaApi\Transaction\Installments;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class VivaWallet
{

	private $test_mode; // Boolean 
 	private $client_id; // Client ID, Provided by wallet
 	private $client_secret; // Client Secret, Provided by wallet
    private $url; // Url to make request, sandbox or live (sandbox APP_ENV=dev or test) (live APP_ENV=prod)
    private $merchant_id; //Merchant ID , Provided by wallet
    private $api_key; //Api Key, Provided by wallet
	private $headers; //Set the authorization to curl

    public function __construct(ParameterBagInterface $environment){
		$this->test_mode = true;
 		$this->client_id = 'ef7ee87mrt0grg62dbmwms0xzvu29owz5202f9b03ceo7.apps.vivapayments.com';
		$this->client_secret = '4M7ug3jfUh1wZ2Q442Y0L3MDxHz35E';
		$this->api_key = '71-}w%';
        $this->url = $environment->get("kernel.environment") == 'prod' ? 'https://www.vivapayments.com' : 'https://demo-api.vivapayments.com';
    }

	/**
	* Create an authentication Token to pass to client side js  
	* @return string $accessToken 
	**/
	public function getCardChargeToken(){

		$baseUrl = Url::getUrl($this->test_mode); //Test mode, default is false
		$accessToken = (new Authorization())
		->setClientId($this->client_id) // Client ID, Provided by wallet
		->setClientSecret($this->client_secret) // Client Secret, Provided by wallet
		->setTestMode($this->test_mode) // Test mode, default is false, can be skipped
		->getAccessToken();
		return $accessToken;
	}

	/**
	* Create a charge transaction
	*@param $client // Information of the user 
	*@param $trans // Information of the charge transaction 
	**/	
	public function setCharge(array $client, array $trans){

		$customer = (new Customer())
			->setEmail($client['email'])
			->setPhone($client['phone'])
			->setFullName($client['full_name'])
	      	->setRequestLang($client['request_lang'])
      		->setCountryCode($client['country_code']);

		$transaction = (new Charge())
			->setClientId($this->client_id) // Client ID, Provided by wallet
			->setClientSecret($this->client_secret) // Client Secret, Provided by wallet
			->setTestMode($this->test_mode) // Test mode, default is false, can be skipped
			->setSourceCode('') // Source code, provided by wallet
			->setAmount($trans['amount']) // The amount to charge in currency's smallest denomination (e.g amount in pounds x 100)
			->setInstallments($trans['installments']) // Installments, can be skipped if not used
			->setChargeToken($trans['charge_token']) // Charge token obtained at front end
 			->setMerchantTrns( $trans['merchant_trans'])
 			->setCustomerTrns($trans['customer_trans'])
			->setTipAmount($trans['tip_amount'])
			->setCustomer($customer)
			->setPreAuth($trans['pre_auth']); //If true, a PreAuth transaction will be performed. This will hold the selected amount as unavailable (without the customer being charged) for a period of time.

		$result = $transaction->send();

		if (!empty($transaction->getError()))
			return [
				'status' => 0,
				'data' => $transaction->getError()
			];
			
		return [
			'status' => 1,
			'data' => $result
		];
	}

	/**
	* Create a charge transaction, the amount is captured and charged. 
	*@param $client // Information of the user 
	*@param $trans // Information of the charge transaction 
	**/	
	public function setAutorization(array $client, array $trans){

		$customer = (new Customer())
			->setEmail($client['email'])
			->setPhone($client['phone'])
			->setFullName($client['full_name'])
	      	->setRequestLang($client['request_lang'])
      		->setCountryCode($client['country_code']);

		$transaction = (new Authorization())
			->setClientId($this->client_id) // Client ID, Provided by wallet
			->setClientSecret($this->client_secret) // Client Secret, Provided by wallet
			->setTestMode($this->test_mode) // Test mode, default is false, can be skipped
			->setSourceCode('') // Source code, provided by wallet
			->setAmount($trans['amount']) // The amount to pre-auth in currency's smallest denomination (e.g amount in pounds x 100)
			->setInstallments($trans['installments']) // Installments, can be skipped if not used
			->setChargeToken($trans['charge_token']) // Charge token obtained at front end
			->setCustomer($customer)
			->setPreAuth($trans['pre_auth']);//If true, a PreAuth transaction will be performed. This will hold the selected amount as unavailable (without the customer being charged) for a period of time.

		$result = $transaction->send();

		if (!empty($transaction->getError()))
			return [
				'status' => 0,
				'data' => $transaction->getError()
			];
			
		return [
			'status' => 1,
			'data' => $result
		];
	}


	/**
	* Capture a charge transaction
	*@param $t_i // Transaction id of authorization transaction
	*@param $amount // The amount to capture in currency's smallest denomination (e.g amount in pounds x 100)
	**/
	public function setCapture(string $t_i, int $amount){

		$transaction = (new Capture())
			->setClientId($this->client_id) // Client ID, Provided by wallet
			->setClientSecret($this->client_secret) // Client Secret, Provided by wallet
			->setTestMode($this->test_mode) // Test mode, default is false, can be skipped
			->setTransactionId($t_i) // Transaction id of authorization transaction
			->setAmount($amount); // The amount to capture in currency's smallest denomination (e.g amount in pounds x 100)

		$result = $transaction->send();

		if (!empty($transaction->getError()))
			return [
				'status' => 0,
				'data' => $transaction->getError()
			];
			
		return [
			'status' => 1,
			'data' => $result
		];
	}


	/**
	* Cancel a charge transaction
	*@param  $t_i // Transaction id of authorization transaction
	*@param $amount // The amount to capture in currency's smallest denomination (e.g amount in pounds x 100)
	**/
	public function setCancel(string $t_i, int $amount){

		$transaction = (new Cancel())
			->setClientId($this->client_id) // Client ID, Provided by wallet
			->setClientSecret($this->client_secret) // Client Secret, Provided by wallet
			->setTestMode($this->test_mode) // Test mode, default is false, can be skipped
			->setTransactionId($t_i) // Transaction id of authorization transaction
			->setAmount($amount)// The amount to capture in currency's smallest denomination (e.g amount in pounds x 100)
			->setSourceCode(''); // Source code, provided by wallet

		$result = $transaction->send();

		if (!empty($transaction->getError()))
			return [
				'status' => 0,
				'data' => $transaction->getError()
			];
			
		return [
			'status' => 1,
			'data' => $result
		];
	}



	/**
	* Is possible to get charge token at backend.
	* It may be required in custom integration, more details can be found here: https://developer.vivawallet.com/online-checkouts/native-checkout-v2/
	* @param $card // All the info of the card to make the charge
	* @param $url_redirect // Url to redirect when authentication session finished
	**/
	public function getChargeTokenAtBackend(array $card, string $url_redirect){

		$transaction = (new ChargeToken())
			->setClientId($this->client_id) // Client ID, Provided by wallet
			->setClientSecret($this->client_secret) // Client Secret, Provided by wallet
			->setTestMode($this->test_mode) // Test mode, default is false, can be skipped
			->setAmount($card['amount']) // The amount in currency's smallest denomination (e.g amount in pounds x 100)
			->setCvc($card['cvc']) // Card cvc code
			->setNumber($card['card_number']) // Card number
			->setHolderName($card['holder_name']) // Card holder name
			->setExpirationYear($card['expiration_year']) // Card expiration year
			->setExpirationMonth($card['expiration_month']) // Card expiration month
			->setSessionRedirectUrl($url_redirect); // Url to redirect when authentication session finished
		$result = $transaction->send();

		if (!empty($transaction->getError()))
			return [
				'status' => 0,
				'data' => $transaction->getError()
			];
			
		// Get charge token
		// $chargeToken = $result->chargeToken;
		// $redirectToACSForm = $result->redirectToACSForm;	
		return [
			'status' => 1,
			'data' => $result
		];

	}

	/**
	* Check for installments
	* Retrieve the maximum number of installments allowed on a card.
	*@param $card_number // Number of the credit card
	**/
	public function getInstalments(string $card_number){

		$transaction = (new Installments())
			->setClientId($this->client_id) // Client ID, Provided by wallet
			->setClientSecret($this->client_secret) // Client Secret, Provided by wallet
			->setTestMode($this->test_mode) // Test mode, default is false, can be skipped
			->setNumber($card_number); // Card number

		$result = $transaction->send();

		if (!empty($transaction->getError()))
			return [
				'status' => 0,
				'data' => $transaction->getError()
			];
			
		// Get number of installments
		// $installments = $result->maxInstallments;
		return [
			'status' => 1,
			'data' => $result
		];
	}


#### Create the Template

# templates/admin/payment/native.html


```html
<pre>

  <script type="text/javascript" src="https://www.vivapayments.com/web/checkout/v2/js"></script>

    <form action="" method="POST" id="payment-form" class="container pt-4">
      <div class="form-row">
        <label>
          <span>Name</span>
          <input type="text" size="20" name="name" autocomplete="off" value="Pedro V" />
        </label>
      </div>
      <div class="form-row">
        <label>
          <span>Phone</span>
          <input type="text" size="20" name="phone" autocomplete="off" value="963963963" />
        </label>
      </div>
      <div class="form-row">
        <label>
          <span>Email</span>
          <input type="text" size="20" name="email" autocomplete="off" value="vgspedro@gmail.com" />
        </label>
        </div>  
        <div class="form-row">
          <label>
            <span>Cardholder Name</span>
            <input type="text" size="20" name="txtCardHolder" autocomplete="off" data-vp="cardholder" value="Pedro" />
            </label>
        </div>

        <div class="form-row">
            <label>
                <span>Card Number</span>
                <input type="text" size="20" name="txtCardNumber" autocomplete="off" data-vp="cardnumber" value="4111111111111111" />
            </label>
        </div>
        <div class="form-row">
            <label>
                <span>CVV</span>
                <input type="text" name="txtCVV" size="4" autocomplete="off" data-vp="cvv" value="111" />
            </label>
        </div>
        <div class="form-row">
          <label>
            <span>Expiration (MM/YYYY)</span>
          </label>
          <input type="text" size="2" name="txtMonth" autocomplete="off" data-vp="month" value="10" />
          <span> / </span>
          <input type="text" size="04" name="txtYear" autocomplete="off" data-vp="year" value="2024" />
        </div>
        <input name="token" type="hidden">
           <div class="form-row">
        <label title="Check your VivaWallet account to see the current status of the Payment"> Payment Actions
          <select name="action">
            <option value="charge">Charge Only</option>
            <option value="authorization">Authorized</option>
            <option value="charge_capture">Charge & Capture</option>
            <option value="charge_cancel">Charge & Cancel</option>
          </select>
        </label>
      </div>
        <button class="btn btn-success" type="button" id="submit">Submit Payment </button>
    </form>
    <hr>
	<h3>Options</h3>
	Charge Only = Create a transaction to be Captured<br>
	Authorized = Create a transaction and Captured the amount<br>
	Charge & Capture = Create a transaction then Capture the amount<br>
	Charge & Cancel = Create a transaction then Cancel the transaction<br>


    <div id="threed-pane" style="height: 450px;width:500px"></div>

    <script type="text/javascript">
      $(document).ready(function () {
        VivaPayments.cards.setup({
          baseURL: '{{ payment_url }}',
          authToken: '{{ viva_token }}',
          cardHolderAuthOptions: {
            cardHolderAuthPlaceholderId: 'threed-pane',
              cardHolderAuthInitiated: function () {
                $('#threed-pane').show();
              },
              cardHolderAuthFinished: function () {
                $('#threed-pane').hide();
              }
            },
            installmentsHandler: function (response) {
              if (!response.Error) {
                if (response.MaxInstallments == 0)
                  return;
                $('#drpInstallments').show();
                for (i = 1; i <= response.MaxInstallments; i++) {
                  $('#drpInstallments').append($("<option>").val(i).text(i));
                }
              }
              else {
                toastr['error'](response.Error);
              }
            }
          });
          $('#submit').on('click', function (evt) {
            evt.preventDefault();
            VivaPayments.cards.requestToken({
              amount: {{amount}}
            }).done(function (data) {

              $('[name=token]').val(data.chargeToken)

              $('.loader').removeClass('d-none');
                setTimeout(function(){
                $.ajax({  
                  url:'{{path("payment_submit")}}',
                  type: "POST",
                  data: $('#payment-form').serialize(),
                  cache: false,
                  success: function(data){  
                    $('.loader').addClass('d-none');
                    console.log(data)
                    if (data.status == 1){
                      toastr['success']('{%trans%}success{%endtrans%} - Transaction '+data.message.transactionId);
                    }
                    else if (data.status == 0){
                      toastr['info'](data.message);
                    }
                    else{
                      for(var i in data.data)
                        obj += data.data[i]+'<br>';
                      toastr['info'](obj);
                  }
                },
                error:function(data){
                  $('.loader').addClass('d-none');
                  toastr['error']('{%trans%}wifi_error{%endtrans%}');
                }
              })
            }, 500)
            console.log(data);
            //alert(data.chargeToken);
          });
        });
      });
    </script>
```

#### Add the Routes 

# config/routes.yaml

payment:
    path: /admin/payment
    controller: App\Controller\PaymentController::index
    
payment_submit:
    path: /admin/payment-submit
    controller: App\Controller\PaymentController::submit
    condition: 'request.isXmlHttpRequest()'
    methods: [POST]



## Prerequisites

Complete prerequisite steps from https://developer.vivawallet.com/online-checkouts/native-checkout-v2/ and obtain your `Client ID` and `Client Secret`.
You'll need to set up a payment source with Native Checkout V2  as the integration method and get a `Source Code`.

## Get card charge token

Create payment form and `Charge Token` at front end as described here: https://developer.vivawallet.com/online-checkouts/native-checkout-v2/
You'll need to have `Access Token` and `Base URL` at front end and you can get them as follows:


```php
$baseUrl = \ATDev\Viva\Transaction\Url::getUrl("[Test Mode]"); // Test mode, default is false

$accessToken = (new \ATDev\Viva\Transaction\Authorization())
	->setClientId("[Client ID]") // Client ID, Provided by wallet
	->setClientSecret("[Client Secret]") // Client Secret, Provided by wallet
	->setTestMode("[Test Mode]") // Test mode, default is false, can be skipped
	->getAccessToken();
```

Now, when you have `Charge Token` you can make actual transactions.

## Transactions

### CHARGE

```php
$customer = (new \ATDev\Viva\Transaction\Customer())
	->setEmail("[Customer Email]")
	->setPhone("[Customer Phone]")
	->setFullName("[Customer Full Name]");

$transaction = (new ATDev\Viva\Transaction\Charge())
	->setClientId("[Client ID]") // Client ID, Provided by wallet
	->setClientSecret("[Client Secret]") // Client Secret, Provided by wallet
	->setTestMode("[Test Mode]") // Test mode, default is false, can be skipped
	->setSourceCode("[Source Code]") // Source code, provided by wallet
	->setAmount((int) "[Amount]") // The amount to charge in currency's smallest denomination (e.g amount in pounds x 100)
	->setInstallments((int) "[Installments]") // Installments, can be skipped in not used
	->setChargeToken("[Charge Token]") // Charge token obtained at front end
	->setCustomer($customer);

$result = $transaction->send();

if (!empty($transaction->getError())) {

	// Log the error message
	// $error = $transaction->getError();

} else {

	// Save transaction id
	// $transactionId = $result->transactionId;
}
```

### AUTHORIZATION

```php
$customer = (new \ATDev\Viva\Transaction\Customer())
	->setEmail("[Customer Email]")
	->setPhone("[Customer Phone]")
	->setFullName("[Customer Full Name]");

$transaction = (new ATDev\Viva\Transaction\Authorization())
	->setClientId("[Client ID]") // Client ID, Provided by wallet
	->setClientSecret("[Client Secret]") // Client Secret, Provided by wallet
	->setTestMode("[Test Mode]") // Test mode, default is false, can be skipped
	->setSourceCode("[Source Code]") // Source code, provided by wallet
	->setAmount((int) "[Amount]") // The amount to pre-auth in currency's smallest denomination (e.g amount in pounds x 100)
	->setInstallments((int) "[Installments]") // Installments, can be skipped in not used
	->setChargeToken("[Charge Token]") // Charge token obtained at front end
	->setCustomer($customer);

$result = $transaction->send();

if (!empty($transaction->getError())) {

	// Log the error message
	// $error = $transaction->getError();

} else {

	// Save transaction id
	// $transactionId = $result->transactionId;
}
```

### CAPTURE

Make sure you have recurring payments enabled in your account.

```php
$transaction = (new \ATDev\Viva\Transaction\Capture())
	->setClientId("[Client ID]") // Client ID, Provided by wallet
	->setClientSecret("[Client Secret]") // Client Secret, Provided by wallet
	->setTestMode("[Test Mode]") // Test mode, default is false, can be skipped
	->setTransactionId("[Transaction ID]") // Transaction id of authorization transaction
	->setAmount((int) "[Amount]"); // The amount to capture in currency's smallest denomination (e.g amount in pounds x 100)

$result = $transaction->send();

if (!empty($transaction->getError())) {

	// Log the error message
	// $error = $transaction->getError();
} else {

	// Save transaction id
	// $transactionId = $result->transactionId;
}
```

### CANCEL

Make sure you have refunds enabled in your account.

```php
$transaction = (new \ATDev\Viva\Transaction\Cancel())
	->setClientId("[Client ID]") // Client ID, Provided by wallet
	->setClientSecret("[Client Secret]") // Client Secret, Provided by wallet
	->setTestMode("[Test Mode]") // Test mode, default is false, can be skipped
	->setSourceCode("[Source Code]") // Source code, provided by wallet
	->setTransactionId("[Transaction ID]") // Transaction id of charge, authorization or capture transaction
	->setAmount((int) "[Amount]"); // The amount to refund in currency's smallest denomination (e.g amount in pounds x 100)

$result = $transaction->send();

if (!empty($transaction->getError())) {

	// Log the error message
	// $error = $transaction->getError();
} else {

	// Save transaction id
	// $transactionId = $result->transactionId;
}
```

## Get charge token at backend

It's possible to get charge token at backend. It may be required in custom integration, more details can be found here: https://developer.vivawallet.com/online-checkouts/native-checkout-v2/

```php
$transaction = (new \ATDev\Viva\Transaction\ChargeToken())
	->setClientId("[Client ID]") // Client ID, Provided by wallet
	->setClientSecret("[Client Secret]") // Client Secret, Provided by wallet
	->setTestMode("[Test Mode]") // Test mode, default is false, can be skipped
	->setAmount((int) "[Amount]"); // The amount in currency's smallest denomination (e.g amount in pounds x 100)
	->setCvc("[Cvc code]") // Card cvc code
	->setNumber("[Card number]") // Card number
	->setHolderName("[Holder name]") // Card holder name
	->setExpirationYear((int) "[Expiration Year]") // Card expiration year
	->setExpirationMonth((int) "[Expiration Month]") // Card expiration month
	->setSessionRedirectUrl("[Session redirect url]"); // Url to redirect when authentication session finished

$result = $transaction->send();

if (!empty($transaction->getError())) {

	// Log the error message
	// $error = $transaction->getError();
} else {

	// Get charge token
	// $chargeToken = $result->chargeToken;
	// $redirectToACSForm = $result->redirectToACSForm;
}
```

## Check for installments

Retrieve the maximum number of installments allowed on a card.

```php
$transaction = (new \ATDev\Viva\Transaction\Installments())
	->setClientId("[Client ID]") // Client ID, Provided by wallet
	->setClientSecret("[Client Secret]") // Client Secret, Provided by wallet
	->setTestMode("[Test Mode]") // Test mode, default is false, can be skipped
	->setNumber("[Card number]"); // Card number

$result = $transaction->send();

if (!empty($transaction->getError())) {

	// Log the error message
	// $error = $transaction->getError();
} else {

	// Get number of installments
	// $installments = $result->maxInstallments;
}
```

## Unit tests

Tests are run by `./vendor/bin/phpunit tests`. Although the library code is designed to be compatible with `php 5.6`, testing
requires `php 7.3` as minimum because of `phpunit` version `9`.