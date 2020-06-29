<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ProposalStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('proposal_statuses')->truncate();

        DB::table('proposal_statuses')->insert([
            ['name' => __('form.draft')],
            ['name' => __('form.sent')],
            ['name' => __('form.open')],
            ['name' => __('form.revised')],
            ['name' => __('form.declined')],
            ['name' => __('form.accepted')],
            ['name' => __('form.expired')],

        ]);
    }
}
