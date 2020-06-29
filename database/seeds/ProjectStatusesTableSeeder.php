<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ProjectStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('project_statuses')->truncate();

        DB::table('project_statuses')->insert([
            ['name' => __('form.not_started')],
            ['name' => __('form.in_progress')],
            ['name' => __('form.on_hold')],
            ['name' => __('form.cancelled')],
            ['name' => __('form.finished')],


        ]);
    }
}
