<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Setting;

class NewTicketCreated extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $ticket;
    private $ticket_message;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($ticket, $ticket_message)
    {
        $this->ticket           = $ticket;
        $this->ticket_message   = $ticket_message;

    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if(isset($this->ticket->customer_contact_id) && $this->ticket->customer_contact_id)
        {
            // It's a customer
            $email = Setting::get_email_template_by_name('new_ticket_opened_sent_to_customer');          
      
            $replacement = [
                'customer_name'     => $this->ticket->name,
                'department_name'   => $this->ticket->department->name,
                'ticket_number'     => $this->ticket->number,
                'ticket_priority'   => $this->ticket->priority->name,
                'ticket_link'       => anchor_link($this->ticket->number, route('cp_show_ticket_page', $this->ticket->id )),
                'ticket_message'    => $this->ticket_message,
                'ticket_subject'    => $this->ticket->subject
            ];
            

            
        }
        else
        {
            // This is a Potential Customer - Not registered as a customer in the system
            $email = Setting::get_email_template_by_name('new_ticket_opened_sent_to_non_customer');

            $replacement = [
                'customer_name'     => $this->ticket->name,
                'department_name'   => $this->ticket->department->name,
                'ticket_number'     => $this->ticket->number,
                'ticket_priority'   => $this->ticket->priority->name,          
                'ticket_message'    => $this->ticket_message,
                'ticket_subject'    => $this->ticket->subject
            ];
            
        }

        $rec['email_template'] = short_code_parser($email->template, $replacement);

        $mail = $this->subject($email->subject)->view('emails.generic')->with('rec', $rec);

        if(isset($email->from_name) && $email->from_name && isset($email->from_email_address) && $email->from_email_address )
        {
            $mail->from($email->from_email_address, $email->from_name);
        }

        return $mail;       
        
    }

    
   
}
