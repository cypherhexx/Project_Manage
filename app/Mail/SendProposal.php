<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendProposal extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $rec;
    private $proposal;
    private $pdf_file_path;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data_to_pass_in_view, $proposal, $pdf_file_path = NULL)
    {
        //
        $this->rec                          = $data_to_pass_in_view;
        $this->proposal                     = $proposal;
        $this->pdf_file_path                = $pdf_file_path;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject    = __('form.proposal') . " : ". $this->proposal->title ;
        $from_name  = config('constants.company_name');
        $from_email = config('constants.company_email');

    
        if($email = get_setting('email_template_proposal_sent_to_customer'))
        {
            $subject        = (isset($email->subject)) ? : $subject ;

            if((isset($email->subject)))
            {
                $subject        = short_code_parser($email->subject, [
                    'proposal_title' => $this->proposal->title, 'proposal_number' =>  $this->proposal->number
                ]); 
            }
            

            $from_name      = (isset($email->from_name)) ? $email->from_name : $from_name ;
            $from_email     = (isset($email->from_email_address)) ? $email->from_email_address : $from_email ;
        }       

        $template = $this->subject($subject)->from($from_email, $from_name)->view('emails.proposal')->with('rec', $this->rec);       

        if($this->pdf_file_path)
        {            
            $template->attach($this->pdf_file_path, [
                    'as'    => $this->proposal->number.'.pdf',
                    'mime'  => 'application/pdf',
            ]);

            $this->withSwiftMessage(function ($message) {
                    $message->pdf_file_path = $this->pdf_file_path;
            });

        }

        return $template;
        
    }
}
