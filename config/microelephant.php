<?php

return [

	'helper_file' 					=> '', // example: app_path('Helpers/my_helper.php')
	
	// Boolean (TRUE or FALSE) 		
	'enable_view_templating' 		=> FALSE,

	// The folder must be inside resource/view folder
	'view_template_folder' 			=> 'my_custom',

	// Type Array
	'admin_css'			 			=> [

		 /*
         * CSS files you want to include in the header section of admin panel  ...
         */
		 //'https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.css',

	],

	// Type Array
	'admin_js' 						=> [

 		/*
         * Js files you want to include in the footer section of admin panel  ...
         */
 		//'https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js',
	],



	// Type Array
	'customer_css'			 		=> [

		 /*
         * CSS files you want to include in the header section of customer panel  ...
         */
         // 'https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.css',

	],

	// Type Array
	'customer_js' 					=> [

 		/*
         * Js files you want to include in the footer section of customer panel  ...
         */
 		// 'https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js',

	],


	'payment_method_classes' 		=> [
		
 		//'paypal' => \App\Services\PaymentGateway\Paypal::class,

	],

];