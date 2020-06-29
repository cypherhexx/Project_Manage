<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Role::class, function (Faker $faker) {

    return [
    	
    ];
});


$factory->state(App\Role::class, 'administrator', [
    'name' => 'Administrator',    
]);

$factory->state(App\Role::class, 'software_engineer', [
    'name' => 'Software Engineer',    
]);

$factory->state(App\Role::class, 'customer_support_executive', [
    'name' => 'Customer Support Executive',    
]);





$factory->define(App\User::class, function (Faker $faker) {

	$first_name = $faker->firstName;
	$last_name  = $faker->lastName;

    return [
    	'short_code' 		=> gen_team_member_short_code($first_name . " ". $last_name),
    	'code'              => $faker->numberBetween(1000,9000),
        'first_name' 		=> $first_name,
        'last_name' 		=> $last_name,
        'email' 			=> $faker->unique()->email,
        'password' 			=> bcrypt('123456'), // secret
        'remember_token' 	=> str_random(10),

        
    ];
});


$factory->state(App\User::class, 'administrator', [
    'is_administrator' => TRUE,
    'role_id'		   => 1,
    'email'            => 'admin@demo.com',
]);

$factory->state(App\User::class, 'software_engineer', [ 
    'role_id'		   => 2
]);


$factory->state(App\User::class, 'customer_support_executive', [ 
    'role_id'		   => 3
]);

//  ----------------------------- Customer stuffs-------------------------------------








