<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageTableSeeder extends Seeder {

    public function run()
    {

        DB::table('languages')->truncate();



        $data = [
            [ 'name' => 'Chinese'],
            [ 'name' => 'Japanese'],
            [ 'name' => 'Turkish'],
            [ 'name' => 'Romanian'],
            [ 'name' => 'Indonesia'],
            [ 'name' => 'Spanish'],
            [ 'name' => 'German'],
            [ 'name' => 'Persian'],
            [ 'name' => 'Catalan'],
            [ 'name' => 'Swedish'],
            [ 'name' => 'Vietnamese'],
            [ 'name' => 'Dutch'],
            [ 'name' => 'Italian'],
            [ 'name' => 'English'],
            [ 'name' => 'French'],
            [ 'name' => 'Portuguese'],
            [ 'name' => 'Portuguese_br'],
            [ 'name' => 'Russian'],
            [ 'name' => 'Polish'],


       ];

        DB::table('languages')->insert($data);

    }
}