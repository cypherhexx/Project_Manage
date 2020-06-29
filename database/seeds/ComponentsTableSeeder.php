<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ComponentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

        DB::table('components')->truncate();

        DB::table('components')->insert([
            ['name' => 'Project' ],
            ['name' => 'Invoice' ],
            ['name' => 'Customer' ],
            ['name' => 'Estimate' ],
            ['name' => 'Contract' ],
            ['name' => 'Ticket' ],
            ['name' => 'Expense' ],
            ['name' => 'Lead' ],
            ['name' => 'Proposal' ],
            ['name' => 'Task' ],
            ['name' => 'Payment'],
            ['name' => 'Vendor'],
            ['name' => 'Team Member'],
            ['name' => 'Timesheet'],
            ['name' => 'Credit Note'],
            

        ]);
    }
}
