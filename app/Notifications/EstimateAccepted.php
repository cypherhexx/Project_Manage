<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EstimateAccepted extends Notification implements ShouldQueue
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
        //
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
        $accepted_by    = $this->estimate->accepted_by_first_name . " " . $this->estimate->accepted_by_last_name . " ";
          
        $message        = sprintf(__('form.notify_accepted_your_estimate'), $accepted_by, $this->estimate->number);


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
        $accepted_by    = $this->estimate->accepted_by_first_name . " " . $this->estimate->accepted_by_last_name . " ";
          
        $message        = sprintf(__('form.notify_accepted_your_estimate'), $accepted_by, $this->estimate->number);

        return [
            //
            'message'   => $message,
            'url'       => route('show_estimate_page', $this->estimate->id) ,
        ];
    }
}
