<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BillingTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('billing_types')->truncate();

        DB::table('billing_types')->insert([

            ['name' => __('form.fixed_rate')],
            ['name' => __('form.project_hours')],
            ['name' => __('form.task_hours')],


        ]);
    }
}
