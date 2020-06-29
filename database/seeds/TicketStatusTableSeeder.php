<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ticket_statuses')->truncate();

        DB::table('ticket_statuses')->insert([

            [
            	'name' => __('form.open') , 'is_system_default' => TRUE , 'sequence_number' => 1
            ],
            [
            	'name' => __('form.in_progress') , 'is_system_default' => TRUE , 'sequence_number' => 2
            ],
            [
            	'name' => __('form.answered') , 'is_system_default' => TRUE , 'sequence_number' => 3
            ],
            [
            	'name' => __('form.on_hold') , 'is_system_default' => TRUE , 'sequence_number' => 4
            ],
            [
            	'name' => __('form.closed') , 'is_system_default' => TRUE , 'sequence_number' => 5
            ],

        ]);
    }
}
