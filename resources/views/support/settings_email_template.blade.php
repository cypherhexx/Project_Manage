@extends('setup.index')
@section('title', __('form.settings') . " : " .__('form.email'))
@section('setting_page')

<style type="text/css">
  fieldset.scheduler-border {
    border: 1px groove #ddd !important;
    padding: 0 1.4em 1.4em 1.4em !important;
    margin: 0 0 1.5em 0 !important;
    -webkit-box-shadow:  0px 0px 0px 0px #000;
            box-shadow:  0px 0px 0px 0px #000;
}

    legend.scheduler-border {
        font-size: 12px !important;
        font-weight: bold !important;
        text-align: left !important;
        width:auto;
        padding:0 10px;
        border-bottom:none;
    }
</style>

<?php 
 $templates = [
  [ 
    'title' => 'New Ticket opened by Team/Autoresponse, Sent to Customer',
    'name' => 'opened_by_team_sent_to_customer'
  ],

  [ 
    'title' => 'New Ticket opened by Team/Autoresponse - Sent to Non Customers',
    'name' => 'opened_by_team_sent_to_non_customer'
  ]
 ];
?>


<div class="main-content">
   
   <h5>@lang('form.email_templates')</h5>
   <hr>
   <div class="row">
      <div class="col-md-8">
         <form role="form" class="form-horizontal" action="" enctype="multipart/form-data" action="{{ route('patch_ticket_email_template') }}" method="post" autocomplete="off" >
            {{ csrf_field()  }}
            {{ method_field('PATCH') }}
            
         
@foreach($templates as $template)
  
  <fieldset class="scheduler-border">
    <legend class="scheduler-border">{{ $template['title'] }}</legend>
            <div class="row">
               <div class="col-md-9"><input type="text" name="settings[{{ $template['name'] }}][subject]" class="form-control form-control-sm" placeholder="@lang('form.subject')"></div>
               <div class="col-md-3">
                  
                  <div class="dropdown short_code float-md-right">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      @lang('form.available_short_codes')
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                      @foreach($data['short_cdes'] as $short_code)
                      <a class="dropdown-item" href="#" data-code="{{ $short_code }}">{{ $short_code }}</a>
                      @endforeach                
                    </div>
                  </div>


               </div>

            </div>   
            <div style="clear: both;"></div>        

            <div class="form-group" style="margin-top: 10px;">
              
               <textarea  rows="10" class="form-control form-control-sm {{ showErrorClass($errors , 'settings.'.$template['name']) }}" name="settings[{{ $template['name'] }}][text]">{{ old_set($template['name'], NULL, $rec) }}</textarea>
               <div class="invalid-feedback">{{ showError($errors, 'settings.'.$template['name']) }}</div>
            </div>

</fieldset>
  

@endforeach



            

            



            <?php echo bottom_toolbar(); ?>
         </form>
      </div>
      <div class="col-md-4">
         
      </div>
   </div>
</div>
@endsection
@section('onPageJs')
<script>
   $(function() {
   
      $('.short_code .dropdown-item').click(function(e){

         e.preventDefault();

          var textarea     = $('textarea[name=template]');

          var $short_code  = $(this).data('code');

          var cursorPos    = textarea.prop('selectionStart');
          var v            = textarea.val();
          var textBefore   = v.substring(0,  cursorPos);
          var textAfter    = v.substring(cursorPos, v.length);

          textarea.val(textBefore + $short_code + textAfter);



      });
   
   });
   
   
</script>
@endsection