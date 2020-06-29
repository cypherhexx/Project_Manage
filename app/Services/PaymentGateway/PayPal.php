<?php 
namespace App\Services\PaymentGateway;

use App\Services\PaymentGateway\Contracts\GatewayContract;
use App\Invoice;

class Paypal implements GatewayContract {

	
	function unique_identifier_id() : string
	{
		return 'paypal';
	}

	function display_name() : string
	{
		return __('form.paypal');
	}

	public function view_file_for_settings_page() : string 
	{

		return 'payment.modes.online.paypal';
	}
	
	
	function validation_rules() : array
	{
		return [
            'settings.paypal_label'                 => 'required',
            'settings.paypal_username'              => 'required',
            'settings.paypal_password'              => 'required',
            'settings.paypal_signature'             => 'required',
        ];
	}


	function validation_messages() : array
	{
		return [
            'settings.paypal_label.required'         => sprintf(__('form.field_is_required'), __('form.label')),
            'settings.paypal_username.required'      => sprintf(__('form.field_is_required'), __('form.paypal_username')),
            'settings.paypal_password.required'      => sprintf(__('form.field_is_required'), __('form.paypal_password')),
            'settings.paypal_signature.required'     => sprintf(__('form.field_is_required'), __('form.paypal_signature')),
        ];
	}


	public function form_input_field_name_gateway_name() : string 
	{
		return 'paypal_label';
	}

	public function form_input_field_name_gateway_status() : string 
	{
		return 'paypal_active';
	}

	
	function process_payment(Invoice $invoice, $data)
	{
		//return redirect()->away('https://www.sandbox.paypal.com');
		return view('payment.modes.online.checkout.paypal', compact('data'))->with('invoice', $invoice);
	}


	
       
}