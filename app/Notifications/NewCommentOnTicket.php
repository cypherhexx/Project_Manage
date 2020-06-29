<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewCommentOnTicket extends Notification implements ShouldQueue
{
    use Queueable;

    private $ticket;    
    private $link_to_comment;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($ticket, $link_to_comment)
    {
       $this->ticket                            = $ticket;       
       $this->link_to_comment                   = $link_to_comment;
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
        
        return (new MailMessage)
                    ->subject(__('form.new_comment_on') ." : ". $this->ticket->number)
                    ->line(__('form.you_have_a_new_reply_on_ticket') . " : ". $this->ticket->number)
                    ->action(__('form.view_comment'), $this->link_to_comment )
                    ->line(__('form.thank_you'));
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
            'message'   => __('form.you_have_a_new_reply_on_ticket') . " : ". $this->ticket->number ,
            'url'       => $this->link_to_comment ,
        ];
    }
}
