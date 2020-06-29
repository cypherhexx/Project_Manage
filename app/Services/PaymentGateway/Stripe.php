<?php 
namespace App\Services\PaymentGateway;

use App\Services\PaymentGateway\Contracts\GatewayContract;
use App\Invoice;
use Stripe\Stripe as StripeClient;
use Stripe\Charge;
use Illuminate\Http\Request;
use App\Services\PaymentGateway\Contracts\PaymentBean;

class Stripe implements GatewayContract {

	function unique_identifier_id() : string
	{
		return 'stripe';
	}

	function display_name() : string
	{
		return __('form.stripe');
	}

	function view_file_for_settings_page() : string 
	{

		return 'payment.modes.online.stripe';
	}

	function validation_rules() : array
	{
		return [
            'settings.stripe_label'                 => 'required',
            'settings.stripe_api_secret_key'        => 'required',
            'settings.stripe_api_publishable_key'   => 'required',
        ];
	}


	function validation_messages() : array
	{
		return [
            'settings.stripe_label.required'                 => sprintf(__('form.field_is_required'), __('form.label')),
            'settings.stripe_api_secret_key.required'        => sprintf(__('form.field_is_required'), __('form.stripe_api_secret_key')),
            'settings.stripe_api_publishable_key.required'   => sprintf(__('form.field_is_required'), __('form.stripe_api_publishable_key')),
        ];
	}


	function form_input_field_name_gateway_name() : string 
	{
		return 'stripe_label';
	}

	function form_input_field_name_gateway_status() : string 
	{
		return 'stripe_active';
	}

	
	function process_payment(Invoice $invoice, array $data)
	{
		return view('payment.modes.online.checkout.stripe', compact('data'))->with('invoice', $invoice);
	}
	

	 function charge(Invoice $invoice, Request $request)
    {
        $stripe_token   = $request->stripeToken;
        $email_address  = $request->stripeEmail;
        $amount         = $request->stripeAmount;

        
        // Get the Stripe API Key and other information
        $api_credentials = get_payment_gateway_info('stripe') ;

        // Go through the process only if stripe is enabled
        if((isset($api_credentials->stripe_active)) && $api_credentials->stripe_active)
        {
            // Set Stripe's API Private Key
            StripeClient::setApiKey($api_credentials->stripe_api_secret_key);       

            // Go through the process if invoice record exists                      
           

            try {
                    // Get the currency iso code and symbol
                    $currency = $invoice->get_currency();    

                    // Make Request to Stripe API to charge the amount to the customer
                    $charge = Charge::create([
                    'amount'        => convert_amount_to_lowest($amount) , 
                    'currency'      => $currency['iso'] , 
                    'source'        => $stripe_token,                       
                    'description'   => str_replace('{invoice_number}', $invoice->number , $api_credentials->stripe_description_dashboard)
                    ]);
                    
                    
                    // Check if payment was successful, else throw unknown_error exception
                    if(isset($charge->status) && $charge->status == 'succeeded')
                    {
                        // Payment was successful
                        $reference 			= (isset($charge->id)) ? $charge->id : NULL;
                        $note 				= __('form.payment_received_via_stripe') . " - ". $email_address ;
                        $api_response 		= [ 'email' => $email_address ,'response' => $charge ];


                        $paymentBean = new PaymentBean();

                        $paymentBean->date(date("Y-m-d"))
                                    ->amount($amount)
                                    ->payment_mode_id($api_credentials->payment_mode_id)
                                    ->reference($reference)
                                    ->note($note)
                                    ->api_response($api_response);

                        $payment = $invoice->payment_received($paymentBean);    
                      	                  
                       return TRUE;                    
                                                  
                    }             
          
      
            } 
            catch (DecryptException $e) {
                 
                
            }
            catch(\Stripe\Error\Card $e) {                 
            
              if(isset( $e->getJsonBody()['error']['message'] ))
              {
                    $error_message = $e->getJsonBody()['error']['message'] ;
              }        
              
            } catch (\Stripe\Error\RateLimit $e) {
              
              // Too many requests made to the API too quickly
             
            } catch (\Stripe\Error\InvalidRequest $e) {
              // Invalid parameters were supplied to Stripe's API
                
             

            } catch (\Stripe\Error\Authentication $e) {
              // Authentication with Stripe's API failed
              
             
            } catch (\Stripe\Error\ApiConnection $e) {
              // Network communication with Stripe failed
               
              

            } catch (\Stripe\Error\Base $e) {
              // Display a very generic error to the user, and maybe send
              // yourself an email
              
             
              
            } catch (\Exception $e) {
              // Something else happened, completely unrelated to Stripe
               
            }

           
         
        }
       

        return FALSE;
    }
       
}