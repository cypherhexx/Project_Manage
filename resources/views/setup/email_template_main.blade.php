@extends('setup.index')
@section('title', __('form.settings') . " : " .__('form.email_template'))
@section('setting_page')
<div class="main-content">
   <h5>@lang('form.email_templates')</h5>
   <hr>
	@include('setup.email_template_list')
</div>	
@endsection