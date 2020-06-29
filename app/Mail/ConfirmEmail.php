<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ConfirmEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    private $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $rec['name']    = $this->user->contact_first_name . " ". $this->user->contact_last_name;
        $rec['url']     = route('verify_email', $this->user->verification_token );
        $subject = config('constants.company_name') . " - Confirm your email address" ;
        return $this->subject($subject)->view('emails.verify_email', compact('rec'));
    }
}
