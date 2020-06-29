<?php

use Faker\Generator as Faker;
use App\NumberGenerator;


$factory->define(App\Payment::class, function (Faker $faker) {



    $invoices 		= \App\Invoice::where('status_id', '<>', INVOICE_STATUS_PAID)->get();

    $key_to_pick 	= $faker->randomElement(range(0, (count($invoices) - 1) ));

    $invoice 		= $invoices[$key_to_pick];
    $amount_paid 	= $invoice->total - ($invoice->amount_paid + $invoice->applied_credits);


    return [           
	    'number'            => NumberGenerator::gen(COMPONENT_TYPE_PAYMENT),
	    'date'              => date("Y-m-d"),
	    'invoice_id'        => $invoice->id,
	    'amount'            => $amount_paid ,              
	    'payment_mode_id'   => 1 ,
	    'transaction_id'    => $faker->numberBetween(100000, 200000),
	    'note'              => $faker->text(10),
	    'entry_by'          => 1,
	    
    ];
});



$factory->afterCreating(App\Payment::class, function ($payment, $faker) {

	$invoice = $payment->invoice;
	$invoice->status_id = INVOICE_STATUS_PAID;
    $invoice->save();
});


