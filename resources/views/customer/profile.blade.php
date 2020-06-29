<div class="main-content">
   <h5>@lang('form.profile') 
      @if(check_perm('customers_edit'))  
      <a style="font-size: 12px; vertical-align: top;" href="{{ route('edit_customer_page', $rec->id) }}"><i class="far fa-edit"></i></a>
      @endif
   </h5>
   <div class="table-responsive-sm">
   <table class="table table-sm" style="font-size: 13px; ">
      <tbody>
          <tr>
            <td>@lang('form.customer_number')</td>
            <td>{{ $rec->number }}</td>
         </tr>
         <tr>
            <td>@lang('form.name')</td>
            <td>{{ $rec->name }}</td>
         </tr>
         
         <tr>
            <td>@lang('form.phone')</td>
            <td>{{ $rec->phone }}</td>
         </tr>
         <tr>
            <td>@lang('form.website')</td>
            <td><a href="{{ $rec->website }}">{{ $rec->website }}</a></td>
         </tr>
         <tr>
            <td>@lang('form.vat_number')</td>
            <td>{{ $rec->vat_number }}</td>
         </tr>
         <tr>
            <td>@lang('form.default_language')</td>
            <?php $language = $rec->language ;?>
            <td>{{ ($language) ? $language->name : '' }}</td>
         </tr>
         <tr>
            <td>@lang('form.billing_address')</td>
            <td>
               <div >
                  <?php echo nl2br($rec->address)?>
                  <div>{{ $rec->city }} {{ $rec->state }}</div>
                  <?php $country = $rec->country; ?>
                  <div>{{ $rec->zip_code }} {{  ($country) ? $country->name : ''}}</div>
               </div>
            </td>
         </tr>
         <tr>
            <td>@lang('form.shipping_address')</td>
            <td>
               <?php echo nl2br($rec->shipping_address)?>
               <div>{{ $rec->shipping_city }} {{ $rec->shipping_state }}</div>
               <?php $shipping_country = $rec->shipping_country; ?>
               <div>{{ $rec->shipping_zip_code }} {{  ($shipping_country) ? $shipping_country->name : ''}}</div>
            </td>
         </tr>
      </tbody>
   </table>
</div>
</div>