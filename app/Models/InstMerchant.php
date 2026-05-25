<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstMerchant extends Model
{
    use HasFactory;
     protected $fillable = [
        'merchant_id',
        'request_id',
        'mobile',
        'email',
        'aadhaar',
        'pan',
        'bank_account_no',
        'bank_ifsc',
        'latitude',
        'longitude',
        'otp_reference_id',
        'hash',
        'otp',
        'outlet_id',
        'ipay_uuid',
        'orderid',
        'status',
        'provider_response'
    ];
}
