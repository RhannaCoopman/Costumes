<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCityCoordinatesTable extends Migration
{
    public function up()
    {
        Schema::create('city_coordinates', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('zipcode');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('city_coordinates');
    }
}
