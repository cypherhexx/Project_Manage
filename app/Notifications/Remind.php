<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Reminder;

class Remind extends Notification implements ShouldQueue
{
    use Queueable;

    private $reminder;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Reminder $reminder)
    {
        $this->reminder = $reminder;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database','mail'];
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

        return (new MailMessage)->line($this->reminder->description)->subject(__('form.reminder'));                    
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $route_name = get_url_route_name_by_model_class(get_class($this->reminder->remindable));

        return [
            'message'   => $this->reminder->description,
            'url'       => route($route_name,$this->reminder->remindable->id),
        ];
    }
}
