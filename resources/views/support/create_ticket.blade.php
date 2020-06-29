@extends('layouts.main')
@section('title', (isset($rec->id)) ? __('form.edit') . " ". __('form.ticket') :   __('form.new_ticket')   )
@section('content')


@include('support.partials.form')

@endsection
@section('onPageJs')

<script type="text/javascript">
    $(function(){

        <?php echo tinyMceJsSript('#details'); ?>        


            $('select[name=pre_defined_replies_id]').change(function(){
            
                if(this.value)
                {
                    get_predefined_content(this.value);

                }
                
            });


        <?php if(isset($data['knowledge_base_link_list'])) { ?>

        $(".insert_knowledge_base_link").select2({
          data: <?php echo json_encode($data['knowledge_base_link_list']) ?>,
          theme: "bootstrap",

                minimumResultsForSearch: -1,
                placeholder: function(){
                    $(this).data('placeholder');
                },
                maximumSelectionSize: 6
        });

        $('.insert_knowledge_base_link').on('change', function() {
            
              var text = $(".insert_knowledge_base_link option:selected").text();
              var url = $(this).val();

              if(text && url)
              {
                 var str = '<a href="' + url + '">'+ text + '</a>';
                 tinyMCE.triggerSave();
                 var content = $('#details').val();                               
                 tinymce.activeEditor.setContent(content + " " + str);
                 $('.insert_knowledge_base_link').val(null).trigger("change");
              }
              
        });

        <?php } ?>


    });


    function get_predefined_content($id)
    {
        $.post( "{{ route("get_ticket_predefined_reply") }}", { "_token": "{{ csrf_token() }}", id : $id})
                .done(function( response ) {
                    if(response.status == 1)
                    {
                        tinyMCE.triggerSave();
                        var content = $('#details').val();                               
                        tinymce.activeEditor.setContent(content + " " + response.data.details);
                        $('select[name=pre_defined_replies_id]').val(null).trigger("change");
                    }
                
                });
    }
</script>

@yield('innerPageJs')
@endsection