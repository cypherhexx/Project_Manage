<?php

use Faker\Generator as Faker;
use App\ProposalItem;
use App\Customer;
use App\NumberGenerator;

$factory->define(App\Proposal::class, function (Faker $faker) {

	$customer = Customer::find($faker->randomElement(range(1,3)));

	$contact  = $customer->primary_contact;

	$content = '<h3>ABOUT US</h3>
<p>{company_name} is one of the leading Information Technology Solution Companies in the country with a strong talented team that provides extensive range of products and services. It has built a reputation for innovation and delivering excellence in development and design. Incorporated both in the United States and in France, {company_name} offers the most advanced IT solutions, supporting full business cycle: preliminary consulting, system development and deployment, quality assurance and 24x7 supports.</p>
<p><img style="display: block; margin-left: auto; margin-right: auto;" src="/laravel-filemanager/photos/1/newLIFECYCLEsupport.jpg" alt="" /></p>
<h3>Products</h3>
<p>{proposal_items}</p>
<p>&nbsp;</p>
<p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</p>
<p>The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from "de Finibus Bonorum et Malorum" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.</p>
<p>&nbsp;</p>
<h3>Where can I get some?</h3>
<p>There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even slightly believable. If you are going to use a passage of Lorem Ipsum, you need to be sure there isn\'t anything embarrassing hidden in the middle of text. All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary, making this the first true generator on the Internet. It uses a dictionary of over 200 Latin words, combined with a handful of model sentence structures, to generate Lorem Ipsum which looks reasonable. The generated Lorem Ipsum is therefore always free from repetition, injected humour, or non-characteristic words etc</p>';

    return [
        'url_slug'						=> md5(microtime()), 
        'number' 						=> NumberGenerator::gen(COMPONENT_TYPE_PROPOSAL), 
        'title'							=> $faker->text(80), 
        'content'						=> $content , 
        'component_id'					=> COMPONENT_TYPE_CUSTOMER, 
        'component_number'				=> $customer->id,
        'date' 							=> date("Y-m-d"),
        'open_till' 					=> date('Y-m-d', strtotime("+20 day")),
        'currency_id'					=> ($customer->currency_id) ?? 1,
        'discount_type_id'				=> NULL,
        'status_id'						=> $faker->randomElement([ PROPOSAL_STATUS_DRAFT, PROPOSAL_STATUS_SENT, PROPOSAL_STATUS_OPEN ]),
        'assigned_to'					=> 2,
        'send_to'						=> $contact->first_name . " ". $contact->last_name,
        'email'							=> $contact->email,
        'phone'							=> $contact->phone,
        'address'						=> $customer->address,
        'city'							=> $customer->city,
        'state'							=> $customer->state,
        'country_id'					=> $customer->country_id,
        'zip_code'						=> $customer->zip_code,
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


$factory->afterCreating(App\Proposal::class, function ($proposal, $faker) {

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



    $proposal->item_line()->saveMany([
    	new ProposalItem([
    		'description' 		=> 'Website Design',
            'long_description' 	=> 'Consectetur ad voluptate numquam corporis ea. Tempore sit tempore et officia.',
            'quantity' 			=> 1,
            'unit' 				=> NULL,
            'rate' 				=> $first_product_rate,
            'tax_id' 			=> json_encode(["18_tax1","10_tax2"]),
            'sub_total' 		=> $first_product_rate,
    	]),
    	new ProposalItem([
    		'description' 		=> 'Samsung LCD Monitor',
            'long_description' 	=> 'Et harum voluptate nam. Neque officia animi culpa aperiam ut.',
            'quantity' 			=> 1,
            'unit' 				=> NULL,
            'rate' 				=> $second_product_rate,
            'tax_id' 			=> json_encode(["18_tax1"]),
            'sub_total' 		=> $second_product_rate,

    	]),
    ]);

    $proposal->taxes 		= $taxes;
    $proposal->sub_total 	= $sub_total;
    $proposal->tax_total 	= $tax_total;
    $proposal->total 		= $total;
    $proposal->save();
});
