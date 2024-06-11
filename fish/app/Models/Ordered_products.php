<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Goods;

class Ordered_products extends Model
{
    use HasFactory;

    protected $table = 'order_items';
    protected $fillable = ['order_id', 'product_id', 'quantity']; 
        
    public function Product()
    {
        return $this->belongsTo(Goods::class, 'product_id');
    }
}