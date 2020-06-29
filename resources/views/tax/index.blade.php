@extends('setup.index')
@section('title', __('form.taxes'))
@section('setting_page')
<div class="main-content">
   <div class="row">
      <div class="col-md-6">
         <h5>@lang('form.taxes')</h5>
      </div>
      <div class="col-md-6">
         <div class="float-md-right">
            <!-- Button trigger modal -->
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal">
            @lang('form.new_tax')
            </button>
         </div>
      </div>
   </div>
   <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <h5 class="modal-title" id="exampleModalCenterTitle">@lang('form.tax')</h5>
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
                     <div class="invalid-feedback d-block name"></div>
                  </div>
                  <div class="form-group">
                     <label>@lang('form.rate') <span class="required">*</span> (@lang('form.without_percent_sign'))</label>
                     <input type="text" class="form-control form-control-sm" name="rate">
                     <div class="invalid-feedback d-block rate"></div>
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
   <hr>
   <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
      <thead>
         <tr>
            <th>@lang("form.name")</th>
            <th>@lang("form.rate") ({{ strtolower(__('form.percent')) }})</th>
            <th>@lang("form.options")</th>
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
                    "url": '{!! route("datatables_taxes") !!}',
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



            });

            $('#myModal').on('hidden.bs.modal', function (e) {

                $('.invalid-feedback').html("");
                $("#myModal").find("input[type=text], textarea, input[type=hidden]").val("");
            });


            $( "input[type=text], textarea" ).focus(function() {

                $(this).next('.invalid-feedback').html("");
            });

            $('#submitForm').click(function (e) {
                e.preventDefault();

                var id = $('input[name=id]').val();

                var url = (id) ? "{{ route('patch_tax') }}" : "{{ route('post_tax') }}";

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

                            $("#myModal").find("input[type=text], textarea").val("");

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

            $.post( "{{ route("get_information_tax") }}", { "_token": "{{ csrf_token() }}", id : id})
                .done(function( response ) {
                    if(response.status == 1)
                    {

                        var obj = response.data;
                        $('input[name=id]').val(obj.id);
                        $('input[name=name]').val(obj.name);
                        $('input[name=rate]').val(obj.rate);
                        $('#myModal').modal('show');


                    }
                    else
                    {

                    }
                });


        });


        $(document).on('keydown','#myModal input[name=rate]',function(evt){            
            
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || (charCode > 57 && charCode != 190 && charCode != 110)))
            {
                evt.preventDefault();
            } 
            else 
            {
                return true;
            }

        });

       

    </script>
@endsection