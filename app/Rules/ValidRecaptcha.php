<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidRecaptcha implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $secretKey = config('constants.google_recaptcha_secret_key');

        if($secretKey)
        {
            $url            = 'https://www.google.com/recaptcha/api/siteverify?secret='.$secretKey.'&response='.$value;

            $verifyResponse = file_get_contents($url);
            $responseData   = json_decode($verifyResponse);
            
            return (isset($responseData->success) && $responseData->success) ? TRUE : FALSE;

        }
        else
        {
            return TRUE;
        }

        
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('form.incorrect_recaptcha');
    }
}
