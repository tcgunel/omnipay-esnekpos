<?php

namespace Omnipay\Esnekpos\Models;

class EnrollmentResponseModel extends BaseModel
{
    public ?string $ORDER_REF_NUMBER;
    public ?string $STATUS;
    public ?string $RETURN_CODE;
    public ?string $RETURN_MESSAGE;
    public ?string $RETURN_MESSAGE_TR;
    public ?string $ERROR_CODE;
    public ?string $DATE;
    public ?string $URL_3DS;
    public ?string $REFNO;
    public ?string $HASH;
    public ?string $COMMISSION_RATE;
    public ?string $CUSTOMER_NAME;
    public ?string $CUSTOMER_MAIL;
    public ?string $CUSTOMER_PHONE;
    public ?string $CUSTOMER_ADDRESS;
    public ?string $CUSTOMER_CC_NUMBER;
    public ?string $CUSTOMER_CC_NAME;
    public ?bool $IS_NOT_3D_PAYMENT;
    public ?string $VIRTUAL_POS_VALUES;
    public ?string $RETURN_MESSAGE_3D;
    public ?string $BANK_AUTH_CODE;
}


