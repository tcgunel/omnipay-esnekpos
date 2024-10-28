<?php

namespace Omnipay\Esnekpos\Models;

class INSTALLMENTModel extends BaseModel
{
    public ?string $FAMILY;
    public ?int $INSTALLMENT;
    public ?int $RATE;
    public ?int $AMOUNT_PER_INSTALLMENT;
    public ?int $AMOUNT_TOTAL;
    public ?int $AMOUNT_BE_SEND_TO_DEALER;
}


