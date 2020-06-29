<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Setting;

class TicketReply extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $ticket;
    private $comment;
    private $ticket_message;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($ticket, $comment)
    {
        $this->ticket           = $ticket;
        $this->comment          = $comment;
        $this->ticket_message   = $comment->body;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    { 
        if($this->ticket->department->enable_auto_ticket_import && $this->ticket->department->email)
        {
            // This will appear as a discussion thead on Email
            $mail = $this->from($this->ticket->department->email, config()->get('constants.company_name') . ' - ' .$this->ticket->department->name)      
            ->replyTo($this->ticket->department->email, $this->ticket->department->name)
            ->subject('['. $this->ticket->number .'] Re: '. $this->ticket->subject)
            ->view('emails.ticket_comments')->with('ticket', $this->ticket);

            // Attach files if exist
            $attachments = $this->comment->attachments()->get();

            if(count($attachments) > 0)
            {
                foreach ($attachments as $attachment) 
                {   
                    $file = storage_path('app/'. $attachment->name);    
                    if(file_exists($file))
                    {
                        $mail->attach($file);
                    }
                    
                }
            }

            return $mail;
        }
        else
        {
            $subject = '['. $this->ticket->number .'] Re: '. $this->ticket->subject;

            $mail = $this->subject($subject);


            $replacement = [
                'customer_name'     => $this->ticket->name,
                'department_name'   => $this->ticket->department->name,
                'ticket_number'     => $this->ticket->number,
                'ticket_priority'   => $this->ticket->priority->name,                
                'ticket_message'    => $this->ticket_message,
                'ticket_subject'    => $subject
            ]; 

            if(isset($this->ticket->customer_contact_id) && $this->ticket->customer_contact_id)
            {
                // It's a customer
                $email = Setting::get_email_template_by_name('ticket_reply_from_team_sent_to_customer');  

                $replacement['ticket_link'] = anchor_link($this->ticket->number, route('cp_show_ticket_page', $this->ticket->id )) ;

            }
            else
            {
                // It's not a customer
                $email = Setting::get_email_template_by_name('ticket_reply_from_team_sent_to_non_customer');  
                
                // Attach files if exist
                $attachments = $this->comment->attachments()->get();

                if(count($attachments) > 0)
                {
                    foreach ($attachments as $attachment) 
                    {   
                        $file = storage_path('app/'. $attachment->name);    
                        if(file_exists($file))
                        {
                            $mail->attach($file);
                        }
                        
                    }
                }
            }
            
            $rec['email_template'] = short_code_parser($email->template, $replacement);


            

            if(isset($email->from_name) && $email->from_name && isset($email->from_email_address) && $email->from_email_address )
            {
                $mail->from($email->from_email_address, $email->from_name);
            }

            $mail->view('emails.generic')->with('rec', $rec);

            return $mail;

            
        }


        
    }
}
