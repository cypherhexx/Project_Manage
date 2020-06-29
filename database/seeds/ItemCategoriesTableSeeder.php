<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
class ItemCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('item_categories')->truncate();

        DB::table('item_categories')->insert([
            ['name' => __('form.product')],
            ['name' => __('form.service')],
        

        ]);
    }
}
