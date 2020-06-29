<?php

use Illuminate\Database\Seeder;
use App\RolePermission;
use Faker\Generator as Faker;
use App\Currency;
use App\NumberGenerator;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $faker = \Faker\Factory::create();

       


        factory(App\Role::class)->states('administrator')->create();
        $software_engineer = factory(App\Role::class)->states('software_engineer')->create();
        $software_engineer->permissions()->saveMany([
            		new RolePermission(['name' => 'projects_view', 'value' => 1]),
            		new RolePermission(['name' => 'projects_create', 'value' => 1]),
            		new RolePermission(['name' => 'projects_edit', 'value' => 1]),
            		new RolePermission(['name' => 'projects_delete', 'value' => 1]),
            		new RolePermission(['name' => 'tasks_view', 'value' => 1]),
            		new RolePermission(['name' => 'tasks_create', 'value' => 1]),
            		new RolePermission(['name' => 'tasks_edit', 'value' => 1]),
            		new RolePermission(['name' => 'tasks_delete', 'value' => 1]),
            		new RolePermission(['name' => 'teams_view', 'value' => 1]),

            		new RolePermission(['name' => 'teams_create', 'value' => 1]),
            		new RolePermission(['name' => 'teams_edit', 'value' => 1]),
            		new RolePermission(['name' => 'team_members_view', 'value' => 1]),
            		new RolePermission(['name' => 'team_members_create', 'value' => 1]),
            		new RolePermission(['name' => 'team_members_edit', 'value' => 1]),
            		new RolePermission(['name' => 'team_members_delete', 'value' => 1]),

            	]);

        $cse = factory(App\Role::class)->states('customer_support_executive')->create();

        $cse->permissions()->saveMany([
            		new RolePermission(['name' => 'tickets_view', 'value' => 1]),
            		new RolePermission(['name' => 'tickets_view_own', 'value' => 1]),
            		new RolePermission(['name' => 'tickets_create', 'value' => 1]),
            		new RolePermission(['name' => 'tickets_edit', 'value' => 1]),
            		new RolePermission(['name' => 'tickets_delete', 'value' => 1]),
            		new RolePermission(['name' => 'Knowledge_base_view', 'value' => 1]),
            		new RolePermission(['name' => 'Knowledge_base_create', 'value' => 1]),
            		new RolePermission(['name' => 'Knowledge_base_edit', 'value' => 1]),
            		new RolePermission(['name' => 'Knowledge_base_delete', 'value' => 1]),

            	]);

    	factory(App\User::class)->states('administrator')->create();
     	factory(App\User::class, 2)->states('software_engineer')->create();
     	factory(App\User::class, 2)->states('customer_support_executive')->create();
		
         DB::table('teams')->insert([
            ['name'     => 'Backend Developers' , 'leader_user_id' => 1 ],
            ['name'     => 'Designers' , 'leader_user_id' => 1],
            ['name'     => 'Field Engineers' , 'leader_user_id' => 1 ],
            
        ]);


        DB::table('customer_groups')->insert([
            ['name'     => 'Wholesaler' ],
            ['name'     => 'VIP' ],
            ['name'     => 'High Budget' ],
            ['name'     => 'Low Budget' ],
        ]);

     	// Insert Currency            
        $currency                    = new Currency();
        $currency->code              = 'USD';
        $currency->symbol            = '$';     
        $currency->is_default        = TRUE;          
        $currency->save();

     	DB::table('currencies')->insert([
            ['code' => 'USD' , 'symbol' => '$', 'is_default' => TRUE],
            ['code' => 'AUD' , 'symbol' => '$', 'is_default' => FALSE],
            ['code' => 'GBP' , 'symbol' => '£', 'is_default' => FALSE],
        ]);


        DB::table('customer_groups')->insert([
            ['name' 	=> 'Wholesaler' ],
            ['name' 	=> 'VIP' ],
            ['name' 	=> 'High Budget' ],
            ['name' 	=> 'Low Budget' ],
        ]);


        DB::table('tags')->insert([
            ['name' 	=> 'Tomorrow' ],
            ['name' 	=> 'Important' ],            
        ]);
		
		DB::table('expense_categories')->insert([
            ['name' 	=> 'Insurance' ],
            ['name' 	=> 'IT and Internet Expenses' ], 
            ['name' 	=> 'Meals' ],         
            ['name' 	=> 'Telephone' ], 
            ['name' 	=> 'Travel Expense' ],
        ]);

       
		
		DB::table('departments')->insert([
            ['name' 	=> 'Marketing'],
            ['name' 	=> 'Sales'],
            ['name' 	=> 'Abuse'],
            
            
        ]);

        DB::table('payment_modes')->insert([
            ['name'     => __('form.bank') ],            
            
        ]);



       
		DB::table('taxes')->insert([
            ['name' 	=> 'TAX1', 'rate' => 18 , 'display_as' => 18 ."_". str_replace(" ", "_", strtolower(trim('TAX1')))  ],
            ['name' 	=> 'TAX2', 'rate' => 10 , 'display_as' => 10 ."_". str_replace(" ", "_", strtolower(trim('TAX2')))  ],
            ['name' 	=> 'TAX3', 'rate' => 5 , 'display_as' => 5 ."_". str_replace(" ", "_", strtolower(trim('TAX3')))  ],
        ]);

   		
     	DB::table('items')->insert([            
            	['item_category_id' => 1, 'name' => 'Consultanting Service', 'description' => $faker->text(100), 
            	'rate' => 350 , 'unit' => '', 'tax_id_1' => 1, 'tax_id_2' => NULL],

            	['item_category_id' => 2, 'name' => 'Samsung LCD Monitor', 'description' => $faker->text(100), 
            	'rate' => 550 , 'unit' => '', 'tax_id_1' => 1, 'tax_id_2' => NULL ],


            	['item_category_id' => 2, 'name' => 'Google Nexus 7 Tablet', 'description' => $faker->text(100), 
            	'rate' => 250 , 'unit' => '', 'tax_id_1' => 1, 'tax_id_2' => NULL ],             
            
            	['item_category_id' => 1, 'name' => 'Website Design', 'description' => $faker->text(100), 
            	'rate' => 1000 , 'unit' => '', 'tax_id_1' => 1, 'tax_id_2' => NULL ],


            	['item_category_id' => 1, 'name' => 'Car Wash', 'description' => $faker->text(100), 
            	'rate' => 20 , 'unit' => '', 'tax_id_1' => 1, 'tax_id_2' => NULL ],
            
            
        ]);
		

        $this->main_customer_create($faker);
     	factory(App\Customer::class, 4)->create();
		
		factory(App\Lead::class, 5)->states('google')->create();
     	factory(App\Lead::class, 5)->states('facebook')->create();
		factory(App\Vendor::class, 5)->create();

		factory(App\Project::class)->states('BILLING_TYPE_TASK_HOURS')->create();
		factory(App\Project::class)->states('BILLING_TYPE_PROJECT_HOURS')->create();
		factory(App\Project::class)->states('BILLING_TYPE_FIXED_RATE')->create();


		factory(App\Invoice::class, 5)->create();
        factory(App\Payment::class, 2)->create();
		factory(App\Estimate::class, 5)->create();
		factory(App\Proposal::class, 2)->create();
		factory(App\Task::class, 4)->states('for_lead')->create();
		factory(App\Expense::class, 10)->create();

        factory(App\ArticleGroup::class)->states('sales')->create();
        factory(App\ArticleGroup::class)->states('info')->create();
        factory(App\ArticleGroup::class)->states('company')->create();
        factory(App\ArticleGroup::class)->states('abuse')->create();
        factory(App\ArticleGroup::class)->states('account')->create();
        factory(App\ArticleGroup::class)->states('technical')->create();
		factory(App\Ticket::class, 15)->create();
        factory(App\PreDefinedReply::class, 10)->create();
     
		
        $this->special_setup($faker);


        

    }

    function special_setup($faker)
    {
        $pusher = '{"app_id":"628356","app_key":"6c5df51d77ddee1d8ad0","app_secret":"af4f6b90a9c96272e222","app_cluster":"ap1","is_enable":true}';


        $payment_gateways = '{"paypal":{"payment_mode_id":6,"paypal_label":"Paypal","paypal_username":"paypal_api","paypal_password":"paypal_password","paypal_signature":"api_sig","paypal_description_dashboard":"Dscription","paypal_currencies":"USD,CAD,GBP","paypal_test_mode_enabled":"1","paypal_active":"1"},"stripe":{"payment_mode_id":2,"stripe_label":"Sripte LTE","stripe_api_secret_key":"sk_test_9rRMThBsLosdJuBIbTIVuP4Q","stripe_api_publishable_key":"pk_test_JBnqGXZs3HVpaR4bBwPFXoTm","stripe_description_dashboard":"Payment for Invoice {invoice_number}","stripe_currencies":"USD, EURO","stripe_test_mode_enabled":"1","stripe_active":"1"}}';
        
        $settings = [

            'company_phone' => $faker->phoneNumber,
            'company_address' => $faker->streetAddress,
            'company_email' => $faker->unique()->email,

            'company_email_send_using' => 'mailgun',
            'company_email_mailgun_domain' => 'elephant.mailgun.org',
            'company_email_mailgun_key' => '520ad8c89b2d23e48a15f8f',
            'company_email_from_address' => 'info@microelephant.io',
            'company_email_smtp_host'    => '',
            'company_email_smtp_port'    => '',
            'company_email_encryption'    => '',
            'company_email_smtp_username'    => '',
            'company_email_smtp_password'    => '',
            'email_signature'    => 'Microelephant Team',
            'pusher' => $pusher,
            'google_recaptcha_secret_key' => '6Lf1unoUAAAAAM2ciYycllukRnzk9dFPqMDfZ_lk',
            'google_recaptcha_site_key' => '6Lf1unoUAAAAAEwmAGca8IXWceNqF_usErQcgxBR',
            'enable_google_recaptcha' => 1,

            'company_logo_internal' => 'public/uploads/logo_internal.png',
            'company_logo_internal_small' => 'public/uploads/logo_internal_125x34.png',
            'favicon' => 'public/uploads/favicon.ico',
            'payment_gateways' => $payment_gateways

        ];

        foreach ($settings as $key => $value) 
        {
            $obj = \App\Setting::updateOrCreate(['option_key' => $key ]);
            $obj->option_value = $value;
            $obj->save();
        }


         DB::table('payment_modes')->insert([
            ['name'     => 'Stripe', 'is_online' => TRUE ],                        
        ]);

    }




    function main_customer_create($faker)
    {
        $title      = $faker->title;    
        $address    = $faker->streetAddress;
        $city       = $faker->city;
        $state      = $faker->state;
        $zip_code   = $faker->postcode;
        $country_id = 1;

        $data = [
           'number'                                     => NumberGenerator::gen(COMPONENT_TYPE_CUSTOMER) ,
           'name'                                       => $faker->company, 
           'vat_number'                                 => $faker->numberBetween(1000, 2000),
           'phone'                                      => $faker->phoneNumber, 
           'website'                                    => 'http://www.'.$faker->domainName, 
           'address'                                    => $address, 
           'city'                                       => $city, 
           'state'                                      => $state, 
           'zip_code'                                   => $zip_code, 
           'country_id'                                 => $country_id, 
           'shipping_is_same_as_billing'                => TRUE, 
           'shipping_address'                           => $faker->streetAddress, 
           'shipping_city'                              => $city, 
           'shipping_state'                             => $state, 
           'shipping_zip_code'                          => $zip_code,
           'shipping_country_id'                        => $country_id,
           'notes'                                      => $faker->text(100) , 
            'default_language'                          => NULL, 
           'currency_id'                                => 1, 
           'created_by'                                 => 1
        ];

        $customer = \App\Customer::create($data);

        $customer->groups()->attach([1,2]);

        \App\CustomerContact::create([ 
            'customer_id'               => $customer->id,          
            'first_name'                => $faker->firstName,
            'last_name'                 => $faker->lastName,
            'email'                     => 'customer@demo.com',
            'phone'                     => $faker->phoneNumber, 
            'position'                  => $faker->jobTitle,       
            'password'                  => bcrypt('123456'),       
            'is_primary_contact'        => TRUE,   
        
        ]);



    }
}
