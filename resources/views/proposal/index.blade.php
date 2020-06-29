@extends('layouts.main')
@section('title', __('form.proposals'))
@section('content')


<div id="proposal" v-cloak>

        <!-- Modal -->
<div class="modal fade" id="sendEmailModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">{{ sprintf(__('form.send___to_email'), __('form.proposal')) }}</h5>        
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <small id="emailHelp" class="form-text text-muted">Will be sent to the primary contact of the customer</small>
        
            <form method="post" action="{{ route( 'proposal_send_to_email') }}">
                {{ csrf_field()  }}
                <input type="hidden" name="proposal_id" id="proposal_id" value="">
              <div class="form-group">
                <label>@lang('form.cc')</label>
                <input type="email" class="form-control" name="email_cc">                
              </div>
              <div class="custom-control custom-checkbox">
                  <input checked type="checkbox" class="custom-control-input" name="add_attachment" value="1" id="customCheck1">
                  <label class="custom-control-label" for="customCheck1">@lang('form.attach_proposal_as_pdf')</label>
              </div>
              <hr>
              <div class="form-group">
                <label for="exampleInputPassword1">Preview Template</label>
                <textarea class="form-control" id="email_template" name="email_template"><?php echo nl2br($data['email_template']); ?></textarea>
              </div>
           
            </form>

      </div>
      <div class="modal-footer">
        
        <button type="button" class="btn btn-primary" v-on:click.prevent="send_to_email()">@lang('form.send')</button>
      </div>
    </div>
  </div>
</div>

        

        <div class="white-background">

            <div class="row">
              <div class="col-md-6">
                 <h5>@lang('form.proposals')</h5>
              </div>
              <div class="col-md-6">
                 <div class="float-md-right">
                        @if(check_perm('proposals_create'))
                            <a class="btn btn-primary btn-sm" href="{{ route('add_proposal_page') }}" role="button">@lang('form.new_proposal')</a>
                        @endif

                        @if(check_perm(['proposals_view', 'proposals_view_own']))                  
                            <a v-if="layout.left_pane =='col-md-12'" v-on:click.prevent="toggleWindow('col-md-5')" class="btn btn-secondary btn-sm" href="#"><i class="fas fa-angle-double-left"></i></a>
                            <a v-if="layout.left_pane =='col-md-5'" v-on:click.prevent="toggleWindow('col-md-12')" class="btn btn-secondary btn-sm" href="#"><i class="fas fa-angle-double-right"></i></a>                    
                        @endif

                  </div>  
              </div>
           </div>
        </div>

        <br>

@if(check_perm(['proposals_view', 'proposals_view_own']))
        <div class="row">

            <div  v-bind:class="layout.left_pane">
                <div class="main-content">

                    @include('proposal.filter')
                    <table class="table dataTable no-footer dtr-inline collapsed" width="100%" id="data">
                        <thead>
                        <tr>
                            <th>@lang("form.proposal_#")</th>
                            <th>@lang("form.title")</th>
                            <th>@lang("form.to")</th>
                            <th>@lang("form.total")</th>
                            <th>@lang("form.date")</th>
                            <th>@lang("form.open_till")</th>
                            <th>@lang("form.tags")</th>
                            <th>@lang("form.date_created")</th>
                            <th>@lang("form.status")</th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>

            <div  v-bind:class="layout.right_pane">
                <div class="main-content">

                    <ul class="nav nav-tabs">
                        <li class="nav-item">
                            <a class="nav-link" v-bind:class="{'active':(currentView === 'proposal_details' )}" href="#" @click.prevent="currentView='proposal_details'">@lang('form.proposal')</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" v-bind:class="{'active':(currentView === 'proposal_items' )}" href="#" @click.prevent="currentView='proposal_items'">@lang('form.proposal_items')</a>
                        </li>



                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tooltip" data-placement="top" title="{{ __('form.toggle_full_view') }}" href="#" v-on:click.prevent="toggleExpand()"><i class="fas fa-expand"></i></a>
                        </li>

                    </ul>

                    <br>

                    <div class="row">
                        <div class="col-md-2">
                            <button type="button" class="btn btn-sm btn-outline-primary">@{{ item_status.name }}</button>

                        </div>
                        <div class="col-md-10">
                            <div class="float-md-right">

                                <a v-bind:href="'{{ route('edit_proposal_page') }}/'+ id" data-toggle="tooltip" data-placement="top" title="{{ __('form.edit') }}" class="btn btn-sm btn-outline-info"><i class="far fa-edit"></i></a>


                                <div class="btn-group" role="group">
                                    <button id="btnGroupDrop1" type="button" class="btn btn-sm btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="far fa-file-pdf"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop1">

                                        <a target="_blank" v-bind:href="'{{ route('download_proposal') }}/'+ id" class="dropdown-item">@lang('form.download')</a>

                                    </div>
                                </div>


                                <a href="#" v-on:click.prevent="open_send_to_email_modal(id)" data-toggle="tooltip" data-placement="top" title="{{ __('form.send_to_email') }}" class="btn btn-sm btn-outline-info"><i class="fas fa-envelope"></i></a>


                                <div class="btn-group" role="group">
                                    <button id="btnGroupDrop1" type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        @lang('form.more')
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop1">
                                        <a target="_blank" v-bind:href="url_to_proposal_customer_view" class="dropdown-item">{{ sprintf(__('form.view_as_customer'), __('form.proposal')) }}</a>
                                        <span v-if="!records.hide_status_dropdown">
                                        @if(check_perm('proposals_edit') && isset($rec['item_statuses']) && (count($rec['item_statuses']) > 0))
                                            @foreach($rec['item_statuses'] as $item_status)
                                                <a v-if="item_status.id != {{ $item_status->id }}" v-on:click.prevent="changeStatus({{ $item_status->id }})" class="dropdown-item" data-item-id="{{ $item_status->id }}" href="#">@lang('form.mark_as') {{ $item_status->name }}</a>
                                            @endforeach
                                        @endif
                                        </span>
                                        @if(check_perm('proposals_delete'))
                                        <a v-bind:href="'{{ route('delete_proposal') }}/'+ id" style="color: red;" class="dropdown-item delete_item">@lang('form.delete_proposal')</a>
                                        @endif
                                    </div>
                                </div>

                                <a v-if="records.hide_status_dropdown" v-bind:href="records.link_to_converted_component" class="btn btn-primary btn-sm">@{{ records.link_text }}</a>

                                @if(check_perm(['invoices_create', 'estimates_create']))
                                <div class="btn-group" role="group" v-if="!records.hide_status_dropdown">
                                    <button id="convertButton" v-bind:disabled="!records.is_customer" id="btnGroupDrop1" type="button" class="btn btn-sm btn-outline-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        @lang('form.convert')
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop1">
                                        @if(check_perm('estimates_create'))
                                        <a v-bind:href="'{{ route('convert_to_estimate_from_proposal') }}/'+ id" class="dropdown-item">@lang('form.estimate')</a>
                                        @endif

                                        @if(check_perm('invoices_create'))
                                        <a v-bind:href="'{{ route('convert_to_invoice_from_proposal') }}/'+ id"  class="dropdown-item">@lang('form.invoice')</a>
                                        @endif

                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                    </div>

                    <hr>

                    <component :is="currentView" @changed_to_default_view="defaultView" @change_layout="changeLayout" @item_records="itemRecords"  v-bind:id="id" transition="fade" transition-mode="out-in"></component>
                </div>
            </div>
        </div>
    
    </div>

    <template id="proposal_items_template" >
        <div>
            <br>
            <span v-html="rec"></span>
        </div>
    </template>


    <template id="proposal_template" >
        <div>
            <br>
            <span v-html="rec"></span>

            <hr>
            <a v-on:click.prevent="shortCodeList" href="#" >@lang('form.available_short_codes')</a>
            <hr>
            <div style="display: none;" id="proposal_short_codes">
                @include('proposal.partials.show.proposal_short_codes')
            </div>

            <br>
            <editor id="editor1" v-model="editorContent" :init="proposalEditorInit"></editor>
            <div class="form-text text-muted" v-html="statusBarText"></div>
        </div>
    </template>
@endif    

@endsection

@section('onPageJs')

    @include('generic.select2_vue_single_js')
    @include('generic.datepicker_vue_js')
    <script>


        $(function () {

            var skipAjax = false, // flag to use fake AJAX
                skipAjaxDrawValue = 0; // draw sent to server needs to match draw returned by server

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
                    { className: "text-right", "targets": [1,2] }
                    // { className: "text-center", "targets": [5] }




                ],
                "ajax": {
                    "url": '{!! route("datatables_proposal") !!}',
                    "type": "POST",
                    'headers': {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    data: function(data) { //this function allows interaction with data to be passed to server
                        if (skipAjax) { //if fake AJAX flag is set
                            skipAjaxDrawValue = data.draw; //get draw value to be sent to server
                        }
                         data.status_id                 = $("select[name=status_id]").val();
                        return data; //pass on data to be sent to server
                    },
                    beforeSend: function(jqXHR, settings) { //this function allows to interact with AJAX object just before data is sent to server
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

            $('#datatableFitler select').change(function(){

                dataTable.draw();
            });

            dataTable.on( "click", ".showInformation", function(e) {
                e.preventDefault();

                var id = $(this).data('id');
                updateQueryStringParam('id', id);
                vm.showInformation(dataTable, id);
            });
        });

        Vue.directive('numericOnly', {
            bind: function bind(el) {
                el.addEventListener('keyup', function () {
                    var regex = /^[0-9]*$/;
                    if (!regex.test(el.value)) {
                        el.value = el.value.slice(0, -1);
                    }
                });
            }
        });



        var proposalItems = Vue.component('proposal_items', {
            template: '#proposal_items_template',
            props: ['id'],
            watch : {
                id : function (newVal, oldVal) {
                    this.getProposalItems();
                }
            },
            data: function() {
                return {
                    rec : ""
                }
            },
            created: function mounted() {
                this.getProposalItems();
            },
            methods : {
                getProposalItems : function getProposal() {
                    $scope = this;

                    $.get( "{{ route('get_proposal_items_ajax') }}/", { id: this.id } )
                        .done(function( response ) {

                            if(response.status == 1)
                            {
                                $scope.rec = response.html ;
                                // $scope.$emit('item_status', response.item_status );
                            }
                            else
                            {
                                $scope.rec = "";
                            }


                        }).fail(function() {
                        $scope.rec = "";
                    });

                }


            }
        });
        tinyMceScope = "";
        editorChangeHandler = "";
        lastSaved = false;
        var proposalDetails = Vue.component('proposal_details', {
            template: '#proposal_template',
            props: ['id'],
            watch : {
                id : function (newVal, oldVal) {
                    this.getProposal();
                }
            },
            data: function() {
                $scope  = this;
                return {
                    rec : "",                    
                    statusBarText : "",
                    proposalEditorInit : {
                        branding: false,
                        theme: "modern",
                        paste_data_images: true,

                        plugins: [
                            "advlist autolink lists link image charmap hr anchor pagebreak",
                            "wordcount visualblocks visualchars code fullscreen",
                            "nonbreaking save table contextmenu",
                            "paste textcolor colorpicker autoresize"
                        ],
                        width: '100%',
                        height: "auto",
                        autoresize_min_height: 400,
                        autoresize_max_height: "auto",

                        setup: function(editor) {
                            // on Focusing the Editor, Expand the layout
                            editor.on('focus', function(e) {

                                $scope.$emit('change_layout', ['hide-content', 'col-md-12']);
                            });

                            // editor.on('keyup', function (e) {
                            //     $scope.tinyMceChange(editor);
                            // });
                            editor.on('change', function(e) {
                                if($.isFunction($scope.tinyMceChange))
                                {
                                    $scope.tinyMceChange(editor);
                                }
                                
                            });
                            editor.on('Undo', function(e) {
                                $scope.tinyMceChange(editor);
                            });
                            editor.on('Redo', function(e) {
                                $scope.tinyMceChange(editor);
                            });
                            //  editor.on('keyup', function(e) {
                            //     $scope.tinyMceChange(editor);
                            // });
                            tinyMceScope = $scope;
                            setInterval(function(){
                                 tinyMceScope.tinyMceChange(editor);
                             }, 10000);


                        },
                        toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
                        toolbar2: "media | forecolor backcolor",
                        image_advtab: true,
                        // FileManager Part
                        relative_urls: false,
                        file_browser_callback : function(field_name, url, type, win) {
                          var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                          var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;

                          var cmsURL = "{{ url('/') }}/" + 'laravel-filemanager?field_name=' + field_name;
                          if (type == 'image') {
                            cmsURL = cmsURL + "&type=Images";
                          } else {
                            cmsURL = cmsURL + "&type=Files";
                          }

                          tinyMCE.activeEditor.windowManager.open({
                            file : cmsURL,
                            title : 'Filemanager',
                            width : x * 0.8,
                            height : y * 0.8,
                            resizable : "yes",
                            close_previous : "no"
                          });
                        }
                        //End of File Manager Part
                        

                    },
                    editorContent : ""
                }
            },
            components: {
                'editor': Editor //
            },
            created: function mounted() {
                this.getProposal();




            },
            methods : {

                shortCodeClicked : function ($text) {

                    tinymce.get('editor1').execCommand('mceInsertContent', false, $text );

                },
                shortCodeList : function () {
                    $("#proposal_short_codes").slideToggle();
                },
                tinyMceChange : function (editor) {
                    $scope = this;
                    $.post( "{{ route('save_proposal_content') }}", {
                        "_token": "{{ csrf_token() }}",
                        id: $scope.id ,
                        content : editor.getContent()

                    } );
                   
                    $scope.statusBarText = "{{ __('form.last_saved_at') }} " + moment().format("h:mm:ss a"); 

                },
                getProposal : function getProposal() {
                    $scope = this;

                    $.get( "{{ route('get_proposal_details_ajax') }}/", { id: this.id } )
                        .done(function( response ) {

                            if(response.status == 1)
                            {
                                $scope.rec = response.html ;
                                
                                $scope.$emit('item_records', { record : response.records, 
                                    status : response.item_status,
                                    url_to_proposal_customer_view : response.url_to_proposal_customer_view
                                } );
                                $scope.editorContent = response.proposal_content ;
                            }
                            else
                            {
                                $scope.rec = "";
                                $scope.editorContent = "";
                            }


                        }).fail(function() {
                        $scope.rec = "";
                        $scope.editorContent = "";
                    });

                }


            }
        });


        var vm = new Vue({

            el: '#proposal',
            components: {
                proposalDetails : proposalDetails,
                proposalItems : proposalItems
            },
            data: {
                id : "",
                url_to_proposal_customer_view : "",
                item_status :{
                    id : "",
                    name : ""
                },
                records : "",
                layout : {
                    left_pane : 'col-md-12',
                    right_pane : 'hide-content'
                },

                currentView: '',
                defaultComponentView : 'proposal_details'
            },

            // Fetches data when the component is created.
            created: function created() {

            },
            mounted: function mounted() {

            },
            computed: {

            },
            methods: {
                open_send_to_email_modal : function($proposal_id){

                    $("#proposal_id").val($proposal_id);
                    
                    $('#sendEmailModal').modal('show');

                    tinymce.init({
                    selector: '#email_template',
                    branding: false,
                    height: 500,
                  });
                   
                },
                send_to_email : function(){
                    $('#sendEmailModal').find('form').submit();
                },
                convertToolTipText : function ($msg) {

                    return (this.records.is_customer != true) ? "": $msg ;

                },
                changeLayout : function (parm) {
                    this.layout.left_pane = parm[0] ;
                    this.layout.right_pane = parm[1];
                },
                defaultView : function () {

                    this.currentView = this.defaultComponentView;
                },
                changeStatus : function (statusId) {
                    $scope = this;
                    if(statusId)
                    {
                        $.post( "{{ route('ajax_change_proposal_status') }}", { id: $scope.id, status_id: statusId,  "_token": "{{ csrf_token() }}" })
                            .done(function( data ) {

                                if(data.status == 1)
                                {
                                    trigger_vue_itemStatus(data.item_status);
                                }

                            });
                    }

                },
                itemStatus: function (value) {

                    this.item_status = {
                        id : value.id,
                        name : value.name
                    }
                },
                itemRecords : function ($rec) {
                    if($rec.status)
                    {
                        this.itemStatus($rec.status);
                    }
                    this.records = $rec.record;

                    if(this.records.is_customer != true)
                    {
                        $('#convertButton').tooltip({
                            title: '{{ __('form.convert_disable_msg') }}',
                            placement: 'top',
                            trigger: 'hover'
                        });
                    }
                    this.url_to_proposal_customer_view = $rec.url_to_proposal_customer_view;

                },
                toggleExpand : function toggleExpand() {

                    $scope = this;

                    if($scope.layout.left_pane == 'col-md-5')
                    {
                        $scope.layout = {
                            left_pane : 'hide-content',
                            right_pane : 'col-md-12'
                        };

                    }
                    else
                    {
                        $scope.layout = {
                            left_pane : 'col-md-5',
                            right_pane : 'col-md-7'
                        };

                    }
                    //dataTable.recalculate();

                },
                toggleWindow : function toggleWindow($val) {
                    $scope = this;

                    if($val == 'col-md-5')
                    {
                        $scope.layout = {
                            left_pane : 'col-md-5',
                            right_pane : 'col-md-7'
                        };

                    }
                    else
                    {
                        $scope.layout = {
                            left_pane : 'col-md-12',
                            right_pane : 'hide-content'
                        };

                    }
                    skipAjax = false;
                    dataTable.draw('page');
                },
                showInformation : function showInformation(dataTable, Id) {

                    $scope = this;

                    $scope.id = Id;


                    $scope.layout = {
                        left_pane : 'col-md-5',
                        right_pane : 'col-md-7'
                    };

                    $scope.currentView = $scope.defaultComponentView;
                    //dataTable.recalculate();
                    skipAjax = false;
                    dataTable.draw('page');



                }






            }

        });


        function trigger_vue_itemStatus($data)
        {
            vm.itemStatus($data);
        }




        $(function() {


            function onPageLoad()
            {

                var $url_parameters = get_url_parameters();

                if($url_parameters.hasOwnProperty('id'))
                {
                    id = $url_parameters['id'];
                    vm.showInformation(dataTable, id);
                }
            }
            onPageLoad();


        });
    </script>
@endsection