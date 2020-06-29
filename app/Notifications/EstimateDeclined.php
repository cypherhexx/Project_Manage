<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EstimateDeclined extends Notification implements ShouldQueue
{
    use Queueable;

    private $estimate;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($estimate)
    {
        $this->estimate = $estimate;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database' ,'mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $message        = sprintf(__('form.notify_estimate_has_been_declined'), $this->estimate->number);

        return (new MailMessage)
                    ->line($message)
                    ->action(__('form.view_estimate'), route('show_estimate_page', $this->estimate->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $message        = sprintf(__('form.notify_estimate_has_been_declined'), $this->estimate->number);
        
        return [
            //
            'message'   => $message,
            'url'       => route('show_estimate_page', $this->estimate->id) ,
        ];
    }
}
