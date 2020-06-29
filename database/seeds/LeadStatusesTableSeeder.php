<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class LeadStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('lead_statuses')->truncate();

        DB::table('lead_statuses')->insert([
            ['name' => __('form.customer'), 'is_system' => TRUE],
            ['name' => 'New' , 'is_system' => FALSE],
            ['name' => 'Contacted' , 'is_system' => FALSE ],
            ['name' => 'Qualified' , 'is_system' => FALSE],
            ['name' => 'Proposal Sent' , 'is_system' => FALSE],

        ]);
    }
}
