<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class InvoiceStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('invoice_statuses')->truncate();

        DB::table('invoice_statuses')->insert([
            ['name' => __('form.paid')],
            ['name' => __('form.unpaid')],
            ['name' => __('form.partially_paid')],
            ['name' => __('form.over_due')],
            ['name' => __('form.cancelled')],
            ['name' => __('form.draft')],

        ]);
    }
}
