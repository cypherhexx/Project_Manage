@extends('layouts.main')

@section('content')
    @php
        $route_name = Route::currentRouteName();
        $group_name = ($route_name == 'edit_team_member_page' || $route_name == 'add_team_member_page') ? 'show_form' : app('request')->input('group');

  

    @endphp

    @if($group_name == '')
        @include('team_member.profile')
    @elseif($group_name == 'show_form')
        @include('team_member.form')
    @elseif($group_name == 'tasks')
        @include('team_member.tasks')
    @elseif($group_name == 'notifications')
        @include('team_member.notifications')
    @elseif($group_name == 'change-password' && (auth()->user()->id == $rec->id))
        @include('team_member.change_password')    
    @endif                

  
@endsection

@section('onPageJs')

    @yield('innerPageJS')

    <script type="text/javascript">
        $(function(){

            $('.upload_photo').click(function(e){
                e.preventDefault();
                $('#file').focus().trigger("click");
            });

            @if(isset($rec->id))
                $('#file').change(function(){

                        var fd = new FormData();
                        var files = $('#file')[0].files[0];
                        fd.append('file',files);
                        fd.append('_token', "{{ csrf_token() }}");
                        fd.append('member_id', "{{ $rec->id }}");
                        
                        $('.uploading_spinner').show();

                        // AJAX request
                        $.ajax({
                          url: '{{ route("team_member_change_photo", $rec->id) }}',
                          type: 'post',
                          data: fd,
                          contentType: false,
                          processData: false,
                          success: function(response){

                            $('.uploading_spinner').hide();
                            if(response.status == 1)
                            {                        
                              $('.member-avatar').attr("src", response.file_url);
                            }
                            else
                            {                         
                                swal(response.msg);
                            }
                          }
                        });
                });
            @endif

        });
    </script>

@endsection