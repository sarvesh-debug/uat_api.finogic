<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManulFund extends Model
{
    use HasFactory;

    protected $table = 'manul_fund';

    protected $fillable = [
        'amount',
        'opbalance',
        'clbalance',
        'remark',
        'added_by',
    ];

    public function user()
{
    return $this->belongsTo(\App\Models\User::class, 'added_by');
}
}
