<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEstimate extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $rec;
    private $estimate;
    private $pdf_file_path;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data_to_pass_in_view, $estimate, $pdf_file_path = NULL)
    {
        //
        $this->rec                  = $data_to_pass_in_view;
        $this->estimate             = $estimate;
        $this->pdf_file_path        = $pdf_file_path;
    
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject    = __('form.estimate') . " : ". $this->estimate->number ;
        $from_name  = config('constants.company_name');
        $from_email = config('constants.company_email');

    
        if($email = get_setting('email_template_estimate_sent_to_customer'))
        {
            $subject        = (isset($email->subject)) ? short_code_parser($email->subject, ['estimate_number' => $this->estimate->number ]) : $subject ;
            $from_name      = (isset($email->from_name)) ? $email->from_name : $from_name ;
            $from_email     = (isset($email->from_email_address)) ? $email->from_email_address : $from_email ;
        }       
         

        $template = $this->subject($subject)->from($from_email, $from_name)->view('emails.estimate')->with('rec', $this->rec);        
        
        if($this->pdf_file_path)
        {
               $template->attach($this->pdf_file_path, [
                    'as'    => $this->estimate->number.'.pdf',
                    'mime'  => 'application/pdf',
                ]);
               
               $this->withSwiftMessage(function ($message) {
                    $message->pdf_file_path = $this->pdf_file_path;
                });

        }

        return $template;

    }
}

