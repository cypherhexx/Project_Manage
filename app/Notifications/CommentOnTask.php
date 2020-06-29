<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class CommentOnTask extends Notification implements ShouldQueue
{
    use Queueable;

    private $task;
    private $comment;
    private $updated;
    private $comment_by;
    private $subject;
    

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($task, $comment, $comment_by, $updated = NULL)
    {
        $this->task         = $task;
        $this->comment      = $comment;
        $this->updated      = $updated;
        

        $this->comment_by   = $comment_by->first_name . " ". $comment_by->last_name;

        if($this->updated)
        {
            $this->subject = __('form.comment_updated_on') . " " .  __('form.task') . " : " . $this->task->number ;
        }
        else
        {
            $this->subject = __('form.new_comment_on') . " " . __('form.task') ." : " . $this->task->number ;
        }


    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return (isset($notifiable->customer_id)) ? ['mail'] : ['database','mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {   
        if(!isset($notifiable->customer_id))
        {   
            // Its a Team Member
            push_notification($notifiable->id);

            $link_to_comment = route('show_task_comment',  [$this->task->id, $this->comment->id ] );
        }
        else
        {
            $link_to_comment = route('cp_show_task_comment',  [$this->task->project->id, $this->task->id, $this->comment->id ] );
        }

        if($this->updated)
        {
            $message = $this->comment_by. " " .__('form.has_updated_comment_on') . " " .  __('form.task') . " : " . $this->task->number ;

        }
        else
        {
            $message = $this->comment_by. " " .__('form.has_commented_on') . " " . __('form.task') ." : " . $this->task->number ;
        }
       


        return (new MailMessage)
                    ->subject($this->subject)
                    ->line($message )
                    ->action(__('form.view_comment'), $link_to_comment )
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
        $link_to_comment = route('show_task_comment',  [$this->task->id, $this->comment->id ] );

        return [
            'message'   => $this->subject,
            'url'       => $link_to_comment ,
        ];
    }
}
