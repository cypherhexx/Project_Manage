<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TaskAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    private $task;
    private $assigned_by;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($task, $assigned_by)
    {
        //
        $this->task         = $task;
        $this->assigned_by  = $assigned_by;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
       push_notification($notifiable->id);

        return (new MailMessage)
                    ->line(__('form.you_have_a_new_task') . " : ". $this->task->title)
                    ->line(__('form.task_#') . " : ". $this->task->number)
                    ->action(__('form.view_task'), route('show_task_page', $this->task->id));
                    //->line('Thank you for using our application!')
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
            'message' => __('form.you_have_a_new_task') . " : ". $this->task->title ,
            'url'  => route('show_task_page', $this->task->id),
        ];
    }
}
