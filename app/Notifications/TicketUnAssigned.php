<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TicketUnAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    private $ticket;
    private $unassigned_by;
    private $message;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($ticket, $unassigned_by)
    {
        $this->ticket           = $ticket;
        $this->unassigned_by    = $unassigned_by->first_name. " ". $unassigned_by->last_name;       
        $this->message          = sprintf(__('form.you_have_been_unassigned_from_ticket'), $this->ticket->number, $this->unassigned_by) ;
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
                    ->subject(__('form.ticket_unassigned'))
                    ->line($this->message)                    
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
            'message'   => $this->message ,
            'url'       => route('show_ticket_page', $this->ticket->id),
        ];
    }
}
