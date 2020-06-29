 @extends('layouts.main')
@section('title', __('form.payment_receipt') ." : " .$rec->number)
@section('content')

<div class="main-content">
    <div class="row">
        <div class="col-md-6">
            <h5>@lang('form.payment')</h5>
        </div>

        <div class="col-md-6 text-right">
            <a target="_blank" href="{{ route('download_receipt', $rec->id) }}" class="btn btn-sm btn-outline-info"><i class="far fa-file-pdf"></i> @lang('form.view_pdf')</a>

            @if(check_perm('payments_delete'))
            <a  href="{{ route('delete_payment_page', $rec->id) }}" class="btn btn-sm btn-outline-danger delete_item"><i class="far fa-trash-alt"></i> @lang('form.delete')</a>
            @endif



        </div>
        <div class="col-md-12"><hr></div>
    </div>

    <div class="row">
        <div class="col-md-8">
            @include('payment.receipt')
        </div>

        <div class="offset-md-1 col-md-3">
            <small class="form-text">@lang('form.note')</small>
            {{ $rec->note }}
            <hr>
            <small class="form-text">@lang('form.transaction_id')/@lang('form.reference')</small>
            {{ $rec->transaction_id }}
         </div>

            
    </div>    
    
    

</div>
@endsection