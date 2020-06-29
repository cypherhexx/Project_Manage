<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\CustomerContact;
use App\Department;
use App\Ticket;
use App\Comment;
use App\Attachment;
use App\PotentialCustomer;
use App\NumberGenerator;
use EmailReplyParser\Parser\EmailParser;


class ImportTicketFromEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:ticket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import ticket from email accounts for each department';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $departments = Department::all();

        foreach ($departments as $department) 
        {
            if($department->enable_auto_ticket_import)
            {  
                echo "Found a new department to fetch emails ... $department->name \n";             
                $this->fetch_emails([
                            'host'          => $department->imap_host,
                            'port'          => $department->imap_port,
                            'encryption'    => $department->imap_encryption,
                            'validate_cert' => false,
                            'username'      => ($department->imap_username) ?? $department->email ,
                            'password'      => $department->imap_password,
                            // 'protocol'      => $department->imap_encryption,
                            'protocol'      => 'imap',
                        ], $department);
            }
        }
        
    }

    function fetch_emails($config, $department)
    {
        $oClient = new \Webklex\IMAP\Client($config);
       
        echo "Fetching emails...\n";
        $oClient->connect();

        // To Check connection
        // $o $oClient->checkConnection();

        $aFolder = $oClient->getFolders();

        //Loop through every Mailbox
        /** @var \Webklex\IMAP\Folder $oFolder */
        foreach($aFolder as $oFolder)
        {

            //Get all Messages of the current Mailbox $oFolder
            /** @var \Webklex\IMAP\Support\MessageCollection $aMessage */
            $aMessage = $oFolder->query()->unseen()->get();
            
            $i = 0;
            /** @var \Webklex\IMAP\Message $oMessage */
            foreach($aMessage as $oMessage)
            {
                // If it contains message
                if($oMessage->getHTMLBody(true))
                {
                    // Check if it's an existing customer contact
                    $contact        = CustomerContact::where('email', $oMessage->getFrom()[0]->mail )->get();                    

                    if(count($contact) > 0)
                    {
                        $contact_id                         = $contact->first()->id;                    
                        $emails[$i]['customer_contact_id']  = $contact_id;   
                        $user_type                          = USER_TYPE_CUSTOMER;
                    }
                    else
                    {
                        // Check if it's an existing potential customer
                        $contact = PotentialCustomer::where('email', $oMessage->getFrom()[0]->mail )->get();

                        if(count($contact) > 0)
                        {
                            $contact_id = $contact->first()->id;
                        }
                        else
                        {
                            $contact = PotentialCustomer::create(['email' => $oMessage->getFrom()[0]->mail ]);
                            $contact_id = $contact->id;
                        }

                        $user_type  = USER_TYPE_POTENTIAL_CUSTOMER;
                    }

                    $sender_name    = trim(strtr( $oMessage->getFrom()[0]->full ,['<' => '', '>' => '', '[' => '', ']' => '' , $oMessage->getFrom()[0]->mail => '']));
                   

                    $emails[$i]['contact_id']                 = $contact_id;            
                    $emails[$i]['user_type']                  = $user_type ;
                    $emails[$i]['subject']                    = $oMessage->getSubject();            
                    $emails[$i]['message']                    = trim($oMessage->getHTMLBody(true)) ;
                    $emails[$i]['message_in_plain_text']      = trim($oMessage->getTextBody()) ;
                    $emails[$i]['email']                      = $oMessage->getFrom()[0]->mail;
                    $emails[$i]['sender_name']                = $sender_name;
                    
                    $aAttachment = $oMessage->getAttachments();

                    $aAttachment->each(function ($oAttachment) use (&$emails, $i) 
                    {
                        $file_name = uniqid().'.'.$oAttachment->getExtension();

                        $emails[$i]['attachments'][] = [                    
                                                        'name'          => 'public/attachments/'.$file_name,
                                                        'display_name'  => $oAttachment->getName(),
                                                        'short_code'    => uniqid(),
                                                    ];
                        echo "Downloading attachment ... \n";                            
                        /** @var \Webklex\IMAP\Attachment $oAttachment */
                        $oAttachment->save(storage_path('app/public/attachments'), $file_name);
                        
                    });
                    
                    $i++;
                }
            }
        }

       if(!empty($emails))
       {
         echo "Processing emails...\n";
         $this->process_emails($emails, $department);
       }
    }

    function process_emails($emails, Department $department)
    {
        foreach ($emails as $email) 
        {
            $attachments = (isset($email['attachments']) && is_countable($email['attachments']) && count($email['attachments']) > 0) ? $email['attachments'] : [] ;
            /* check the subject of the email to see if it contains a ticket number. If it contains then find the ticket and submit it as 
             a reply otherwise create a new ticket.
             */
            preg_match(REGEX_PATTERN_EXTRACT_TICKET_FROM_EMAIL_SUBJECT, $email['subject'] , $matches);

           if(is_countable($matches) && count($matches) > 0 && isset($matches[1]))
           {
                echo "It is a reply to an existing ticket ... \n";  

                $ticket_number = str_replace(array( '[', ']' ), '', trim($matches[1]));

                // Extracting the main reply from body of the email(As it generally contains previous messages and other stuffs)

                // So, Look for ##- Please type your reply above this line -## in email body, and get the position of the occurance
                 preg_match(REGEX_PATTERN_EXTRACT_REPLY_MESSEGE_FROM_EMAIL_BODY, $email['message'] , $matches, PREG_OFFSET_CAPTURE);

                 // If the ooccurance is found
                 if(isset($matches[0][1]))
                 {
                    // Extract the message before the occurance
                    $message = substr($email['message'], 0, $matches[0][1]);                      

                 }
                 else
                 {
                    // Use third party library to fetch the reply part from email body      

                    $email_parser = (new EmailParser())->parse($email['message_in_plain_text']);

                    $fragment = current($email_parser->getFragments());

                    $message = $fragment->getContent();

                 }

                 /* Remove some extra garbage like Quoted Headers- Extract the message before something like following:
                        On Monday, 3 December 2018, 1:17, Whatever <user@domain.com> wrote:
                     */

                 preg_match('/(On.*?\ wrote:)/', $message, $matches, PREG_OFFSET_CAPTURE);

                 if(isset($matches[0][1]))
                 {
                    $message = substr($message, 0, $matches[0][1]);  
                 }
                                   

                // Remove all HTML tags except few, and using html_tidy function to close unfinished tags
                $message = $this->html_tidy(strip_tags($message, '<div><a><br><p>'));                               

                 
                // Finally Pass this to proper function to insert in database
                 $this->add_reply_to_existing_ticket($ticket_number, $email['email'], $email['sender_name'], $message, $attachments, 
                    $email['contact_id'], $email['user_type'] );     

                
           }
           else
           {
                echo "It is a new ticket ... \n"; 
                $this->create_ticket([
                        'subject'               => $email['subject'] , 
                        'customer_contact_id'   => (isset($email['customer_contact_id'])) ?  $email['customer_contact_id'] : NULL  , 
                        'name'                  => $email['sender_name'] , 
                        'email'                 => $email['email'] ,
                        'department_id'         => $department->id,
                        'ticket_priority_id'    => 1,
                        'ticket_service_id'     => NULL,                                                 
                        'ticket_status_id'      => TICKET_STATUS_OPEN,
                        'assigned_to'           => 1,
                        'created_by'            => $email['contact_id'] ,   
                        'user_type'             => $email['user_type'] , 
                        'message'               => strip_tags($email['message'] , '<br>'),
                        'attachments'           => $attachments,
        

                ]);
           }

        }
    }

    private function create_ticket($data)
    {
        DB::beginTransaction();
        $success = false;

        try {
            echo "Creating new ticket ... \n"; 
            $data['number']       = NumberGenerator::gen(COMPONENT_TYPE_TICKET);     

            // Saving Data        
            $ticket  = Ticket::create($data);     

            $comment            = new Comment();
            $comment->body      = $data['message'];
            $comment->user_id   = $data['created_by'];
            $comment->user_type = $data['user_type'];
            $ticket->comments()->save($comment);

            // Save the attachments (If exists)       
       
            if(isset($data['attachments']) && is_array($data['attachments']) && count($data['attachments']) > 0)
            {
                $attachment = new Attachment();
                $attachment->add_from_console($data['attachments'], $comment, $data['user_type'], $data['created_by'] );       
            
            }


            DB::commit();
            $success = true;
            echo "Created...\n"; 

            echo "Sending notification ...\n"; 
             // Send Notification to all Members of the department
            $ticket->notify_new_ticket_created_by_customer();

            

        } catch (\Exception  $e) {
            $success = false;

            DB::rollback();
            echo "Found Error..." . $e->getMessage() . " " . $e->getLine() . " ". $e->getFile() ; 
        }
    }

    private function add_reply_to_existing_ticket($ticket_number, $email, $sender_name, $message, $attachments, $user_id, $user_type)
    {
        echo "Adding Reply to an existing ticket...\n";

        DB::beginTransaction();
        $success = false;

        try {

            $ticket = Ticket::where('number', $ticket_number)->where('email', $email)->get();            
     
            if(count($ticket) > 0)
            {
                $ticket = $ticket->first();
                $ticket->ticket_status_id    = TICKET_STATUS_OPEN;
                $ticket->save();
                
                $comment            = new Comment();
                $comment->body      = $message;
                $comment->user_id   = $user_id;
                $comment->user_type = $user_type;
                $ticket->comments()->save($comment);

                // Save the attachments (If exists)       
           
                if(!empty($attachments) && is_array($attachments) && count($attachments) > 0)
                {
                    $attachment = new Attachment();
                    $attachment->add_from_console($attachments, $comment, $user_type, $user_id );       
                
                }

                DB::commit();
                $success = true;
                echo "Reply Added ...\n"; 


                echo "Sending notification ...\n"; 
                // Send notification to Responsible Team Members
                $ticket->notify_reply_from_customer($comment); 

            }           

        } catch (\Exception  $e) {
            $success = false;

            DB::rollback();
           echo "Found Error..." . $e->getMessage() . " " . $e->getLine() . " ". $e->getFile() ; 
        }
    }


    private function html_tidy($src)
    {
        // Remove Style Attribute
        $src = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $src);  
        // Remove Class and ID attribute
        $src = preg_replace('#\s(id|class)="[^"]+"#', '', $src); 

        libxml_use_internal_errors(true);
        $x = new \DOMDocument;
        $x->loadHTML('<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />'.$src);
        $x->formatOutput = true;
        $ret = preg_replace('~<(?:!DOCTYPE|/?(?:html|body|head))[^>]*>\s*~i', '', $x->saveHTML());
        $html = trim(str_replace('<meta http-equiv="Content-Type" content="text/html;charset=utf-8">','',$ret));

        // Remove Unused tags
        $message = preg_replace('/<([^>\s]+)[^>]*>(?:\s*(?:<br \/>|&nbsp;|&thinsp;|&ensp;|&emsp;|&#8201;|&#8194;|&#8195;)\s*)*<\/\1>/', '', $html);

        // Remove empty spaces between html tags
        return preg_replace('/(\>)\s*(\<)/m', '$1$2', $html);

    }
}
