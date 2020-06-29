<div style="font-size: 13px;">
   <div class="row">
      <div class="col-md-6 col-sm-6">
         <h4 class="bold">
            <a href="{{ (check_perm('proposals_edit')) ? route('edit_proposal_page', $rec->id) : '#' }}">
            <span id="proposal-number">{{ $rec->number }}</span>
            </a>
         </h4>
         <h6>{{ $rec->title  }}</h6>
         <br>
         <address>
            <div><b>{{ Config::get('constants.company_name') }}</b></div>
            <div><?php echo Config::get('constants.company_full_address') ?></div>
         </address>
      </div>
      <div class="col-sm-6 text-right">
         <span class="bold">@lang('form.to'):</span>
         <address>
            <?php
               if($rec->component_id == COMPONENT_TYPE_LEAD)
                   {
                       $href = route('show_lead_page', $rec->component_number);
                       $tool_tip_title = __('form.lead');
                   }
                   else
                       {
                           $href = route('view_customer_page', $rec->component_number);
                           $tool_tip_title = __('form.customer');
                       }
               ?>
            <a href="{{ $href }}" data-toggle="tooltip" data-placement="top" title="{{ $tool_tip_title }}" >
            <b>{{ $rec->send_to }}</b>
            </a>
            <br> <?php echo nl2br($rec->address); ?>                
            <br> <?php echo $rec->city; ?>
            <br> <?php echo $rec->state ; ?>
            <br> <?php echo $rec->zip_code ; ?>    
            @if(isset($rec->country->name))
            <br> <?php echo $rec->country->name ; ?>
            @endif
            @if($rec->phone)
            <br> <a href="tel:{{ $rec->phone }}">{{ $rec->phone }}</a>
            @endif
            <br> 
            <a href="mailto:{{ $rec->email }}">{{ $rec->email }}</a>
         </address>
         <p class="no-mbot">
            @lang('form.proposal_date'):  {{ sql2date($rec->date) }}
         </p>
         <p class="no-mbot">
            <span class="bold">@lang('form.open_till_date'): {{ sql2date($rec->open_till) }}</span>
         </p>
      </div>
   </div>
   <br><br>
   @if($rec->status_id == PROPOSAL_STATUS_ACCEPTED && $rec->accepted_by_first_name)
   <div>
      <h5>@lang('form.accepted_by')</h5>
      <div>@lang('form.name') : {{ $rec->accepted_by_first_name  . " ". $rec->accepted_by_last_name}}</div>
      <div>@lang('form.email') : {{ $rec->accepted_by_email }}</div>
      <div>@lang('form.signature')</div>
      <div><img src="{{ asset(Storage::url($rec->accepted_by_signature)) }}"></div>
   </div>
   @endif 
</div>