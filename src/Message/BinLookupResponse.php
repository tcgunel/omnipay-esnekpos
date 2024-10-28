<?php

namespace Omnipay\Esnekpos\Message;

use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\Esnekpos\Models\BinLookupResponseModel;

class BinLookupResponse extends AbstractResponse
{
    /**
     * @throws \JsonException
     */
    public function __construct(RequestInterface $request, $data)
    {
        parent::__construct($request, $data);
        $this->data = new BinLookupResponseModel(
            json_decode($data->getBody(), true, 512, JSON_THROW_ON_ERROR)
        );
    }

    public function isSuccessful(): bool
    {
        return ! empty($this->data->Card_Type);
    }

    public function getMessage(): string
    {
        return $this->data->Card_Type;
    }

    public function getData(): BinLookupResponseModel
    {
        return $this->data;
    }
}
