<?php

namespace Omnipay\Esnekpos\Tests\Feature;

use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Esnekpos\Message\FetchInstallmentOptionsRequest;
use Omnipay\Esnekpos\Message\FetchInstallmentOptionsResponse;
use Omnipay\Esnekpos\Models\FetchInstallmentOptionsResponseModel;
use Omnipay\Esnekpos\Models\INSTALLMENTModel;
use Omnipay\Esnekpos\Tests\TestCase;

class FetchInstallmentOptionsTest extends TestCase
{
	/**
	 * @throws InvalidRequestException
	 * @throws InvalidCreditCardException
	 * @throws \JsonException
	 */
	public function test_fetch_installment_options_request()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/FetchInstallmentOptionsRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new FetchInstallmentOptionsRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		$expected = [
			'MERCHANT'                => 'test_merchant',
			'MERCHANT_KEY'            => 'test_merchant_key',
			'AMOUNT'                  => '100.50',
			'BIN'                     => '411111',
			'MERCHANT_PUBLIC_TOKEN'   => '',
			'COMMISSION_FOR_CUSTOMER' => 1,
		];

		self::assertEquals($expected, $data);
	}

	public function test_fetch_installment_options_request_validation_error()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/FetchInstallmentOptionsRequest-ValidationError.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new FetchInstallmentOptionsRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidRequestException::class);

		$request->getData();
	}

	public function test_fetch_installment_options_request_card_number_validation_error()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/FetchInstallmentOptionsRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		// Set card number too short (less than 8 digits)
		$options['card']['number'] = '4111';

		$request = new FetchInstallmentOptionsRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidCreditCardException::class);
		$this->expectExceptionMessage('Card number should have at least 6 to maximum of 19 digits');

		$request->getData();
	}

	/**
	 * @throws \JsonException
	 */
	public function test_fetch_installment_options_response_success()
	{
		$httpResponse = $this->getMockHttpResponse('FetchInstallmentOptionsResponseSuccess.txt');

		$response = new FetchInstallmentOptionsResponse($this->getMockRequest(), $httpResponse);

		$this->assertTrue($response->isSuccessful());

		$this->assertEquals('Basarili', $response->getMessage());

		$data = $response->getData();

		$this->assertInstanceOf(FetchInstallmentOptionsResponseModel::class, $data);
		$this->assertEquals('SUCCESS', $data->STATUS);
		$this->assertEquals('0', $data->RETURN_CODE);
		$this->assertEquals('Basarili', $data->RETURN_MESSAGE);

		$this->assertIsArray($data->INSTALLMENTS);
		$this->assertCount(3, $data->INSTALLMENTS);

		/** @var INSTALLMENTModel $firstInstallment */
		$firstInstallment = $data->INSTALLMENTS[0];

		$this->assertInstanceOf(INSTALLMENTModel::class, $firstInstallment);
		$this->assertEquals('WORLD', $firstInstallment->FAMILY);
		$this->assertEquals(1, $firstInstallment->INSTALLMENT);
		$this->assertEquals(0, $firstInstallment->RATE);
		$this->assertEquals(10050, $firstInstallment->AMOUNT_PER_INSTALLMENT);
		$this->assertEquals(10050, $firstInstallment->AMOUNT_TOTAL);
		$this->assertEquals(9900, $firstInstallment->AMOUNT_BE_SEND_TO_DEALER);

		/** @var INSTALLMENTModel $thirdInstallment */
		$thirdInstallment = $data->INSTALLMENTS[2];

		$this->assertInstanceOf(INSTALLMENTModel::class, $thirdInstallment);
		$this->assertEquals('WORLD', $thirdInstallment->FAMILY);
		$this->assertEquals(6, $thirdInstallment->INSTALLMENT);
		$this->assertEquals(10, $thirdInstallment->RATE);
	}

	public function test_fetch_installment_options_response_api_error()
	{
		$httpResponse = $this->getMockHttpResponse('FetchInstallmentOptionsResponseApiError.txt');

		$response = new FetchInstallmentOptionsResponse($this->getMockRequest(), $httpResponse);

		$this->assertFalse($response->isSuccessful());

		$this->assertEquals('Islem basarisiz', $response->getMessage());

		$data = $response->getData();

		$this->assertInstanceOf(FetchInstallmentOptionsResponseModel::class, $data);
		$this->assertEquals('FAILURE', $data->STATUS);
		$this->assertEquals('99', $data->RETURN_CODE);
	}

	/**
	 * @throws \JsonException
	 */
	public function test_fetch_installment_options_send_full_flow()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/FetchInstallmentOptionsRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new FetchInstallmentOptionsRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->setMockHttpResponse('FetchInstallmentOptionsResponseSuccess.txt');

		/** @var FetchInstallmentOptionsResponse $response */
		$response = $request->send();

		$this->assertInstanceOf(FetchInstallmentOptionsResponse::class, $response);
		$this->assertTrue($response->isSuccessful());
		$this->assertEquals('Basarili', $response->getMessage());
	}

	/**
	 * @throws \JsonException
	 */
	public function test_fetch_installment_options_sends_correct_http_payload()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/FetchInstallmentOptionsRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new FetchInstallmentOptionsRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->setMockHttpResponse('FetchInstallmentOptionsResponseSuccess.txt');

		$request->send();

		$httpRequests = $this->getMockedRequests();

		$this->assertCount(1, $httpRequests);

		$sentRequest = $httpRequests[0];

		$this->assertEquals('POST', $sentRequest->getMethod());
		$this->assertStringContainsString('/api/services/GetInstallments', (string) $sentRequest->getUri());
		$this->assertEquals('application/json', $sentRequest->getHeaderLine('Content-Type'));

		$body = json_decode((string) $sentRequest->getBody(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertEquals('test_merchant', $body['MERCHANT']);
		$this->assertEquals('test_merchant_key', $body['MERCHANT_KEY']);
		$this->assertEquals('100.50', $body['AMOUNT']);
		$this->assertEquals('411111', $body['BIN']);
		$this->assertEquals(1, $body['COMMISSION_FOR_CUSTOMER']);
	}
}
