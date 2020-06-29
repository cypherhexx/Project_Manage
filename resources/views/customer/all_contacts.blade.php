@extends('layouts.main')
@section('title', __('form.customer_contacts'))
@section('content')
<style>
    .hide-content{
        display: none;
    }
    .scroller{
        overflow-y: auto; max-height: 450px;
    }

    .fa-star {
   cursor: pointer;
   color: transparent;
   -webkit-text-stroke-width: 1px;
   -webkit-text-stroke-color: orange;
   font-size: 15px;
}

.fa-star:hover {
   color: orange;
}

.star-important {
   color: orange;
}

</style>

<div id="contacts" v-cloak>
   <div class="white-background">
      <div class="row">
         <div class="col-md-6">
            <h5>@lang('form.customer_contacts')</h5>
         </div>
         <div class="col-md-6">
            <div class="float-md-right">
               @if(check_perm('customers_create'))  
               <a class="btn btn-primary btn-sm" href="{{ route('add_customer_page') }}">@lang('form.new_customer')</a>      
               <a class="btn btn-primary btn-sm" href="{{ route('import_customer_page') }}">@lang('form.import_customers')</a>
               @endif
               @if(check_perm('customers_view'))  
               <a class="btn btn-primary btn-sm" href="{{ route('customers_list') }}">@lang('form.customers')</a>
               <a v-if="layout.left_pane =='col-md-12'" v-on:click.prevent="toggleWindow('col-md-5')" class="btn btn-secondary btn-sm" href="#"><i class="fas fa-angle-double-left"></i></a>
               <a v-if="layout.left_pane =='col-md-5'" v-on:click.prevent="toggleWindow('col-md-12')" class="btn btn-secondary btn-sm" href="#"><i class="fas fa-angle-double-right"></i></a>      
               @endif           
            </div>
         </div>
      </div>
   </div>
   <br>
   <div class="row">
      <div  v-bind:class="layout.left_pane" >
         <div class="main-content" v-bind:class="{ 'scroller': (layout.left_pane == 'col-md-5') }" >
            <table class="table dataTable no-footer dtr-inline collapsed" width="100%" id="data">
               <thead>
                  <tr>
                     <th>@lang("form.full_name")</th>
                     <th>@lang("form.email")</th>
                     <th>@lang("form.customer")</th>
                     <th>@lang("form.phone")</th>
                     <th>@lang("form.position")</th>
                  </tr>
               </thead>
            </table>
         </div>
      </div>
      <div  v-bind:class="layout.right_pane">
         <div class="white-background" v-if="Object.keys(records).length > 0">
            @include('customer.contacts.profile')
         </div>
      </div>
   </div>
</div>
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
        pageLength: 10,
        ordering: false,
        "columnDefs": [
            // { className: "text-right", "targets": [1] },
            // { className: "text-center", "targets": [4] }
                { responsivePriority: 1, targets: 0 },
                { responsivePriority: 2, targets: 2 },



        ],
        "ajax": {
            "url": '{!! route("datatables_customer_contacts_all") !!}',
            "type": "POST",
            'headers': {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
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



   



        var vm = new Vue({

            el: '#contacts',
            components: {
                
            },
            data: {
                id : "",
                records : [],           
                layout : {
                    left_pane : 'col-md-12',
                    right_pane : 'hide-content'
                },
                
            },

           
            methods: {
                

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
                get_contact_information: function get_contact_information(id){

                    var postData = { "_token": "{{ csrf_token() }}", contact_id : id };
                    $scope = this;
                    $.post( "{{ route('get_customer_contact_details_for_displaying') }}", postData).done(function( response ) {
                
                        $scope.records = response.data;

                    });

                },
                showInformation : function showInformation(dataTable, Id) {

                    $scope = this;

                    $scope.id = Id;


                    $scope.layout = {
                        left_pane  : 'col-md-5',
                        right_pane : 'col-md-7'
                    };

                    

                    
                    //dataTable.recalculate();
                    skipAjax = false;
                    dataTable.draw('page');

                    $scope.get_contact_information(Id);

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