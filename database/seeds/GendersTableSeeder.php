<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class GendersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('genders')->truncate();

        DB::table('genders')->insert([

            ['name' => __('form.male')],
            ['name' => __('form.female')],


        ]);
    }
}
