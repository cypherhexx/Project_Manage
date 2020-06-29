<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Invoice;
use Carbon\Carbon;
use App\NumberGenerator;

class GenerateRecurringInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gen:recurring_invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates new invoices from exsting invoices that are marked as recurring invoice';

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
        $invoices =  Invoice::where('recurring_invoice_type', '<>', '0')->whereNotNull('recurring_invoice_type')->with(['item_line'])->get();

            if(count($invoices) > 0)
            {
                echo "Found invoices ... \n";        
                foreach ($invoices as $invoice) 
                {       
                    // If the period is not infinity check how many cycles it has finished
                    if($invoice->is_recurring_invoice_period_infinity != TRUE)
                    {
                        echo "Recurring period is not infinity \n";
                        if($invoice->recurring_invoice_custom_num_of_times_ran >= $invoice->recurring_invoice_total_cycle)
                        {
                            // it has already reached it quota so no need to send any more
                            echo "Reached its quota, skipping the rest of the loop \n";
                            continue;
                        }
                    }

                    if($invoice->date_of_last_recurring_invoice_generated)
                    {
                        $date       = date("Y-m-d", strtotime($invoice->date_of_last_recurring_invoice_generated));
                    }
                    else
                    {
                        $date       =  $invoice->date ;
                    }                   
                    

                    $date       = Carbon::createFromFormat('Y-m-d', $date);
                    echo "Getting the date when it is supposed to generate the invoice \n";
                    $date_of_when_it_is_supposed_to_be_generated = $this->get_the_date_of_when_it_is_supposed_to_be_generated($invoice, $date);

                    $now = Carbon::now();

                    if($now->greaterThanOrEqualTo($date_of_when_it_is_supposed_to_be_generated))
                    {
                        // Create the invoice
                        $this->create_invoice($invoice);
                        echo "Invoice created";
                    }
                    else
                    {
                        echo "This is not the time to create the invoice \n";
                    }              
                }
            }

            /*  if recurring_invoice_type is between 1-12
                    check if date_of_last_recurring_invoice_generated is empty, if empty then  compare date_of_last_recurring_invoice_generated with invoice date otherwise with date_of_last_recurring_invoice_generated              

                check if is_recurring_invoice_period_infinity = TRUE
                    if true then create the
                    if not compare the recurring_invoice_total_cycle and recurring_invoice_custom_num_of_times_ran
             */   
            

            /*
                if recurring_invoice_type == custom
                    calclate the number of days/date after the invoice has to be generated from invoice date
                 check if is_recurring_invoice_period_infinity = TRUE
                    if true then create the
                    if not compare the recurring_invoice_total_cycle and recurring_invoice_custom_num_of_times_ran

            */
    }


    private function create_invoice($invoice)
    {
        $new_invoice            = $invoice->replicate();
        $new_invoice->url_slug  = md5(microtime());
        $new_invoice->number    = NumberGenerator::gen(COMPONENT_TYPE_INVOICE);
        $new_invoice->date      = date("Y-m-d");
        $new_invoice->due_date  = NULL;

        unset($new_invoice->recurring_invoice_type);
        unset($new_invoice->recurring_invoice_total_cycle);
        unset($new_invoice->recurring_invoice_custom_parameter);
        unset($new_invoice->recurring_invoice_custom_type);
        unset($new_invoice->recurring_invoice_custom_num_of_times_ran);
        unset($new_invoice->is_recurring_invoice_period_infinity);
        unset($new_invoice->date_of_last_recurring_invoice_generated);        
        unset($new_invoice->created_at);
        unset($new_invoice->updated_at);
        unset($new_invoice->deleted_at);
        unset($new_invoice->amount_paid);
        unset($new_invoice->applied_credits);
        
        $new_invoice->save();

        $invoice->children()->attach([$new_invoice->id]);

        $relations = $invoice->getRelations();
 
        if(is_countable($relations) && count($relations) > 0)
        {
            foreach ($relations as $relation => $entries)
            {
                foreach($entries as $entry)
                {

                    $e = $entry->replicate();
                    
                    if ($e->push())
                    {
                        $new_invoice->{$relation}()->save($e);
                    }

                }
            }
        }

     
        $invoice->recurring_invoice_custom_num_of_times_ran = $invoice->recurring_invoice_custom_num_of_times_ran + 1;
        $invoice->date_of_last_recurring_invoice_generated = date("Y-m-d");
        $invoice->save();

    }


    private function get_the_date_of_when_it_is_supposed_to_be_generated($invoice, $dt)
    {
        if($invoice->recurring_invoice_type == 'custom')
        {
          
                switch ($invoice->recurring_invoice_custom_type) 
                {
                    case 'days':
                            $date_it_is_supposed_to_be_generated = $dt->addDays($invoice->recurring_invoice_custom_parameter);
                        break;
                    case 'weeks':
                            $date_it_is_supposed_to_be_generated = $dt->addWeeks($invoice->recurring_invoice_custom_parameter);
                        break;
                    case 'months':
                            $date_it_is_supposed_to_be_generated = $dt->addMonths($invoice->recurring_invoice_custom_parameter);
                        break;                                
                    default:
                        $date_it_is_supposed_to_be_generated = $dt->addYears($invoice->recurring_invoice_custom_parameter);
                        break;
                }
        }
        else
        {
            // recurring_invoice_type is between 1-12 (month)
            $date_it_is_supposed_to_be_generated = $dt->addMonths($invoice->recurring_invoice_type);                        

        }

        return $date_it_is_supposed_to_be_generated;
    }
}
