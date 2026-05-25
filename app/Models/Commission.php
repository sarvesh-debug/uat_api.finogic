<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use HasFactory;

    protected $fillable = [
        'packages',
        'service',
        'sub_service',
        'from_amount',
        'to_amount',
        'charge_in',
        'charge',
        'commissions_in',
        'commissions',
        'tds_in',
        'tds',
        'packagesId'
    ];
}
