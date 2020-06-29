@extends('layouts.main')
@section('title', __('form.task') . " : " .$rec->title )
@section('content')
 @php
        $route_name = Route::currentRouteName();
        $group_name = app('request')->input('group');
        $sub_group_name = app('request')->input('subgroup');
    @endphp

<style>

.comment_form{
    display: none;
}
</style>

    <div class="main-content">
        

        <div class="row">
            <div class="col-md-8">
                <h5>@lang('form.task') : ({{ $rec->number }}) {{ $rec->title }}</h5>
                <hr>
                <ul class="nav nav-tabs">
                  <li class="nav-item">
                    <a class="nav-link {{ is_active_nav('', $group_name) }}" href="{{ route('show_task_page', $rec->id)}}">@lang('form.details')</a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link {{ is_active_nav('timesheet', $group_name) }}" href="{{ route('show_task_page', $rec->id)}}?group=timesheet">@lang('form.timesheet')</a>
                  </li>
    
                </ul>

                    <?php if($group_name == 'timesheet' ) {?>
                        @include('task.partials.show.timesheet')
                    <?php } else { ?> 
                        <br>                       
                        <small><b>@lang('form.description')</b></small>
                        <br>
                        <p style="font-size: 14px;"><?php echo nl2br($rec->description); ?></p>
                        @include('task.partials.show.parent_task')
                        @include('task.partials.show.sub_tasks')
                        <hr>
                        @include('task.partials.show.comments')     
                    <?php } ?>

            </div>

            <div class="col-md-4" style="background-color: #f0f5f7; margin-top: -15px; margin-bottom: -15px;">
                    @include('task.partials.show.task_information')
            </div>
        </div>

          
    </div>
@endsection
@section('onPageJs')

@yield('innerPageJS')
    <script>

    function atWho()
        {
            $('.comment').atwho({
                at: "@",
                
                insertTpl: '@[${key}]',
                displayTpl: "<li>${name}</li>",
                
                callbacks: {
                    remoteFilter: function(query, callback){
                        // $.getJSON('{{ route('get_members_for_suggestion_list') }}'+query, function(data){
                        //     console.log(data);
                        //     callback(data);
                        // });
                        if(query)
                        {
                            $.getJSON('{{ route('get_members_for_suggestion_list') }}' , {q: query}, function(data) {
                                callback(data);
                            });
                            
                        }
                        
                    }
          
                }
            });
        }

        $(document).on('keypress', '.comment', function() {
            atWho();
        });



        $(function () {

            
            atWho();


        dataTable = "";
            
        dataTable = $('#data').DataTable({
                
                searching: false,
                "lengthChange": false,
                responsive: true,
                processing: true,
                serverSide: true,
                //iDisplayLength: 5
                pageLength: 10,
                ordering: false,
                // "columnDefs": [
                //     { className: "text-right", "targets": [2,4] },
                //     { className: "text-center", "targets": [5] }
                //
                //
                // ],
                "ajax": {
                    "url": '{!! route("datatable_tasks_comments", $rec->id) !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function ( d ) {
                        
                        <?php  
                            $comment = app('request')->input('comment') ; 
                            if($comment)
                            {
                              ?>

                              d.comment_id = "{{ $comment }}";

                        <?php } ?>    
                            
                            
                        
                        
                    }
                },
                "fnRowCallback": function( nRow, aData, iDisplayIndex, iDisplayIndexFull ) {
                    
                    <?php 
                        $comment = app('request')->input('comment') ; 
                            if($comment)
                            {
                              ?>                         

                              $('html, body').animate({
                                scrollTop: $("#data").offset().top
                                }, 1000);


                        <?php } ?>    

                    
                }

            });


            $('.assigned_to').change(function(){

                    var member_id = $(this).val();

                    $.post( "{{ route('assign_task', $rec->id) }}", { "_token": "{{ csrf_token() }}", member_id: member_id })
                      .done(function( data ) {
                        $.jGrowl(data.msg, { position: 'bottom-right'});
                    });


            });


        });


        $(document).on('click', '.edit_comment', function(e) {

            e.preventDefault();
            var parentDiv = $(this).parent('div').parent('div');

            parentDiv.find('.coment_area').hide();

            parentDiv.find('.comment_form').show();
        });


        $(document).on('click', '.cancel_commenting', function(e) {

            e.preventDefault();
            var parentDiv = $(this).parent('form').parent('div').parent('div');

            parentDiv.find('.coment_area').show();

            parentDiv.find('.comment_form').hide();
        });


        
    </script>

@endsection