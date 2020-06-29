<?php

namespace App\Services\PaymentGateway\Contracts;

class PaymentBean {

	private $date;

	private $amount;

	private $payment_mode_id;

	private $reference;

	private $note;

	private $api_response;


	public function getDate()
	{
		return $this->date;
	}

	public function date($date)
	{
		$this->date = $date;
		return $this;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function amount($amount)
	{
		$this->amount = $amount;
		return $this;
	}

	public function getPayment_mode_id()
	{
		return $this->payment_mode_id;
	}

	public function payment_mode_id($payment_mode_id)
	{
		$this->payment_mode_id = $payment_mode_id;
		return $this;
	}

	public function getReference()
	{
		return $this->reference;
	}

	public function reference($reference)
	{
		$this->reference = $reference;
		return $this;
	}

	public function getNote()
	{
		return $this->note;
	}

	public function note($note)
	{
		$this->note = $note;
		return $this;
	}

	public function getApi_response()
	{
		return $this->api_response;
	}

	public function api_response(array $api_response)
	{
		$this->api_response = $api_response;
		return $this;
	}
}