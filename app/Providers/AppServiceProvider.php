<?php

namespace App\Providers;

use App\Setting;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {       
        //
        Schema::defaultStringLength(191);
        
        if(env('ENABLE_HTTPS') == TRUE)
        {
          \URL::forceScheme('https'); 
        }   
        
        // Requiments
        // GD Library (>=2.0)
        // Imagick PHP extension (>=6.5.7)

        if (App::runningInConsole())
        {
            // Loading the Application Based configurations
            Setting::setup_app_config();
        }
        else
        {

            if(config('microelephant.enable_view_templating'))
            {
                $this->enable_view_templating();                
            }

        }
         

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        
        if(file_exists(config('microelephant.helper_file')))
        {
            require_once config('microelephant.helper_file');    
        }
    }

    private function enable_view_templating()
    {
        View::composer('*', function ($view) {            
           
           $view_paths      = config('view.paths');
           $template_folder = config('microelephant.view_template_folder');

           if($template_folder)
           {
                if(is_array($view_paths) && (count($view_paths) > 0))
                {
                    $view_path = $view_paths[0];

                    if (view()->exists($template_folder .'/'. $view->getName())) 
                    {  
                        $view->setPath($view_path.'/'. $template_folder .'/'. str_replace('.', '/', $view->getName() ). '.blade.php' );               

                    }      
                }       
           }

                         

        });

    }
        
}
