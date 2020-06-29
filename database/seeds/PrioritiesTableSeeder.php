<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class PrioritiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        DB::table('priorities')->truncate();

        DB::table('priorities')->insert([
            ['name' => 'Low'],
            ['name' => 'Medium'],
            ['name' => 'High'],
            ['name' => 'Urgent'],

        ]);
    }
}
