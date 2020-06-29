<div id="smart_summary" v-cloak>
 <div class="content-header">
    <h6>
      @lang('form.smart_summary') 
      <a class="btn btn-sm btn-light float-md-right" style="font-size: 12px;" v-on:click.prevent="toggleModal" href="#">@lang('form.add')</a> 
    </h6>
  </div>
   <div class="white-background" style="margin-bottom: 20px;">
      
      <div class="modal fade" id="smartSummaryModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
         <div class="modal-dialog" role="document">
            <div class="modal-content">
               <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">@lang('form.smart_summary')</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close" v-on:click.prevent="toggleModal">
                  <span aria-hidden="true">&times;</span>
                  </button>
               </div>
               <div class="modal-body">
                  <form>
                     <div class="form-group">
                        <label for="recipient-name" class="col-form-label">@lang('form.name') <span class="required">*</span></label>
                        <input type="text" class="form-control form-control-sm" v-model="formInput.name" v-bind:readonly="nameIsReadOnly">
                        <div class="invalid-feedback d-block name"></div>
                     </div>
                     <div class="form-group">
                        <label for="message-text" class="col-form-label">@lang('form.description') <span class="required">*</span></label>                       
                        <textarea class="form-control form-control-sm" v-model="formInput.description"></textarea>
                        <div class="invalid-feedback d-block description"></div>
                     </div>
                  </form>
               </div>
               <div class="modal-footer">
                  <button v-on:click.prevent="toggleModal" type="button" class="btn btn-secondary" data-dismiss="modal">@lang('form.close')</button>
                  <button type="button" class="btn btn-primary" v-on:click.prevent="save">@lang('form.submit')</button>
               </div>
            </div>
         </div>
      </div>
      <div v-if="Object.keys(summary_list).length > 0" style="font-size: 12px;">
         <div v-for="key in Object.keys(summary_list)">
              <div>
                    <b><a href="#" v-on:click.prevent="editItem(key)" style="color: inherit;">@{{ key }}</a> </b>
                    <a href="#"  v-on:click.prevent="removeItem(key)" class="float-md-right">x</a>
              </div>
              <p v-html="summary_list[key]"></p>
              <hr>
         </div>
      </div>
   </div>
</div>
