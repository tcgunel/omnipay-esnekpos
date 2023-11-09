<?php

namespace Omnipay\Esnekpos\Models;

class TRANSACTIONModel extends BaseModel
{
    public ?string $TRANSACTION_ID;
    public ?string $STATUS_NAME;
    public ?int $STATUS_ID;
    public ?string $AMOUNT;
    public ?string $DATE;
    public ?MERCHANT_AMOUNT_TRANSFER_DETAIL $MERCHANT_AMOUNT_TRANSFER_DETAIL;
}


