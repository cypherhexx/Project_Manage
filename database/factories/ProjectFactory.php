<?php

use Faker\Generator as Faker;
use App\NumberGenerator;
use App\Milestone;
use Carbon\Carbon;

$factory->define(App\Project::class, function (Faker $faker) {

	$project_settings = '{"tabs":{"tasks":"on","timesheets":"on","files":"on","milestones":"on","gantt_view":"on","invoices":"on","estimates":"on"},"permissions":{"view_tasks":"on","create_tasks":"on","edit_tasks":"on","comment_on_tasks":"on","view_task_comments":"on","view_task_attachments":"on","upload_on_tasks":"on","view_task_total_logged_time":"on","view_finance_overview":"on","view_milestones":"on","view_gantt":"on","view_timesheets":"on","view_team_members":"on","upload_files":"on"}}';

    return [
        'number' 							=> NumberGenerator::gen(COMPONENT_TYPE_PROJECT),               
        'calculate_progress_through_tasks' 	=> TRUE,
        'progress' 							=> NULL,        
        'start_date' 						=> date("Y-m-d"),
        'dead_line' 						=> date('Y-m-d', strtotime("+30 day")),
        'description' 						=> $faker->text(200),
        'status_id' 						=> PROJECT_STATUS_IN_PROGRESS,
        'settings' 							=> $project_settings,
        'created_by' 						=> 1,
    ];
});


$factory->state(App\Project::class, 'BILLING_TYPE_TASK_HOURS', [
    	'name' 								=> 'Ecommerce Website', 
        'customer_id' 						=> 1,
        'billing_type_id' 					=> BILLING_TYPE_TASK_HOURS,
        'billing_rate' 						=> 32,
]);

$factory->state(App\Project::class, 'BILLING_TYPE_PROJECT_HOURS', [
    	'name' 								=> 'TVC Video Production', 
        'customer_id' 						=> 2,
        'billing_type_id' 					=> BILLING_TYPE_PROJECT_HOURS,
        'billing_rate' 						=> 32,
]);

$factory->state(App\Project::class, 'BILLING_TYPE_FIXED_RATE', [
    	'name' 								=> 'ERP System Development', 
        'customer_id' 						=> 3,
        'billing_type_id' 					=> BILLING_TYPE_FIXED_RATE,
        'billing_rate' 						=> 5000,
]);



$factory->afterCreating(App\Project::class, function ($project, $faker) {

	$project->members()->attach([1,2,3]);
	$project->tag_attach([1]);


        

	$project->milestones()->saveMany([
            		new Milestone(['name'                                      => 'Planning',
								        'background_color'                          => '#DDCEF1',
								        'background_text_color'                     => '#141313',    
								        'due_date'                                  => date('Y-m-d', strtotime("+10 day")),
								        'description'                               => $faker->text(30),
								        'show_description_to_customer'              => TRUE,
								        'order'                                     => 1
								    ]),
            		new Milestone(['name'                                      		=> 'Design',
								        'background_color'                          => '#5776DB',
								        'background_text_color'                     => '#F7EDED',    
								        'due_date'                                  => date('Y-m-d', strtotime("+15 day")),
								        'description'                               => $faker->text(30),
								        'show_description_to_customer'              => TRUE,
								        'order'                                     => 2
								    ]),
            		new Milestone(['name'                                      		=> 'Development',
								        'background_color'                          => '#30AC94',
								        'background_text_color'                     => '#EFEDED',    
								        'due_date'                                  => date('Y-m-d', strtotime("+20 day")),
								        'description'                               => $faker->text(30),
								        'show_description_to_customer'              => TRUE,
								        'order'                                     => 3
								    ]),
            		new Milestone(['name'                                      		=> 'System Integration & Testing',
								        'background_color'                          => '#E3AC24',
								        'background_text_color'                     => '#F7F1F1',    
								        'due_date'                                  => date('Y-m-d', strtotime("+30 day")),
								        'description'                               => $faker->text(30),
								        'show_description_to_customer'              => TRUE,
								        'order'                                     => 4
								    ]),
            		

    ]);

	if($project->billing_type_id == BILLING_TYPE_TASK_HOURS)
	{
		factory(App\Task::class, 8)->states('project_1', 'rate', 'milestone')->create();
	}
	elseif($project->billing_type_id == BILLING_TYPE_PROJECT_HOURS)
	{
		factory(App\Task::class, 8)->states('project_2', 'rate', 'milestone')->create();
	}
	else
	{
		factory(App\Task::class, 8)->states('project_3', 'milestone')->create();
	}



});


$factory->define(App\Task::class, function (Faker $faker) {

	
	$day = $faker->randomNumber(2);

    return [    	
    	'number'          	=> NumberGenerator::gen(COMPONENT_TYPE_TASK),
	    'start_date'  		=> date("Y-m-d"),
	    'due_date'        	=> date('Y-m-d', strtotime("+$day day")),
	    'created_by'      	=> 1,
	    'user_type'       	=> USER_TYPE_TEAM_MEMBER,
	    'assigned_to'		=> $faker->randomElement([1,2]), 
	    'priority_id'		=> $faker->randomElement(range(1,4)), 
	    'title' 			=> $faker->text(50), 
	    'description'		=> $faker->text,	    
	    'status_id'			=> $faker->randomElement(range(1,5)),      
    ];
});




$factory->state(App\Task::class, 'project_1', function ($faker) {
    return [
        'component_id' 		=> COMPONENT_TYPE_PROJECT, 
	    'component_number'	=> 1
    ];
});

$factory->state(App\Task::class, 'project_2', function ($faker) {
    return [
        'component_id' 		=> COMPONENT_TYPE_PROJECT, 
	    'component_number'	=> 2
    ];
});

$factory->state(App\Task::class, 'project_3', function ($faker) {
    return [
        'component_id' 		=> COMPONENT_TYPE_PROJECT, 
	    'component_number'	=> 3
    ];
});

$factory->state(App\Task::class, 'rate', function ($faker) {
    return [
        'hourly_rate'		=> $faker->randomNumber(2),
	    'is_billable'		=> TRUE, 
    ];
});

$factory->state(App\Task::class, 'milestone', function ($faker) {
    return [
        'milestone_id' 		=> $faker->randomElement(range(1,4)),
    ];
});

$factory->state(App\Task::class, 'for_lead', function ($faker) {
    return [
        'component_id' 		=> COMPONENT_TYPE_LEAD, 
	    'component_number'	=> $faker->randomElement(range(1,3)),   
    ];
});


$factory->define(App\Timesheet::class, function (Faker $faker) {

	$start_time             = Carbon::now();
    $end_time               = Carbon::now()->addHours($faker->randomFloat(2, 1, 5));  
	
    return [    	
    	'user_id'          	=> $faker->randomElement([1,2]), 	   
        'start_time'        => $start_time->format("Y-m-d H:i:s"),
        'end_time'          => $end_time->format("Y-m-d H:i:s"),
        'duration'          => $end_time->diff($start_time)->format('%H:%I'),
        'note'              => $faker->text(20),

    
    ];
});


$factory->afterCreating(App\Task::class, function ($task, $faker) {

	if($task->status_id == TASK_STATUS_IN_PROGRESS && ($task->is_billable == TRUE))
	{
		$task->timesheets()->save(factory(App\Timesheet::class)->make());
	}
});