<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;
    protected $table = 'orders';
    protected $fillable = ['surname', 'name', 'patronymic', 'phone_number', 'city', 'street', 'house', 'send_type', 'payment_type', 'status']; 

}
