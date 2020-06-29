<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class LeadSourcesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('lead_sources')->truncate();

        DB::table('lead_sources')->insert([
            ['name'     => 'Google' ],
            ['name'     => 'Facebook' ],
            ['name'     => 'Twitter' ],
            ['name'     => 'Trade Fair'],            

        ]);
    }
}
