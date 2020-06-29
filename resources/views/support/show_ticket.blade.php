@extends('layouts.main')
@section('title', __('form.tickets'))
@section('content')
@php
$route_name     = Route::currentRouteName();
$group_name     = app('request')->input('group');
$sub_group_name = app('request')->input('subgroup');
@endphp
<div style="margin-bottom: 20%;">
   <div class="main-content" style="margin-bottom: 10px !important">
      <div class="row">
         <div class="col-md-9">
            <h5>{{ $rec->subject }} ({{ $rec->number }}) </h5>
         </div>
         <div class="col-md-3">
            <div class="btn-group tickets_status d-flex float-md-right">
               @if(check_perm('tickets_delete') )
               <a class="btn btn-danger btn-sm delete_item" href="{{ route('delete_ticket', $rec->id) }}" role="button">{{ __('form.delete')}}</a>    
               @endif
               <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
               {{ $rec->status->name }}
               </button>
               <div class="dropdown-menu dropdown-menu-right">
                  @foreach($data['ticket_status_id_list'] as $id=>$name)
                  <a class="dropdown-item" data-id="{{ $id }}" href="#">{{ $name }}</a>
                  @endforeach
               </div>
            </div>
         </div>
      </div>
      <span class="badge badge-pill badge-info">{{ __('form.priority') . " : ". $rec->priority->name }}</span>
      <span class="badge badge-pill badge-secondary">{{ __('form.department') . " : ". $rec->department->name }}</span>
      <hr>
      @include('support.partials.navigation')
   </div>
   @if($group_name == '')
   @include('support.partials.add_reply')
   @elseif($group_name == 'note')
   @include('support.partials.add_note')
   @elseif($group_name == 'other-tickets')
   @include('support.partials.other_tickets')    
   @elseif($group_name == 'tasks')
   @include('support.partials.tasks')
   @elseif($group_name == 'settings')
   @include('support.partials.form')
   @endif
   <hr>
   <h4>@lang('form.conversations')</h4>
   <hr>
   @include('support.partials.comment_thread')
</div>
@endsection
@section('onPageJs')
    <script>

        $(function() {

            <?php if(Request::query('jumpto')) {?>
             $('html, body').animate({
                    scrollTop: $("#{{ Request::query('jumpto') }}").offset().top
                }, 1000);
             <?php } ?>


            $(".tickets_status .dropdown-item").click(function(e){
                e.preventDefault();               

                  $(".tickets_status .dropdown-toggle").text($(this).text());
                  update_ticket_status($(this).data('id'));

            });


            <?php echo tinyMceJsSript('#details'); ?>


            $('select[name=pre_defined_replies_id]').change(function(){
            
                if(this.value)
                {
                    get_predefined_content(this.value);

                }
                
            });

            var data = [
          { id: 0, text: 'enhancement',
          children: [{
                id  : 5,
                text: 'enhancement child1'
             },
             {
                id      : 6,
                text    : 'enhancement child2'
               
             }]
          }, 
          { id: 1, text: 'bug' }, 
          { id: 2, text: 'duplicate' }, 
          { id: 3, text: 'invalid' }, 
          { id: 4, text: 'wontfix' }
    ];


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



function update_ticket_status(ticket_status_id)
    {
        var postData = {
            "_token" : "{{ csrf_token() }}", 
            ticket_status_id : ticket_status_id,
            id : "{{ $rec->id }}"
        };

        $.post( "{{ route("ticket_change_status") }}", postData).done(function( response ) {                
                    if(response.status == 1)
                    {
                        
                    }
                
        });
    }


    </script>

@yield('innerPageJs')
@endsection