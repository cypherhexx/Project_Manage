<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      
       Schema::table('expenses', function (Blueprint $table) {
            
            $table->foreign('expense_category_id')->references('id')->on('expense_categories');  
            $table->foreign('currency_id')->references('id')->on('currencies');  
            $table->foreign('payment_mode_id')->references('id')->on('payment_modes');  
            $table->foreign('customer_id')->references('id')->on('customers'); 
            $table->foreign('vendor_id')->references('id')->on('vendors');  
            $table->foreign('project_id')->references('id')->on('projects'); 
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users');  

        });


        Schema::table('customers', function (Blueprint $table) {
            
            $table->foreign('country_id')->references('id')->on('countries');  
            $table->foreign('shipping_country_id')->references('id')->on('countries');           
            $table->foreign('currency_id')->references('id')->on('currencies');
            $table->foreign('created_by')->references('id')->on('users');         

        });

         Schema::table('tag_customers_groups', function (Blueprint $table) {
            
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');  
            $table->foreign('group_id')->references('id')->on('customer_groups')->onDelete('cascade');           
                

        });



        Schema::table('projects', function (Blueprint $table) {
            
            $table->foreign('customer_id')->references('id')->on('customers');  
            $table->foreign('billing_type_id')->references('id')->on('billing_types');           
            $table->foreign('status_id')->references('id')->on('project_statuses');
            $table->foreign('created_by')->references('id')->on('users');        

        });


         Schema::table('customer_contacts', function (Blueprint $table) {
            
            $table->foreign('customer_id')->references('id')->on('customers');                  

        });

        Schema::table('items', function (Blueprint $table) {
            
            $table->foreign('item_category_id')->references('id')->on('item_categories');
            $table->foreign('tax_id_1')->references('id')->on('taxes');    
            $table->foreign('tax_id_2')->references('id')->on('taxes');                  

        });

        Schema::table('leads', function (Blueprint $table) {
            
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('lead_status_id')->references('id')->on('lead_statuses'); 
            $table->foreign('lead_source_id')->references('id')->on('lead_sources');
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');                           
            $table->foreign('last_contacted_by')->references('id')->on('users'); 
            $table->foreign('created_by')->references('id')->on('users');       
            

        });


        Schema::table('tasks', function (Blueprint $table) {
            
            $table->foreign('component_id')->references('id')->on('components');
            $table->foreign('parent_task_id')->references('id')->on('tasks'); 
            $table->foreign('milestone_id')->references('id')->on('milestones')->onDelete('set null');
            $table->foreign('priority_id')->references('id')->on('priorities');
           
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->foreign('status_id')->references('id')->on('task_statuses');              

        });


        Schema::table('proposals', function (Blueprint $table) {
            
            $table->foreign('component_id')->references('id')->on('components');
            $table->foreign('currency_id')->references('id')->on('currencies'); 
            $table->foreign('status_id')->references('id')->on('proposal_statuses');
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('created_by')->references('id')->on('users');             

        });

        Schema::table('proposal_items', function (Blueprint $table) {
            
            $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('cascade');                         

        });


        Schema::table('estimates', function (Blueprint $table) {
            
            $table->foreign('status_id')->references('id')->on('estimate_statuses');
            $table->foreign('currency_id')->references('id')->on('currencies'); 
            $table->foreign('customer_id')->references('id')->on('customers');
            
            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('created_by')->references('id')->on('users');   

            $table->foreign('sales_agent_id')->references('id')->on('users');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('shipping_country_id')->references('id')->on('countries');
            $table->foreign('invoice_id')->references('id')->on('invoices');            
            

        });

        Schema::table('estimate_items', function (Blueprint $table) {
            
            $table->foreign('estimate_id')->references('id')->on('estimates')->onDelete('cascade');                        

        });


         Schema::table('invoices', function (Blueprint $table) {
            
            $table->foreign('status_id')->references('id')->on('invoice_statuses');
            $table->foreign('currency_id')->references('id')->on('currencies'); 
            $table->foreign('customer_id')->references('id')->on('customers');
            
            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('created_by')->references('id')->on('users');   

            $table->foreign('sales_agent_id')->references('id')->on('users');
            $table->foreign('country_id')->references('id')->on('countries');
            $table->foreign('shipping_country_id')->references('id')->on('countries');
                 
            

        });

        Schema::table('invoice_items', function (Blueprint $table) {
            
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');                      

        });


        Schema::table('payments', function (Blueprint $table) {
            
            $table->foreign('invoice_id')->references('id')->on('invoices'); 
            $table->foreign('payment_mode_id')->references('id')->on('payment_modes');                        

        });


        Schema::table('time_sheets', function (Blueprint $table) {
            $table->foreign('task_id')->references('id')->on('tasks'); 
            $table->foreign('user_id')->references('id')->on('users'); 
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('invoice_item_id')->references('id')->on('invoice_items')->onDelete('set null');                       

        });


        Schema::table('milestones', function (Blueprint $table) {
            
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');                         

        });


        Schema::table('role_permissions', function (Blueprint $table) {
            
            $table->foreign('role_id')->references('id')->on('roles');                       

        });

        Schema::table('users', function (Blueprint $table) {
            
            $table->foreign('role_id')->references('id')->on('roles');                       

        });


        Schema::table('teams', function (Blueprint $table) {
            
            $table->foreign('leader_user_id')->references('id')->on('users')->onDelete('cascade');                       

        });


        Schema::table('user_teams', function (Blueprint $table) {
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');                       

        });


        
        Schema::table('vendors', function (Blueprint $table) {
            
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('country_id')->references('id')->on('countries');                        

        });


        Schema::table('task_check_lists', function (Blueprint $table) {
            
            $table->foreign('task_id')->references('id')->on('tasks');                                   

        });


        Schema::table('taggables', function (Blueprint $table) {
            
            $table->foreign('tag_id')->references('id')->on('tags');                                   

        });

      

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
