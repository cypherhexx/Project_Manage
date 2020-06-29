<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Mail\NewTicketCreated;
use App\Invoice;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       DB::table('settings')->truncate();

        // Settings
        $settings = [
            'company_name'                      => 'Microelephant',
            'company_phone'                     => '',
            'company_address'                   => '',
            'company_city'                      => 'New York',
            'company_state'                     => 'New York',
            'company_zip_code'                  => 2323,
            'company_country'                   => 'USA',
          
            'decimal_symbol'                    => '.',
            'number_of_digits_after_decimal'    => '2',
            'digit_grouping_symbol'             => ',',
            'digit_grouping_method'             => '2',
            'round_to'                          => '1',
            'date_format'                       => 'd/m/Y',
            'system_starting_year'              => date("Y"),
            'time_zone'                         => 'America/New_York',
            'default_language'                  => 'en',
            'is_price_round_enabled'            => TRUE,
            // 'payment_gateways'                  => json_encode([
            //     'stripe'        => [
            //         'id'                => 2,
            //         'name'              => 'Stripe',
            //         'is_active'         => TRUE,

            //     ],
            //     'paypal'        => [
            //         'id'                => 3,
            //         'name'              => 'Paypal',
            //         'is_active'         => FALSE,

            //     ]
            // ]),
            
            'email_predefined_header'   => $this->email_template_header(),
            'email_predefined_footer'   => $this->email_template_footer(),


        ];

        foreach ($settings as $key=>$value)
        {

            DB::table('settings')->insert(['option_key' => $key, 'option_value' => $value]);
        }

        $templates = [          
            'new_ticket_opened_sent_to_customer'            => $this->new_ticket_opened_sent_to_customer(),
            'new_ticket_opened_sent_to_non_customer'        => $this->new_ticket_opened_sent_to_non_customer(), 
            'ticket_reply_from_team_sent_to_customer'       => $this->ticket_reply_from_team_sent_to_customer(), 
            'ticket_reply_from_team_sent_to_non_customer'   => $this->ticket_reply_from_team_sent_to_non_customer(),
            'email_template_invoice_sent_to_customer'       => $this->email_template_invoice_sent_to_customer(),
            'email_template_estimate_sent_to_customer'      => $this->email_template_estimate_sent_to_customer(),
            'email_template_proposal_sent_to_customer'      => $this->email_template_proposal_sent_to_customer(),
            

        ];

        foreach ($templates as $key=>$value)
        {

            DB::table('settings')->insert(['option_key' => $key, 'option_value' => $value, 'auto_load_disabled' => TRUE ]);
        }
    }


    function email_template_estimate_sent_to_customer()
    {
        $template = 'Dear {contact_first_name} {contact_last_name}

Thank you for your estimate request.

You can view the estimate on the following link: {estimate_number}

Please contact us for more information.

Kind Regards,
{email_signature}';

return json_encode(['subject' => 'Estimate - {estimate_number}' , 'template' => $template]);

    }

   function email_template_proposal_sent_to_customer()
    {
        $template = 'Dear {contact_name},
        
Please find our attached proposal.

This proposal is valid until: {proposal_open_till} . You can also view the proposal on the following link: 

{proposal_link}

We look forward to your feedback.

Kind Regards
{email_signature}';

return json_encode(['subject' => 'Proposal - {proposal_title}' , 'template' => $template]);
    } 


    function email_template_invoice_sent_to_customer()
    {
        $template = 'Hi {contact_first_name} {contact_last_name},

We are contacting you in regard to a new invoice {invoice_number}. that has been created on your account. You may find the invoice attached.

We look forward to conducting future business with you.

Kind Regards
{company_name}';

return json_encode(['subject' => 'Invoice - {invoice_number}' , 'template' => $template]);
    }

    
    function new_ticket_opened_sent_to_non_customer()
    {
        $template = 'Hi {customer_name},

Thank you for contacting our support team. A support ticket has been opened for your request. You will be notified when a response is made, by email.

Ticket Number   : {ticket_number}
Department      : {department_name}
Priority        : {ticket_priority}
Message         : {ticket_message}

Thanks 
Support Team';

        return json_encode(['subject' => 'New Support Ticket Opened' , 'template' => $template]);
    }

    function new_ticket_opened_sent_to_customer()
    {
        $template = 'Hi {customer_name},

A new ticket has been issued as per your support request.

Ticket Number   : {ticket_number}
Department      : {department_name}
Priority        : {ticket_priority}
Ticket Link     : {ticket_link}
Message         : {ticket_message}

Thanks 
Support Team';

        return json_encode(['subject' => 'New Support Ticket Opened' , 'template' => $template]);
    }



function ticket_reply_from_team_sent_to_customer()
    {
        $template = 'Hi {customer_name},

You have a new reply to ticket {ticket_link}

Ticket Number   : {ticket_number}
Department      : {department_name}
Priority        : {ticket_priority}
Subject         : {ticket_subject}
Ticket Link     : {ticket_link}
Message         : {ticket_message}

Thanks 
Support Team';

        return json_encode(['subject' => 'New Ticket Reply' , 'template' => $template]);
    }

function ticket_reply_from_team_sent_to_non_customer()
    {
        $template = 'Hi {customer_name},

You have a new reply to ticket {ticket_link}

Ticket Number   : {ticket_number}
Department      : {department_name}
Priority        : {ticket_priority}
Subject         : {ticket_subject}
Message         : {ticket_message}

Thanks 
Support Team';

        return json_encode(['subject' => 'New Ticket Reply' , 'template' => $template]);
    }


function email_template_header()
    {
        return '<!doctype html>
<html>
   <head>
      <meta name="viewport" content="width=device-width" />
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
      <style>
         body {
         background-color: #f6f6f6;
         font-family: sans-serif;
         -webkit-font-smoothing: antialiased;
         font-size: 14px;
         line-height: 1.4;
         margin: 0;
         padding: 0;
         -ms-text-size-adjust: 100%;
         -webkit-text-size-adjust: 100%;
         }
         table {
         border-collapse: separate;
         mso-table-lspace: 0pt;
         mso-table-rspace: 0pt;
         width: 100%;
         }
         table td {
         font-family: sans-serif;
         font-size: 14px;
         vertical-align: top;
         }
         /* -------------------------------------
         BODY & CONTAINER
         ------------------------------------- */
         .body {
         background-color: #f6f6f6;
         width: 100%;
         }
         /* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
         .container {
         display: block;
         margin: 0 auto !important;
         /* makes it centered */
         max-width: 680px;
         padding: 10px;
         width: 680px;
         }
         /* This should also be a block element, so that it will fill 100% of the .container */
         .content {
         box-sizing: border-box;
         display: block;
         margin: 0 auto;
         max-width: 680px;
         padding: 10px;
         }
         /* -------------------------------------
         HEADER, FOOTER, MAIN
         ------------------------------------- */
         .main {
         background: #fff;
         border-radius: 3px;
         width: 100%;
         }
         .wrapper {
         box-sizing: border-box;
         padding: 20px;
         }
         .footer {
         clear: both;
         padding-top: 10px;
         text-align: center;
         width: 100%;
         }
         .footer td,
         .footer p,
         .footer span,
         .footer a {
         color: #999999;
         font-size: 12px;
         text-align: center;
         }
         hr {
         border: 0;
         border-bottom: 1px solid #f6f6f6;
         margin: 20px 0;
         }
         /* -------------------------------------
         RESPONSIVE AND MOBILE FRIENDLY STYLES
         ------------------------------------- */
         @media only screen and (max-width: 620px) {
         table[class=body] .content {
         padding: 0 !important;
         }
         table[class=body] .container {
         padding: 0 !important;
         width: 100% !important;
         }
         table[class=body] .main {
         border-left-width: 0 !important;
         border-radius: 0 !important;
         border-right-width: 0 !important;
         }
         }
      </style>
   </head>
   <body class="">
      <table border="0" cellpadding="0" cellspacing="0" class="body">
      <tr>
         <td> </td>
         <td class="container">
            <div class="content">
            <!-- START CENTERED WHITE CONTAINER -->
            <table class="main">
            <!-- START MAIN CONTENT AREA -->
      <tr>
         <td class="wrapper">
            <table border="0" cellpadding="0" cellspacing="0">
      <tr>
         <td>';
    }
   

   function email_template_footer()
   {
     return '</td>
</tr>
</table>
</td>
</tr>
<!-- END MAIN CONTENT AREA -->
</table>
<!-- START FOOTER -->
<div class="footer">
   <table border="0" cellpadding="0" cellspacing="0">
      <tr>
         <td class="content-block">
            <span>@[company_name]</span>
         </td>
      </tr>
      <tr>
         <td class="content-block">
            <span>@[company_logo]</span>
         </td>
      </tr>
   </table>
</div>
<!-- END FOOTER -->
<!-- END CENTERED WHITE CONTAINER -->
</div>
</td>
<td> </td>
</tr>
</table>
</body>
</html>';
   }
}
