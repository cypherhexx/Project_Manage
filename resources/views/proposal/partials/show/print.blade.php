<htmlpageheader name="firstpageheader" style="display:none">
   <div class="row">
      <div class="col-md-6">
         <div><b>{{ Config::get('constants.company_name') }}</b></div>
         <address>
            <div><?php echo Config::get('constants.company_full_address') ?></div>
         </address>
         <br>
         <div style="font-size: 12px;">
            <div style="font-weight: bold; ">@lang('form.to'):</div>
            <address style="line-height: 18px;">     
               {{ $rec->send_to }}
               <br> <?php echo nl2br($rec->address); ?>                
               <br> <?php echo $rec->city; ?>
               <br> <?php echo $rec->state ; ?>
               <br> <?php echo $rec->zip_code ; ?> 
               @if(isset($rec->country->name))
               <br> <?php echo $rec->country->name ; ?>
               @endif
            </address>
         </div>
      </div>
      <div class="col-md-6 text-right">
         <h1 style="margin-bottom: 0; color: #007bff; font-size: 26px;">{{  strtoupper(__('form.proposal')) }}</h1>
         <div>{{ $rec->number }}</div>
         <br>
         <div style="font-size: 12px;">
            <div>@lang('form.date') :  {{ sql2date($rec->date) }}</div>
            <div>@lang('form.proposal_open_till_date') : {{ ($rec->open_till) ? sql2date($rec->open_till) : '' }}</div>
            @if($rec->due_date)
            <div>@lang('form.due_date'): {{ sql2date( $rec->due_date) }}</div>
            @endif    
         </div>
      </div>
   </div>
   <h5>{{ $rec->title }}</h5>
</htmlpageheader>
<?php echo $rec->content; ?>