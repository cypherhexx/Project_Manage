@extends('layouts.main')
@section('title', __('form.teams'))
@section('content')
<div class="main-content">
   <div class="row">
      <div class="col-md-6">
         <h5>@lang('form.teams')</h5>
      </div>
      <div class="col-md-6">
         <div class="float-md-right">
            @if(check_perm('teams_create'))
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal">
            @lang('form.new_team')
            </button>
            @endif
         </div>
      </div>
   </div>
   <hr>
   <!-- Modal -->
   <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalCenterTitle">@lang('form.team')</h5>
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
               </button>
            </div>
            <div class="modal-body">
               <form id="myModalForm" action="" method="POST">
                  <input type="hidden" name="id" value="">
                  <div class="form-group">
                     <label>@lang('form.name') <span class="required">*</span></label>
                     <input type="text" class="form-control form-control-sm" name="name">
                     <div class="error name"></div>
                  </div>
                  <div class="form-group ">
                     <label>@lang('form.team_leader')</label>
                     <?php
                        echo form_dropdown('leader_user_id', $data['users_list'] , '', "class='form-control  selectpicker'");
                        ?>
                  </div>
                  <div class="form-group">
                     <label for="group_id">@lang('form.members')</label>
                     <div class="select2-wrapper">
                        <?php echo form_dropdown("member_id[]", $data['users_list'] , '', "class='form-control form-control-sm select2-multiple' multiple='multiple'") ?>
                     </div>
                     <div class="invalid-feedback d-block">@php if($errors->has('member_id')) { echo $errors->first('member_id') ; } @endphp</div>
                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('form.close')</button>
               <button type="button" class="btn btn-primary" id="submitForm">@lang('form.submit')</button>
            </div>
         </div>
      </div>
   </div>
   <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
      <thead>
         <tr>
            <th>@lang("form.name")</th>
            <th>@lang("form.leader")</th>
            <th>@lang("form.members")</th>
         </tr>
      </thead>
   </table>
</div>
@endsection


@section('onPageJs')
    <script>
        $(function() {

            var dataTable = $('#data').DataTable({
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
                // "columnDefs": [
                //     { className: "text-right", "targets": [2,4] },
                //     { className: "text-center", "targets": [5] }
                //
                //
                // ],
                "ajax": {
                    "url": '{!! route("teams_list") !!}',
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


            


            $('#myModal').on('shown.bs.modal', function () {

                $( ".selectpicker" ).select2( {
                    theme: "bootstrap",
                    placeholder: function(){
                        $(this).data('placeholder');
                    },
                    maximumSelectionSize: 6
                } );

               $( ".select2-multiple" ).select2( {
                    theme: "bootstrap",
                    placeholder: "Nothing Selected",
                    maximumSelectionSize: 6
                } );

            });

            $('#myModal').on('hidden.bs.modal', function (e) {

                $('.error').html("");
                $("#myModal").find("input[type=text], textarea, input[type=hidden], select").val("");
            });

            $( "input[type=text], textarea" ).focus(function() {

                $(this).next('.error').html("");
            });


            $('#submitForm').click(function (e) {
                e.preventDefault();

                var id = $('input[name=id]').val();

                var url = (id) ? "{{ route("patch_team") }}" : "{{ route("post_team") }}";

                var postData = $('#myModalForm').serializeArray();
                postData.push({ "name": "_token", "value" : "{{ csrf_token() }}" });


                $.post( url , postData )
                    .done(function( response ) {
                        if(response.status == 2)
                        {

                            $.each(response.errors, function( index, value ) {

                                $('.' + index).html(value.join());
                            });


                        }
                        else
                        {
                            dataTable.draw();

                            $("#myModal").find("input[type=text], textarea, input[type=hidden], select").val("");

                            $('#myModal').modal('hide');
                        }
                    });



            });






        });

        $(document).on('click','.edit_item',function(e){
            //  $(this) = your current element that clicked.
            // additional code
            e.preventDefault();
            var id = $(this).data('id');

            $.post( "{{ route("get_information_team") }}", { "_token": "{{ csrf_token() }}", id : id})
                .done(function( response ) {
                    if(response.status == 1)
                    {

                        var obj = response.data;
                        $('input[name=id]').val(obj.id);

                        $('input[name=name]').val(obj.name);
                        $('select[name=leader_user_id]').val(obj.leader_user_id);
                        //$('select[name=member_id]').val(obj.member_id);

                        $( ".select2-multiple" ).select2().val(obj.member_id).trigger("change");
                        $('#myModal').modal('show');


                    }
                    else
                    {

                    }
                });


        });
    </script>
@endsection