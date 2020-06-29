<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class EstimateStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('estimate_statuses')->truncate();

        DB::table('estimate_statuses')->insert([
            ['name' => __('form.draft')],
            ['name' => __('form.sent')],
            ['name' => __('form.expired')],
            ['name' => __('form.declined')],
            ['name' => __('form.accepted')],

        ]);
    }
}
