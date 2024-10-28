<?php

namespace Omnipay\Esnekpos\Models;

class BinLookupResponseModel extends BaseModel
{
    public ?string $Bank_Name;
    public ?string $Bank_Brand;
    public ?string $Card_Type;
    public ?string $Card_Family;
    public ?string $Card_Kind;
}


