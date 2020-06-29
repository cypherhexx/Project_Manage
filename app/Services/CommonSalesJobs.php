<?php 
namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\MessageBag;
use Illuminate\Http\Request;
use App\Invoice;
use App\Expense;
use App\TimeSheet;
use App\Task;

class CommonSalesJobs {

	private $model ;

 	function __construct(Model $model) {
        
        $this->model = $model;
    }

    private function is_in_assoc_array($array, $to_look_for, $value)
    {

        if(count($array) > 0)
        {
            foreach ($array as $key=>$item)
            {

                if(trim($item[$to_look_for]) == trim($value))
                {
                    return TRUE;
                }
            }

        }

        return FALSE;

    }


    

    function merge_tax_dropdown_information($tax_id_list)
    {
        
        if(isset($this->model->taxes) && is_array($this->model->taxes))
        {            
            $array_of_taxes_used = json_decode($this->model->taxes, true);

            if(!empty($tax_id_list) && !empty($array_of_taxes_used) && is_array($array_of_taxes_used))
            {
                foreach ($array_of_taxes_used as $row)
                {
                    if(!$this->is_in_assoc_array($tax_id_list, 'id',  $row['id']))
                    {
                        $row['text'] = $row['name'];
                        array_push($tax_id_list, $row);
                    }

                }
                return $tax_id_list;
            }

        }
        else
        {
            return $tax_id_list;
        }
    }

    public function insert_item_line($inputs, $item_line_class_name, $forign_key_in_item_line)
    {
        $item_line = (isset($inputs->items)) ? $inputs->items : [];

    	if(!empty($item_line) && is_array($item_line))
        {           
            foreach($item_line as $key=>$row)
            {                   
                $item_line[$key][$forign_key_in_item_line] = $this->model->id;               

                // Insert Item
                $item = $item_line_class_name::create($item_line[$key]);

                if($this->model instanceof Invoice)
                {
                    $this->update_timsheet_and_task($row, $inputs, $item);
                }
                
            }

        }
    }

 	public function update_item_line($requested_items, $item_line_class_name, $forign_key_in_item_line)
 	{
 		// Update Item Line
        if(isset($requested_items) && count($requested_items) > 0 && is_array($requested_items))
        {

            $item_line = $this->model->item_line()->get();
            
            $ids_of_existing_items = [];
            
            if(count($item_line) > 0)
            {
                $ids_of_existing_items = (array_column($item_line->toArray(), 'id'));
            }
            
            $ids_of_submitted_items = array_column($requested_items, 'id');


            $items_to_remove = array_diff($ids_of_existing_items, $ids_of_submitted_items);

            if(count($items_to_remove) > 0)
            {
                $item_line_class_name::whereIn('id', $items_to_remove)->delete();
            }

            foreach( $requested_items as $row)
            {
                if(isset($row['id']) && $row['id'])
                {
                    $item = $item_line_class_name::find($row['id']);
                    $item->update($row) ;  
                }
                else
                {
                    $row[$forign_key_in_item_line] = $this->model->id;
                    $item = $item_line_class_name::create($row);
                }                    

            }
        }
 	}


    function update_timsheet_and_task($row, $inputs, $item)
    {
        $invoice = $this->model;

        // If Expenses or Task Timesheet was included 
        if(isset($row['component_id']) && $row['component_id'] && isset($row['component_number']) && $row['component_number'])
        {
            if($row['component_id'] == COMPONENT_TYPE_TIMESHEET)
            {
                // Update TimeSheet Table
                $timesheet = TimeSheet::find($row['component_number']);
               
                $timesheet->invoice_id          = $invoice->id;
                $timesheet->invoice_item_id     = $item->id;
                $timesheet->save();

                if($inputs->invoicing_for_project)
                {
                    $timesheet->task->status_id = TASK_STATUS_COMPLETE;
                    $timesheet->task->save();
                }
            }
            elseif($row['component_id'] == COMPONENT_TYPE_TASK)
            {
                $task = Task::find($row['component_number']);

                $timesheets = $task->timesheets;

                if(count($timesheets) > 0)
                {
                    foreach ($timesheets as $timesheet) 
                    {
                        $timesheet->invoice_id          = $invoice->id;
                        $timesheet->invoice_item_id     = $item->id;
                        $timesheet->save();
                    }
                    
                }
                if($inputs->invoicing_for_project)
                {
                    $task->status_id = TASK_STATUS_COMPLETE;
                    $task->save();
                }
                
            }       
            elseif($row['component_id'] == COMPONENT_TYPE_EXPENSE)
            {
                $expenses = Expense::find($row['component_number']);
                $expenses->invoice_id          = $invoice->id;
                $expenses->save();
            }
            
        }
    }


    public static function populate_item_line_data(MessageBag $errors, Request $request)
    {
        $request_all = $request->all();
        
        if ($errors->has('items.*')) 
        {
            foreach ($errors->get('items.*') as $key => $message) 
            {
                preg_match_all('/\d+/', $key, $matches);

                if (isset($matches[0][0])) {
                    $row_number = $matches[0][0];

                    $item_key = explode('items.' . $row_number . '.', $key)[1];

                    $request_all['items'][$row_number]['validation_error'][$item_key] = implode(",", $message);
                }


            }
        }

        return $request_all;
    }
 }