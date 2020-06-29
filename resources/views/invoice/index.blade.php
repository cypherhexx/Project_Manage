@extends('layouts.main')
@section('title', __('form.invoices'))
@section('content')
<style>
    .hide-content{
        display: none;
    }

    .modal-mask {
       position: fixed;
       z-index: 9998;
       top: 0;
       left: 0;
       width: 100%;
       height: 100%;
       background-color: rgba(0, 0, 0, .5);
       display: table;
       transition: opacity .3s ease;
    }

.modal-wrapper {
   display: table-cell;
   vertical-align: middle;
}

</style>

<div id="invoice" v-cloak>

        <!-- Modal -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{ sprintf(__('form.send___to_email'), __('form.invoice')) }}</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
            <form method="post" action="{{ route( 'invoice_send_to_email') }}">
                {{ csrf_field()  }}
                <input type="hidden" name="invoice_id" id="invoice_id" value="">
                <input type="hidden" name="customer_id" id="customer_id" value="">
                <div class="form-group">
                    <label for="project_id">@lang('form.email')</label>
                    <?php echo form_dropdown('customer_contact_id', [], "", "class='form-control form-control-sm email'"); ?>
                    <div class="invalid-feedback d-block"></div>
                </div>           


              <div class="form-group">
                <label>@lang('form.cc')</label>
                <input type="email" class="form-control form-control-sm" name="email_cc">
                
              </div>
              <div class="custom-control custom-checkbox">
                  <input checked type="checkbox" class="custom-control-input" name="add_attachment" value="1" id="customCheck1">
                  <label class="custom-control-label" for="customCheck1">@lang('form.attach_invoice_as_pdf')</label>
              </div>
              <hr>
              <div class="form-group">
                <label>@lang('form.preview_template')</label>
                <textarea class="form-control" id="email_template" name="email_template"><?php echo nl2br($data['email_template']); ?></textarea>
              </div>
           
            </form>

      </div>
      <div class="modal-footer">
        
        <button type="button" class="btn btn-primary" v-on:click.prevent="send_to_email()">@lang('form.send')</button>
      </div>
    </div>
  </div>
</div>
<!-- End of Modal -->



     <div class="white-background">

        <div class="row">
              <div class="col-md-6">
                 <h5>@lang('form.invoices')</h5>
              </div>
              <div class="col-md-6">
                 <div class="float-md-right">
                       @if(check_perm('invoices_create'))
                            <a class="btn btn-primary btn-sm" href="{{ route('add_invoice_page') }}" role="button">@lang('form.new_invoice')
                         </a>
                        @endif
                         <a class="btn btn-primary btn-sm" href="{{ route('recurring_invoices_list') }}" role="button">@lang('form.recurring_invoices')
                         </a>

                        @if(check_perm(['invoices_view', 'invoices_view_own']))                     
                        <a v-if="layout.left_pane =='col-md-12'" v-on:click.prevent="toggleWindow('col-md-5')" class="btn btn-secondary btn-sm" href="#"><i class="fas fa-angle-double-left"></i></a>
                         <a v-if="layout.left_pane =='col-md-5'" v-on:click.prevent="toggleWindow('col-md-12')" class="btn btn-secondary btn-sm" href="#"><i class="fas fa-angle-double-right"></i></a>                     
                        @endif
                  </div>  
              </div>
           </div>

        <hr>
         @include('invoice.stats')

     </div>

    @if(check_perm(['invoices_view', 'invoices_view_own'])) 
        <br>

    <div class="row">

        <div  v-bind:class="layout.left_pane">
            <div class="main-content">

                @include('invoice.filter')
                <table class="table dataTable no-footer dtr-inline collapsed" width="100%" id="data">
                    <thead>
                    <tr>
                        <th>@lang("form.invoice_#")</th>
                        <th>@lang("form.amount")</th>
                        <th>@lang("form.total_tax")</th>
                        <th>@lang("form.date")</th>
                        <th>@lang("form.customer")</th>
                        <th>@lang("form.project")</th>
                        <th>@lang("form.tags")</th>                        
                        <th>@lang("form.due_date")</th>
                        {{--<th>@lang("form.reference")</th>--}}
                        <th>@lang("form.status")</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>

        <div  v-bind:class="layout.right_pane">
            <div class="main-content" v-if="id">

                <ul class="nav nav-tabs">
                    <li class="nav-item">
                        <a class="nav-link" v-bind:class="{'active':(currentView === 'invoice-details' )}" href="#" @click.prevent="currentView='invoice-details'">@lang('form.invoice')</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" v-bind:class="{'active':(currentView === 'paymentList' )}" href="#" @click.prevent="currentView='paymentList'">@lang('form.payments')</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" v-bind:class="{'active':(currentView === 'invoiceSettings' )}" href="#" @click.prevent="currentView='invoiceSettings'">@lang('form.settings')</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" v-bind:class="{'active':(currentView === 'childInvoices' )}" href="#" @click.prevent="currentView='childInvoices'">@lang('form.child_invoices')</a>
                    </li>


                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tooltip" data-placement="top" title="{{ __('form.toggle_full_view') }}" href="#" v-on:click.prevent="toggleExpand()"><i class="fas fa-expand"></i></a>
                    </li>

                </ul>

                <br>

                <div class="row">
                   <div class="col-md-2">
                       <button type="button" class="btn btn-sm btn-outline-primary">@{{ item_status.name }}</button>

                   </div>
                    <div class="col-md-10">
                        <div class="float-md-right">

                            @if(check_perm('invoices_edit')) 
                            <a v-bind:href="'{{ route('edit_invoice_page') }}/'+ id" data-toggle="tooltip" data-placement="top" title="{{ __('form.edit') }}" class="btn btn-sm btn-outline-info"><i class="far fa-edit"></i></a>
                            @endif

                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-sm btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="far fa-file-pdf"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop1">

                                    <a target="_blank" v-bind:href="'{{ route('download_invoice') }}/'+ id" class="dropdown-item">@lang('form.download')</a>

                                </div>
                            </div>


                            <a href="#" v-on:click.prevent="open_send_to_email_modal(id)" data-toggle="tooltip" data-placement="top" title="{{ __('form.send_to_email') }}" class="btn btn-sm btn-outline-info"><i class="fas fa-envelope"></i></a>


                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    @lang('form.more')
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop1">
                                    <a target="_blank" v-bind:href="url_to_invoice_customer_view" class="dropdown-item">{{ sprintf(__('form.view_as_customer'), __('form.invoice')) }}</a>

                                    @if(check_perm('invoices_edit'))
                                        @if(isset($rec['item_statuses']) && (count($rec['item_statuses']) > 0))
                                            @foreach($rec['item_statuses'] as $item_status)
                                                <a v-if="item_status.id != {{ $item_status->id }}" v-on:click.prevent="changeStatus({{ $item_status->id }})" class="dropdown-item" data-item-id="{{ $item_status->id }}" href="#">@lang('form.mark_as') {{ $item_status->name }}</a>
                                        @endforeach
                                        @endif
                                    @endif

                                    @if(check_perm('invoices_delete')) 
                                        <a v-bind:href="'{{ route('delete_invoice') }}/'+ id" style="color: red;" class="dropdown-item delete_item">@lang('form.delete_invoice')</a>
                                    @endif
                                </div>
                            </div>
                            @if(check_perm('payments_create'))
                                <button v-bind:disabled="item_status.id == '{{ INVOICE_STATUS_PAID }}'" type="button" @click.prevent="currentView='receive_payment'" class="btn btn-sm btn-outline-success">@lang('form.record_payment')</button>
                            @endif
                        </div>
                    </div>

                </div>

                <hr>

                <component :is="currentView" @changed_to_default_view="defaultView" @item_status="itemStatus" v-bind:id="id" v-bind:records="records" transition="fade" transition-mode="out-in"></component>
            </div>
        </div>
    </div>
    @endif
    </div>

<template id="payment_list_template" >
    <div>
        <table class="table" style="font-size: 13px;">
            <thead>
            <tr>
                <th>@lang('form.payment_#')</th>
                <th>@lang("form.payment_mode")</th>
                <th>@lang("form.transaction_id")</th> 
                <th>@lang('form.date')</th>
                <th class="text-right">@lang('form.payment_amount')</th>
             
            </tr>
            </thead>
            <tbody v-if="rec.length > 0">
                <tr v-for="row in rec">


                    <td><a v-bind:href="gen_payment_page_url(row.id)">@{{ row.number }}</a></td>
                    <td>@{{ row.payment_mode.name }}</td>
                    <td>@{{ row.transaction_id }}</td>
                    <td>@{{ row.date }}</td>
                    <td class="text-right">@{{ row.amount }}</td>
                   
                </tr>
            </tbody>

        </table>
    </div>
</template>


@include('invoice.partials.vue_js_template_child_invoices')

@include('invoice.partials.vue_js_template_settings')

<template id="invoice-template" >
    <div>
        <br>
        <span v-html="rec"></span>
    </div>
</template>

<template id="receive_payment_template" >
    @include('invoice.partials.receive_payment_vue_js')
</template>



@endsection

@section('onPageJs')

    @include('generic.select2_vue_single_js')
    @include('generic.datepicker_vue_js')
    <script>

        $(function () {

            var skipAjax = false, // flag to use fake AJAX
                skipAjaxDrawValue = 0; // draw sent to server needs to match draw returned by server

            dataTable = $('#data').DataTable({

                dom: 'Bfrtip',
                buttons: [

                    {
                        init: function(api, node, config) {
                            $(node).removeClass('btn-secondary')
                        },
                        className: "btn-light btn-sm",
                        extend: 'collection',
                        text: 'Export',
                        buttons: [
                            'copy',
                            'excel',
                            'csv',
                            'pdf',
                            'print'
                        ]
                    }
                ],
                "language": {
                    "lengthMenu": '_MENU_ ',
                    "search": '',
                    "searchPlaceholder": "{{ __('form.search') }}"
                    // "paginate": {
                    //     "previous": '<i class="fa fa-angle-left"></i>',
                    //     "next": '<i class="fa fa-angle-right"></i>'
                    // }
                }

                ,

                pageResize: true,
                responsive: true,
                processing: true,
                serverSide: true,
                // iDisplayLength: 5,
                pageLength: {{ data_table_page_length() }},
                ordering: false,
                "columnDefs": [
                    { className: "text-right", "targets": [1,2] }
                    // { className: "text-center", "targets": [5] }




                ],
                "ajax": {
                    "url": '{!! route("datatables_invoice") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: function(data) { //this function allows interaction with data to be passed to server
                        if (skipAjax) { //if fake AJAX flag is set
                            skipAjaxDrawValue = data.draw; //get draw value to be sent to server
                        }
                        data.status_id                 = $("select[name=status_id]").val();
                        return data; //pass on data to be sent to server
                    },
                    beforeSend: function(jqXHR, settings) { //this function allows to interact with AJAX object just before data is sent to server
                        if (skipAjax) { //if fake AJAX flag is set
                            var lastResponse = dataTable.ajax.json(); //get last server response
                            lastResponse.draw = skipAjaxDrawValue; //change draw value to match draw value of current request

                            this.success(lastResponse); //call success function of current AJAX object (while passing fake data) which is used by DataTable on successful response from server

                            skipAjax = false; //reset the flag

                            return false; //cancel current AJAX request
                        }
                    }
                }
            }).
            on('mouseover', 'tr', function() {
                jQuery(this).find('div.row-options').show();
            }).
            on('mouseout', 'tr', function() {
                jQuery(this).find('div.row-options').hide();
            });


            dataTable.on( "click", ".showInformation", function(e) {
                e.preventDefault();

                var id = $(this).data('id');
                updateQueryStringParam('id', id);
                vm.showInformation(dataTable, id);
            });

            $('#datatableFitler select').change(function(){

                dataTable.draw();
            });
            
        });

        Vue.directive('numericOnly', {
            bind: function bind(el) {
                el.addEventListener('keyup', function () {
                    var regex = /^[0-9]*$/;
                    if (!regex.test(el.value)) {
                        el.value = el.value.slice(0, -1);
                    }
                });
            }
        });

        var receivePayment = Vue.component('receive_payment', {
            template: '#receive_payment_template',
            props: ['id', 'records'],
            data: function() {
                return {
                    invoice_id : this.id,
                    amount : this.records.amount_due,
                    date : moment().format('DD-MM-YYYY'),
                    payment_mode_id: 1,
                    credit_notes: [],
                    tmp_balance_due : this.records.amount_due,
                    amount_to_credit : 0,
                    options: <?php echo json_encode($rec['payment_mode_id_list']); ?>,
                    errors : [],
                    show_apply_credit_button : true
                }
            },
            created: function created() {


            },
            filters: {
              format_money: function (value) {
                if (!value) return 0
             
                return accounting.formatNumber(value)
              }
            },
            methods:{
                isNumber: function(evt) {
                    evt = (evt) ? evt : window.event;
                    var charCode = (evt.which) ? evt.which : evt.keyCode;
                    if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
                        evt.preventDefault();
                    } else {
                        return true;
                    }
                },
                toggleModal: function (event) {
                 
                     if($('#applyCreditModal').is(':visible'))
                     {
                         
                         $('#applyCreditModal').modal('toggle'); 
                     }
                     else
                     {
                         $('#applyCreditModal').modal({show: false,backdrop: 'static'}).modal('show');   
        
                     }
                 
                },
                calculate_total_amount_to_credit : function calculate_total_amount_to_credit(){
                    total = 0;
                    $scope = this;
                    $.each(this.credit_notes , function( index, value ) {
                        
                        var amount              = (value.amount_to_credit) ? value.amount_to_credit : 0;
                        var credit_note_amount  = (value.total) ? value.total : 0;
                        var amount_credited     = (value.amount_credited) ? value.amount_credited : 0;
                        var remaining_amount    = credit_note_amount - amount_credited;

                        if(amount > remaining_amount )
                        {                           
                          
                            $scope.errors[index] = '<?php echo __('form.please_enter_a_value_less_than_or_equal_to') ?>' + " " + accounting.formatNumber(remaining_amount);
                        }
                        else
                        {
                            $scope.errors[index] = false;
                        }
     
                        total += parseFloat(amount);
                    });

              

                    this.amount_to_credit   = total;
                    this.tmp_balance_due    = (parseFloat(this.records.amount_due) - parseFloat(this.amount_to_credit));
                 
                    if(this.amount_to_credit > this.records.amount_due)
                    {
                       this.show_apply_credit_button = false;
                    }
                    else
                    {
                      this.show_apply_credit_button = true;
                    }

                },
                applyCreditButton : function () {
                   $scope = this;

                   var postData = { "_token" : "{{ csrf_token() }}", customer_id : $scope.records.customer_id } ;              

                    $.post("{{ route('get_available_credit_notes_by_customer_id')}}" , postData).done(function( response ) {
                         $scope.credit_notes = response;
                         $scope.toggleModal();
                         // $scope.errors = Array($scope.credit_notes.length).fill(false);
                    });


                },
                cancelButton : function () {
                    this.$emit('changed_to_default_view')
                },
                submitButton : function () {
                    $scope = this;
                    var fields = ['amount', 'date', 'payment_mode_id'];
                    var error = [];

                    $.each(fields, function( index, field ) {

                        if(!$scope[field])
                        {
                            $('.' + field).show();
                            error.push(1);
                        }

                    });

                    if(this.amount > this.records.amount_due)
                    {
                       $("#error_msg_exceding_amount").show();
                        error.push(1);
                    }


                    if(error.length == 0)
                    {
                        $("#receivePaymentForm")[0].submit();
                    }

                }

            }
        });

        var paymentList = Vue.component('paymentList', {
            template: '#payment_list_template',
            props: ['id'],
            data: function() {
                return {
                    rec : ""
                }
            },
            created: function mounted() {
                this.getPayments();
            },
            methods:{

                gen_payment_page_url: function gen_payment_page_url($id) {

                    var url = "{{ route('show_payment_page', ':id') }}";

                    return url.replace(':id', $id);

                }, 
                getPayments: function getPayments() {

                    $scope = this;

                    $.get( "{{ route('get_invoice_payments') }}/", { id: this.id } )
                        .done(function( response ) {

                            if(response.status == 1)
                            {
                                $scope.rec = response.data ;
                            }
                            else 
                            {
                               $scope.rec = []; 
                            }


                        }).fail(function() {
                        $scope.rec = "";
                    });

                }

            }
        });


        var invoiceSettings = Vue.component('invoiceSettings', {
            template: '#recurring_invoice_template',
            props: ['id', 'records'],
            data: function() {
                return {
                    formInput : {
                        recurring_invoice_type                  : (this.records.recurring_invoice_type) ? this.records.recurring_invoice_type: 0 ,
                        recurring_invoice_total_cycle           : this.records.recurring_invoice_total_cycle,
                        is_recurring_invoice_period_infinity    : this.records.is_recurring_invoice_period_infinity,
                        recurring_invoice_custom_parameter      : this.records.recurring_invoice_custom_parameter,
                        recurring_invoice_custom_type           : this.records.recurring_invoice_custom_type

                    },
                    recurring_invoice_custom_type_list : <?php echo json_encode($data['recurring_invoice_custom_type_list']); ?>,
                    recurring_invoice_types_list : <?php echo json_encode($data['recurring_invoice_type']); ?>
                }
            },            
            created: function mounted() {
                
            },
            methods:{

                isNumber: function(evt) {
                    evt = (evt) ? evt : window.event;
                    var charCode = (evt.which) ? evt.which : evt.keyCode;
                    if ((charCode > 31 && (charCode < 48 || charCode > 57)) && charCode !== 46) {
                        evt.preventDefault();
                    } else {
                        return true;
                    }
                },
                submitButton: function submitButton() {

                    $scope = this;
                    
                
                    this.formInput["id"] = this.id;
                    this.formInput["_token"] = "{{ csrf_token() }}";

                    $.post("{{ route('update_recurring_invoice_setting')}}" , this.formInput ).done(function( response ) {
                        
                        $.jGrowl(response.msg, { position: 'bottom-right'});    
                    });


                    

                }

            }
        });


        var childInvoices = Vue.component('childInvoices', {
            template: '#child_invoices_template',
            props: ['id'],
            data: function() {
                return {
                    invoices : [],
                  
                    formInput : {
                                              

                    },
                 

                }
            },            
            created: function mounted() {
                this.fetchChildInvoices();
            },
            methods:{

                fetchChildInvoices: function fetchChildInvoices() {

                    var $scope = this;

                    this.formInput["invoice_id"] = this.id;
                    this.formInput["_token"] = "{{ csrf_token() }}";

                    $.post("{{ route('get_child_invoices')}}" , this.formInput ).done(function( response ) {
                        
                        $scope.invoices = response.data;
                       
                    });
                }


            }
        });


        var invoiceDetails = Vue.component('invoice-details', {
            template: '#invoice-template',
            props: ['id'],
            watch : {
                id : function (newVal, oldVal) {
                    this.getInvoice();
                }
            },
            data: function() {
                return {
                    rec : ""
                    
                }
            },
            created: function mounted() {
                this.getInvoice();
            },
            methods : {
                getInvoice : function getInvoice() {
                    $scope = this;

                    $.get( "{{ route('get_invoice_details_ajax') }}/", { id: this.id } )
                        .done(function( response ) {

                            if(response.status == 1)
                            {
                                $scope.rec = response.html ;                                
                                $scope.$emit('item_status', response.item_status, response.url_to_invoice_customer_view,
                                response.records );
                            }
                            else
                            {
                                $scope.rec = "";
                            }


                        }).fail(function() {
                        $scope.rec = "";
                    });

                }


            }
        });

        var vm = new Vue({

            el: '#invoice',
            components: {
                invoiceDetails : invoiceDetails,
                receivePayment : receivePayment
            },
            data: {
                id : "",
                records : [],
                item_status :{
                    id : "",
                    name : ""
                },
                url_to_invoice_customer_view : "",
                layout : {
                    left_pane : 'col-md-12',
                    right_pane : 'hide-content'
                },
                currentView: ''
            },

            // Fetches data when the component is created.
            created: function created() {

            },
            mounted: function mounted() {

            },
            computed: {

            },
            methods: {
                open_send_to_email_modal : function($invoice_id){
                    // To clear the previous value
                    $("#customer_id").val("");
                    
                    $("#invoice_id").val($invoice_id); 

                    $("#customer_id").val(this.records.customer_id);
                    
                    $('#sendEmailModal').modal('show');
                   

                    tinymce.init({
                    selector: '#email_template',
                    branding: false,
                    height: 500,
                  });
                   
                },
                send_to_email : function(){
                    $('#sendEmailModal').find('form').submit();
                },

                defaultView : function () {

                    this.currentView = 'invoice-details';
                },
                changeStatus : function (statusId) {
                    $scope = this;
                    if(statusId)
                    {
                        $.post( "{{ route('ajax_change_invoice_status') }}", { id: $scope.id, status_id: statusId,  "_token": "{{ csrf_token() }}" })
                            .done(function( data ) {

                                if(data.status == 1)
                                {
                                    $scope.itemStatus(data.item_status);
                                }

                            });
                    }

                },
                itemStatus: function (value, url, records) {

                    this.item_status = {
                        id : value.id,
                            name : value.name
                    };

                    if(url)
                    {
                       this.url_to_invoice_customer_view = url; 
                    }
                    if(records)
                    {
                        this.records = records;
                    }
                },
             
                toggleExpand : function toggleExpand() {

                    $scope = this;

                    if($scope.layout.left_pane == 'col-md-5')
                    {
                        $scope.layout = {
                            left_pane : 'hide-content',
                            right_pane : 'col-md-12'
                        };

                    }
                    else
                    {
                        $scope.layout = {
                            left_pane : 'col-md-5',
                            right_pane : 'col-md-7'
                        };

                    }
                    //dataTable.recalculate();

                },
                toggleWindow : function toggleWindow($val) {
                    $scope = this;

                    if($val == 'col-md-5')
                    {
                        $scope.layout = {
                            left_pane : 'col-md-5',
                            right_pane : 'col-md-7'
                        };

                    }
                    else
                    {
                        $scope.layout = {
                            left_pane : 'col-md-12',
                            right_pane : 'hide-content'
                        };

                    }
                    skipAjax = false;
                    dataTable.draw('page');
                },
                showInformation : function showInformation(dataTable, Id) {

                    $scope = this;

                    $scope.id = Id;


                    $scope.layout = {
                            left_pane : 'col-md-5',
                            right_pane : 'col-md-7'
                    };

                    $scope.currentView = 'invoice-details';
                    //dataTable.recalculate();
                    skipAjax = false;
                    dataTable.draw('page');



                }






            }

        });






        $(function() {



            function onPageLoad()
            {

                var $url_parameters = get_url_parameters();

                if($url_parameters.hasOwnProperty('id'))
                {
                    id = $url_parameters['id'];
                    vm.showInformation(dataTable, id);
                }
            }
            onPageLoad();


    });

    $('#sendEmailModal').on('shown.bs.modal', function () {
            load_email_address();
        });


        function load_email_address()
        {
            
            var customer_id = $("#customer_id").val();

            if(customer_id)
            {

                var url = '{{ route("get_contact_emails_by_customer_id", ":id") }}';
                url = url.replace(':id', customer_id);

                $.post(url, { customer_id: customer_id, "_token": "{{ csrf_token() }}" })
                .done(function( response ) {

                    if(response.status == 1)
                    {
                        records = response.data;

                        var email = $( ".email" );
                        
                        email.select2( {
                            theme: "bootstrap",
                            minimumResultsForSearch: -1,
                            placeholder: function(){
                                $(this).data('placeholder');
                            },
                            maximumSelectionSize: 6,
                            data: records,
                            escapeMarkup: function(markup) {
                                return markup;
                              },
                            templateResult: function(data) {
                                return data.email + '<small class="form-text text-muted">' + data.name + '</small>';
                              },

                             templateSelection: function(data) {
                                return data.email ;
                              }
                        } );
                        email.css('width', '100%');
                            
                          
                    }

                });

                
            }
        }    
</script>
@endsection