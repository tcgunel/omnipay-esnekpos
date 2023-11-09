<?php

namespace Omnipay\Esnekpos\Message;

use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Item;
use Omnipay\Esnekpos\Exceptions\OmnipayEsnekposEnrollmentRequestException;
use Omnipay\Esnekpos\Exceptions\OmnipayEsnekposEnrollmentResponseException;
use Omnipay\Esnekpos\Models\EnrollmentResponseModel;
use Omnipay\Esnekpos\Traits\PurchaseGettersSetters;
use Omnipay\Common\Message\AbstractRequest;

class PurchaseRequest extends AbstractRequest
{
    use PurchaseGettersSetters;

    private string $endpoint = '/api/pay/EYV3DPay';

    /**
     * @throws InvalidRequestException
     * @throws InvalidCreditCardException
     */
    protected function validateAll()
    {
        $this->validate(
            'merchant',
            'merchant_key',
            'returnUrl',
            'currency',
            'transactionId',
            'amount',
            'installment',
            'clientIp',

            'card',
            'items',
        );

        if (!$this->getCard()->getEmail()) {
            throw new InvalidCreditCardException("The e-mail is required");
        }

        if (!$this->getCard()->getPhone()) {
            throw new InvalidCreditCardException("The phone is required");
        }

        if (!$this->getCard()->getBillingCity()) {
            throw new InvalidCreditCardException("The billingCity is required");
        }

        if (!$this->getCard()->getAddress1()) {
            throw new InvalidCreditCardException("The billingAddress1 is required");
        }
    }

    /**
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     * @throws \Omnipay\Common\Exception\InvalidCreditCardException
     */
    public function getData()
    {
        $this->validateAll();

        $enrollment = $this->enroll();

        if ($enrollment->STATUS !== 'SUCCESS' || $enrollment->RETURN_CODE !== '0'){

            throw new OmnipayEsnekposEnrollmentResponseException($enrollment->RETURN_MESSAGE_TR, $enrollment->RETURN_CODE);

        }

        return $enrollment;
    }

    /**
     * @throws InvalidRequestException
     * @throws \JsonException|OmnipayEsnekposEnrollmentRequestException
     */
    protected function enroll(): EnrollmentResponseModel
    {
        $data = [
            'Config'     => [
                'MERCHANT'         => $this->getMerchant(),
                'MERCHANT_KEY'     => $this->getMerchantKey(),
                'BACK_URL'         => $this->getReturnUrl(),
                'PRICES_CURRENCY'  => $this->getCurrency(),
                'ORDER_REF_NUMBER' => $this->getTransactionId(),
                'ORDER_AMOUNT'     => $this->getAmount(),
            ],
            'CreditCard' => [
                'CC_NUMBER'          => $this->getCard()->getNumber(),
                'EXP_MONTH'          => $this->getCard()->getExpiryDate('m'),
                'EXP_YEAR'           => $this->getCard()->getExpiryDate('Y'),
                'CC_CVV'             => $this->getCard()->getCvv(),
                'CC_OWNER'           => $this->getCard()->getName(),
                'INSTALLMENT_NUMBER' => $this->getInstallment(),
            ],
            'Customer'   => [
                'FIRST_NAME' => $this->getCard()->getFirstName(),
                'LAST_NAME'  => $this->getCard()->getLastName(),
                'MAIL'       => $this->getCard()->getEmail(),
                'PHONE'      => $this->getCard()->getPhone(),
                'CITY'       => $this->getCard()->getCity(),
                'STATE'      => '-',
                'ADDRESS'    => $this->getCard()->getAddress1(),
                'CLIENT_IP'  => $this->getClientIp()
            ],
            'Product'    => [],
        ];

        /** @var Item $item */
        foreach ($this->getItems() as $item){
            $data['Product'][] = [
                'PRODUCT_ID'          => '-',
                'PRODUCT_NAME'        => $item->getName(),
                'PRODUCT_CATEGORY'    => '-',
                'PRODUCT_DESCRIPTION' => $item->getDescription(),
                'PRODUCT_AMOUNT'      => $item->getPrice(),
            ];
        }

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

            throw new OmnipayEsnekposEnrollmentRequestException($this->getEndpoint(), $httpResponse->getStatusCode());

        }

        return new EnrollmentResponseModel(
            json_decode($httpResponse->getBody(), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @param EnrollmentResponseModel $data
     */
    public function sendData($data): PurchaseResponse
    {
        return new PurchaseResponse($this, $data);
    }
}
