<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CityCoordinatesSeeder extends Seeder
{
    public function run()
    {
        $file = fopen(database_path('seeds/BE.txt'), 'r');
        while (($data = fgetcsv($file, 0, "\t")) !== FALSE) {
            DB::table('city_coordinates')->insert([
                'city' => $data[2],
                'zipcode' => $data[1],
                'latitude' => $data[8],
                'longitude' => $data[9],
            ]);
        }
        fclose($file);
    }
}

