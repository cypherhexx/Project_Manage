@extends('layouts.main')
@section('title', (isset($rec->id)) ?  __('form.edit_expense')  : __('form.add_new_expense'))
@section('content')

    <style>
        .hide-content{
            display: none;
        }
    </style>
    <form method="post" action="{{ (isset($rec->id)) ? route( 'patch_expense', $rec->id) : route('post_expense') }}" enctype="multipart/form-data">

        {{ csrf_field()  }}

        @if(isset($rec->id))
            {{ method_field('PATCH') }}
        @endif


       <div class="row">
           <div class="col-md-6">
               <div class="main-content">

                   <h5>{{ (isset($rec->id)) ?  __('form.edit_expense')  : __('form.add_new_expense') }}</h5>
                    <hr>
                   <a href="#" class="upload_link">
                       <div class="upload-area">
                           <div>@lang('form.attach_receipt')</div>
                           <div style="font-size: 11px; color: black;">@lang('form.max_size_one_mb')</div>
                           <div style="font-size: 11px; color: black;">@lang('form.accepted_file_types'): jpeg,bmp,png,doc,docx,pdf </div>
                       </div>
                   </a>

                   <div class="invalid-feedback d-block">@php if($errors->has('attachment')) { echo $errors->first('attachment') ; } @endphp</div>
                   <input type="hidden" name="attachment_removed">
                   <input type="file" name="attachment" id="attachment" accept=".png,.jpeg,.jpg,.bmp,.doc, .docx,.pdf" style="display: none;">
                   <div id="preview_content" class="{{ (isset($rec->display_type) && $rec->display_type == 'div') ? '' : 'hide-content' }}" style="width: 120px; height: 120px; background-color: #8eb4cb; text-align: center;"></div>
                   <div>
                       <img id="preview_image" class="{{ (isset($rec->attachment_url) && $rec->display_type == 'image') ? '' : 'hide-content' }}" style="width: 120px; height: 120px;"
                            src="{{ (isset($rec->attachment_url) && $rec->display_type == 'image') ? $rec->attachment_url : '' }}"  />
                   </div>

                    <a href="#" id="remove_preview" class="{{ (isset($rec->attachment_url) && $rec->attachment_url) ? '' : 'hide-content' }}">@lang('form.remove')</a>
                   <hr>
                   <div class="form-row">

                       <div class="form-group col-md-6">
                           <label>@lang('form.date') <span class="required">*</span></label>
                           <input type="text" class="form-control datepicker form-control-sm" name="date" value="{{ old_set('date', NULL, $rec) }}">
                       </div>

                       <div class="form-group col-md-6">
                           <label>@lang('form.category') <span class="required">*</span></label>
                           <div class="select2-wrapper">
                               <?php echo form_dropdown("expense_category_id", $data['categories'], old_set("expense_category_id", NULL, $rec), "class='form-control form-control-sm selectpicker' ") ?>
                           </div>
                           <div class="invalid-feedback d-block">@php if($errors->has('expense_category_id')) { echo $errors->first('expense_category_id') ; } @endphp</div>
                       </div>

                   </div>

                   <div class="form-group">
                       <label>@lang('form.amount') <span class="required">*</span></label>
                       <input type="text" class="form-control form-control-sm {{ showErrorClass($errors, 'amount') }}" name="amount" value="{{ old_set('amount', NULL,$rec) }}">
                        <div class="invalid-feedback d-block">{{ showError($errors, 'amount') }}</div>
                   </div>

                   <div class="form-group">
                      <label>@lang('form.vendor')</label>
                      <div class="select2-wrapper">
                          <?php echo form_dropdown("vendor_id", $data['vendor_id_list'], old_set("vendor_id", NULL, $rec), "class='form-control form-control-sm selectpicker'") ?>
                      </div>
                      <div class="invalid-feedback d-block">@php if($errors->has('vendor_id')) { echo $errors->first('vendor_id') ; } @endphp</div>
                    </div>

                   <div class="form-group">
                       <label>@lang('form.customer')</label>
                       <?php
                       $errorClass = ($errors->has('customer_id')) ? 'is-invalid' : '';
                       echo form_dropdown('customer_id', $data['customer_id_list'] , old_set('customer_id', NULL,$rec), "class='form-control  customer_id $errorClass '");

                       ?>
                       <div class="invalid-feedback d-block">{{ showError($errors, 'customer_id') }}</div>

                   </div>


                 


                   <?php $customer_id = old_set('customer_id', NULL,$rec); ?>

                   <div class="form-group project_selection" style="{{ ($customer_id) ? '' : 'display:none' }}">
                       <label>@lang('form.project')</label>
                       <?php echo form_dropdown('project_id', $data['project_id_list'], old_set('project_id', NULL, $rec), "class='form-control selectpicker  project_id'"); ?>                     

                       
                       <div class="invalid-feedback d-block">{{ showError($errors, 'project_id') }}</div>
                   </div>

               

                  <div id="is_billable" style="{{ ($customer_id) ? '' : 'display:none' }}">
                   <div class="form-check">
                        <input class="form-check-input" name="is_billable" type="checkbox" value="1" {{ (old_set('is_billable', NULL,$rec)) ? 'checked' : '' }}>
                        <label class="form-check-label" for="defaultCheck1">
                            @lang('form.is_billable')
                        </label>

                    </div>
                    <br>
                  </div>  


                   <div class="form-group">
                       <label>@lang('form.name') <i data-toggle="tooltip" data-placement="top" title="{{ __('form.tooltip.expense.form.name') }}" class="fas fa-question-circle"></i> </label>
                       <input type="text" class="form-control form-control-sm" name="name" value="{{ old_set('name', NULL,$rec) }}">
                   </div>

                   <div class="form-group">
                       <label>@lang('form.note') <i data-toggle="tooltip" data-placement="top" title="{{ __('form.tooltip.expense.form.note') }}" class="fas fa-question-circle"></i></label>
                       <textarea name="note" class="form-control form-control-sm" >{{ old_set('note', NULL,$rec) }}</textarea>
                   </div>






                   <?php bottom_toolbar(__('form.submit'))?>
               </div>
           </div>

           <div class="col-md-6">
           
                <div class="main-content">
                    <h5>@lang('form.advanced_options')</h5>
                    <hr>

                    <div class="form-group">
                        <label>@lang('form.currency') <span class="required">*</span></label>
                        <div class="select2-wrapper">

                         
                          <?php 

                          $default_currency_id = (old_set("currency_id", NULL, $rec)) ? old_set("currency_id", NULL, $rec) :  config('constants.default_currency_id');
                          ?>
                            <?php echo form_dropdown("currency_id", $data['currency_id_list'], $default_currency_id, "class='form-control form-control-sm selectpicker' ") ?>
                        </div>
                        <div class="invalid-feedback d-block">@php if($errors->has('currency_id')) { echo $errors->first('currency_id') ; } @endphp</div>
                    </div>

                    <div class="form-row">

                        <div class="form-group col-md-6">
                            <label>@lang('form.payment_mode')</label>
                            <div class="select2-wrapper">
                                <?php echo form_dropdown("payment_mode_id", $data['payment_mode_id_list'], old_set("payment_mode_id", NULL, $rec), "class='form-control form-control-sm selectpicker'") ?>
                            </div>
                            <div class="invalid-feedback d-block">@php if($errors->has('payment_mode_id')) { echo $errors->first('payment_mode_id') ; } @endphp</div>
                        </div>

                        <div class="form-group col-md-6">
                            <label>@lang('form.reference')</label>
                            <input type="text" class="form-control form-control-sm" name="reference" value="{{ old_set('reference', NULL,$rec) }}">
                        </div>


                    </div>

                    <div class="form-group">
                        <label>@lang('form.tax') <span class="required">*</span></label>
                        <div class="select2-wrapper">
                            <?php echo form_dropdown("tax_id[]", $data['tax_id_list'], old_set("tax_id", NULL, $rec), "class='form-control form-control-sm select2-multiple' multiple='multiple' ") ?>
                        </div>
                        <div class="invalid-feedback d-block">@php if($errors->has('tax_id')) { echo $errors->first('tax_id') ; } @endphp</div>
                    </div>

                </div>
           </div>
       </div>



    </form>


@endsection

@section('onPageJs')

    <script>
        $(function () {

            // Populating Datepicker
            <?php
                    $date  = old_set('date', NULL, $rec) ;
                    if($date){

            ?>
            $('input[name=date]').data('daterangepicker').setStartDate('{{ $date }}');
            <?php } ?>

            // End of Populating Datepicker


            // Image Upload

            $('#remove_preview').click(function (e) {
                e.preventDefault();
                $("#attachment").val("");
                $('#preview_image').attr('src', "").hide();
                $("#preview_content").hide();
                $('input[name=attachment_removed]').val(1);
                $(this).hide();
            });
            function readURL(input) {
                if (input.files && input.files[0]) {
                    var reader = new FileReader();

                    reader.onload = function (e) {
                        $('#preview_image').attr('src', e.target.result).show();
                    };

                    reader.readAsDataURL(input.files[0]);
                }
            }

            $('.upload_link').click(function (e) {

                e.preventDefault();

                $('#attachment').trigger('click');
            });

            $("#attachment").change(function(){
                var file = $(this).val();
                var ext = file.split('.').pop();

             
                if($.inArray( ext , [ "png", "jpeg", "jpg", "bmp" ] )  == -1 )
                {

                    $('#preview_image').attr('src', "").hide();
                    $("#preview_content").show();


                }
                else
                {

                    $("#preview_content").hide();
                    readURL(this);
                }


                $("#remove_preview").show();

            });

            // End of Image Upload
            project_id = "";

            var customer_id = $( ".customer_id" );

            customer_id.select2( {
                theme: "bootstrap",
                minimumInputLength: 2,
                maximumSelectionSize: 6,
                placeholder: "{{ __('form.select_and_begin_typing') }}",
                allowClear: true,

                ajax: {
                    url: '{{ route("search_customer") }}',
                    data: function (params) {
                        return {
                            search: params.term
                        }


                    },
                    dataType: 'json',
                    processResults: function (data) {
                        //params.page = params.page || 1;
                        // Tranforms the top-level key of the response object from 'items' to 'results'
                        return {
                            results: data.results
                            // pagination: {
                            //     more: (params.page * 10) < data.count_filtered
                            // }
                        };
                    }




                },

                templateResult: function (obj) {

                    return obj.name;
                },
                templateSelection: function (obj) {

                    if(obj && obj.id)
                    {
                        if(obj.currency_id)
                        {
                            $('select[name=currency_id]').val(obj.currency_id).trigger('change');
                        }
                        else
                        {
                            $('select[name=currency_id]').val("<?php echo config('constants.default_currency_id'); ?>").trigger('change');
                        }
                        selectProject();
                    }

                    return obj.name ||  obj.text
                }

            } );

            customer_id.on("change", function(e) {

                if(!$(this).val())
                {
                    project_id.val(null).trigger("change");
                    $('select[name=currency_id]').val("<?php echo config('constants.default_currency_id'); ?>").trigger('change');
                    $('.project_selection').hide();
                }
                else
                {
                  $('#is_billable').hide();                   
                  $( "input[name=is_billable]" ).prop( "checked", false );
                }
            });

            customer_id.on('select2:select', function(selection){
            
                    $('#is_billable').show();
            });

            function selectProject() {

                $('.project_selection').show();

                project_id = $( ".project_id" ).select2( {
                    theme: "bootstrap",
                    minimumInputLength: 2,
                    maximumSelectionSize: 6,
                    placeholder: "{{ __('form.select_and_begin_typing') }}",
                    allowClear: true,

                    ajax: {
                        url: '{{ route("get_project_by_customer_id") }}',
                        data: function (params) {
                            return {
                                search: params.term,
                                customer_id:  $('select[name=customer_id]').val()

                            }


                        },
                        dataType: 'json',
                        processResults: function (data) {
                            //params.page = params.page || 1;
                            // Tranforms the top-level key of the response object from 'items' to 'results'
                            return {
                                results: data.results
                                // pagination: {
                                //     more: (params.page * 10) < data.count_filtered
                                // }
                            };
                        }




                    },

                    templateResult: function (obj) {

                        return obj.name;
                    },
                    templateSelection: function (obj) {

                        return obj.name ||  obj.text 
                      }

                } );

            }


        });
    </script>

@endsection

