<div class="content-header">
   <div class="row">
      <div class="col-md-4">@lang('form.lead')</div>      
      <div class="col-md-8">
         <div class="float-md-right">
            <button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#logTouchModal"> <i class="fas fa-address-book"></i> @lang('form.log_touch')</button>

            @if(check_perm('leads_edit'))
            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
               <a class="btn btn-light btn-sm" href="{{ route('edit_lead_page', $rec->id) }}" >
               <i class="far fa-edit"></i> @lang('form.edit')  
               </a>
               <div class="btn-group" role="group">
                  <button id="btnGroupDrop1" type="button" class="btn btn-light btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  @lang('form.more')
                  </button>
                  <div class="dropdown-menu dropdown-menu-right" aria-labelledby="btnGroupDrop1"> 
                     <a class="dropdown-item" href="{{ route('mark_as_lost', $rec->id) }}">
                     {{ ($rec->is_lost) ? __('form.undo_mark_as_lost') : __('form.mark_as_lost') }}
                     </a>
                     <a class="dropdown-item" href="{{ route('mark_as_junk', $rec->id) }}">
                     {{ ($rec->deleted_at) ? __('form.undo_mark_as_junk') : __('form.mark_as_junk') }}
                     </a>
                     @if(check_perm('leads_delete'))
                     <a class="dropdown-item delete_item" href="{{ route('delete_lead', $rec->id) }}">@lang('form.delete')</a>
                     @endif
                  </div>
               </div>
            </div>
            @endif  
            <div class="btn-group">
               @if(isset($rec->customer_id) && $rec->customer_id)
               <a href="{{ route('view_customer_page', $rec->customer_id ) }}" class="btn btn-light btn-sm"><i class="fas fa-user"></i> @lang('form.edit_customer_profile')</a>
               @elseif(isset($rec->id) && !$rec->customer_id && ($rec->lead_status_id != LEAD_STATUS_CUSTOMER) )
               <a href="{{ route('add_customer_page', $rec->id ) }}" class="btn btn-light btn-sm"><i class="fas fa-user"></i> @lang('form.convert_to_customer')</a>
               @endif
            </div>
         </div>
      </div>
   </div>
</div>
<div class="white-background" style="margin-bottom: 20px;">
   <div class="row">
      <div class="col-md-8">
         <div class="row">
            <div class="col-md-3 pr-1">
               <?php profile_photo_upload_html($rec->photo); ?>
            </div>
            <div class="col-md-9 pl-1" >
               <h4>{{ $rec->first_name . " ". $rec->last_name }} <i class="fas fa-star {{ ($rec->is_important) ? 'star-important' : '' }}" data-toggle="tooltip" data-placement="top" title="{{ ($rec->is_important) ? __('form.unmark_as_important') : __('form.mark_as_important') }}"></i></h4>
               <div>{{ $rec->position }}, {{ $rec->company }}</div>
               <div>{{ $rec->city }}, {{ $rec->state }}, {{ (isset($rec->country->name)) ? $rec->country->name : '' }}</div>
               <div class="quick-preview">
                  <table>
                     <tr>
                        <td>@lang('form.email')</td>
                        <td>: <a  href="mailto: {{ $rec->email }}">{{ $rec->email }}</a></td>
                     </tr>
                     <tr>
                        <td>@lang('form.phone')</td>
                        <td>: {{ $rec->phone }}</td>
                     </tr>
                  </table>
               </div>
            </div>
         </div>
         <br>
         <small class="text-muted">@lang('form.tags')</small>
         <div><?php echo $rec->get_tags_as_badges(); ?></div>
         <small class="form-text text-muted">
         @lang('form.assigned_to') :
         <?php 
          
            echo (isset($rec->assigned->first_name)) ? anchor_link($rec->assigned->first_name . " ". $rec->assigned->last_name, route('member_profile', $rec->assigned->id)) : '' ?>
         </small>
      </div>
      <div class="col-md-4">
         <fieldset class="scheduler-border">
            <legend class="scheduler-border">@lang('form.status')</legend>
            <div class="quick-preview">
               <div class="caption"><i class="fas fa-comment"></i> @lang('form.last_contacted')</div>
               @if($rec->last_contacted && isset($rec->person_last_contacted->first_name))
               <table>
                  <tr>
                     <td>{{ ($rec->last_contacted) ? date("d-m-Y h:i a", strtotime($rec->last_contacted)) : '' }}</td>                     
                  </tr>            
                  <tr>
                     <td>@lang('form.By') <?php echo anchor_link($rec->person_last_contacted->first_name. " ". $rec->person_last_contacted->last_name, route('member_profile', $rec->person_last_contacted->id)) ?></td>            
                  </tr>
               </table>
               @endif
            </div>
            <hr>
            <div class="quick-preview">
               <div class="caption"><i class="fas fa-filter"></i> @lang('form.details')</div>
               <table>
                  <tr>
                     <td>@lang('form.status')</td>
                     <td>: {{  $rec->status->name }}</td>
                  </tr>
                  <tr>
                     <td>@lang('form.source')</td>
                     <td>: {{ $rec->source->name }}</td>
                  </tr>
                  <tr>
                     <td>@lang('form.created')</td>
                     <td>: {{ ($rec->created_at) ? $rec->created_at->diffForHumans() : '' }} </td>
                  </tr>
               </table>
               @if($rec->is_lost)
               <span class="badge badge-danger">{{ __('form.lost')}}</span>
               @endif
               @if($rec->deleted_at)
               <span class="badge badge-warning">{{ __('form.junk')}}</span>
               @endif
               @if($rec->customer_id)
               <span class="badge badge-success">{{ __('form.customer')}}</span>
               @endif
            </div>
         </fieldset>
      </div>
   </div>


</div>