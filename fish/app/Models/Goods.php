<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Special_offers;
use Illuminate\Database\Eloquent\Relations\HasOne;


class Goods extends Model
{
    use HasFactory;
    protected $table = 'products';
    protected $fillable = ['category_id', 'name', 'price', 'photo']; 
    
    public function specialOffer(): HasOne
    {
        return $this->hasOne(Special_offers::class, 'product_id');
    }
}
