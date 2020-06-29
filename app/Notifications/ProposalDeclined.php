<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ProposalDeclined extends Notification implements ShouldQueue
{
    use Queueable;
    private $proposal;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($proposal)
    {
        $this->proposal = $proposal;
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

        $message        = sprintf(__('form.notify_proposal_has_been_declined'), $this->proposal->title);

        return (new MailMessage)
                    ->line($message)
                    ->action(__('form.view_proposal'), route('show_proposal_page', $this->proposal->id));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        $message        = sprintf(__('form.notify_proposal_has_been_declined'), $this->proposal->title);

        return [
            //
            'message'   => $message,
            'url'       => route('show_proposal_page', $this->proposal->id),
        ];
    }
}
