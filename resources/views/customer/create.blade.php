@extends('layouts.main')
<?php
$page_title = (isset($rec->id)) ? __('form.edit') . " " .__('form.customer') . " : " .$rec->name : __('form.add_new_customer');
?>
@section('title', $page_title)
@section('content')
<div class="main-content">
   <h5>{{ $page_title }}</h5>
   <hr>
   <form method="post" action='{{ (isset($rec->id)) ? route( 'patch_customer', $rec->id) : route('post_customer') }}'>
   {{ csrf_field()  }}
   @if(isset($rec->id))
   {{ method_field('PATCH') }}
   @endif
   @include('customer.partials.details')
   @if(isset($rec->lead_id))
   <input type="hidden" name="lead_id" value="{{ $rec->lead_id }}">
   <input type="hidden" name="social_links" value="{{ $rec->social_links }}">
   <input type="hidden" name="smart_summary" value="{{ $rec->smart_summary }}">
   <input type="hidden" name="photo" value="{{ $rec->photo }}">
   @endif
   <?php bottom_toolbar(__('form.submit'))?>
   </form>
</div>
@endsection


@section('onPageJs')

    <script>
        $(function(){


        $('#fa-eye').click(function(e){
                e.preventDefault();
                var field_type = ($("#password").attr('type') == 'password') ? 'text' : 'password';
                $("#password").attr('type', field_type );
            });

        $('#fa-sync').click(function(e){
            e.preventDefault();
            $("#password").val("");
        });

        

            $("#shipping_is_same_as_billing").click(function (e) {

                var inputLists = [
                    { 'name' : 'address', 'type' : 'textarea' },
                    { 'name' : 'city', 'type' : 'input' },
                    { 'name' : 'state', 'type' : 'input' },
                    { 'name' : 'zip_code', 'type' : 'input' },
                    { 'name' : 'country_id', 'type' : 'select' }

                ];


                if(this.checked)
                {

                    $.each(inputLists, function( key, row ) {

                        var selected_value = $(row.type +'[name=' + row.name + ']').val();
                        //$copied = $(row.type +'[name=' + row.name + ']').val();

                        if(row.type == 'select')
                        {
                             

                            var target_select = $(row.type +'[name=shipping_' + row.name + ']');    
                            var options = target_select.data('select2').options.options;

                            target_select.select2(options).val(selected_value).attr("disabled", "disabled");
                        }
                        else
                        {
                            $(row.type +'[name=shipping_' + row.name + ']').val(selected_value).attr("disabled", "disabled");
                        }

                        

                    });
                }
                else
                {
                    $.each(inputLists, function( key, row ) {

                        $(row.type +'[name=shipping_' + row.name + ']').removeAttr("disabled");

                        if(row.type == 'select')
                        {
                          
                            var target_select = $(row.type +'[name=shipping_' + row.name + ']');    
                            var options = target_select.data('select2').options.options;
                            target_select.select2(options).val(null).removeAttr("disabled");
                        }

                    });
                }

            });

        });
    </script>

@endsection