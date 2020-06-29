<?php

use Faker\Generator as Faker;
use App\NumberGenerator;
use App\InvoiceItem;

$factory->define(App\Invoice::class, function (Faker $faker) {

	$address 	= $faker->streetAddress;
    $city 		= $faker->city;
    $state 		= $faker->state;
    $zip_code 	= $faker->postcode;
    $country_id = 1;
   

    return [
      	   'url_slug' 						=> md5(microtime()),
           'number' 						=> NumberGenerator::gen(COMPONENT_TYPE_INVOICE),
           'reference' 						=> NULL,
           'customer_id' 					=> $faker->randomElement(range(1,5)),
           'project_id' 					=> NULL,          
           
           'address' 						=> $address, 
	       'city' 							=> $city, 
	       'state' 							=> $state, 
	       'zip_code' 						=> $zip_code, 
	       'country_id' 					=> $country_id, 
	   
	       'shipping_address' 				=> $faker->streetAddress, 
	       'shipping_city' 					=> $city, 
	       'shipping_state' 				=> $state, 
	       'shipping_zip_code' 				=> $zip_code,
	       'shipping_country_id' 			=> $country_id,

            'currency_id' 					=> 1,
            'discount_type_id' 				=> NULL,
            'status_id' 					=> INVOICE_STATUS_UNPAID,
            'sales_agent_id' 				=> 1,


            'admin_note' 					=> $faker->text(100),
            'client_note' 					=> $faker->text(30),
            'terms_and_condition' 			=> $faker->text(50),

            'date' 							=> date("Y-m-d"),
            'due_date' 						=> date('Y-m-d', strtotime("+10 day")),

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
            'allow_partial_payment' 		=> $faker->randomElement([NULL,1]), 
            
         
         
    ];
});


$factory->afterCreating(App\Invoice::class, function ($invoice, $faker) {

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



    $invoice->item_line()->saveMany([
    	new InvoiceItem([
    		'description' 		=> 'Website Design',
            'long_description' 	=> 'Consectetur ad voluptate numquam corporis ea. Tempore sit tempore et officia.',
            'quantity' 			=> 1,
            'unit' 				=> NULL,
            'rate' 				=> $first_product_rate,
            'tax_id' 			=> json_encode(["18_tax1","10_tax2"]),
            'sub_total' 		=> $first_product_rate,
    	]),
    	new InvoiceItem([
    		'description' 		=> 'Samsung LCD Monitor',
            'long_description' 	=> 'Et harum voluptate nam. Neque officia animi culpa aperiam ut.',
            'quantity' 			=> 1,
            'unit' 				=> NULL,
            'rate' 				=> $second_product_rate,
            'tax_id' 			=> json_encode(["18_tax1"]),
            'sub_total' 		=> $second_product_rate,

    	]),
    ]);

    $invoice->taxes 	= $taxes;
    $invoice->sub_total = $sub_total;
    $invoice->tax_total = $tax_total;
    $invoice->total 	= $total;
    $invoice->save();
});




       				


                    