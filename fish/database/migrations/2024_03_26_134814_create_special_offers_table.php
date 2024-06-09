<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('special_offers', function (Blueprint $table) {
            $table->id();
           // $table->integer('type');
            $table->foreignId('product_id')->constrained('products');
            $table->float('special_price');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('special_offers');
    }
};

