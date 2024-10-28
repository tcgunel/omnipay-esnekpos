<?php

namespace Omnipay\Esnekpos\Models;

class FetchInstallmentOptionsResponseModel extends BaseModel
{
    public ?string $STATUS;
    public ?string $RETURN_CODE;
    public ?string $RETURN_MESSAGE;
    /** @var array|null|INSTALLMENTModel[]  */
    public ?array $INSTALLMENTS;
}


