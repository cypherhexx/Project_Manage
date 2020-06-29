@extends('layouts.main')
@section('title', (isset($rec->id)) ? __('form.edit_invoice') : __('form.create_new_invoice'))
@section('content')
<style>
  [v-cloak] {
    display: none;
  }
</style>

    <div id="sales" v-cloak>

    <div v-if="unbilledTasks.length > 0" style="background-color: #fff; padding: 20px; margin-bottom: 20px;">
        
        
        <div class="row">
                <div class="col-md-6"><h5>@lang('form.unbilled_tasks_and_expenses')</h5></div>
                <div class="col-md-6 text-right">
                    <a href="#" style="color: red;" v-on:click.prevent="unbilledTasks = []"><i class="fas fa-times"></i></a>
                </div>
         </div>   
        <table class="table table-sm" style="font-size: 13px;">
              <thead>
                <tr>
                  <th scope="col">@lang('form.task')</th>
                  <th scope="col" class="text-right">@lang('form.rate')</th>
                  <th scope="col" class="text-right">@lang('form.decimal_time')/@lang('form.quantity')</th>
                  <th scope="col" class="text-right">@lang('form.sub_total')</th>
                  <th scope="col" class="text-right">@lang('form.action')</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in unbilledTasks">
                  
                  <td><a v-bind:href="item.url_to_source" target="_blank">@{{ item.description }}</a></td>
                  <td class="text-right">@{{ item.rate }}</td>
                  <td class="text-right">@{{ item.quantity }}</td>                  
                  <td class="text-right">@{{ item.formatted_sub_total }}</td>
                  <td class="text-right"><a href="#" v-on:click.prevent="add_unbilled_task_to_invoice(item, index)" target="_blank">@lang('form.add_to_invoice')</a></td>
                </tr>
                
              </tbody>
        </table>

      
    </div>    

    <form method="post" action="{{ (isset($rec->id)) ? route( 'patch_invoice', $rec->id) : route('post_invoice') }}">

        {{ csrf_field()  }}
        @if(isset($rec->id))
            {{ method_field('PATCH') }}
        @endif

        @include('invoice.partials.general')
        @include('generic.items')
        @include('invoice.partials.notes')

        @if((isset($rec->proposal_id)) && $rec->proposal_id)
            <input type="hidden" name="proposal_id" value="{{ $rec->proposal_id }}" />
        @endif

        @if((isset($rec->estimate_id)) && $rec->estimate_id)
            <input type="hidden" name="estimate_id" value="{{ $rec->estimate_id }}" />
        @endif

        @if((isset($rec->expense_id)) && $rec->expense_id)
            <input type="hidden" name="expense_id" value="{{ $rec->expense_id }}" />
        @endif

        @if((isset($rec->invoicing_for_project)) && $rec->invoicing_for_project)
            <input type="hidden" name="invoicing_for_project" value="{{ $rec->invoicing_for_project }}" />
        @endif

    </form>
    </div>
@endsection
@section('onPageJs')

    <script>
        vm = ""; <?php // <<< Need this vm to trigger vue function below, after a customer is selected; the is located in generic.items_js ?>

        $(function () {

         

            var customer_id = $( ".customer_id" );

            customer_id.select2( {
                theme: "bootstrap",
                minimumInputLength: 2,
                maximumSelectionSize: 6,
                placeholder: "{{ __('form.select_and_begin_typing') }}",
                allowClear: true,
                "language": {
                   "noResults": function(){
                       return "<?php echo __('form.no_results_found') ?>";
                   }
               },


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

                    return obj.name || "<?php echo __('form.searching'); ?>" ;
                },
                templateSelection: function (obj) {

                    if(obj && obj.email)
                    {
                        $("input[name=send_to]").val(( obj.contact_name) ? obj.contact_name : obj.name );
                        $("input[name=email]").val(obj.email);
                        $("input[name=phone]").val(obj.phone);

                        $("textarea[name=address]").val(obj.address);
                        $("input[name=city]").val(obj.city);
                        $("input[name=state]").val(obj.state);
                        $("input[name=zip_code]").val(obj.zip_code);

                        $("textarea[name=shipping_address]").val(obj.address);
                        $("input[name=shipping_city]").val(obj.city);
                        $("input[name=shipping_state]").val(obj.state);
                        $("input[name=shipping_zip_code]").val(obj.zip_code);


                        $("select[name=currency_id]")
                        .select2({
                            theme: "bootstrap",
                            placeholder: function(){
                                $(this).data('placeholder');
                            },
                            maximumSelectionSize: 6
                        }).val(obj.currency_id).trigger("change");



                        $("select[name=country_id]").select2({
                            theme: "bootstrap",
                            placeholder: function(){
                                $(this).data('placeholder');
                            },
                            maximumSelectionSize: 6
                        }).val(obj.country_id).trigger("change");


                         $("select[name=shipping_country_id]").select2({
                            theme: "bootstrap",
                            placeholder: function(){
                                $(this).data('placeholder');
                            },
                            maximumSelectionSize: 6
                        }).val(obj.shipping_country_id).trigger("change");
                         
                    }

                    return obj.name ||  obj.text
                }

            } );


            $( ".customer_id" ).on('select2:select', function(selection){
            
                    vm.customerSelected( $(this).val() );
                    $(".project_id").val(null).trigger('change');
            });
            $( ".customer_id" ).on('select2:unselect', function(selection){
            
                    vm.customerSelected( $(this).val() );
            });

            // Project
            var project_id = $( ".project_id" );
           

            project_id.select2( {
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
                            customer_id : $( ".customer_id" ).val()
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

        });
    </script>
@include('generic.items_js')
@endsection