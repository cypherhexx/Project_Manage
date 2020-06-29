<?php

use Faker\Generator as Faker;
use App\NumberGenerator;

$factory->define(App\Ticket::class, function (Faker $faker) {

	$customer = App\Customer::find( $faker->randomElement(range(1,4)) );
	$contact  = $customer->primary_contact;

    return [
       'number' 				=> NumberGenerator::gen(COMPONENT_TYPE_TICKET),
       'subject' 				=> $faker->realText(60),
       'customer_contact_id' 	=> $contact->id, 
       'name'					=> $contact->first_name . " ". $contact->last_name,		 
       'email' 					=> $contact->email, 
       'project_id' 			=> NULL,  
       'department_id'			=> $faker->randomElement(range(1,3)), 
       'ticket_priority_id' 	=> $faker->randomElement(range(1,3)), 
       'ticket_service_id' 		=> NULL,         
       'assigned_to' 			=> 1, 
       'ticket_status_id' 		=> $faker->randomElement([1,3]),  
       'created_by' 			=> $contact->id,
       'user_type' 				=> USER_TYPE_CUSTOMER
    ];
});


$factory->afterCreating(App\Ticket::class, function ($ticket, $faker) {


	$comment 			      = new \App\Comment();
    $comment->body 		      = $faker->realText(300);
    $comment->user_id 	      = $ticket->customer_contact_id;
    $comment->user_type       = USER_TYPE_CUSTOMER;
    $ticket->comments()->save($comment);  


    if($ticket->ticket_status_id == TICKET_STATUS_ANSWERED)
    {
		$user 						= \App\User::find(1);

		$comment 			      	= new \App\Comment();
	    $comment->body 		      	= $faker->realText($faker->randomElement(range(200,500)));
	    $comment->user_id 	      	= 1;
	    $comment->user_type       	= USER_TYPE_TEAM_MEMBER;
	    $ticket->comments()->save($comment);

	    $ticket->last_reply 		= date('Y-m-d H:i:s', strtotime("+1 hour"));
	    $ticket->save();
    }

});




$factory->define(App\PreDefinedReply::class, function (Faker $faker) {
    return [      
       'name'        => $faker->realText($faker->randomElement(range(20,40))),
       'details'     => $faker->realText($faker->randomElement(range(300,500))),
       
    ];
});