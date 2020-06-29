
<div class="main-content">
    <div class="bg-light text-dark" style="margin: -20px; padding: 10px;">
        <h5>@lang('form.profile') 
            @if(check_perm('vendors_edit'))  
                <a href="{{ route('edit_vendor_page', $rec->id) }}"><i class="far fa-edit"></i></a>
            @endif
        </h5>

    </div>
    <br>
    <div class="row">
        <div class="col-md-6">
            <small><b>@lang('form.name')</b></small>
            <div>{{ $rec->name }}</div>

            

            <hr>


            <small><b>@lang('form.phone')</b></small>
            <div>{{ $rec->phone }}</div>
          
          <hr>
           <small><b>@lang('form.website')</b></small>
                    <div>{{ $rec->website }}</div>



        </div>
        <div class="col-md-6">

         <small><b>@lang('form.primary_contact')</b></small>
         <div>{{ $rec->contact_first_name . " " . $rec->contact_last_name  }}
            <small class="form-text">{{ $rec->contact_position  }}</small>
         </div>
                

         
        
        <hr>

         <small><b>@lang('form.primary_contact') @lang('form.email')</b></small>
         <div>{{ $rec->contact_email  }}</div>

         <hr>

         <small><b>@lang('form.primary_contact') @lang('form.phone')</b></small>
         <div>{{ $rec->contact_phone  }}</div>


        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-md-6">
            <small><b>@lang('form.address')</b></small>
            <address><?php echo nl2br($rec->address)?></address>
            <div>{{ $rec->city }} {{ $rec->state }}</div>
            <?php $country = $rec->country; ?>
            <div>{{ $rec->zip_code }} {{  ($country) ? $country->name : ''}}</div>
        </div>
        <div class="col-md-6">
            
        </div>
    </div>
</div>
