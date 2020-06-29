<?php

namespace App\Console\Commands;
use Illuminate\Support\Facades\Mail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update status of Invoice, Estimate, Proposals others';

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
        DB::table('invoices')
            ->where('due_date', '<', date("Y-m-d"))
            ->whereIn('status_id', [INVOICE_STATUS_UNPAID, INVOICE_STATUS_PARTIALLY_PAID ])
            ->update(['status_id' => INVOICE_STATUS_OVER_DUE ]);


        DB::table('estimates')
            ->where('expiry_date', '<', date("Y-m-d"))
            ->where('status_id', ESTIMATE_STATUS_SENT)
            ->update(['status_id' => ESTIMATE_STATUS_EXPIRED ]);  

        DB::table('proposals')
            ->where('open_till', '<', date("Y-m-d"))
            ->where('status_id', '<>',PROPOSAL_STATUS_EXPIRED)
            ->update(['status_id' => PROPOSAL_STATUS_EXPIRED ]);  
    }
}
