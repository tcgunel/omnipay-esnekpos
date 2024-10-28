<?php

namespace Omnipay\Esnekpos\Message;

use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Esnekpos\Exceptions\OmnipayEsnekposFetchTransactionRequestException;
use Omnipay\Esnekpos\Traits\PurchaseGettersSetters;

class FetchInstallmentOptionsRequest extends AbstractRequest
{
    use PurchaseGettersSetters;

    private string $endpoint = '/api/services/GetInstallments';

    /**
     * @throws InvalidRequestException
     * @throws InvalidCreditCardException
     */
    public function getData(): array
    {
        $this->validate(
            'merchant',
            'merchant_key',
            'amount',
        );

        if (! is_null($this->getCard()->getNumber()) && ! preg_match('/^\d{8,19}$/', $this->getCard()->getNumber())) {
            throw new InvalidCreditCardException('Card number should have at least 6 to maximum of 19 digits');
        }

        return [
            'MERCHANT' => $this->getMerchant(),
            'MERCHANT_KEY' => $this->getMerchantKey(),
            'AMOUNT' => $this->getAmount(),
            'BIN' => substr($this->getCard()->getNumber(), 0, 6),
            'MERCHANT_PUBLIC_TOKEN' => '',
            'COMMISSION_FOR_CUSTOMER' => $this->getIsCommissionBelongsToCustomer(),
        ];
    }

    /**
     * @throws OmnipayEsnekposFetchTransactionRequestException
     * @throws \JsonException
     */
    public function sendData($data): FetchInstallmentOptionsResponse
    {
        $httpResponse = $this->httpClient->request(
            'POST',
            $this->getEndpoint(),
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            json_encode($data, JSON_THROW_ON_ERROR)
        );

        if ($httpResponse->getStatusCode() !== 200) {

            throw new OmnipayEsnekposFetchTransactionRequestException('Fetch Installment Options Request sırasında bir hata oluştu.', $httpResponse->getStatusCode());
        }

        return new FetchInstallmentOptionsResponse($this, $httpResponse);
    }
}
