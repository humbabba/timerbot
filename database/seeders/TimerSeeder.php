<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('timers')->insert([
          'name' => 'ARC',
          'message' => 'Sharing ends at 8:58 p.m. to allow time for a beneficiary to do a reading and for us to pray out.',
          'created_at' => now(),
          'updated_at' => now(),
        ]);
    }
}
