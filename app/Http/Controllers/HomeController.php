<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use App\Lead;
use App\Project;
use App\Task;
use App\Invoice;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       
       return view('home');
    }

    function stats()
    {        
        
        $data = [
            'invoices_awaiting_payment' => Invoice::home_page_stat(),
            'converted_leads'           => Lead::home_page_stat() ,
            'projects_in_progress'      => Project::home_page_stat(),
            'tasks_not_finished'        => Task::home_page_stat(),

        ];
        return response()->json($data);
    }

    function global_search()
    {
        $result = [];
        $searh_key = Input::get('query');

        
        if($searh_key)
        {
            $vendor_contacts    = 'Vendors Contacts';
            $vendors            = __('form.vendors');
            $customers          = __('form.customers');
            $customer_Contact   = __('form.customer_Contact');
            $lead               = __('form.lead');
            $invoice            = __('form.invoice');
            $estimate           = __('form.estimate');
            $proposal           = __('form.proposal');
            $project            = __('form.project');
            $task               = __('form.task');


            if($searh_key[0] == '#')
            {

                // Search for Tags
                $searh_key = str_replace('#', "", $searh_key);

                $tag_ids = DB::select("SELECT id FROM tags WHERE name like  ?", [ $searh_key . '%' ]);

                if(count($tag_ids) > 0)
                {
                    // Convert to Array from Object
                    $tag_ids = array_column(json_decode(json_encode($tag_ids), TRUE), 'id')  ;
                    // Convert to comma separated value
                    $tag_ids = implode(",", $tag_ids);

                    if(check_perm('invoices_view')) 
                    {
                        $sql['invoices'] = "SELECT invoices.id AS id, number AS name, 'invoice' AS type, 'show_invoice_page' AS route_name 
                        FROM taggables INNER JOIN invoices ON taggables.taggable_id = invoices.id 
                        WHERE tag_id IN (?) AND taggable_type = 'App\\\\Invoice' ORDER BY invoices.id ASC ";
                    }    
                    
                    if(check_perm('estimates_view')) 
                    {
                        $sql['estimates'] = "SELECT estimates.id AS id, number AS name, 'estimate' AS type, 'show_estimate_page' AS route_name 
                        FROM taggables INNER JOIN estimates ON taggables.taggable_id = estimates.id 
                        WHERE tag_id IN (?) AND taggable_type = 'App\\\\Estimate' ";
                    }

                    if(check_perm('proposals_view')) 
                    {
                        $sql['proposals'] = "SELECT proposals.id AS id, number AS name, 'proposal' AS type, 'show_proposal_page' AS route_name 
                        FROM taggables INNER JOIN proposals ON taggables.taggable_id = proposals.id 
                        WHERE tag_id IN (?) AND taggable_type = 'App\\\\Proposal' ";
                    }

                    if(check_perm('projects_view')) 
                    {
                        $sql['projects'] = "SELECT projects.id AS id, name, 'project' AS type, 'show_project_page' AS route_name 
                        FROM taggables INNER JOIN projects ON taggables.taggable_id = projects.id 
                        WHERE tag_id IN (?) AND taggable_type = 'App\\\\Project' ";
                    }

                    if(check_perm('leads_view')) 
                    {
                        $sql['leads'] = "SELECT leads.id AS id, CONCAT_WS (' ', first_name, last_name) AS name, 'lead' AS type, 'show_lead_page' AS route_name 
                        FROM taggables INNER JOIN leads ON taggables.taggable_id = leads.id 
                        WHERE tag_id IN (?) AND taggable_type = 'App\\\\Lead' ";
                    }

                    if(check_perm('tasks_view')) 
                    {
                        $sql['tasks'] = "SELECT tasks.id AS id, title AS name, 'task' AS type, 'show_task_page' AS route_name 
                        FROM taggables INNER JOIN tasks ON taggables.taggable_id = tasks.id 
                        WHERE tag_id IN (?) AND taggable_type = 'App\\\\Task' ";
                    }
               
                    if(isset($sql) && !empty($sql))
                    {
                        $query = $this->run_query($sql, $tag_ids);         
                    }
                    else
                    {
                        $query = NULL;
                    }
                   


                }
                else
                {
                    $query = NULL;
                }   
               

                
            }
            else
            {

                 // Vendors
                if(check_perm('vendors_view')) 
                { 
                    $sql['vendors']  = "SELECT id, name, '$vendors' AS type, 'view_vendor_page' AS route_name , '' AS extra_column
                    FROM vendors       
                    WHERE (vendors.name like ? OR vendors.number like ?  ) AND  vendors.deleted_at IS NULL "; 
                }

                if(check_perm('vendors_view')) 
                {  
                // Vendor Contacts
                    $sql['vendor_contacts']  = "SELECT id, CONCAT_WS (' ', contact_first_name, contact_last_name) AS name, '$vendor_contacts' AS type, 'view_vendor_page' AS route_name , 'name' AS extra_column
                    FROM vendors       
                    WHERE (vendors.contact_first_name like ? OR   vendors.contact_last_name like ?        
                    OR vendors.contact_email like ? OR vendors.contact_phone like ? OR vendors.name like ?) AND  vendors.deleted_at IS NULL "; 
                }

                if(check_perm('customers_view')) 
                {  
                    // Customers
                    $sql['customers']  = "SELECT id, name, '$customers' AS type, 'view_customer_page' AS route_name , '' AS extra_column
                    FROM customers       
                    WHERE (customers.name like ? OR customers.number like ? OR customers.phone like ? ) AND  customers.deleted_at IS NULL ";      
                }


                if(check_perm('customers_view')) 
                {  
                    // Customer Contacts
                    $sql['customer_contacts']  = "SELECT customer_contacts.id, CONCAT_WS (' ', first_name, last_name) AS name, '$customer_Contact' AS type 
                    , 'show_customer_contact' AS route_name , CONCAT_WS('-', customers.id, customers.name) AS extra_column
                    FROM customer_contacts
                    LEFT JOIN customers ON customer_contacts.customer_id = customers.id       
                    WHERE (customer_contacts.first_name like ? OR  customer_contacts.last_name like ? 
                    OR customer_contacts.email like ?
                    OR customer_contacts.phone like ?
                    OR customers.name like ?
                    OR customers.number like ? ) AND  customer_contacts.deleted_at IS NULL ";
                }
               

                if(check_perm('leads_view')) 
                {  
                    // Leads
                    $sql['leads']  = "SELECT id, CONCAT_WS (' ', first_name, last_name) AS name, '$lead' AS type , 'show_lead_page' AS route_name , '' AS extra_column FROM leads       
                    WHERE ((leads.first_name like ?) OR leads.last_name like ? ) AND deleted_at IS NULL ";
       
                }

                if(check_perm('invoices_view')) 
                {  
                    // Invoices
                    $sql['invoices']  = "SELECT invoices.id AS id, invoices.number AS name, '$invoice' AS type , 'show_invoice_page' AS route_name
                    , '' AS extra_column FROM invoices
                    LEFT JOIN customers ON invoices.customer_id = customers.id
                    WHERE (invoices.number like ? OR customers.name like ?) AND (invoices.deleted_at IS NULL AND customers.deleted_at IS NULL ) GROUP BY invoices.id ORDER BY invoices.id ASC  ";
                
                }

               if(check_perm('estimates_view')) 
                {  
                    $sql['estimates']  = "SELECT estimates.id AS id, estimates.number AS name, '$estimate' AS type , 'show_estimate_page' AS route_name 
                    , '' AS extra_column FROM estimates
                    LEFT JOIN customers ON estimates.customer_id = customers.id
                    WHERE (estimates.number like ? OR customers.name like ?) AND (estimates.deleted_at IS NULL AND customers.deleted_at IS NULL ) 
                     GROUP BY estimates.id ORDER BY estimates.id ASC"
                   ;
                
               }

                if(check_perm('proposals_view')) 
                {  
                    $sql['proposals']  = "SELECT * FROM (
                        SELECT proposals.id AS id, proposals.number AS name, '$proposal' AS type  , 'show_proposal_page' AS route_name 
                        , '' AS extra_column FROM proposals
                        LEFT JOIN customers ON proposals.component_number = customers.id AND component_id = '".COMPONENT_TYPE_CUSTOMER."'
                        WHERE (proposals.number like ? OR customers.name like ?) AND (proposals.deleted_at IS NULL AND customers.deleted_at IS NULL ) 

                        UNION ALL

                        SELECT proposals.id AS id, proposals.number AS name, '$proposal' AS type , 'show_proposal_page' AS route_name 
                        , '' AS extra_column FROM proposals
                        LEFT JOIN leads ON proposals.component_number = leads.id AND component_id = '".COMPONENT_TYPE_LEAD."'
                        WHERE (proposals.number like ? OR leads.first_name like ? OR leads.last_name like ?) AND (proposals.deleted_at IS NULL AND leads.deleted_at IS NULL ) 
                    ) p GROUP BY id
                
                    ";
                }
            
               
                if(check_perm('projects_view')) 
                {  
                    // Projects
                    $sql['projects']  = "SELECT projects.id AS id, projects.name AS name, '$project' AS type  , 'show_project_page' AS route_name 
                    , '' AS extra_column FROM projects
                    LEFT JOIN customers ON projects.customer_id = customers.id
                    WHERE (projects.name like ? OR customers.name like ?) AND (projects.deleted_at IS NULL AND customers.deleted_at IS NULL ) 
                    GROUP BY projects.id ORDER BY projects.id ASC ";
             
                }

                
                if(check_perm('tasks_view')) 
                {  
                    // Tasks
                    $sql['tasks']  = "SELECT * FROM (
                        SELECT tasks.id AS id, tasks.title AS name, '$task' AS type , 'show_task_page' AS route_name 
                        , '' AS extra_column FROM tasks
                        LEFT JOIN customers ON tasks.component_number = customers.id AND component_id = '".COMPONENT_TYPE_CUSTOMER."'
                        WHERE (tasks.title like ? OR customers.name like ?) AND (tasks.deleted_at IS NULL AND customers.deleted_at IS NULL ) 

                        UNION ALL

                        SELECT tasks.id AS id, tasks.title AS name, '$task' AS type , 'show_task_page' AS route_name  
                        , '' AS extra_column FROM tasks
                        LEFT JOIN leads ON tasks.component_number = leads.id AND component_id = '".COMPONENT_TYPE_LEAD."'
                        WHERE (tasks.title like ? OR leads.first_name like ? OR leads.last_name like ?) AND (tasks.deleted_at IS NULL AND leads.deleted_at IS NULL ) 

                    ) t GROUP BY id
                
                    ";
                }

                if(isset($sql) && !empty($sql))
                {
                    $query = $this->run_query($sql, $searh_key . '%');     
                }
                else
                {
                    $query = NULL;     
                }
                           
                
                
            }

            if(!empty($query))
            {
                foreach ($query as $row) 
                {
                    if($row->type == $customer_Contact)
                    {
                        $customer   = explode("-", $row->extra_column);
                        $link       = route($row->route_name, $row->id ); // $customer[1] = customer id
                        $type       = ucfirst($row->type) . '<span class="text-success"> '. $customer[1] .'</span>';// $customer[1] = customer name
                    }
                    else
                    {
                        $link       = route($row->route_name, $row->id);
                        $type       = ucfirst($row->type);
                       
                    }

                     $result[] = [
                            'link'          => $link ,
                            'label'         => $row->name,
                            'type'          => '<span class="text-primary"> '. $type .'</span>'
                        ];
                }
            }

        }


        
        return response()->json($result);
    }


    function run_query($sql, $searh_key)
    {
        $sql_query = "";
        $lastkey = count($sql) - 1;
        $i = 0;
        foreach ($sql as $key => $q) 
        {
            $sql_query .= '('.$q .')';
            if($i != $lastkey)
            {
               $sql_query .= ' UNION ' ;
            }
            $i++;
        }

         $number_of_paramters = preg_match_all('/\?/', $sql_query, $matches);

         $query_paramter_binding = [];

         for ($i=1; $i <= $number_of_paramters ; $i++) 
         { 
             $query_paramter_binding[] = $searh_key;
         }

         return DB::select($sql_query, $query_paramter_binding);    
    }
    

    function file_manager()
    {
        return view('file_manager');
    }
}
