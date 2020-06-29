<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TaskStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    private $task;
    private $changed_by;


    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($task, $changed_by)
    {
         //
        $this->task         = $task;
        $this->changed_by   = $changed_by;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return (isset($notifiable->customer_id)) ? ['mail'] : ['database','mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        

        if(!isset($notifiable->customer_id))
        {   
            // Its a Team Member
            push_notification($notifiable->id);

            $link = route('show_task_page',  [ $this->task->id ] );
        }
        else
        {
            $link = route('cp_show_task_comment',  [$this->task->component_number, $this->task->id ] );
        }


        return (new MailMessage)

                    ->line(sprintf(
                        __('form.has_changed_status_to'), 
                        $this->changed_by->first_name . " ". $this->changed_by->last_name, 
                        $this->task->number, $this->task->status->name 
                        ) 
                    )
                   ->action(__('form.view_task'), $link );
                    
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {         
                
        return [
            //
            'message' => sprintf( __('form.has_changed_status_to'), 
                    $this->changed_by->first_name . " ". $this->changed_by->last_name, 
                    $this->task->number, $this->task->status->name ) , 
                
            'url'  => route('show_task_page', $this->task->id),
        ];
    }
}
