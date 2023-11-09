<?php

namespace Omnipay\Esnekpos\Models;

class FetchTransactionResponseModel extends BaseModel
{
    public ?string $STATUS;
    public ?string $RETURN_CODE;
    public ?string $RETURN_MESSAGE;
    public ?string $DATE;
    public ?string $PAYMENT_DATE;
    public ?string $REFNO;
    public ?string $AMOUNT;
    public ?string $ORDER_REF_NUMBER;
    public ?string $INSTALLMENT;
    public ?string $SUCCESS_TRANSACTION_ID;
    /** @var array|null|TRANSACTIONModel[]  */
    public ?array $TRANSACTIONS;
    public ?string $PHYSICAL_POS_ID;
    public ?string $PHYSICAL_POS_TITLE;
    public ?string $PAYMENT_WAY;
}


