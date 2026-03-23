<?php

namespace Omnipay\Esnekpos\Tests\Feature;

use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Esnekpos\Message\BinLookupRequest;
use Omnipay\Esnekpos\Message\BinLookupResponse;
use Omnipay\Esnekpos\Models\BinLookupResponseModel;
use Omnipay\Esnekpos\Tests\TestCase;

class BinLookupTest extends TestCase
{
    /**
     * @throws InvalidCreditCardException
     * @throws \JsonException
     */
    public function test_bin_lookup_request()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/BinLookupRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new BinLookupRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $data = $request->getData();

        $expected = [
            'CardNumber' => '411111',
        ];

        self::assertEquals($expected, $data);
    }

    public function test_bin_lookup_request_validation_error()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/BinLookupRequest-ValidationError.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new BinLookupRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $this->expectException(InvalidCreditCardException::class);
        $this->expectExceptionMessage('Card number should have at least 6 to maximum of 19 digits');

        $request->getData();
    }

    /**
     * @throws \JsonException
     */
    public function test_bin_lookup_response_success()
    {
        $httpResponse = $this->getMockHttpResponse('BinLookupResponseSuccess.txt');

        $response = new BinLookupResponse($this->getMockRequest(), $httpResponse);

        $this->assertTrue($response->isSuccessful());

        $this->assertEquals('CREDIT', $response->getMessage());

        $data = $response->getData();

        $this->assertInstanceOf(BinLookupResponseModel::class, $data);
        $this->assertEquals('Yapi Kredi Bankasi', $data->Bank_Name);
        $this->assertEquals('World', $data->Bank_Brand);
        $this->assertEquals('CREDIT', $data->Card_Type);
        $this->assertEquals('VISA', $data->Card_Family);
        $this->assertEquals('Personal', $data->Card_Kind);
    }

    public function test_bin_lookup_response_api_error()
    {
        $httpResponse = $this->getMockHttpResponse('BinLookupResponseApiError.txt');

        $response = new BinLookupResponse($this->getMockRequest(), $httpResponse);

        $this->assertFalse($response->isSuccessful());

        $data = $response->getData();

        $this->assertInstanceOf(BinLookupResponseModel::class, $data);
        $this->assertNull($data->Card_Type);
        $this->assertNull($data->Bank_Name);
    }

    /**
     * @throws \JsonException
     */
    public function test_bin_lookup_send_full_flow()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/BinLookupRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new BinLookupRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $this->setMockHttpResponse('BinLookupResponseSuccess.txt');

        /** @var BinLookupResponse $response */
        $response = $request->send();

        $this->assertInstanceOf(BinLookupResponse::class, $response);
        $this->assertTrue($response->isSuccessful());
        $this->assertEquals('CREDIT', $response->getMessage());
    }

    /**
     * @throws \JsonException
     */
    public function test_bin_lookup_sends_correct_http_payload()
    {
        $options = file_get_contents(__DIR__ . '/../Mock/BinLookupRequest.json');

        $options = json_decode($options, true, 512, JSON_THROW_ON_ERROR);

        $request = new BinLookupRequest($this->getHttpClient(), $this->getHttpRequest());

        $request->initialize($options);

        $this->setMockHttpResponse('BinLookupResponseSuccess.txt');

        $request->send();

        $httpRequests = $this->getMockedRequests();

        $this->assertCount(1, $httpRequests);

        $sentRequest = $httpRequests[0];

        $this->assertEquals('POST', $sentRequest->getMethod());
        $this->assertStringContainsString('/api/services/EYVBinService', (string) $sentRequest->getUri());
        $this->assertEquals('application/json', $sentRequest->getHeaderLine('Content-Type'));

        $body = json_decode((string) $sentRequest->getBody(), true, 512, JSON_THROW_ON_ERROR);

        $this->assertEquals('411111', $body['CardNumber']);
    }
}
