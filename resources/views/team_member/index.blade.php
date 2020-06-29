@extends('layouts.main')
@section('title', __('form.team_members'))
@section('content')
<div class="main-content">
   <div class="row">
      <div class="col-md-6">
         <h5>@lang('form.team_members')</h5>
      </div>
      <div class="col-md-6">
         <div class="float-md-right">       
            @if(check_perm('team_members_create'))
            <a class="btn btn-primary btn-sm" href="{{ route('add_team_member_page') }}">@lang('form.new_member')</a>
            @endif
         </div>
      </div>
   </div>
   <hr>
   @if(Session::has('validation_message'))
        <p class="alert alert-danger"><?php echo Session::get('validation_message'); ?></p>
    @endif
   <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
      <thead>
         <tr>
            <th>@lang("form.name")</th>
            <th>@lang("form.ID")</th>
            <th>@lang("form.job_title")</th>
            <th>@lang("form.email")</th>
            <th>@lang("form.phone")</th>
            <!-- <th>@lang("form.active")</th> -->
   
            <th>@lang("form.role")</th>
         </tr>
      </thead>
   </table>
   <div id="delete_team_member_modal" class="modal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title">Delete Team Member</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
               <form id="team_member_delete_form" method="POST" action="{{ route( 'delete_team_member') }}">
                  {{ csrf_field()  }}
                  <input type="hidden" name="user_to_delete" value="">
                  <p>@lang('form.team_member_delete_notice')</p>                 

                  <div class="form-group">
                     <label>@lang('form.assign_to') <span class="required">*</span></label>
                     <?php
                        echo form_dropdown('assigned_to', [] , "", "class='form-control team_members'");
                        ?>
                  </div>
                  <div class="assign_to_error_msg d-block invalid-feedback"></div>
               </form>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('form.close')</button>
               <button type="button" class="btn btn-danger" id="confirm_delete">@lang('form.confirm')</button>
            </div>
         </div>
      </div>
   </div>
</div>
@endsection
@section('onPageJs')
    <script>

        $(function() {

            $('#data').DataTable({
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
                responsive: true,
                processing: true,
                serverSide: true,
                //iDisplayLength: 5
                pageLength: {{ data_table_page_length() }},
                ordering: false,
                "columnDefs": [
                    // { className: "text-right", "targets": [2,4] },
                    { className: "text-center", "targets": [4] }


                ],
                "ajax": {
                    "url": '{!! route("datatables_team_members") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }
            }).
            on('mouseover', 'tr', function() {
                jQuery(this).find('div.row-options').show();
            }).
            on('mouseout', 'tr', function() {
                jQuery(this).find('div.row-options').hide();
            });


            $('#delete_team_member_modal').on('show.bs.modal', function (event) {
                
                load_team_member_dropdown();
            });




        });


        $(document).on('change','.customer_status',function(e){

            e.preventDefault();
            var id = $(this).data('id');

            $.post( "{{ route("change_customer_status") }}", {
                "_token": "{{ csrf_token() }}",
                id : id,
                inactive : (this.checked) ? '' : 1

            });      
        });



        $(document).on('click','.delete_team_member',function(e){

            e.preventDefault();
            var team_member_id = $(this).attr('href');
            $('#delete_team_member_modal .modal-body input[name=user_to_delete]').val(team_member_id);
            $('#delete_team_member_modal').modal('show');
            
        });

 

        $(document).on('click','#confirm_delete',function(e){

                e.preventDefault();

                var assigned_to = $('#delete_team_member_modal .modal-body select[name=assigned_to]').val();
                if(!assigned_to)
                {
                    $('.assign_to_error_msg').html("{{ __('form.please_select_a_user') }}");
                }
                else
                {
                    swal({
                          title: global_config.txt_delete_confirm_title ,
                          text:  global_config.txt_delete_confirm_text ,
                          icon: "warning",
                          buttons: {
                                    cancel: {
                                      text: global_config.txt_btn_cancel ,
                                      value: null,
                                      visible: true,
                                      className: "",
                                      closeModal: true,
                                    },
                                    confirm: {
                                      text: global_config.txt_yes ,
                                      value: true,
                                      visible: true,
                                      className: "",
                                      closeModal: true
                                    }
                                  },
                          dangerMode: true,
                          
                        }).then(function (willDelete) {
                          if (willDelete) 
                          {                        
                                $('#team_member_delete_form').submit();                   
                          }
                          else
                          {
                               $('#delete_team_member_modal').modal('toggle');            
                          }

                    });
                }

                

        });



        function load_team_member_dropdown()
        {
            $('.assign_to_error_msg').html("");
            
            var team_members = $( ".team_members" );


            team_members.select2( {
                theme: "bootstrap",
                dropdownParent: $("#delete_team_member_modal"),
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
                    url: '{{ route("search_team_member") }}',
                    data: function (params) {
                        return {
                            search: params.term,
                            user_to_delete : $('#delete_team_member_modal .modal-body input[name=user_to_delete]').val()
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

                    return obj.name ||  obj.text
                }

            } );

        }

    </script>
@endsection