<?php

namespace Omnipay\Esnekpos\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Esnekpos\Models\FetchInstallmentOptionsResponseModel;

class FetchInstallmentOptionsResponse extends AbstractResponse
{
    /**
     * @throws \JsonException
     */
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);
        $this->data = new FetchInstallmentOptionsResponseModel(
            json_decode($data->getBody(), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    public function isSuccessful(): bool
    {
        return $this->data->RETURN_CODE === '0';
    }

    public function getMessage(): string
    {
        return $this->data->RETURN_MESSAGE;
    }

    public function getData(): FetchInstallmentOptionsResponseModel
    {
        return $this->data;
    }
}
