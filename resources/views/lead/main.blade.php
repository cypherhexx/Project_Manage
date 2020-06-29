@extends('layouts.main')
@section('title', (isset($rec->id)) ? __('form.lead').' : '.$rec->first_name . " ". $rec->last_name : __('form.lead') )
@section('content')
@php
$route_name         = Route::currentRouteName();
$group_name         = app('request')->input('group');
$sub_group_name     = app('request')->input('subgroup');
@endphp

<style type="text/css">
   fieldset.scheduler-border {
   border: 1px groove #ddd !important;
   padding: 0 1.4em 1.4em 1.4em !important;
   margin: 0 0 1.5em 0 !important;
   -webkit-box-shadow: 0px 0px 0px 0px #000;
   box-shadow: 0px 0px 0px 0px #000;
}

legend.scheduler-border {
   font-size: 1.2em !important;
   font-weight: bold !important;
   text-align: left !important;
   width: auto;
   padding: 0 10px;
   border-bottom: none;
}

.quick-preview table td {
   font-size: 90%;
   margin-bottom: 5px;
}

.fa-filter {
   color: #A8EB12;
}

.fa-star {
   cursor: pointer;
   color: transparent;
   -webkit-text-stroke-width: 1px;
   -webkit-text-stroke-color: orange;
   font-size: 15px;
}

.fa-star:hover {
   color: orange;
}

.star-important {
   color: orange;
}

.modal-mask {
   position: fixed;
   z-index: 9998;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
   background-color: rgba(0, 0, 0, .5);
   display: table;
   transition: opacity .3s ease;
}

.modal-wrapper {
   display: table-cell;
   vertical-align: middle;
}
.content-header{
   background-color: #f9f9f9; border: 1px solid #ebebeb; -webkit-border-radius: 4px 4px 0 0; padding-left: 8px; padding-right: 7px; padding-top: 5px; padding-bottom: 5px;
}
</style>

   <div class="row">
   <div class="col-md-9">
      @include('lead.partials.overview')
      <div class="main-content">
         <ul class="nav nav-tabs" style="font-size: 12px;">
            <li class="nav-item">
               <a class="nav-link {{ is_active_nav('', $group_name) }}" href="{{ route('show_lead_page', $rec->id )}}">@lang('form.contact_info')</a>
            </li>
            <li class="nav-item">
               <a class="nav-link {{ is_active_nav('proposals', $group_name) }}" href="{{ route('show_lead_page', $rec->id )}}?group=proposals">@lang('form.proposals')</a>
            </li>
            <li class="nav-item">
               <a class="nav-link {{ is_active_nav('tasks', $group_name) }}" href="{{ route('show_lead_page', $rec->id )}}?group=tasks">@lang('form.tasks')</a>
            </li>
            <li class="nav-item">
               <a class="nav-link {{ is_active_nav('notes', $group_name) }}" href="{{ route('show_lead_page', $rec->id )}}?group=notes">@lang('form.notes')</a>
            </li>            
            <li class="nav-item">
               <a class="nav-link {{ is_active_nav('reminders', $group_name) }}" href="{{ route('show_lead_page', $rec->id )}}?group=reminders">@lang('form.reminders')</a>
            </li>
            <li class="nav-item">
               <a class="nav-link {{ is_active_nav('activity_log', $group_name) }}" href="{{ route('show_lead_page', $rec->id )}}?group=activity_log">@lang('form.activity_log')</a>
            </li>
         </ul>
         <br>
         @if($group_name == '')
            @include('lead.contact')
         @elseif($group_name == 'notes')
            @include('lead.notes')
         @elseif($group_name == 'proposals')
            @include('lead.proposals')  
         @elseif($group_name == 'tasks')
            @include('lead.tasks') 
         @elseif($group_name == 'activity_log')
            @include('lead.activities')  
         @elseif($group_name == 'reminders')
            @include('lead.reminder')   
         @endif
      </div>
   </div>
   <div class="col-md-3">
      @include('lead.partials.social_links')
      @include('lead.partials.smart_summary')
   </div>
</div>

@include('lead.partials.log_touch')

@endsection

@section('onPageJs')

   @yield('innerPageJS')

   <script type="text/javascript">
      
      $(function () {


         $('.fa-star').click(function(e){
            e.preventDefault();

               var class_name = 'star-important';


               if($(this).hasClass(class_name))
               {
                  $(this).removeClass(class_name);
                  var is_important = null;                  
                  var tooltip_tile = "{{ __('form.mark_as_important') }}";
               }
               else
               {
                  $(this).addClass(class_name);
                  var is_important = 1;
                  var tooltip_tile = "{{  __('form.unmark_as_important') }}";
               }
               $(this).attr('data-original-title', tooltip_tile);
               var postData = { is_important : is_important, _token : "{{ csrf_token() }}" };               

                $.post( "{{ route('mark_as_important', $rec->id) }}" , postData )
                    .done(function( response ) {
                        $.jGrowl(response.msg, { position: 'bottom-right'});
                    });


         });


         // Log Touch

          $('input[name=date]').daterangepicker({

            singleDatePicker: true,
            showDropdowns: true,
            locale: {
                format: 'DD-MM-YYYY'
            },
            startDate: moment(),
            parentEl: "#logTouchModal .modal-body"

           });


         $('#logTouchModal').on('hidden.bs.modal', function (e) {

                $('.error').html("");
                $("#logTouchModal").find("textarea").val("");
            });

            
            $('#submitLogTouchForm').click(function (e) {
                e.preventDefault();             
                

                var postData = $('#logTouchForm').serializeArray();
                postData.push({ "name": "_token", "value" : "{{ csrf_token() }}" });

                $.post( "{{ route('post_log_touch', $rec->id) }}" , postData )
                    .done(function( response ) {
                        if(response.status == 2)
                        {

                            $.each(response.errors, function( index, value ) {

                                $('.' + index).html(value.join());
                            });                            

                        }
                        else
                        {                           
                            $.jGrowl("{{ __('form.success_add') }}", { position: 'bottom-right'});

                            $("#logTouchModal").find("textarea").val("");

                            $('#logTouchModal').modal('hide');
                        }
                    });



            });
            // End of Log Touch

      });

      
   </script>


   <script type="text/javascript">
      new Vue({
           el: '#smart_summary',
           data: {
             nameIsReadOnly : false,
             formInput : {
                     name: "",
                     description: ""
                 },
              summary_list : <?php echo ($rec->smart_summary) ? $rec->smart_summary : '[]' ; ?>   

             
           },
           methods: {
             initInput : function(){
                 this.formInput = {
                     name: "",
                     description: ""
                 }  
             },
             toggleModal: function (event) {
                 
                 if($('#smartSummaryModal').is(':visible'))
                 {
                     this.initInput();
                     this.nameIsReadOnly = false;
                     $('#smartSummaryModal').modal('toggle'); 
                 }
                 else
                 {
                     $('#smartSummaryModal').modal('show');
                 }
                 
             },
             removeItem: function(itemKey){

                var postData = { '_token' : global_config.csrf_token , name :  itemKey };
                $scope = this;         
                $.post( "<?php echo route('remove_smart_summary', $rec->id) ?>" , postData )
                 .done(function( response ) {
                     
                    Vue.delete($scope.summary_list, itemKey);
                 });

                

             },

             editItem : function(itemKey){
              
               this.formInput    = {
                     name: itemKey,
                     description: this.summary_list[itemKey]
               };
               this.nameIsReadOnly = true;
               this.toggleModal();
             },
             save:function(){
                 
                 this.formInput['_token'] = global_config.csrf_token;
                     
                 var postData = this.formInput;    

                 $scope = this;

                 $.post( "<?php echo route('post_smart_summary', $rec->id) ?>" , postData )
                     .done(function( response ) {
                         if(response.status == 2)
                         {
                             $.each(response.errors, function( index, value ) {

                                 $('.' + index).html(value.join());
                             });                   

                         }
                         else
                         {   
                             $scope.summary_list = response.data;                        
                             $("#smartSummaryModal").find("input[type=text], textarea, select").val("");
                             $scope.toggleModal();
                             

                             
                         }
                     });

             }
           }


           
         });
   </script>

   

<script type="text/javascript">
    new Vue({
  el: '#social_links',
  data: {
    formInput : {
            name: "",
            link: ""
        },
     links : <?php echo ($rec->social_links) ? $rec->social_links : '[]' ; ?>   

    
  },
  methods: {
    initInput : function(){
        this.formInput = {
            name: "",
            link: ""
        }  
    },
    toggleModal: function (event) {
        
        if($('#socialLinkModal').is(':visible'))
        {
            this.initInput();
            $('#socialLinkModal').modal('toggle'); 
        }
        else
        {
            $('#socialLinkModal').modal('show');
        }
        
    },
    removeItem: function(itemKey){

       var postData = { '_token' : global_config.csrf_token , name :  itemKey };
       $scope = this;         
       $.post( "<?php echo route('remove_social_link', $rec->id) ?>" , postData )
        .done(function( response ) {
            
           Vue.delete($scope.links, itemKey);
        });

       

    },
    save:function(){
        
        this.formInput['_token'] = global_config.csrf_token;
            
        var postData = this.formInput;    

        $scope = this;

        $.post( "<?php echo route('post_social_link', $rec->id) ?>" , postData )
            .done(function( response ) {
                if(response.status == 2)
                {
                    $.each(response.errors, function( index, value ) {

                        $('.' + index).html(value.join());
                    });                   

                }
                else
                {   
                    $scope.links = response.data;                        
                    $("#socialLinkModal").find("input[type=text], textarea, select").val("");
                    $scope.toggleModal();
                    

                    
                }
            });

    }
  }


});
</script>


   <?php profile_photo_upload_js('#upload_photo', $rec->id , COMPONENT_TYPE_LEAD ); ?>

@endsection