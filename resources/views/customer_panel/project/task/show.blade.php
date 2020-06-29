@if(check_customer_project_permission($rec->settings->permissions, 'view_tasks')) 

<style type="text/css">

.comment_form{
    display: none;
}
</style>
<?php $task = $rec->task_details ;?>
    <div>
        

        <div class="row">
            <div class="col-md-8">
                <h5>@lang('form.task') : {{ $task->title }}</h5>
                <hr>
                <ul class="nav nav-tabs">
                  <li class="nav-item">
                    <a class="nav-link {{ is_active_nav('', app('request')->input('tab') ) }}" href="{{ route('cp_show_task_page', [$rec->id, $task->id])}}">@lang('form.details')</a>
                  </li>
                  @if(check_customer_project_permission($rec->settings->permissions, 'view_timesheets')) 
                  <li class="nav-item">
                    <a class="nav-link {{ is_active_nav('timesheet', app('request')->input('tab') ) }}" href="{{ route('cp_show_task_page', [$rec->id, $task->id])}}&tab=timesheet">@lang('form.timesheet')</a>
                  </li>
                  @endif  
                </ul>


                    <?php if((app('request')->input('tab') == 'timesheet') && check_customer_project_permission($rec->settings->permissions, 'view_timesheets') ) {?>
                        @include('customer_panel.project.task.partials.show.timesheet')
                    <?php } else { ?> 
                        <br>                       
                        <small><b>@lang('form.description')</b></small>
                        <br>
                        <p style="font-size: 14px;"><?php echo nl2br($task->description); ?></p>
                        @include('customer_panel.project.task.partials.show.parent_task')
                        @include('customer_panel.project.task.partials.show.sub_tasks')                        
                        @include('customer_panel.project.task.partials.show.comments')     
                    <?php } ?>

            </div>

            <div class="col-md-4" style="background-color: #f0f5f7; margin-top: -15px; margin-bottom: -15px;">
                    @include('customer_panel.project.task.partials.show.task_information')
            </div>
        </div>

          
    </div>

@section('onPageJs')

@yield('innerPageJS')
    <script>   



        $(function () {

            
           


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
                    "url": '{!! route("cp_datatable_tasks_comments", $task->id) !!}',
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
@endif