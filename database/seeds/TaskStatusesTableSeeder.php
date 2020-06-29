<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('task_statuses')->truncate();

        DB::table('task_statuses')->insert([

            ['name' => __('form.backlog')],
            ['name' => __('form.in_progress')],
            ['name' => __('form.testing')],
            ['name' => __('form.awaiting_feedback')],
            ['name' => __('form.complete')],

        ]);

    }
}
