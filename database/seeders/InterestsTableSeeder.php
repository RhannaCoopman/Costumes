<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InterestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $interests = [
            ['name' => 'Chatting'],
            ['name' => 'Memes'],
            ['name' => 'Advice'],
            ['name' => 'Recommendations'],
            ['name' => 'Meet-ups'],
        ];

        DB::table('interests')->insert($interests);
    }
}
