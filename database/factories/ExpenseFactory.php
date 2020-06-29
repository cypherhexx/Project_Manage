<?php

use Faker\Generator as Faker;

$factory->define(App\Expense::class, function (Faker $faker) {
	
	$amount = $faker->numberBetween(500, 2000);

    return [
        'expense_category_id'   => $faker->randomElement(range(1,5)), 
        'date'                  => date("Y-m-d") ,
        'amount'                => $amount ,
        'amount_after_tax'      => $amount,
        'name'                  => $faker->text(30) ,
        'note'                  => $faker->text(90) ,        
        'vendor_id'             => $faker->randomElement(range(1,3)),   		
        'currency_id'           => 1,
        'payment_mode_id'       => 1 ,
        'reference'             => NULL,
        'user_id'				=> 1
    ];
});

$factory->afterCreating(App\Expense::class, function ($expense, $faker) {

	if($expense->expense_category_id == 4 OR $expense->expense_category_id == 5)
	{
		$project = \App\Project::find($faker->randomElement(range(1,3)));
		$expense->customer_id           = $project->customer_id ;
      	$expense->project_id            = $project->id;
        $expense->is_billable          	= TRUE ;

        if($project->customer->currency_id)
        {
        	$expense->currency_id           = $project->customer->currency_id;	
        }
        
        $expense->save();
	}
    


});
