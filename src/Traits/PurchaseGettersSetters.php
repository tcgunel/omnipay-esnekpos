<?php

namespace Omnipay\Esnekpos\Traits;

trait PurchaseGettersSetters
{
    public function setMerchant($value)
    {
        return $this->setParameter('merchant', $value);
    }

	public function getMerchant()
	{
		return $this->getParameter('merchant');
	}

    public function setMerchantKey($value)
    {
        return $this->setParameter('merchant_key', $value);
    }

	public function getMerchantKey()
	{
		return $this->getParameter('merchant_key');
	}

    public function setSecure($value)
    {
        return $this->setParameter('secure', $value);
    }

	public function getSecure()
	{
		return $this->getParameter('secure');
	}

    public function getEndpoint()
    {
        if ($this->getTestMode()) {

            return 'https://posservicetest.esnekpos.com' . $this->endpoint;

        }

        return 'https://posservice.esnekpos.com' . $this->endpoint;
    }

    public function setInstallment($value)
    {
        return $this->setParameter('installment', $value);
    }

    public function getInstallment()
    {
        return $this->getParameter('installment');
    }
}
