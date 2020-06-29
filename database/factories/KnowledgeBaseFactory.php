<?php

use Faker\Generator as Faker;

$factory->define(App\ArticleGroup::class, function (Faker $faker) {
    return [
       'description' => $faker->text($faker->numberBetween(100, 200))
    ];
});


$factory->state(App\ArticleGroup::class, 'sales', ['name' 	=> 'Sales', 'slug' => 'sales' ]);
$factory->state(App\ArticleGroup::class, 'info', ['name' 	=> 'Info', 'slug' => 'info' ]);
$factory->state(App\ArticleGroup::class, 'company', ['name' 	=> 'Company', 'slug' => 'company ']);
$factory->state(App\ArticleGroup::class, 'abuse', ['name' 	=> 'Abuse', 'slug' => 'abuse' ]);
$factory->state(App\ArticleGroup::class, 'account', ['name' 	=> 'Account', 'slug' => 'account' ]);
$factory->state(App\ArticleGroup::class, 'technical', ['name' 	=> 'Technical', 'slug' => 'technical' ]);


$factory->afterCreating(App\ArticleGroup::class, function ($article_group, $faker) {

	for ($i=0; $i < 5 ; $i++) { 
		
		$subject = $faker->realText(60);

		\App\Article::create([
	     	'article_group_id' 		=> $article_group->id ,
	     	'subject'				=> $subject, 
	     	'details'				=> $faker->text($faker->numberBetween(500,800)),   
	     	'slug'					=> str_slug($subject)
	 	]); 

	}
});





