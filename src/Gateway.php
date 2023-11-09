<?php

namespace Omnipay\Esnekpos;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\AbstractRequest;
use Omnipay\Esnekpos\Traits\PurchaseGettersSetters;

/**
 * Esnekpos Gateway
 * (c) Tolga Can Günel
 * 2015, mobius.studio
 * http://www.github.com/tcgunel/omnipay-esnekpos
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

    public function getDefaultParameters()
    {
        return [
            "installment"     => "1",
            "secure"          => true,
            "currency"        => 'TRY',
            "description"     => '',

        ];
    }

    public function purchase(array $parameters = [])
    {
        return $this->createRequest('\Omnipay\Esnekpos\Message\PurchaseRequest', $parameters);
    }

    public function fetchTransaction(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Esnekpos\Message\FetchTransactionRequest', $parameters);
    }
}
