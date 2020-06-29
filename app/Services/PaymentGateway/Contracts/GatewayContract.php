<?php 
namespace App\Services\PaymentGateway\Contracts;

use App\Invoice;

interface GatewayContract {	

	public function unique_identifier_id() : string ;

	public function display_name() : string ;

	public function view_file_for_settings_page() : string ;

	public function process_payment(Invoice $invoice, array $data);

	public function validation_rules() : array ;

	public function validation_messages() : array ;

	public function form_input_field_name_gateway_name() : string ;

	public function form_input_field_name_gateway_status() : string ;
       
}