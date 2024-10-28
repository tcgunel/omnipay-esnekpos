<?php

namespace Omnipay\Esnekpos;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Esnekpos\Message\BinLookupRequest;
use Omnipay\Esnekpos\Message\FetchInstallmentOptionsRequest;
use Omnipay\Esnekpos\Message\FetchTransactionRequest;
use Omnipay\Esnekpos\Message\PurchaseRequest;
use Omnipay\Esnekpos\Traits\PurchaseGettersSetters;

/**
 * Esnekpos Gateway
 * (c) Tolga Can Günel
 * 2015, mobius.studio
 * http://www.github.com/tcgunel/omnipay-esnekpos
 *
 * @method \Omnipay\Common\Message\NotificationInterface acceptNotification(array $options = [])
 * @method \Omnipay\Common\Message\RequestInterface completeAuthorize(array $options = [])
 */
class Gateway extends AbstractGateway
{
    use PurchaseGettersSetters;

    public function getName(): string
    {
        return 'Esnekpos';
    }

    public function getDefaultParameters(): array
    {
        return [
            'installment' => '1',
            'secure' => true,
            'currency' => 'TRY',
            'description' => '',
            'is_commission_belongs_to_customer' => 1,

        ];
    }

    public function purchase(array $options = []): AbstractRequest
    {
        return $this->createRequest(PurchaseRequest::class, $options);
    }

    public function fetchTransaction(array $options = []): AbstractRequest
    {
        return $this->createRequest(FetchTransactionRequest::class, $options);
    }

    public function fetchInstallmentOptions(array $options = []): AbstractRequest
    {
        return $this->createRequest(FetchInstallmentOptionsRequest::class, $options);
    }

    public function binLookup(array $options = []): AbstractRequest
    {
        return $this->createRequest(BinLookupRequest::class, $options);
    }
}
