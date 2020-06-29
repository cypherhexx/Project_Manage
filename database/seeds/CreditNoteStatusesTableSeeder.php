<?php

use Illuminate\Database\Seeder;

class CreditNoteStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('credit_note_statuses')->truncate();

        DB::table('credit_note_statuses')->insert([
            ['name' => __('form.open')],
            ['name' => __('form.adjusted')],
            ['name' => __('form.void')],
    

        ]);
    }
}
