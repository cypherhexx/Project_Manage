// ----------------- Attachment Functionality ----------------


/* this expects global_config variable (in the main.blade.php file) to have the following values:
    # url_upload_attachment
    # url_delete_temporary_attachment
*/

    $(document).on('click', '.upload_link', function(e){
            e.preventDefault();
            $('#attachment').trigger('click');
    });

    $(document).on('change', '#attachment', function(e){

            var formId              = $(this).data('form-id');
            formId                  = (formId) ? formId : 'form';
            var shortCodeInputId    = $(this).data('short-code-input-id');


            var fileInput           = document.querySelector('#attachment');

            var xhr = new XMLHttpRequest();
            xhr.open('POST', global_config.url_upload_attachment );
           
            xhr.setRequestHeader("X-CSRF-Token", global_config.csrf_token);


            xhr.upload.onprogress = function(e) 
            {
            //    $('#file_loaded').val(e.loaded);
            //    $('#file_total').val(e.total);
                //console.log("Loaded " + e.loaded + " : Total : " + e.total);
                /* 
                * values that indicate the progression
                * e.loaded
                * e.total
                */
                $("#uploading_on_progress").show();
            };

            xhr.onload = function()
            {
                
                $("#uploading_on_progress").hide();
                

                var data;
                if (xhr.status != 200) 
                {
                    alert('Could not upload the attachment');
                    return;
                }
                if (!xhr.responseText) 
                {
                    alert('Could not upload the attachment');
                    return;
                }
                
                data = JSON.parse(xhr.responseText);

                $key = Math.floor(Math.random() *  1000); 

                $html = '<li class="list-group-item">';

                if(shortCodeInputId)
                {
                    $html += ' <a href="#" data-insert-in="' + shortCodeInputId +'" data-short-code="' + data.short_code +'" class="insert_short_code"> ' + data.display_name +' </a>';
                }
                else
                {
                     $html += data.display_name ;
                }


                $html += ' <a href="' + data.name +'" data-key="' + $key +'" class="btn btn-danger btn-sm remove_tmp_attachment"> <i class="far fa-trash-alt"></i> </a>';

                

                $html += '</li>';

                $('#list_of_attachments').append($html);
                $('<input>').attr({
                    type: 'hidden',
                    class: 'attachment',
                    name: 'attachment[' + $key +']',
                    value: data.encrypted_value_for_input
                }).appendTo(formId);
                

                // Append Short Code to Text Area
                
                if(shortCodeInputId)
                {
                   
                    var box = $(shortCodeInputId);
                    box.val(box.val() + " " + data.short_code );
                }

                $( document ).trigger("upload_complete");
                
                
            };

            // upload success
            if (xhr.readyState == 4 && (xhr.status == 200 || xhr.status == 0))
            {
                // if your server sends a message on upload sucess, 
                // get it with xhr.responseText
               console.log(xhr.responseText);
            }

            var form = new FormData();
            //form.append('title', this.files[0].name);
            form.append('file', fileInput.files[0]);

            xhr.send(form);
        });

            
        

        $(document).on('click', '.remove_tmp_attachment', function(e){
            e.preventDefault();
            var element = $(this);
            
            var key = element.data('key');
            $("input[name='attachment[" + $key +"]']").remove();        
            element.parent().remove();

         

            $.post( global_config.url_delete_temporary_attachment , { 
                "_token": global_config.csrf_token ,
                filename: element.attr('href') 
            } );

            $( document ).trigger("tmp_attachment_removed");
        });