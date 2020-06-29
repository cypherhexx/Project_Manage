<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\User;

use App\Comment;

class MemberMentionedInComment extends Notification implements ShouldQueue
{
    use Queueable;

    private $mentioned_by;
    private $url_to_the_comment;
    private $msg;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $mentioned_by, Comment $comment, $url_to_the_comment)
    {
        //
        $this->mentioned_by         = $mentioned_by;
        $this->url_to_the_comment   = $url_to_the_comment;
        $this->msg = $this->mentioned_by->first_name . " " . $this->mentioned_by->last_name . " ". __('form.mentioned_you_in_a_comment');
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
                    ->line($this->msg)
                    ->action(__('form.view_comment'), $this->url_to_the_comment)
                   ;
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
            'message'   => $this->msg,
            'url'       => $this->url_to_the_comment,
        ];
    }
}
