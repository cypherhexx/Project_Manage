<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TicketAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    private $ticket;
    private $assigned_by;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($ticket, $assigned_by)
    {
        $this->ticket       = $ticket;
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
        push_notification($notifiable->id);

        return (new MailMessage)
                    ->line(__('form.you_have_been_assigned_a_new_ticket') . " : ". $this->ticket->number)
                    
                    ->action(__('form.view_ticket'), route('show_ticket_page', $this->ticket->id));
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
            'message' => __('form.you_have_been_assigned_a_new_ticket') . " : ". $this->ticket->number ,
            'url'  => route('show_ticket_page', $this->ticket->id),
        ];
    }
}
