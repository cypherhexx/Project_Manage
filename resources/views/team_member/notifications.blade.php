<?php

  $s =  (is_current_user($rec->id)) ? __('form.my_account') : __('form.team_member');
?>
@section('title',  $s . " : ". $rec->first_name)

<div class="main-content" style="margin-bottom: 10px !important">
    @include('team_member.partials.profile_menu')
</div> 

<div class="row">

    <div class="col-md-3 col-sm-4">
       @include('team_member.partials.profile_photo')
    </div>


    <div class="col-sm-8 col-md-9">

        <div class="main-content">

        
            <div class="row">
                <div class="col-md-12">
                    
                    <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
                        <thead>
                        <tr>
                            <th>@lang("form.description")</th>
                            <th>@lang("form.time")</th> 
                            <th>@lang("form.status")</th>                             
                        </tr>
                        </thead>
                    </table>
                        


                </div>  
            </div>
        </div>


    
        

     </div>   

</div>


@section('innerPageJS')
    <script>
        $(function() {

      
            
        $('#data').DataTable({
                // "bInfo" : false,
                "bLengthChange": false,
                "language": {
                                "infoFiltered": ""
                },

                searching: false,
                responsive: true,
                processing: true,
                serverSide: true,
    
                pageLength: {{ data_table_page_length() }},
                ordering: false,
                
                "ajax": {
                    "url": '{!! route("datatable_member_notifications") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }
            });

    });

</script>
@endsection