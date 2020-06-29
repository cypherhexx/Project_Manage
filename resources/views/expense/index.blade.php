@extends('layouts.main')
@section('title', __('form.expenses'))
@section('content')
<style>
   .hide-content{
   display: none;
   }
</style>
<div id="expense" v-cloak>
   <div class="white-background">
      <div class="row">
         <div class="col-md-6">
            <h5>@lang('form.expenses')</h5>
         </div>
         <div class="col-md-6">
            <div class="float-md-right">                 
               @if(check_perm('expenses_create')) 
               <a class="btn btn-primary btn-sm" href="{{ route('add_expense_page') }}" role="button">
               @lang('form.record_expense')
               </a>
               @endif
               @if(check_perm(['expenses_view', 'expenses_view_own']))           
               <a v-if="layout.left_pane =='col-md-12'" v-on:click.prevent="toggleWindow('col-md-5')" class="btn btn-secondary btn-sm" href="#"><i class="fas fa-angle-double-left"></i></a>
               <a v-if="layout.left_pane =='col-md-5'" v-on:click.prevent="toggleWindow('col-md-12')" class="btn btn-secondary btn-sm" href="#"><i class="fas fa-angle-double-right"></i></a>           
               @endif
            </div>
         </div>
      </div>
   </div>
   @if(check_perm(['expenses_view', 'expenses_view_own'])) 
   <br>
   <div class="row">
      <div  v-bind:class="layout.left_pane">
         <div class="main-content">
            <table class="table table-expenses dataTable no-footer dtr-inline collapsed" width="100%" id="data">
               <thead>
                  <tr>
                     <th>@lang("form.category")</th>
                     <th>@lang("form.amount")</th>
                     <th>@lang("form.name")</th>
                     <th>@lang("form.date")</th>
                     <th>@lang("form.project")</th>
                     <th>@lang("form.customer")</th>
                     <th>@lang("form.invoice")</th>
                     <th>@lang("form.vendor")</th>
                     <th>@lang("form.reference")</th>
                     <th>@lang("form.payment_mode")</th>
                     <th>@lang("form.attachment")</th>
                  </tr>
               </thead>
            </table>
         </div>
      </div>
      <div  v-bind:class="layout.right_pane">
         <div class="main-content" v-if="id">
            <ul class="nav nav-tabs">
               <li class="nav-item">
                  <a class="nav-link" v-bind:class="{'active':(currentView === 'expense_details' )}" href="#" @click.prevent="currentView='expense_details'">@lang('form.expense')</a>
               </li>
               <li class="nav-item">
                  <a class="nav-link" data-toggle="tooltip" data-placement="top" title="{{ __('form.toggle_full_view') }}" href="#" v-on:click.prevent="toggleExpand()"><i class="fas fa-expand"></i></a>
               </li>
            </ul>
            <br>
            <div class="row">
               <div class="col-md-2">
                  <div v-if="records">
                     <h4>@{{ records.category.name }}</h4>
                     <small>@{{ records.name }}</small>
                  </div>
               </div>
               <div class="col-md-10">
                  <div class="float-md-right">
                     <a v-if="!records.invoice_id" v-bind:href="'{{ route('edit_expense_page') }}/'+ id" data-toggle="tooltip" data-placement="top" title="{{ __('form.edit') }}" class="btn btn-sm btn-outline-info"><i class="far fa-edit"></i></a>
                     <a v-if="!records.invoice_id" v-bind:href="'{{ route('delete_expense') }}/'+ id" data-toggle="tooltip" data-placement="top" title="{{ __('form.delete') }}" class="btn btn-sm btn-outline-info delete_item"><i class="far fa-trash-alt"></i></a>
                     <a v-if="records.customer_id && !records.invoice_id" v-bind:href="'{{ route('convert_to_invoice_from_expense') }}/'+ id" class="btn btn-sm btn-outline-success">@lang('form.convert_to_invoice')</a>
                  </div>
               </div>
            </div>
            <br>
            <component :is="currentView" @item_records="itemRecords" v-bind:id="id" transition="fade" transition-mode="out-in"></component>
         </div>
      </div>
   </div>
   @endif
</div>
<template id="expense-template" >
   <div>
      <div v-if="rec">
         <table class="table table-sm" style="font-size: 13px;">
            <tbody>
               <tr>
                  <td>@lang('form.amount')</td>
                  <td><span class="text-danger">@{{ rec.amount }}</span></td>
               </tr>
               <tr v-if="rec.tax_id">
                  <td>@lang('form.tax')</td>
                  <td>@{{ rec.tax_information }}</td>
               </tr>
               <tr v-if="rec.tax_id">
                  <td>@lang('form.total_with_tax')</td>
                  <td><span class="text-danger">@{{ rec.amount_after_tax }}</span></td>
               </tr>
               <tr v-if="rec.payment_mode">
                  <td>@lang('form.payment_mode')</td>
                  <td>@{{ rec.payment_mode.name }}</td>
               </tr>
               <tr>
                  <td>@lang('form.date')</td>
                  <td>@{{ rec.date }}</td>
               </tr>
               <tr>
                  <td>@lang('form.reference')</td>
                  <td>@{{ rec.reference }}</td>
               </tr>
               <tr v-if="rec.customer">
                  <td>@lang('form.customer')</td>
                  <td v-html="rec.customer_page_link"></td>
               </tr>
               <tr v-if="rec.project">
                  <td>@lang('form.project')</td>
                  <td v-html="rec.project_page_link"></td>
               </tr>
               <tr>
                  <td>@lang('form.note')</td>
                  <td>@{{ rec.note }}</td>
               </tr>
               <tr v-if="rec.attachment_url">
                  <td>@lang('form.attachment')</td>
                  <td v-html="rec.attachment_url"></td>
               </tr>
            </tbody>
         </table>
      </div>
   </div>
</template>
@endsection

@section('onPageJs')
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
                    { className: "text-right", "targets": [1,9] },
                    { className: "text-center", "targets": [3] },
                    { responsivePriority: 1},
                    { responsivePriority: 2},
                    { responsivePriority: 3}


                ],
                "ajax": {
                    "url": '{!! route("datatables_expense") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: function(data) { //this function allows interaction with data to be passed to server
                        if (skipAjax) { //if fake AJAX flag is set
                            skipAjaxDrawValue = data.draw; //get draw value to be sent to server
                        }

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
        });






        var expenseDetails = Vue.component('expense_details', {
            template: '#expense-template',
            props: ['id'],
            watch : {
                id : function (newVal, oldVal) {
                    this.getEstimate();
                }
            },
            data: function() {
                return {
                    rec : ""
                }
            },
            created: function mounted() {
                this.getEstimate();
            },
            methods : {
                getEstimate : function getEstimate() {
                    $scope = this;

                    $.get( "{{ route('get_expense_details_ajax') }}/", { id: this.id } )
                        .done(function( response ) {

                            if(response.status == 1)
                            {
                                $scope.rec = response.records ;
                                $scope.$emit('item_records', { record : response.records} );
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

            el: '#expense',
            components: {
                expenseDetails : expenseDetails
            },
            data: {
                id : "",
                item_status :{
                    id : "",
                    name : ""
                },
                records : "",
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

                itemRecords : function ($rec) {
                    if($rec.status)
                    {
                        this.itemStatus($rec.status);
                    }
                    this.records = $rec.record;


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

                    $scope.currentView = 'expense_details';
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
    </script>
@endsection