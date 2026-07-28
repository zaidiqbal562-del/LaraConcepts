<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_stripe', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->timestamps();
        });

        DB::table('product_stripe')->insert([
            ['name' => 'Starter Plan', 'price' => 19.99, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Pro Plan', 'price' => 49.99, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Business Plan', 'price' => 99.99, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('product_stripe');
    }
};
