<?php

namespace Omnipay\Esnekpos\Tests\Feature;

use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Esnekpos\Exceptions\OmnipayEsnekposEnrollmentRequestException;
use Omnipay\Esnekpos\Exceptions\OmnipayEsnekposEnrollmentResponseException;
use Omnipay\Esnekpos\Message\PurchaseRequest;
use Omnipay\Esnekpos\Message\PurchaseResponse;
use Omnipay\Esnekpos\Models\EnrollmentResponseModel;
use Omnipay\Esnekpos\Tests\TestCase;

class PurchaseTest extends TestCase
{
	/**
	 * @throws InvalidRequestException
	 * @throws InvalidCreditCardException
	 * @throws \JsonException
	 */
	public function test_purchase_request()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		// PurchaseRequest makes HTTP call inside getData(), so we must mock BEFORE calling getData()
		$this->setMockHttpResponse('PurchaseResponseSuccess.txt');

		$data = $request->getData();

		$this->assertInstanceOf(EnrollmentResponseModel::class, $data);
		$this->assertEquals('SUCCESS', $data->STATUS);
		$this->assertEquals('0', $data->RETURN_CODE);
		$this->assertEquals('ORDER-123456', $data->ORDER_REF_NUMBER);
		$this->assertEquals('https://3ds.esnekpos.com/redirect/abc123', $data->URL_3DS);
		$this->assertEquals('REF-789', $data->REFNO);
	}

	/**
	 * @throws InvalidRequestException
	 * @throws InvalidCreditCardException
	 * @throws \JsonException
	 */
	public function test_purchase_request_builds_correct_http_payload()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->setMockHttpResponse('PurchaseResponseSuccess.txt');

		$request->getData();

		$httpRequests = $this->getMockedRequests();

		$this->assertCount(1, $httpRequests);

		$sentRequest = $httpRequests[0];

		$this->assertEquals('POST', $sentRequest->getMethod());
		$this->assertStringContainsString('/api/pay/EYV3DPay', (string) $sentRequest->getUri());
		$this->assertEquals('application/json', $sentRequest->getHeaderLine('Content-Type'));

		$body = json_decode((string) $sentRequest->getBody(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertEquals('test_merchant', $body['Config']['MERCHANT']);
		$this->assertEquals('test_merchant_key', $body['Config']['MERCHANT_KEY']);
		$this->assertEquals('https://example.com/payment-callback', $body['Config']['BACK_URL']);
		$this->assertEquals('TRY', $body['Config']['PRICES_CURRENCY']);
		$this->assertEquals('ORDER-123456', $body['Config']['ORDER_REF_NUMBER']);

		$this->assertEquals('4111111111111111', $body['CreditCard']['CC_NUMBER']);
		$this->assertEquals('12', $body['CreditCard']['EXP_MONTH']);
		$this->assertEquals('2099', $body['CreditCard']['EXP_YEAR']);
		$this->assertEquals('123', $body['CreditCard']['CC_CVV']);
		$this->assertEquals('Example User', $body['CreditCard']['CC_OWNER']);
		$this->assertEquals('1', $body['CreditCard']['INSTALLMENT_NUMBER']);

		$this->assertEquals('Example', $body['Customer']['FIRST_NAME']);
		$this->assertEquals('User', $body['Customer']['LAST_NAME']);
		$this->assertEquals('test@example.com', $body['Customer']['MAIL']);
		$this->assertEquals('5554443322', $body['Customer']['PHONE']);
		$this->assertEquals('127.0.0.1', $body['Customer']['CLIENT_IP']);

		$this->assertCount(1, $body['Product']);
		$this->assertEquals('Test Product', $body['Product'][0]['PRODUCT_NAME']);
		$this->assertEquals('A test product description', $body['Product'][0]['PRODUCT_DESCRIPTION']);
		$this->assertEquals(100.50, $body['Product'][0]['PRODUCT_AMOUNT']);
	}

	public function test_purchase_request_validation_error_missing_amount()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest-ValidationError.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidRequestException::class);

		$request->getData();
	}

	public function test_purchase_request_validation_error_missing_email()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		// Remove email from card
		unset($options['card']['email']);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidCreditCardException::class);
		$this->expectExceptionMessage('The e-mail is required');

		$request->getData();
	}

	public function test_purchase_request_validation_error_missing_phone()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		// Remove phone from card
		unset($options['card']['billingPhone']);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidCreditCardException::class);
		$this->expectExceptionMessage('The phone is required');

		$request->getData();
	}

	public function test_purchase_request_validation_error_missing_billing_city()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		// Remove billingCity from card
		unset($options['card']['billingCity']);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidCreditCardException::class);
		$this->expectExceptionMessage('The billingCity is required');

		$request->getData();
	}

	public function test_purchase_request_validation_error_missing_billing_address()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		// Remove billingAddress1 from card
		unset($options['card']['billingAddress1']);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidCreditCardException::class);
		$this->expectExceptionMessage('The billingAddress1 is required');

		$request->getData();
	}

	/**
	 * @throws \JsonException
	 */
	public function test_purchase_enrollment_response_failure_throws_exception()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		// Mock a failure response from the enrollment API
		$this->setMockHttpResponse('PurchaseResponseFailure.txt');

		$this->expectException(OmnipayEsnekposEnrollmentResponseException::class);

		$request->getData();
	}

	/**
	 * @throws \JsonException
	 */
	public function test_purchase_enrollment_http_error_throws_exception()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		// Mock a 500 HTTP response
		$this->setMockHttpResponse('PurchaseResponseHttpError.txt');

		$this->expectException(OmnipayEsnekposEnrollmentRequestException::class);

		$request->getData();
	}

	/**
	 * @throws \JsonException
	 */
	public function test_purchase_response_success()
	{
		$data = new EnrollmentResponseModel([
			'ORDER_REF_NUMBER' => 'ORDER-123456',
			'STATUS' => 'SUCCESS',
			'RETURN_CODE' => '0',
			'RETURN_MESSAGE' => 'Success',
			'RETURN_MESSAGE_TR' => 'Basarili',
			'ERROR_CODE' => null,
			'DATE' => '2024-01-15 10:30:00',
			'URL_3DS' => 'https://3ds.esnekpos.com/redirect/abc123',
			'REFNO' => 'REF-789',
			'HASH' => 'hash123',
			'COMMISSION_RATE' => '1.5',
			'CUSTOMER_NAME' => 'Example User',
			'CUSTOMER_MAIL' => 'test@example.com',
			'CUSTOMER_PHONE' => '5554443322',
			'CUSTOMER_ADDRESS' => '123 Billing St',
			'CUSTOMER_CC_NUMBER' => '411111******1111',
			'CUSTOMER_CC_NAME' => 'Example User',
			'IS_NOT_3D_PAYMENT' => false,
			'VIRTUAL_POS_VALUES' => null,
			'RETURN_MESSAGE_3D' => null,
			'BANK_AUTH_CODE' => null,
		]);

		$response = new PurchaseResponse($this->getMockRequest(), $data);

		$this->assertTrue($response->isSuccessful());
		$this->assertTrue($response->isRedirect());
		$this->assertEquals('https://3ds.esnekpos.com/redirect/abc123', $response->getRedirectUrl());
		$this->assertEquals('GET', $response->getRedirectMethod());

		$redirectData = $response->getRedirectData();
		$this->assertIsArray($redirectData);
	}

	public function test_purchase_response_failure()
	{
		$data = new EnrollmentResponseModel([
			'ORDER_REF_NUMBER' => 'ORDER-123456',
			'STATUS' => 'FAILURE',
			'RETURN_CODE' => '99',
			'RETURN_MESSAGE' => 'Card declined',
			'RETURN_MESSAGE_TR' => 'Kart reddedildi',
			'ERROR_CODE' => '99',
			'DATE' => '2024-01-15 10:30:00',
			'URL_3DS' => null,
			'REFNO' => null,
			'HASH' => null,
			'COMMISSION_RATE' => null,
			'CUSTOMER_NAME' => null,
			'CUSTOMER_MAIL' => null,
			'CUSTOMER_PHONE' => null,
			'CUSTOMER_ADDRESS' => null,
			'CUSTOMER_CC_NUMBER' => null,
			'CUSTOMER_CC_NAME' => null,
			'IS_NOT_3D_PAYMENT' => null,
			'VIRTUAL_POS_VALUES' => null,
			'RETURN_MESSAGE_3D' => null,
			'BANK_AUTH_CODE' => null,
		]);

		$response = new PurchaseResponse($this->getMockRequest(), $data);

		$this->assertFalse($response->isSuccessful());
		$this->assertFalse($response->isRedirect());
		$this->assertNull($response->getRedirectUrl());
	}

	/**
	 * @throws InvalidRequestException
	 * @throws InvalidCreditCardException
	 * @throws \JsonException
	 */
	public function test_purchase_send_returns_purchase_response()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		// Mock enrollment response for getData() HTTP call
		$this->setMockHttpResponse('PurchaseResponseSuccess.txt');

		/** @var PurchaseResponse $response */
		$response = $request->send();

		$this->assertInstanceOf(PurchaseResponse::class, $response);
		$this->assertTrue($response->isSuccessful());
		$this->assertTrue($response->isRedirect());
		$this->assertEquals('https://3ds.esnekpos.com/redirect/abc123', $response->getRedirectUrl());
	}

	public function test_purchase_uses_test_endpoint_in_test_mode()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);
		$options['testMode'] = true;

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->setMockHttpResponse('PurchaseResponseSuccess.txt');

		$request->getData();

		$httpRequests = $this->getMockedRequests();
		$sentRequest = $httpRequests[0];

		$this->assertStringContainsString('posservicetest.esnekpos.com', (string) $sentRequest->getUri());
	}

	public function test_purchase_uses_production_endpoint_in_live_mode()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/PurchaseRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);
		$options['testMode'] = false;

		$request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->setMockHttpResponse('PurchaseResponseSuccess.txt');

		$request->getData();

		$httpRequests = $this->getMockedRequests();
		$sentRequest = $httpRequests[0];

		$this->assertStringContainsString('posservice.esnekpos.com', (string) $sentRequest->getUri());
		$this->assertStringNotContainsString('posservicetest', (string) $sentRequest->getUri());
	}
}
