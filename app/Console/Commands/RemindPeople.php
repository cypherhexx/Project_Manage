<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Reminder;
use App\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\Remind;

class RemindPeople extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        echo "Searching for list of reminders ... \n";
        $things_to_remind = Reminder::where('date_to_be_notified', '<=', date("Y-m-d H:i:s"))->whereNull('is_notified')->get();

        if(count($things_to_remind) > 0)
        {
            echo "Found list of reminders ... \n";

            foreach ($things_to_remind as $remind) 
            {
                $notifiable_user = User::find($remind->send_reminder_to);

                if($notifiable_user)
                {
                    echo "Sending notification ... \n";
                    Notification::send($notifiable_user, new Remind($remind) );  
                    // Mark the notification as sent
                    $remind->is_notified = TRUE;
                    $remind->save(); 
                    echo "Notification Sent... \n"; 
                }
                else
                {
                    echo "Not a valid user to notify ... \n"; 
                }
                
            }
        }
        else
        {
            echo "No reminders. Good bye ... \n";
        }
    }
}
