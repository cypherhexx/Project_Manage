<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RemoveTemporaryFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'remove:temp_files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove Temporary Files';

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
       $folder_path = storage_path('app/'.TEMPORARY_FOLDER_IN_STORAGE);
       $files       = glob($folder_path.'*.{xlsx}', GLOB_BRACE);
        
       if(is_countable($files) && count($files) > 0)
       {
            foreach($files as $path) 
            {
                 $file          = basename($path); 
                 $file_name     =  str_replace('.xlsx', '', $file);
                 // Convert it to minutes.
                 $difference    = time() - $file_name;

                 // If the difference is more than 5 minutes delete the file.
                 if($difference > 5*60)
                 {
                    // Delete the file
                    unlink($path);
                 }
                
            }
       }
    }
}
