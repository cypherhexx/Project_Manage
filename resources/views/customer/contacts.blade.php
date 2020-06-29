
<div class="main-content">

 <h5 class="float-md-left">@lang('form.contacts')</h5>

        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary btn-sm float-md-right" data-toggle="modal" data-target="#contactModal">
            @lang('form.new_contact')
        </button>
 <div class="clearfix"></div>      
  <hr>

    <!-- Modal -->
    <div id="contactModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">@lang('form.contact')</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="contactModalForm" action="" method="POST" autocomplete="off">
                        {{ csrf_field()  }}

                        <input type="hidden" name="customer_id" value="{{ $rec->id }}">
                        <input type="hidden" name="id" value="">

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>@lang('form.first_name') <span class="required">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="first_name">
                                <div class="error first_name"></div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>@lang('form.last_name') <span class="required">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="last_name">
                                <div class="error last_name"></div>
                            </div>
                        </div>


                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>@lang('form.email') <span class="required">*</span></label>
                                <input type="email" class="form-control form-control-sm" name="email">
                                <div class="error email"></div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>@lang('form.phone')</label>
                                <input type="text" class="form-control form-control-sm" name="phone">
                                <div class="error phone"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>@lang('form.position')</label>
                            <input type="text" class="form-control form-control-sm" name="position">
                            <div class="error position"></div>
                        </div>


                        <div class="form-group">
                            <label>@lang('form.password') <span class="required">*</span></label>

                            <div class="input-group input-group-sm">
                                <input type="password" class="form-control form-control-sm" name="password" id="password">
                                <div class="input-group-append">
                                    <span class="input-group-text"><a  href="#" id="fa-eye"><i class="fas fa-eye"></i></a></span>
                                    <span class="input-group-text"><a href="#" id="fa-sync"><i class="fas fa-sync"></i></a></span>
                                </div>
                            </div>


                            <small class="form-text text-muted">@lang('form.customer_contact_password_note')</small>
                            <div class="error password"></div>
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
    <!-- End of Modal -->


    <table class="table dataTable no-footer dtr-inline collapsed" width="100%" id="data">
        <thead>
        <tr>
            <th>@lang("form.full_name")</th>
            <th>@lang("form.email")</th>
            <th>@lang("form.position")</th>
            <th>@lang("form.phone")</th>
            <th>@lang("form.primary")</th>
            <th>@lang("form.active")</th>
            {{--<th>@lang("form.last_login")</th>--}}

        </tr>
        </thead>
    </table>

</div>

@section('innerPageJS')

    <script>
        $(function () {

            onPageLoad();
            

            $('#fa-eye').click(function(e){
                e.preventDefault();
                var field_type = ($("#password").attr('type') == 'password') ? 'text' : 'password';
                $("#password").attr('type', field_type );
            });

            $('#fa-sync').click(function(e){
                e.preventDefault();
                $("#password").val("");
            });

            dataTable = $('#data').DataTable({

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

                pageResize: true,
                responsive: true,
                processing: true,
                serverSide: true,
                // iDisplayLength: 5,
                pageLength: {{ data_table_page_length() }},
                ordering: false,
                "columnDefs": [
                    { className: "text-right", "targets": [1] },
                    { className: "text-center", "targets": [4] }




                ],
                "ajax": {
                    "url": '{!! route("datatables_customer_contacts") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    "data": function ( d ) {

                        d.customer_id = "{{ $rec->id }}";

                    }
                }
            }).
            on('mouseover', 'tr', function() {
                jQuery(this).find('div.row-options').show();
            }).
            on('mouseout', 'tr', function() {
                jQuery(this).find('div.row-options').hide();
            });




            var contactModal = $('#contactModal');


            contactModal.on('shown.bs.modal', function () {


                $('.error').html("");

            });

            $('#submitForm').click(function (e) {
                e.preventDefault();

                var id = $('input[name=id]').val();

                var url = (id) ? "{{ route("update_customer_contact", $rec->id) }}" : "{{ route("add_customer_contact", $rec->id) }}";


                $.post( url , $('#contactModalForm').serialize() )
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

                            contactModal.find("input[type=text], input[type=email], input[type=password]").val("");

                            contactModal.modal('hide');
                        }
                    });



            });

        });



        $(document).on('change','.primary_contact_radio',function(e){

            e.preventDefault();
            var id = $(this).data('id');
            $('.fa-check').hide();
            $('.primary_contact_radio').show();
            $(this).hide().prev('.fa-check').show();

            $.post( "{{ route("change_customer_primary_contact") }}", {
                "_token": "{{ csrf_token() }}",
                contact_id : id


            });


        });


        $(document).on('change','.contact_status',function(e){

            e.preventDefault();
            var id = $(this).data('id');

            $.post( "{{ route("change_customer_contact_status") }}", {
                "_token": "{{ csrf_token() }}",
                contact_id : id,
                inactive : (this.checked) ? '' : 1

            });


        });


        $(document).on('click','.edit_item',function(e){

            e.preventDefault();
            var id = $(this).data('id');

            show_edit_modal(id);  




        });


        function onPageLoad()
        {

            var $url_parameters = get_url_parameters();

            if($url_parameters.hasOwnProperty('id'))
            {          
                show_edit_modal($url_parameters['id']);
            }
        }

        function show_edit_modal(id)
        {
            if(id)
            {
                updateQueryStringParam('id', id);

                $.post( "{{ route('get_customer_contact_details') }}", { "_token": "{{ csrf_token() }}", contact_id : id})
                .done(function( response ) {
                    if(response.status == 1)
                    {

                        var obj = response.data;
                        $('input[name=id]').val(obj.id);

                        $('input[name=first_name]').val(obj.first_name);
                        $('input[name=last_name]').val(obj.last_name);
                        $('input[name=email]').val(obj.email);
                        $('input[name=phone]').val(obj.phone);
                        $('input[name=position]').val(obj.position);


                        $('#contactModal').modal('show');
                    }
                    else
                    {

                    }
                });
                
            }
        }

    </script>

@endsection