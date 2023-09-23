<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualAccountTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'card_info' => 'json'
    ];
}