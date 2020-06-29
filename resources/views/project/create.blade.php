@extends('layouts.main')
@section('title', (isset($rec->id)) ?  __('form.edit'). " - ". $rec->name : __('form.add_new_project'))
@section('content')
    <div class="container">
        <form method="post" action='{{  (isset($rec->id)) ? route('patch_project', $rec->id) : route('post_project')}}'>
            {{ csrf_field()  }}
            @if(isset($rec->id))
                {{ method_field('PATCH') }}
            @endif
            <div class="row">
                <div class="col-md-7">

                    <div class="main-content">
                        <h5>@lang('form.project')</h5>
                        <hr>
                        <div class="form-group ">
                            <label for="name">@lang('form.name') <span class="required">*</span></label>
                            <input type="text"
                                   class="form-control form-control-sm @php if($errors->has('name')) { echo 'is-invalid'; } @endphp"
                                   id="name" name="name" value="{{ old_set('name', NULL, $rec) }}">
                            <div class="invalid-feedback d-block">@php if($errors->has('name')) { echo $errors->first('name') ; } @endphp</div>
                        </div>

                       <!--  <div class="row">
                            <div class="col">
                                <div class="form-group ">
                                    <label for="prefix">@lang('form.prefix')</label>
                                    <input type="text"
                                           class="form-control form-control-sm @php if($errors->has('prefix')) { echo 'is-invalid'; } @endphp"
                                           id="prefix" name="prefix" value="{{ old_set('prefix', NULL, $rec) }}">
                                    <div class="invalid-feedback d-block">@php if($errors->has('prefix')) { echo $errors->first('prefix') ; } @endphp</div>
                                </div>

                            </div>
                            <div class="col">
                               
                            </div>
                        </div>
 -->
                        <div class="form-group">
                            <label for="customer_id">@lang('form.customer') <span class="required">*</span></label>
                            <?php echo form_dropdown("customer_id", $data['customer_id_list'], old_set("customer_id", NULL, $rec), "class='form-control form-control-sm selectpicker'") ?>
                            <div class="invalid-feedback d-block">@php if($errors->has('customer_id')) { echo $errors->first('customer_id') ; } @endphp</div>
                        </div>



                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="calculate_progress_through_tasks"
                                       name="calculate_progress_through_tasks"
                                       value="1" {{ (old_set('calculate_progress_through_tasks', NULL, $rec)) ? 'checked' : '' }}>
                                <label class="form-check-label"
                                       for="calculate_progress_through_tasks">@lang('form.calculate_progress_through_tasks')</label>
                                <div class="invalid-feedback d-block">@php if($errors->has('calculate_progress_through_tasks')) { echo $errors->first('calculate_progress_through_tasks') ; } @endphp</div>
                            </div>
                        </div>
                        <div class="form-group ">
                            <label for="progress">
                                @lang('form.progress')
                                <span id="progress_rate">{{ (old_set('progress', NULL, $rec) == "") ? 0 : old_set('progress', NULL, $rec)  }}</span>%
                            </label>
                            <input name="progress" id="progress" style="border: none !important;" type="range" min="0" max="100" step="1" value="{{ (old_set('progress', NULL, $rec) == "") ? 0 : old_set('progress', NULL, $rec)  }}" class="form-control form-control-sm">
                            <div class="invalid-feedback d-block">@php if($errors->has('progress')) { echo $errors->first('progress') ; } @endphp</div>

                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="billing_type_id">@lang('form.billing_type') <span class="required">*</span></label>
                                    <?php echo form_dropdown("billing_type_id", $data['billing_type_id_list'], old_set("billing_type_id", NULL, $rec), "class='form-control form-control-sm selectPickerWithoutSearch'") ?>
                                    <div class="invalid-feedback d-block">@php if($errors->has('billing_type_id')) { echo $errors->first('billing_type_id') ; } @endphp</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="status">@lang('form.status')</label>
                                    <?php echo form_dropdown("status_id", $data['status_list'], old_set("status_id", NULL, $rec), "class='form-control form-control-sm selectPickerWithoutSearch'") ?>
                                    <div class="invalid-feedback d-block">@php if($errors->has('status_id')) { echo $errors->first('status_id') ; } @endphp</div>
                                </div>
                            </div>
                        </div>
                        <?php $billing_type_id = old_set("billing_type_id", NULL, $rec) ; ?>
                        <div class="form-group billing_rate" style="{{ ($billing_type_id == BILLING_TYPE_TASK_HOURS) ? 'display: none' : '' }}">
                            <label for="billing_rate">@lang('form.billing_rate')</label>
                            <input type="text"
                                   class="form-control form-control-sm @php if($errors->has('billing_rate')) { echo 'is-invalid'; } @endphp"
                                   id="billing_rate"
                                   name="billing_rate"
                                   value="{{ old_set('billing_rate', NULL, $rec) }}">
                            <div class="invalid-feedback d-block">@php if($errors->has('billing_rate')) { echo $errors->first('billing_rate') ; } @endphp</div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <div class="form-group">
                                    <label for="start_date">@lang('form.start_date') <span class="required">*</span></label>
                                    <input type="text"
                                           class="form-control form-control-sm datepicker @php if($errors->has('company')) { echo 'is-invalid'; } @endphp"
                                           id="start_date" name="start_date"
                                           value="{{ old_set('start_date', NULL, $rec) }}">
                                    <div class="invalid-feedback d-block">@php if($errors->has('start_date')) { echo $errors->first('start_date') ; } @endphp</div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label for="dead_line">@lang('form.dead_line')</label>
                                    <input type="text"
                                           class="form-control form-control-sm initially_empty_datepicker @php if($errors->has('company')) { echo 'is-invalid'; } @endphp"
                                           id="dead_line" name="dead_line"
                                           value="{{ old_set('dead_line', NULL, $rec) }}">
                                    <div class="invalid-feedback d-block">@php if($errors->has('dead_line')) { echo $errors->first('dead_line') ; } @endphp</div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="group_id">@lang('form.members')</label>
                            <div class="select2-wrapper">
                                <?php echo form_dropdown("user_id[]", $data['user_id_list'], old_set("user_id", NULL, $rec), "class='form-control form-control-sm select2-multiple' multiple='multiple'") ?>
                            </div>
                            <div class="invalid-feedback d-block">@php if($errors->has('user_id')) { echo $errors->first('user_id') ; } @endphp</div>
                        </div>

                        <div class="form-group">
                            <label for="group_id">@lang('form.tags')</label>
                            <div class="select2-wrapper">
                                <?php echo form_dropdown("tag_id[]", $data['tag_id_list'], old_set("tag_id", NULL, $rec), "class='form-control form-control-sm select2-multiple' multiple='multiple'") ?>
                            </div>
                            <div class="invalid-feedback d-block">@php if($errors->has('tag_id')) { echo $errors->first('tag_id') ; } @endphp</div>
                        </div>


                        <div class="form-group">
                            <label for="description">@lang('form.description')</label>
                            <textarea id="description" name="description"
                                      class="form-control form-control-sm">{{ old_set('description', NULL, $rec) }}</textarea>
                            <div class="invalid-feedback d-block">@php if($errors->has('description')) { echo $errors->first('description') ; } @endphp</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="main-content">
                        <h5>Project Settings</h5>
                        <hr>
                        @include('project.settings')
                    </div>
                </div>
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
@endsection

@section('onPageJs')
    <script>
        $(function () {

            var progressBarInput = $('input[type="range"]');

            progressBarInput.rangeslider({

                // Feature detection the default is `true`.
                // Set this to `false` if you want to use
                // the polyfill also in Browsers which support
                // the native <input type="range"> element.
                polyfill: false,

                // Default CSS classes
                rangeClass: 'rangeslider',
                disabledClass: 'rangeslider--disabled',
                horizontalClass: 'rangeslider--horizontal',
                verticalClass: 'rangeslider--vertical',
                fillClass: 'rangeslider__fill',
                handleClass: 'rangeslider__handle',

                // Callback function
                onInit: function() {

                },

                // Callback function
                onSlide: function(position, value) {

                    $("#progress_rate").html(value);
                },

                // Callback function
                onSlideEnd: function(position, value) {

                    
                }
            });
            
            
            $("#calculate_progress_through_tasks").change(function() {


                if(this.checked)
                {

                    progressBarInput.prop('disabled', true);
                }
                else
                {
                    progressBarInput.prop('disabled', false);
                }

                progressBarInput.rangeslider('update');
            });


            $("select[name=billing_type_id]").change(function () {

                billing_type_id = $(this).val();

                if(billing_type_id == '{{ BILLING_TYPE_FIXED_RATE }}')
                {
                    $("label[for*='billing_rate']").html("{{ __('form.total_rate') }}");
                }
                else if(billing_type_id == '{{ BILLING_TYPE_PROJECT_HOURS }}')
                {
                    $("label[for*='billing_rate']").html("{{ __('form.rate_per_hour') }}");
                }
                if(billing_type_id == '{{ BILLING_TYPE_TASK_HOURS }}')
                {
                    $('.billing_rate').hide();
                }
                else
                {
                    $('.billing_rate').show();
                }

            });

        });
    </script>

    @yield('innerPageJs')
    @endsection