<?php

namespace Omnipay\Esnekpos\Tests\Feature;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Esnekpos\Message\FetchTransactionRequest;
use Omnipay\Esnekpos\Message\FetchTransactionResponse;
use Omnipay\Esnekpos\Models\FetchTransactionResponseModel;
use Omnipay\Esnekpos\Models\MERCHANT_AMOUNT_TRANSFER_DETAIL;
use Omnipay\Esnekpos\Models\TRANSACTIONModel;
use Omnipay\Esnekpos\Tests\TestCase;

class FetchTransactionTest extends TestCase
{
	/**
	 * @throws InvalidRequestException
	 * @throws \JsonException
	 */
	public function test_fetch_transaction_request()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/FetchTransactionRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new FetchTransactionRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$data = $request->getData();

		$expected = [
			'MERCHANT'         => 'test_merchant',
			'MERCHANT_KEY'     => 'test_merchant_key',
			'ORDER_REF_NUMBER' => 'ORDER-123456',
		];

		self::assertEquals($expected, $data);
	}

	public function test_fetch_transaction_request_validation_error()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/FetchTransactionRequest-ValidationError.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new FetchTransactionRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->expectException(InvalidRequestException::class);

		$request->getData();
	}

	/**
	 * @throws \JsonException
	 */
	public function test_fetch_transaction_response_success()
	{
		$httpResponse = $this->getMockHttpResponse('FetchTransactionResponseSuccess.txt');

		$response = new FetchTransactionResponse($this->getMockRequest(), $httpResponse);

		$this->assertTrue($response->isSuccessful());

		$this->assertEquals('Islem basarili', $response->getMessage());

		$data = $response->getData();

		$this->assertInstanceOf(FetchTransactionResponseModel::class, $data);
		$this->assertEquals('SUCCESS', $data->STATUS);
		$this->assertEquals('0', $data->RETURN_CODE);
		$this->assertEquals('Islem basarili', $data->RETURN_MESSAGE);
		$this->assertEquals('ORDER-123456', $data->ORDER_REF_NUMBER);
		$this->assertEquals('100.50', $data->AMOUNT);
		$this->assertEquals('REF-789', $data->REFNO);
		$this->assertEquals('1', $data->INSTALLMENT);
		$this->assertEquals('TXN-001', $data->SUCCESS_TRANSACTION_ID);
		$this->assertEquals('POS-001', $data->PHYSICAL_POS_ID);
		$this->assertEquals('Test POS', $data->PHYSICAL_POS_TITLE);
		$this->assertEquals('3D', $data->PAYMENT_WAY);

		$this->assertIsArray($data->TRANSACTIONS);
		$this->assertCount(1, $data->TRANSACTIONS);

		/** @var TRANSACTIONModel $transaction */
		$transaction = $data->TRANSACTIONS[0];

		$this->assertInstanceOf(TRANSACTIONModel::class, $transaction);
		$this->assertEquals('TXN-001', $transaction->TRANSACTION_ID);
		$this->assertEquals('Odeme Basarili', $transaction->STATUS_NAME);
		$this->assertEquals(3, $transaction->STATUS_ID);
		$this->assertEquals('100.50', $transaction->AMOUNT);

		$this->assertInstanceOf(MERCHANT_AMOUNT_TRANSFER_DETAIL::class, $transaction->MERCHANT_AMOUNT_TRANSFER_DETAIL);
		$this->assertEquals('EXT-001', $transaction->MERCHANT_AMOUNT_TRANSFER_DETAIL->EXTRACT_ID);
		$this->assertEquals('99.00', $transaction->MERCHANT_AMOUNT_TRANSFER_DETAIL->SENDED_AMOUNT);
		$this->assertEquals('2024-01-16', $transaction->MERCHANT_AMOUNT_TRANSFER_DETAIL->SENDED_DATE);
	}

	public function test_fetch_transaction_response_api_error()
	{
		$httpResponse = $this->getMockHttpResponse('FetchTransactionResponseApiError.txt');

		$response = new FetchTransactionResponse($this->getMockRequest(), $httpResponse);

		$this->assertFalse($response->isSuccessful());

		$this->assertEquals('Islem bulunamadi', $response->getMessage());

		$data = $response->getData();

		$this->assertInstanceOf(FetchTransactionResponseModel::class, $data);
		$this->assertEquals('FAILURE', $data->STATUS);
		$this->assertEquals('99', $data->RETURN_CODE);
	}

	/**
	 * @throws \JsonException
	 */
	public function test_fetch_transaction_send_full_flow()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/FetchTransactionRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new FetchTransactionRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->setMockHttpResponse('FetchTransactionResponseSuccess.txt');

		/** @var FetchTransactionResponse $response */
		$response = $request->send();

		$this->assertInstanceOf(FetchTransactionResponse::class, $response);
		$this->assertTrue($response->isSuccessful());
		$this->assertEquals('Islem basarili', $response->getMessage());
	}

	/**
	 * @throws \JsonException
	 */
	public function test_fetch_transaction_sends_correct_http_payload()
	{
		$options = file_get_contents(__DIR__ . "/../Mock/FetchTransactionRequest.json");

		$options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

		$request = new FetchTransactionRequest($this->getHttpClient(), $this->getHttpRequest());

		$request->initialize($options);

		$this->setMockHttpResponse('FetchTransactionResponseSuccess.txt');

		$request->send();

		$httpRequests = $this->getMockedRequests();

		$this->assertCount(1, $httpRequests);

		$sentRequest = $httpRequests[0];

		$this->assertEquals('POST', $sentRequest->getMethod());
		$this->assertStringContainsString('/api/services/ProcessQuery', (string) $sentRequest->getUri());
		$this->assertEquals('application/json', $sentRequest->getHeaderLine('Content-Type'));

		$body = json_decode((string) $sentRequest->getBody(), true, 512, JSON_THROW_ON_ERROR);

		$this->assertEquals('test_merchant', $body['MERCHANT']);
		$this->assertEquals('test_merchant_key', $body['MERCHANT_KEY']);
		$this->assertEquals('ORDER-123456', $body['ORDER_REF_NUMBER']);
	}
}
