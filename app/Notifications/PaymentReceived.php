<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PaymentReceived extends Notification implements ShouldQueue
{
    use Queueable;

    private $invoice_id;
    private $invoice_number;
    private $amount;
    private $payment_id;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($invoice_id, $invoice_number, $amount, $payment_id)
    {
        //
        $this->invoice_id       = $invoice_id;
        $this->invoice_number   = $invoice_number;
        $this->amount           = $amount;
        $this->payment_id       = $payment_id;
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
        
          
        $message        = sprintf(__('form.payment_received_for_invoice'), $this->amount , $this->invoice_number );       

        $mail = (new MailMessage)->line($message);

        if(!isset($notifiable->customer_id))
        {
           $mail->action(__('form.view_receipt'), route('show_payment_page', $this->payment_id )); 
        }
                 
        return $mail;            
                    
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
       $message        = sprintf(__('form.payment_received_for_invoice'), $this->amount , $this->invoice_number );

       return [
            //
            'message'   => $message,
            'url'       => route('show_payment_page', $this->payment_id ) ,
        ];
    }
}
