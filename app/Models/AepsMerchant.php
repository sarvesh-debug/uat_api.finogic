<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AepsMerchant extends Model
{
    use HasFactory;
    protected $fillable = [
    'request_id',
    'outlet_id',
    'merchant_id',

    'name',
    'mobile',
    'email',

    'aadhaar',
    'pan',

    'request_payload',
    'response_payload',

    'status',
    'kyc_status',
];

protected $casts = [
    'request_payload'  => 'array',
    'response_payload' => 'array',
];
}
