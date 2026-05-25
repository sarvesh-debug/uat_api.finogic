<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;   // 👈 yeh import karo
use Illuminate\Notifications\Notifiable;

class Remittance extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'brand_name',
        'name',
        'remId',
        'phone',
        'email',
        'gst_pan',
        'services',
        'referral',
        'password',
        'panno',
        'aadhar_no',
        'perday_limit',
        'pincode',
        'city',
        'recipient_name',
        'recipient_account',
        'recipient_ifsc',
        'amount',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Remittance.php
public function package()
{
    return $this->belongsTo(Package::class, 'packageId');
}

}
