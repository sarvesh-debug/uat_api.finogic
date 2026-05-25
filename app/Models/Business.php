<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'domain_name',
        'business_id',
        'business_email',
        'name',
        'title',
        'logo',
        'favicon',
        'city',
        'pin',
        'sidebar_color',
        'icon_color'
    ];
}
