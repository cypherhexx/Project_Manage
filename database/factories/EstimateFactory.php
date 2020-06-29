<?php

use Faker\Generator as Faker;
use App\NumberGenerator;
use App\EstimateItem;

$factory->define(App\Estimate::class, function (Faker $faker) {

	$address 	= $faker->streetAddress;
    $city 		= $faker->city;
    $state 		= $faker->state;
    $zip_code 	= $faker->postcode;
    $country_id = 1;

    return [

        'url_slug' 						=> md5(microtime()),
        'number' 						=> NumberGenerator::gen(COMPONENT_TYPE_ESTIMATE),
        'reference' 					=> NULL,
        'customer_id' 					=> $faker->randomElement(range(1,5)),
        'project_id' 					=> NULL, 
        'address' 						=> $address, 
	    'city' 							=> $city, 
	    'state' 						=> $state, 
	    'zip_code' 						=> $zip_code, 
	    'country_id' 					=> $country_id, 	   
	    'shipping_address' 				=> $faker->streetAddress, 
	    'shipping_city' 				=> $city, 
	    'shipping_state' 				=> $state, 
	    'shipping_zip_code' 			=> $zip_code,
	    'shipping_country_id' 			=> $country_id,
	    'currency_id' 					=> 1,
        'discount_type_id' 				=> NULL,
        'status_id' 					=> $faker->randomElement([ ESTIMATE_STATUS_DRAFT, ESTIMATE_STATUS_SENT]),
        'sales_agent_id' 				=> 1,
        'admin_note' 					=> $faker->text(100),
        'client_note' 					=> $faker->text(30),
        'terms_and_condition' 			=> $faker->text(50),
   		'date' 							=> date("Y-m-d"),
        'expiry_date' 					=> date('Y-m-d', strtotime("+20 day")),
        'show_quantity_as' 				=> 'Qty/Hours',
        'sub_total' 					=> 0,
        'discount_method_id' 			=> NULL,
        'discount_rate' 				=> 0,
        'discount_total' 				=> 0,
        'taxes' 						=> NULL,
        'tax_total' 					=> 0,
        'adjustment' 					=> 0,
        'total' 						=> 0,
        'created_by' 					=> 1,

        
        ];
});


$factory->afterCreating(App\Estimate::class, function ($estimate, $faker) {

	$first_product_rate 		= $faker->numberBetween(500, 2000);
	$second_product_rate 		= $faker->numberBetween(500, 2000);
	$sub_total 					= round($first_product_rate + $second_product_rate, 2);

	$tax1_first_item			= ($first_product_rate * 18) / 100 ;
	$tax2_first_item			= ($first_product_rate * 10) / 100 ;
	$tax1_second_item			= ($second_product_rate * 18) / 100;

	$tax_total  				= $tax1_first_item +  $tax2_first_item  +  $tax1_second_item;

    $total 						= round($sub_total + $tax_total, 2);

    $taxes 		= json_encode([
		["id" => "18_tax1","name" => "TAX1 18%","rate" => "18","amount" => $tax1_first_item + $tax1_second_item],
		["id" => "10_tax2","name" => "TAX2 10%","rate" => "10","amount" => $tax2_first_item],

	]);



    $estimate->item_line()->saveMany([
    	new EstimateItem([
    		'description' 		=> 'Website Design',
            'long_description' 	=> 'Consectetur ad voluptate numquam corporis ea. Tempore sit tempore et officia.',
            'quantity' 			=> 1,
            'unit' 				=> NULL,
            'rate' 				=> $first_product_rate,
            'tax_id' 			=> json_encode(["18_tax1","10_tax2"]),
            'sub_total' 		=> $first_product_rate,
    	]),
    	new EstimateItem([
    		'description' 		=> 'Samsung LCD Monitor',
            'long_description' 	=> 'Et harum voluptate nam. Neque officia animi culpa aperiam ut.',
            'quantity' 			=> 1,
            'unit' 				=> NULL,
            'rate' 				=> $second_product_rate,
            'tax_id' 			=> json_encode(["18_tax1"]),
            'sub_total' 		=> $second_product_rate,

    	]),
    ]);

    $estimate->taxes 		= $taxes;
    $estimate->sub_total 	= $sub_total;
    $estimate->tax_total 	= $tax_total;
    $estimate->total 		= $total;
    $estimate->save();
});

