<?php

use Faker\Generator as Faker;
use App\NumberGenerator;

$factory->define(App\Customer::class, function (Faker $faker) {
	
	  $title 		= $faker->title;	
	  $address 	= $faker->streetAddress;
    $city 		= $faker->city;
    $state 		= $faker->state;
    $zip_code 	= $faker->postcode;
    $country_id = 1;

    return [
       'number' 						            => NumberGenerator::gen(COMPONENT_TYPE_CUSTOMER) ,
       'name' 							            => $faker->company, 
       'vat_number' 					          => $faker->numberBetween(1000, 2000),
       'phone' 							            => $faker->phoneNumber, 
       'website' 						            => 'http://www.'.$faker->domainName, 
       'address' 						            => $address, 
       'city' 							            => $city, 
       'state' 							            => $state, 
       'zip_code' 						          => $zip_code, 
       'country_id' 					          => $country_id, 
       'shipping_is_same_as_billing' 	  => TRUE, 
       'shipping_address' 				      => $faker->streetAddress, 
       'shipping_city' 					        => $city, 
       'shipping_state' 				        => $state, 
       'shipping_zip_code' 				      => $zip_code,
       'shipping_country_id' 			      => $country_id,
       'notes' 							            => $faker->text(100) , 
       'default_language' 			        => NULL, 
       'currency_id' 					          => 1, 
       'created_by' 					          => 1
    ];
});


$factory->afterCreating(App\Customer::class, function ($customer, $faker) {
    
    // Customer Contacts
    $customer->contacts()->saveMany([
			    factory(App\CustomerContact::class)->states('primary_contact')->make(),
			    factory(App\CustomerContact::class)->make(),
			    factory(App\CustomerContact::class)->make(),
		]);

    // Customer groups
    $random_number_array = range(1, 4);
	  shuffle($random_number_array );
	  $random_number_array = array_slice($random_number_array ,0,2);
    $customer->groups()->attach($random_number_array);
});


$factory->define(App\CustomerContact::class, function (Faker $faker) {

	$first_name = $faker->firstName;
	$last_name  = $faker->lastName;

    return [    	
    	'first_name' 			 => $first_name,
      'last_name' 			 => $last_name,
      'email' 				   => $faker->unique()->email,
    	'phone'					   => $faker->phoneNumber, 
    	'position' 				 => $faker->jobTitle,    	
    	'password' 				 => 	bcrypt('123456'),   	

    
    ];
});


$factory->state(App\CustomerContact::class, 'primary_contact', [
    'is_primary_contact' => TRUE,    
]);




$factory->define(App\Lead::class, function (Faker $faker) {

	$first_name = $faker->firstName;
	$lastName = $faker->lastName;
	$name =  $first_name. " ". $lastName ;
    return [
    	'lead_status_id'    	=> $faker->randomElement(range(2,4)), 
    	'assigned_to'			=> 1,
    	'first_name' 			=> $first_name,
        'last_name' 			=> $lastName,
        'position' 				=> $faker->jobTitle,    	
        'email' 				=> $faker->unique()->email,
        'website' 				=> 'http://www.'.$faker->domainName, 
    	'phone'					=> $faker->phoneNumber,
    	'company' 				=> $faker->company, 
    	'is_important'			=> TRUE,    	
    	'address' 				=> $faker->streetAddress,
        'city' 					=> $faker->city,
        'state' 				=> $faker->state, 
        'zip_code' 				=> $faker->postcode, 
        'country_id' 			=> 1, 
    	'description'			=> $faker->text(100),
    	'created_by'			=> 1 ,   
    	'social_links'			=> json_encode(["Twitter" => "www.tweeter.com","LinkedIn" => "www.linkedin.com","Facebook" => "www.facebook.com"]),
    	'smart_summary'			=> json_encode(['Experiences' => $name. " has worked at <a target='_blank\' href='#'>Used Car Dealership</a> as a Finance Manager for three years" 
,"Car" => "Owns Porsche 718-cayman","Education" => "Graduated from UCLA. Masters in Economics." ]),

    	'photo'					=> 'public/uploads/avatars/5c02bfc0ef78c.jpeg'
    ];
});

$factory->state(App\Lead::class, 'google', [
    'lead_source_id' => 1,    
]);

$factory->state(App\Lead::class, 'facebook', [
    'lead_source_id' => 2,    
]);


$factory->define(App\Vendor::class, function (Faker $faker) {
	
	
	$address 	= $faker->streetAddress;
    $city 		= $faker->city;
    $state 		= $faker->state;
    $zip_code 	= $faker->postcode;
    $country_id = 1;
  

    return [
       'number' 						=> NumberGenerator::gen(COMPONENT_TYPE_VENDOR) ,
       'name' 							=> $faker->company, 
       'phone' 							=> $faker->phoneNumber, 
       'website' 						=> 'http://www.'.$faker->domainName, 
       'address' 						=> $address, 
       'city' 							=> $city, 
       'state' 							=> $state, 
       'zip_code' 						=> $zip_code, 
       'country_id' 					=> $country_id,      
       'notes' 							=> $faker->text(100) ,       
       'created_by' 					=> 1,
       'contact_first_name'    			=> $faker->firstName ,
       'contact_last_name'     			=> $faker->lastName,
       'contact_email'         			=> $faker->unique()->email,
       'contact_phone'         			=> $faker->phoneNumber,
       'contact_position'      			=> $faker->jobTitle,    
    ];
});
