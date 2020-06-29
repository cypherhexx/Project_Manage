<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Schema::disableForeignKeyConstraints();
         $this->call([
             UsersTableSeeder::class,
             CountriesTableSeeder::class,
             LanguageTableSeeder::class,
             PrioritiesTableSeeder::class,
             ComponentsTableSeeder::class,
             TaskStatusesTableSeeder::class,
             ProposalStatusesSeeder::class,             
             EstimateStatusesTableSeeder::class,
             InvoiceStatusesTableSeeder::class,
             SettingsTableSeeder::class,             
             BillingTypesTableSeeder::class,
             ProjectStatusesTableSeeder::class,
             TicketStatusTableSeeder::class,
             GendersTableSeeder::class,
             ItemCategoriesTableSeeder::class,
             LeadStatusesTableSeeder::class,
             LeadSourcesTableSeeder::class,
             TicketPrioritiesTableSeeder::class,
             CreditNoteStatusesTableSeeder::class,

             
         ]);
         Schema::enableForeignKeyConstraints();
    }

    function update_schema()
    {
        


    }
}
