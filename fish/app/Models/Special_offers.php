<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Special_offers extends Model
{
    use HasFactory;
    protected $table = 'special_offers';
    protected $fillable = ['product_id', 'special_price', 'photo']; 
}
