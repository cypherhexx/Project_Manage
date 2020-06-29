@extends('layouts.main')
@section('title', (isset($rec->id)) ? __('form.edit_task'). " : " . $rec->title : __('form.new_task'))
@section('content')
<div class="main-content">
   <h5>@lang('form.task')</h5>
   <hr>
   <form id="taskForm" method="post" action="{{ (isset($rec->id)) ? route( 'patch_task', $rec->id) : route('post_task') }}">
      {{ csrf_field()  }}
      @if(isset($rec->id))
      {{ method_field('PATCH') }}
      @endif
      <div class="row">
         <div class="col-md-8">
            @include('task.partials.form.left-column')    
            <?php echo bottom_toolbar(); ?>
         </div>
         <div class="col-md-4">
            @include('task.partials.form.right-column')
         </div>
      </div>
   </form>
</div>
@endsection
@section('onPageJs')
    <script>
        $(function () {

            <?php
            $component_id = old_set('component_id', NULL,$rec);

            if($component_id){ ?>
                enableSearchingComponent();

            var labelText = $('.related_to').find("option:selected").text();
            $("label[for*='component_number']").html(labelText);

            <?php } ?>







            $('.related_to').change(function () {

                $('.component_number')
                    .find('option')
                    .remove()
                    .val("");

                var labelText = $(this).find("option:selected").text();
                var labelElement = $("label[for*='component_number']");
                // $( ".component_number" ).val(null).trigger('change');
                if($(this).val())
                {
                    labelElement.html(labelText).parent().show();
                    enableSearchingComponent();
                }
                else
                {
                    labelElement.parent().hide();
                }





            });



            function locationResultTemplater(location) {
                return location.name;
            }

            function locationSelectionTemplater(location) {

                return location.name; // I think its either text or label, not sure.
            }

            function enableSearchingComponent() {


                var selectInput = $( ".component_number" );

                selectInput.select2( {
                    theme: "bootstrap",
                    minimumInputLength: 2,
                    maximumSelectionSize: 6,
                    placeholder: "{{ __('form.select_and_begin_typing') }}",
                    allowClear: true,

                    ajax: {
                        url: '{{ route("task_related") }}',
                        data: function (params) {
                            return {
                                search: params.term,
                                type:  $('.related_to').val()

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

                        $('.component_number')
                            .find('option')
                            .remove()
                            .val("");

                        return obj.name;
                    },
                    templateSelection: function (obj) {

                        
                        return obj.name ||  obj.text 
                    }

                } );

            }


            // Milestone
           
            $( ".component_number" ).on('select2:select', function(selection){

                    var component_number    = $(this).val();
                    var related_to          = $('.related_to').val();
                    if(component_number &&   related_to == '{{ COMPONENT_TYPE_PROJECT }}')
                    {
                        $("#milestone_id").show();

                        var milestone_id = $( ".milestone_id" );

                        milestone_id.select2( {
                            theme: "bootstrap",
                           minimumResultsForSearch: -1,
                            placeholder: "{{ __('form.select') }}",
                            allowClear: true,

                            ajax: {
                                url: '{{ route("get_milestones_by_project_id") }}',
                                data: function (params) {
                                    return {
                                        search: params.term,
                                        project_id:  component_number

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

                            

                                return obj.name;
                            },
                            templateSelection: function (obj) {

                                
                                return obj.name ||  obj.text 
                            }

                        } );
                        

                    }
                    else
                    {
                        $("#milestone_id").hide();
                    }
            });

            <?php if($component_id == COMPONENT_TYPE_PROJECT) { ?>
                $( ".component_number" ).trigger('select2:select');
            <?php } ?>    
            // End of milestone


            var parent_task_id = $( ".parent_task_id" );

            parent_task_id.select2( {
                theme: "bootstrap",
                minimumInputLength: 2,
                maximumSelectionSize: 6,
                placeholder: "{{ __('form.select_and_begin_typing') }}",
                allowClear: true,

                ajax: {
                    url: '{{ route("get_parent_tasks") }}',
                    data: function (term, page) {
                      return {
                          q: term, // search term
                          component_id: $('.related_to').val(),
                          component_number : $('.component_number').val(),
                          task_id: '{{ (isset($rec->id)) ? $rec->id : "" }}'
                          
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

                    return obj.name;
                },
                templateSelection: function (obj) {

                    return obj.name ||  obj.text
                }

            } );


            // TinyMce Editor
            // TinyMce
            tinymce.init({
                        selector: '#description',  // change this value according to your HTML
                       
                        branding: false,
                        theme: "modern",
                        

                        plugins: [
                            "advlist autolink lists link image charmap hr anchor pagebreak",
                            "wordcount visualblocks visualchars code fullscreen",
                            "nonbreaking save table contextmenu",
                            "paste textcolor colorpicker autoresize"
                        ],
                        toolbar1: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
                        //paste_data_images: true,
                        //file_picker_types: 'file image media',
                        // image_advtab: true,
                        // images_upload_handler: function (blobInfo, success, failure) {
                        //     var xhr, formData;
                        //     xhr = new XMLHttpRequest();
                        //     xhr.withCredentials = false;
                        //     xhr.open('POST', "{{ route('upload_task_attachment')}}");
                        //     var token = '{{ csrf_token() }}';
                        //     xhr.setRequestHeader("X-CSRF-Token", token);
                        //     xhr.onload = function() {
                        //         var json;
                        //         if (xhr.status != 200) {
                        //             failure('HTTP Error: ' + xhr.status);
                        //             return;
                        //         }
                        //         json = JSON.parse(xhr.responseText);

                        //         if (!json || typeof json.location != 'string') {
                        //             failure('Invalid JSON: ' + xhr.responseText);
                        //             return;
                        //         }
                        //         success(json.location);
                        //     };
                        //     formData = new FormData();
                           
                        //     formData.append('file', blobInfo.blob(), blobInfo.filename());
                        //     xhr.send(formData);
                        // }

                });




        });


    </script>

@endsection