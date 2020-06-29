@extends('layouts.main')
@section('title', __('form.payment_receipt') ." : " .$rec->number)
@section('content')

    <div class="row">
        <div class="col-md-4">
            <div class="main-content">
                
               
                <form method="post" action="{{ route( 'patch_payment', $rec->id) }}">
                    {{ csrf_field()  }}
                    @if(isset($rec->id))
                        {{ method_field('PATCH') }}
                    @endif
                    <div class="form-group">
                        <label>@lang('form.amount_received') <span class="required">*</span> </label>
                        <input type="text" class="form-control form-control-sm @php if($errors->has('amount')) { echo 'is-invalid'; } @endphp" name="amount" value="{{ old_set('amount', NULL, $rec) }}">
                        <div class="invalid-feedback">@php if($errors->has('amount')) { echo $errors->first('amount') ; } @endphp</div>
                    </div>
                    <div class="form-group">
                        <label>@lang('form.payment_date') <span class="required">*</span> </label>
                        <input type="text" class="form-control form-control-sm datepicker  @php if($errors->has('date')) { echo 'is-invalid'; } @endphp" name="date" value="{{ old_set('date', NULL,$rec) }}">
                        <div class="invalid-feedback">@php if($errors->has('date')) { echo $errors->first('date') ; } @endphp</div>
                    </div>
                    <div class="form-group">
                        <label>@lang('form.payment_mode') <span class="required">*</span> </label>
                        <div class="select2-wrapper">
                            <?php echo form_dropdown("payment_mode_id", $data['payment_mode_id_list'], old_set("payment_mode_id", NULL, $rec), "class='form-control selectpicker'") ?>
                        </div>
                        <div class="invalid-feedback">@php if($errors->has('payment_mode_id')) { echo $errors->first('payment_mode_id') ; } @endphp</div>
                    </div>
                    <div class="form-group">
                        <label>
                            @lang('form.payment_method')

                            <i class="fa fa-question-circle" data-toggle="tooltip" data-placement="top" title="{{ __('form.tooltip.payment_method') }}"></i>


                        </label>
                        <input type="text" class="form-control form-control-sm  @php if($errors->has('payment_method')) { echo 'is-invalid'; } @endphp" name="payment_method" value="{{ old_set('payment_method', NULL,$rec) }}">
                        <div class="invalid-feedback">@php if($errors->has('payment_method')) { echo $errors->first('payment_method') ; } @endphp</div>
                    </div>

                    <div class="form-group">
                        <label>@lang('form.transaction_id')</label>
                        <input type="text" class="form-control form-control-sm  @php if($errors->has('transaction_id')) { echo 'is-invalid'; } @endphp" name="transaction_id" value="{{ old_set('transaction_id', NULL,$rec) }}">
                        <div class="invalid-feedback">@php if($errors->has('transaction_id')) { echo $errors->first('transaction_id') ; } @endphp</div>
                    </div>
                    <div class="form-group">
                        <label>@lang('form.note')</label>
                        <textarea id="note" name="note" rows="4" class="form-control">{{ old_set('note', NULL, $rec) }}</textarea>
                        <div class="invalid-feedback">@php if($errors->has('note')) { echo $errors->first('note') ; } @endphp</div>
                    </div>

                    <div class="row bottom-toolbar">
                        <div  class="col-md-12">
                            <div style="text-align: right;">
                                <input type="submit" class="btn btn-primary" value="@lang('form.submit')"/>

                            </div>
                        </div>
                    </div>

                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="main-content">
                <div class="row">
                    <div class="col-md-6">
                        <h5>@lang('form.payment')</h5>
                    </div>

                    <div class="col-md-6 text-right">
                        <a target="_blank" href="{{ route('download_receipt', $rec->id) }}" class="btn btn-sm btn-outline-info"><i class="far fa-file-pdf"></i> @lang('form.view_pdf')</a>

                        <a  href="{{ route('delete_payment_page', $rec->id) }}" class="btn btn-sm btn-outline-danger delete_item"><i class="far fa-trash-alt"></i> @lang('form.delete')</a>




                    </div>
                </div>
                <hr>
                @include('payment.receipt')
            </div>
        </div>
    </div>
@endsection
@section('onPageJs')
@endsection