<?php

use Illuminate\Database\Seeder;

class PaymentModesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('payment_modes')->truncate();

        DB::table('payment_modes')->insert([

            ['name' => __('form.bank'), 'is_online' => FALSE]          

        ]);
    }
}
