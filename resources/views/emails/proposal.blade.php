@extends('emails.layout')
@section('email_content')
	<?php echo nl2br($rec['email_template']); ?>
@endsection