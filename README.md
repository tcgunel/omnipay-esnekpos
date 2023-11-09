# 3D İşlem Başlat

```php
/** @var \Omnipay\Esnekpos\Gateway $gateway */
$gateway = \Omnipay\Omnipay::create('Esnekpos');

$gateway
    ->setTestMode(true)
    ->setMerchant('MERCHANT')
    ->setMerchantKey('MERCHANT_KEY');

$options = [
    'transactionId' => 'abc87264872642zzzz',
    'amount'        => 600,
    'installment'        => 1,
    'card'          => [
        // You can supply \Omnipay\Common\CreditCard object here.
        'firstName'        => 'Example',
        'lastName'         => 'User',
        'number'           => '4159560047417732',
        'expiryMonth'      => '08',
        'expiryYear'       => '2024',
        'cvv'              => '123',
        'billingAddress1'  => '123 Billing St',
        'billingAddress2'  => 'Billsville',
        'billingCity'      => 'Billstown',
        'billingPostcode'  => '12345',
        'billingState'     => 'CA',
        'billingCountry'   => 'TR',
        'billingPhone'     => '5554443322',
        'shippingAddress1' => '123 Shipping St',
        'shippingAddress2' => 'Shipsville',
        'shippingCity'     => 'Shipstown',
        'shippingPostcode' => '54321',
        'shippingState'    => 'NY',
        'shippingCountry'  => 'TR',
        'shippingPhone'    => '5554443322',
    ],

    'clientIp' => '127.0.0.1',
    'items'    => [
        // You can supply \Omnipay\Common\ItemBag here.
        [
            'name'        => 'Perspiciatis et facilis tempore facilis.',
            'description' => 'My notion was that she was talking. \'How CAN I have done that?\' she thought. \'I must be a LITTLE larger, sir, if you like,\' said the King and Queen of Hearts, carrying the King\'s crown on a.',
            'quantity'    => 6,
            'price'       => 100,
        ],
    ],

    'secure'    => true,
    'returnUrl' => 'http://localhost/return.php',
];

/** @var \Omnipay\Esnekpos\Message\PurchaseResponse $response */
$response = $gateway->purchase($options)->send();

if ($response->isRedirect()) {

    // Does form submit to provider. Will return to returnUrl.
    $render = $response->getRedirectResponse();

    echo $render;

} else {

    var_dump($response->getData());
    var_dump($response->isSuccessful());

}
```

# Ödeme Durumu Sorgulama

```php
/** @var \Omnipay\Esnekpos\Gateway $gateway */
$gateway = \Omnipay\Omnipay::create('Esnekpos');

$gateway
    ->setTestMode(true)
    ->setMerchant('MERCHANT')
    ->setMerchantKey('MERCHANT_KEY');

/** @var \Omnipay\Esnekpos\Message\FetchTransactionRequest $verify */
$verify = $gateway->fetchTransaction();

$verify->setTransactionId('abc87264872642zzzz');

$verify_response = $verify->send();

var_dump($verify_response->getData());

if ($verify_response->isSuccessful() && collect($data->TRANSACTIONS)->where('STATUS_ID', \Omnipay\Esnekpos\Constants\Statuses::PAYMENT_SUCCESS)->isNotEmpty()){

    var_dump(true);

}else{

    var_dump(false);

}
```
