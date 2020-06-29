<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ProjectStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    private $project;
    private $changedByUser;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($project, $changedByUser)
    {
        //
        $this->project          = $project;
        $this->changedByUser    = $changedByUser;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $message = sprintf(__('form.status_of_project_hash_been_changed'), $this->project->name , $this->project->status->name, $this->changedByUser->first_name. " ". $this->changedByUser->last_name );

        return (new MailMessage)                    
                    ->line($message);
               
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
        ];
    }
}
