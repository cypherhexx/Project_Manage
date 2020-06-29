<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use database\seeds\DemoDataSeeder;

class GenerateDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gen:demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush database and generate dummy data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
       \Artisan::call('migrate:fresh');

       \Artisan::call('db:seed');
  
 
       \Artisan::call('db:seed', [
            '--class'     => 'DemoDataSeeder',
        ]);
    }
}
