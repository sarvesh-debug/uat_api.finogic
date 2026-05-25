<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiPipe extends Model
{
    use HasFactory;
     protected $fillable = [
        'service',
        'pipe',
        'status',
        'description'
    ];
}
