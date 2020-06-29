<!DOCTYPE html>
<html>
<head>
	<title>{{ $rec->number }}</title>
	 <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/css/bootstrap.min.css" integrity="sha384-9gVQ4dYFwwWSjIDZnLEWnxCjeSWFphJiwGPXr1jddIhOegiu1FwO5qRGvFXOdJZ4" crossorigin="anonymous">
	 <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand" href="#"><img src="{{ get_company_logo() }}"></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample07" aria-controls="navbarsExample07" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExample07">
          <ul class="navbar-nav mr-auto">
            
          </ul>
         
        </div>
      </div>
    </nav>

    <br>
	<div class="container">
            <div class="row" style="background-color: #fff; padding-left: 80px; padding-right: 80px; padding-top: 20px;">

            	<div class="col-md-6">
            		<div class="{{ get_estimate_status_badge($rec->status_id) }}">{{  $rec->status->name }}</div>
            	</div>
            	<div class="col-md-6">

                <div class="float-md-right">
            		<a class="btn btn-light" href="{{ route('download_estimate', $rec->id) }}">@lang('form.download')</a>

                 @if($rec->status_id == ESTIMATE_STATUS_SENT)
                 <button type="button" id="decline" class="btn btn-danger"><i class="fas fa-times"></i> @lang('form.decline')</button>
                 <button type="button" id="accept" class="btn btn-success" data-toggle="modal" data-target="#acceptModal"><i class="fas fa-check"></i> @lang('form.accept')</button>
                @endif

                </div>

            	</div>

                <div class="col-md-12">                  
                    	<hr>
				    	<?php echo $data['html'];?>
                    	
					
                </div>
            </div>
        </div>


<!-- Modal -->
<div class="modal fade" id="acceptModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">@lang('form.signature_and_confirmation_of_identity')</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
            <form id="acceptModalForm" method="POST" action="">
             {{ csrf_field()  }}
              <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="first_name">@lang('form.first_name')</label>
                    <input type="text" class="form-control form-control-sm" name="first_name">
                    <div class="invalid-feedback d-block first_name"></div>
                  </div>

                  <div class="form-group col-md-6">
                    <label for="last_name">@lang('form.last_name')</label>
                    <input type="text" class="form-control form-control-sm" name="last_name">
                    <div class="invalid-feedback d-block last_name"></div>
                  </div>

              </div>

              <div class="form-group">
                <label for="email">@lang('form.email')</label>
                <input type="email" class="form-control form-control-sm" name="email">   
                <div class="invalid-feedback d-block email"></div>             
              </div>


    

            <label for="email">@lang('form.signature')</label>
            <div id="signature-pad" class="signature-pad">
                <div class="signature-pad--body">
                    
                  <canvas style="border: 2px solid #eee;"></canvas>
                </div>
                <div class="signature-pad--footer">
                   

                  <div class="signature-pad--actions">
                    <div>
                      <button type="button" class="btn btn-light btn-sm clear" data-action="clear">@lang('form.clear')</button>          
                      <button type="button" class="btn btn-light btn-sm" data-action="undo">@lang('form.undo')</button>

                    </div>
                    
                  </div>
                </div>
            </div>
            <div class="invalid-feedback d-block signature"></div>     
            <hr>
             @lang('form.disclaimer_electronic_signature')


            </form>

      </div>
      <div class="modal-footer">        
        <button type="button" class="btn btn-primary" id="submitForm">@lang('form.sign')</button>
      </div>
    </div>
  </div>
</div>

<form id="decline_form" action="{{ route('decline_estimate', $rec->id) }}" method="POST">
    {{ csrf_field()  }}
</form>    




    <footer class="footer" style="margin-top: 60px; margin-bottom: 60px;">
      <div class="container">
        <div class="text-muted text-center"></div>
      </div>
    </footer>



<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.0/umd/popper.min.js" integrity="sha384-cs/chFZiN24E4KMATLdqdvsezGxaGsi4hLGOzlXwp5UZB1LY//20VyM2taTB4QvJ" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.0/js/bootstrap.min.js" integrity="sha384-uefMccjFJAIv6A+rW+L4AHf99KvxDjWSu1z9VI8SKNVmz4sk7buKt/6v9KI65qnm" crossorigin="anonymous"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@2.3.2/dist/signature_pad.min.js"></script>
<script type="text/javascript">
    
    $(function(){

        $('#decline').click(function(e){

            e.preventDefault();

            swal({
              title: "<?php echo __('form.delete_confirm_title') ?>" ,
              text: "<?php echo __('form.delete_confirm_text') ?>",
              icon: "warning",
              buttons: {
                        cancel: {
                          text: "<?php echo __('form.btn_cancel') ?>",
                          value: null,
                          visible: true,
                          className: "",
                          closeModal: true,
                        },
                        confirm: {
                          text: "<?php echo __('form.yes') ?>",
                          value: true,
                          visible: true,
                          className: "",
                          closeModal: true
                        }
                      },
              dangerMode: true,
              
            }).then(function (willDelete) {
              if (willDelete) {
                $("#decline_form").submit();
              } 
            });



        });



        $('#acceptModal').on('show.bs.modal', function (e) {
            
           signatureResize();
        })
        

        $("#submitForm").click(function(e){

            e.preventDefault();


            var submitButtonContext = this;

            if(signaturePad.isEmpty())
            {
                $(".signature").html("{{ __('form.please_give_your_signature_above') }}");
            }
            else
            {
                  signature_value = signaturePad.removeBlanks();
                    //signature_value = signaturePad.toDataURL();             

                    var postData = $('#acceptModalForm').serializeArray();
                        postData.push({ "name": "signature", "value" : signature_value });

                    $(submitButtonContext).html("{{ __('form.please_wait') }}").prop('disabled', true); 
                        $.post( "{{ route('accept_estimate', $rec->id )}}" , postData )
                            .done(function( response ) {
                                if(response.status == 1)
                                {
                                  // $('#acceptModal').modal('toggle');

                                
                                  window.location.reload();                                 


                                }
                                else if(response.status == 2)
                                {
                                    $.each(response.errors, function( index, value ) {

                                        $('.' + index).html(value.join());
                                    });
                                }
                            }).always(function() {

                              $(submitButtonContext).html("{{ __('form.sign') }}").prop('disabled', false); 

                            });
            }


        });

        $('input').on('focus', function(){

          $(this).next('div').html('');
        });

      
        @if(Session::has('estimate_flash_message'))
            swal("{{ __('form.thank_you')}}", "", "success"); 
        @endif

    });





        var wrapper = document.getElementById("signature-pad");
        var clearButton = wrapper.querySelector("[data-action=clear]");
        var changeColorButton = wrapper.querySelector("[data-action=change-color]");
        var undoButton = wrapper.querySelector("[data-action=undo]");
        var savePNGButton = wrapper.querySelector("[data-action=save-png]");
        var saveJPGButton = wrapper.querySelector("[data-action=save-jpg]");
        var saveSVGButton = wrapper.querySelector("[data-action=save-svg]");
        var canvas = wrapper.querySelector("canvas");
        var signaturePad = new SignaturePad(canvas, {
          // It's Necessary to use an opaque color when saving image as JPEG;
          // this option can be omitted if only saving as PNG or SVG
          backgroundColor: 'rgb(255, 255, 255)'
        });



      function signatureResize()
      {
          
          $('canvas').attr('height', '200px');
          $('canvas').attr('width', 'auto');
      }   
  // Adjust canvas coordinate space taking into account pixel ratio,
  // to make it look crisp on mobile devices.
  // This also causes canvas to be cleared.
  function resizeCanvas() {

    // When zoomed out to less than 100%, for some very strange reason,
    // some browsers report devicePixelRatio as less than 1
    // and only part of the canvas is cleared then.
    var ratio =  Math.max(window.devicePixelRatio || 1, 1);

    // This part causes the canvas to be cleared
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext("2d").scale(ratio, ratio);

    // This library does not listen for canvas changes, so after the canvas is automatically
    // cleared by the browser, SignaturePad#isEmpty might still return false, even though the
    // canvas looks empty, because the internal data of this library wasn't cleared. To make sure
    // that the state of this library is consistent with visual state of the canvas, you
    // have to clear it manually.
    signaturePad.clear();
  }

  // On mobile devices it might make more sense to listen to orientation change,
  // rather than window resize events.
  window.onresize = resizeCanvas;
  resizeCanvas();

  function download(dataURL, filename) {
    if (navigator.userAgent.indexOf("Safari") > -1 && navigator.userAgent.indexOf("Chrome") === -1) {
      window.open(dataURL);
    } else {
      var blob = dataURLToBlob(dataURL);
      var url = window.URL.createObjectURL(blob);

      var a = document.createElement("a");
      a.style = "display: none";
      a.href = url;
      a.download = filename;

      document.body.appendChild(a);
      a.click();

      window.URL.revokeObjectURL(url);
    }
  }

  // One could simply use Canvas#toBlob method instead, but it's just to show
  // that it can be done using result of SignaturePad#toDataURL.
  function dataURLToBlob(dataURL) {
    // Code taken from https://github.com/ebidel/filer.js
    var parts = dataURL.split(';base64,');
    var contentType = parts[0].split(":")[1];
    var raw = window.atob(parts[1]);
    var rawLength = raw.length;
    var uInt8Array = new Uint8Array(rawLength);

    for (var i = 0; i < rawLength; ++i) {
      uInt8Array[i] = raw.charCodeAt(i);
    }

    return new Blob([uInt8Array], { type: contentType });
  }

  clearButton.addEventListener("click", function (event) {
    signaturePad.clear();
  });

  undoButton.addEventListener("click", function (event) {
    var data = signaturePad.toData();

    if (data) {
      data.pop(); // remove the last dot or line
      signaturePad.fromData(data);
    }
  });


  SignaturePad.prototype.removeBlanks = function () {

              canvas = this._ctx.canvas;

              // First duplicate the canvas to not alter the original
              var croppedCanvas = document.createElement('canvas'),
                  croppedCtx    = croppedCanvas.getContext('2d');

                  croppedCanvas.width  = canvas.width;
                  croppedCanvas.height = canvas.height;
                  croppedCtx.drawImage(canvas, 0, 0);

              // Next do the actual cropping
              var w         = croppedCanvas.width,
                  h         = croppedCanvas.height,
                  pix       = {x:[], y:[]},
                  imageData = croppedCtx.getImageData(0,0,croppedCanvas.width,croppedCanvas.height),
                  x, y, index;

              for (y = 0; y < h; y++) {
                  for (x = 0; x < w; x++) {
                      index = (y * w + x) * 4;
                      if (imageData.data[index+3] > 0) {
                          pix.x.push(x);
                          pix.y.push(y);

                      }
                  }
              }
              pix.x.sort(function(a,b){return a-b});
              pix.y.sort(function(a,b){return a-b});
              var n = pix.x.length-1;

              w = pix.x[n] - pix.x[0];
              h = pix.y[n] - pix.y[0];
              var cut = croppedCtx.getImageData(pix.x[0], pix.y[0], w, h);

              croppedCanvas.width = w;
              croppedCanvas.height = h;
              croppedCtx.putImageData(cut, 0, 0);

              return croppedCanvas.toDataURL();
          }


</script>    

</body>
</html>


