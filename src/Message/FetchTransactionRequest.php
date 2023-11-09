<?php

namespace Omnipay\Esnekpos\Message;

use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Esnekpos\Exceptions\OmnipayEsnekposFetchTransactionRequestException;
use Omnipay\Esnekpos\Traits\PurchaseGettersSetters;

class FetchTransactionRequest extends AbstractRequest
{
    use PurchaseGettersSetters;

    private string $endpoint = '/api/services/ProcessQuery';

    /**
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     * @throws \Omnipay\Common\Exception\InvalidCreditCardException
     */
    public function getData(): array
    {
        $this->validate(
            'merchant',
            'merchant_key',
            'transactionId',
        );

        return [
            'MERCHANT'         => $this->getMerchant(),
            'MERCHANT_KEY'     => $this->getMerchantKey(),
            'ORDER_REF_NUMBER' => $this->getTransactionId(),
        ];
    }

    /**
     * @return FetchTransactionResponse
     * @throws \JsonException
     */
    public function sendData($data)
    {
        $httpResponse = $this->httpClient->request(
            'POST',
            $this->getEndpoint(),
            [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        if ($httpResponse->getStatusCode() !== 200) {

            throw new OmnipayEsnekposFetchTransactionRequestException('Fetch Transaction Request sırasında bir hata oluştu.', $httpResponse->getStatusCode());

        }

        return new FetchTransactionResponse($this, $httpResponse);
    }
}
