<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TicketPrioritiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('ticket_priorities')->truncate();

        DB::table('ticket_priorities')->insert([
            ['name' => 'High'],
            ['name' => 'Medium'],
            ['name' => 'Low' ]    

        ]);
    }
}
