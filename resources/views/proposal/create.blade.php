@extends('layouts.main')
@section('title', (isset($rec->id)) ? __('form.proposal') :  __('form.new_proposal') )
@section('content')
    <div id="sales" style="margin-bottom: 20%;">
    <form method="post" action="{{ (isset($rec->id)) ? route( 'patch_proposal', $rec->id) : route('post_proposal') }}">

        {{ csrf_field()  }}
        @if(isset($rec->id))
            {{ method_field('PATCH') }}
        @endif

        @include('proposal.partials.general')
        @include('generic.items')



    </form>
    </div>
@endsection
@section('onPageJs')

    <script>
        $(function () {
            $('.related_to').change(function () {

                $('.component_number')
                    .find('option')
                    .remove()
                    .val("");

                var labelText = $(this).find("option:selected").text();

                var labelElement = $("label[for*='component_number']");

                ($(this).val()) ? labelElement.html(labelText + ' <span class="required">*</span>') : labelElement.html("&nbsp");
            });


            var selectInput = $( ".component_number" );

            selectInput.select2( {
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
                    url: '{{ route("related_component") }}',
                    data: function (params) {
                        return {
                            search: params.term,
                            type:  $('.related_to').val()

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

                    $('.component_number')
                        .find('option')
                        .remove()
                        .val("");

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



                        $("select[name=country_id]").select2({
                            theme: "bootstrap",
                            placeholder: function(){
                                $(this).data('placeholder');
                            },
                            maximumSelectionSize: 6
                        }).val(obj.country_id).trigger("change");
                    }

                    return obj.name ||  obj.text
                }

            } );

        });
    </script>
    @include('generic.items_js')
@endsection