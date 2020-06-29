<?php

function profile_photo_upload_html($photo)
    {
        ?>

        <?php $photo_url = ($photo) ? asset(Storage::url($photo)) : asset('images/user-placeholder.jpg') ; ?>
          <div class="img__wrap">
          
           <img class="card-img-top img-fluid img__img member-avatar" src="<?php echo $photo_url; ?>" alt="">
           <div class="img__description_layer">
    <p class="img__description"><a href="#" id="upload_photo" style="font-size: 13px; text-align: center; color: #fff;">
      <?php echo __('form.change_photo'); ?></a></p>

  </div>
            </div> 
      
           <span class="uploading_spinner text-center" style="display: none; font-size: 12px;"><?php echo __('form.uploading'); ?> ..</span>

           <div style="display: none;">
              <form>
                 <input type="file" id='file' name="file" >
              </form>
            </div>

        <?php
    }


    function profile_photo_upload_js($click_button, $profile_id, $component_id)
    {
        ?>

         <script type="text/javascript">

          $(function(){

              $('<?php echo $click_button; ?>').click(function(e){
                e.preventDefault();
                $('#file').focus().trigger("click");
              });

              $('#file').change(function(){

                    var fd = new FormData();
                    var files = $('#file')[0].files[0];
                    fd.append('file',files);
                    fd.append('_token', "<?php echo csrf_token(); ?>");
                    fd.append('profile_id', "<?php echo $profile_id; ?>");
                    fd.append('component_id', "<?php echo $component_id; ?>");
                    
                    $('.uploading_spinner').show();

                    // AJAX request
                    $.ajax({
                      url: '<?php echo route('change_profile_photo'); ?>',
                      type: 'post',
                      data: fd,
                      contentType: false,
                      processData: false,
                      success: function(response){

                        $('.uploading_spinner').hide();
                        if(response.status == 1)
                        {                        
                          $('.member-avatar').attr("src", response.file_url);
                        }
                        else
                        {                         
                            swal(response.msg);
                        }
                      }
                    });
              });
          });
         </script>


        <?php
    }