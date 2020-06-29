<style>
    .hourselect{
        z-index: 1100 !important;
    }


</style>
<!-- Button trigger modal -->
<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#mileStoneModal">
    @lang('form.new_milestone')
</button>
<a href="#" id="switchView" class="btn btn-light btn-sm"><i class="fas fa-bars"></i> @lang('form.milestone_list')</a>
<hr>

<div id="mileStoneCanbanBoard">
    @include('project.partials.milestones_grid')
</div>


<!-- Modal -->
<div id="mileStoneModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalCenterTitle">@lang('form.milestone')</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="timeSheetModalForm" action="" method="POST">

                    <input type="hidden" name="project_id" value="{{ $rec->id }}">
                    <input type="hidden" name="id" value="">
                    <div class="form-group">
                        <label>@lang('form.name') <span class="required">*</span></label>
                        <input type="text" class="form-control form-control-sm" name="name">
                        <div class="error name"></div>

                    </div>

                    <div class="form-group">
                        <label>@lang('form.due_date') <span class="required">*</span></label>
                        <input type="text" class="form-control form-control-sm datePicker" name="due_date">
                        <div class="error due_date"></div>
                    </div>

                    <div class="form-group">
                        <label>@lang('form.description')</label>
                        <textarea class="form-control form-control-sm" rows="2" name="description"></textarea>
                        <div class="error description"></div>
                    </div>

                    <div class="form-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="show_description_to_customer" id="show_description_to_customer" value="1">
                            <label class="form-check-label">
                                @lang('form.show_description_to_customer')
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>@lang('form.order')</label>
                        <input type="text" class="form-control form-control-sm" name="order">
                        <div class="error order"></div>

                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>@lang('form.background_color')</label>
                            <input type="text" class="form-control form-control-sm" name="background_color" id="background_color">
                            <div class="error background_color"></div>
                        </div>

                        <div class="form-group col-md-6">
                            <label>@lang('form.background_text_color')</label>
                            <input type="text" class="form-control form-control-sm" name="background_text_color" id="background_text_color">
                            <div class="error background_text_color"></div>
                        </div>

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


<div id="milestoneList">

    <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="data">
        <thead>
        <tr>
            <th>@lang("form.name")</th>
            <th>@lang("form.due_date")</th>

        </tr>
        </thead>
    </table>
</div>


@section('innerPageJS')
    <script>
        $(function() {

            $('#background_color').colorpicker({
              format: 'hex'
            });
            $('#background_text_color').colorpicker({
              format: 'hex'
            });

        var skipAjax = false, // flag to use fake AJAX
            skipAjaxDrawValue = 0; // draw sent to server needs to match draw returned by server



            var dataTable = $('#data').DataTable({
                dom: 'Bfrtip',
                buttons: [

                    {
                        init: function(api, node, config) {
                            $(node).removeClass('btn-secondary')
                        },
                        className: "btn-outline-info btn-sm",
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
                    "url": '{!! route("get_project_milestones", $rec->id) !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    beforeSend: function(jqXHR, settings) { //this function allows to interact with AJAX object just before data is sent to server

                        var skipAjax = $("#milestoneList").is(":hidden");
                        if (skipAjax) { //if fake AJAX flag is set
                            var lastResponse = dataTable.ajax.json(); //get last server response
                            lastResponse.draw = skipAjaxDrawValue; //change draw value to match draw value of current request

                            this.success(lastResponse); //call success function of current AJAX object (while passing fake data) which is used by DataTable on successful response from server

                            skipAjax = false; //reset the flag

                            return false; //cancel current AJAX request
                        }
                    }

                }
            }).
            on('mouseover', 'tr', function() {
                jQuery(this).find('div.row-options').show();
            }).
            on('mouseout', 'tr', function() {
                jQuery(this).find('div.row-options').hide();
            });

            <?php if(count($rec->milestones) > 0) {?>
                $('#milestoneList').hide();
            <?php } else {?>
                $('#mileStoneCanbanBoard').hide();
            <?php } ?>    
            $('#switchView').click(function (e) {

                e.preventDefault();

                var mileStoneCanbanBoard = $("#mileStoneCanbanBoard");
                var milestoneList = $('#milestoneList');

                if(mileStoneCanbanBoard.is(":visible"))
                {
                    mileStoneCanbanBoard.hide();
                    milestoneList.show();
                }
                else
                {
                    milestoneList.hide();
                    mileStoneCanbanBoard.show();

                }


            });


            $('#mileStoneModal').on('shown.bs.modal', function () {

                $("input[name=project_id]").val("{{ $rec->id }}");
                $('.datePicker').daterangepicker({
                    parentEl: "#mileStoneModal",
                    singleDatePicker: true,


                    locale: {
                        format: 'DD-MM-YYYY'
                    }


                });

            });

            $('#mileStoneModal').on('hidden.bs.modal', function (e) {

                $('.error').html("");
                $("#mileStoneModal").find("input[type=text], textarea, input[type=hidden]").val("");
            });


            $( "input[type=text], textarea" ).focus(function() {

                $(this).next('.error').html("");
            });

            $('#submitForm').click(function (e) {
                e.preventDefault();

                var id = $('input[name=id]').val();

                var url = (id) ? "{{ route("update_project_milestone") }}" : "{{ route("add_project_milestone") }}";

                var postData = $('#timeSheetModalForm').serializeArray();
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
                            // dataTable.draw();

                            // $("#mileStoneModal").find("input[type=text], textarea").val("");

                            $('#mileStoneModal').modal('hide');

                            window.location.reload() ;
                        }
                    });



            });

            // Kanban
            <?php if(count($rec->milestones) > 0 ) { ?>
            var itemContainers = [].slice.call(document.querySelectorAll('.board-column-content'));
            var columnGrids = [];
            var boardGrid;

            // Define the column grids so we can drag those
            // items around.
            itemContainers.forEach(function (container) {

              // Instantiate column grid.
              var grid = new Muuri(container, {
                items: '.board-item',
                layoutDuration: 400,
                layoutEasing: 'ease',
                dragEnabled: true,
                dragSort: function () {
                  return columnGrids;
                },
                dragSortInterval: 0,
                dragContainer: document.body,
                dragReleaseDuration: 400,
                dragReleaseEasing: 'ease'
              })
              .on('dragStart', function (item) {
                // Let's set fixed widht/height to the dragged item
                // so that it does not stretch unwillingly when
                // it's appended to the document body for the
                // duration of the drag.
                item.getElement().style.width = item.getWidth() + 'px';
                item.getElement().style.height = item.getHeight() + 'px';
              })
              .on('dragReleaseEnd', function (item) {
                // Let's remove the fixed width/height from the
                // dragged item now that it is back in a grid
                // column and can freely adjust to it's
                // surroundings.

                // Update Information
                var element = $(item.getElement());
                var task_id = element.data('task');
                var milestone_id = element.parent('div').parent('div').data('milestone');

                

                $.post( "{{ route("task_update_milestone") }}", { 
                    "_token": "{{ csrf_token() }}", 
                    milestone_id : milestone_id,
                    task_id : task_id,
                })
                .done(function( response ) {
                    if(response.status == 1)
                    {

                        
                    }
                    else
                    {

                    }
                });


                // Just in case, let's refresh the dimensions of all items
                // in case dragging the item caused some other items to
                // be different size.
                columnGrids.forEach(function (grid) {
                  grid.refreshItems();
                });
              })
              .on('layoutStart', function () {
                // Let's keep the board grid up to date with the
                // dimensions changes of column grids.
                boardGrid.refreshItems().layout();
              });

              // Add the column grid reference to the column grids
              // array, so we can access it later on.
              columnGrids.push(grid);

            });

            // Instantiate the board grid so we can drag those
            // columns around.
            boardGrid = new Muuri('.board', {
              layout: {
                horizontal: true,
              },
              layoutDuration: 400,
              layoutEasing: 'ease',
              dragEnabled: true,
              dragSortInterval: 0,
              dragStartPredicate: {
                handle: '.board-column-header'
              },
              dragReleaseDuration: 400,
              dragReleaseEasing: 'ease'
            });
            <?php } ?>
            // End of Kanban




        });

        $(document).on('click','.edit_item',function(e){
            //  $(this) = your current element that clicked.
            // additional code
            e.preventDefault();
            var id = $(this).data('id');

            $.post( "{{ route("get_milestone_information") }}", { "_token": "{{ csrf_token() }}", milestone_id : id})
                .done(function( response ) {
                    if(response.status == 1)
                    {

                        var obj = response.data;
                        $('input[name=id]').val(obj.id);

                        $('input[name=name]').val(obj.name);
                        $('input[name=due_date]').val(obj.due_date);
                        $('textarea[name=description]').val(obj.description);
                        $('input[name=order]').val(obj.order);
                        $('input[name=background_color]').val(obj.background_color);
                        $('input[name=background_text_color]').val(obj.background_text_color);

                        if(obj.show_description_to_customer == true)
                        {
                            $('#show_description_to_customer').prop('checked', true);
                        }
                        else
                        {
                            $('#show_description_to_customer').prop('checked', false);
                        }

                        $('#mileStoneModal').modal('show');


                    }
                    else
                    {

                    }
                });


        });
    </script>
@endsection