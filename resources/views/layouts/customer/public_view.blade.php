<!DOCTYPE html>
<html>
   <head>
      <title>@yield('title')</title>
      <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css">
      <link rel="stylesheet" href="{{ asset('css/app.css') }}">
      <style type="text/css">
         .stripe-button-el{
         float: right;
         margin-left: 10px;
         }
      </style>
      @yield('onPageCss')
   </head>
   <body>
      <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
         <div class="container">
            <a class="navbar-brand" href="#"><img src="{{ get_company_logo(NULL, TRUE) }}"></a>
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
            <div class="col-md-12">               
               @yield('content')
            </div>
         </div>
      </div>
      <footer class="footer" style="margin-top: 60px; margin-bottom: 60px;">
         <div class="container">
            <div class="text-muted text-center"></div>
         </div>
      </footer>
      <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script> 
      <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.6/umd/popper.min.js" integrity="sha384-wHAiFfRlMFy6i5SRaxvfOCifBUQy1xHdJ/yoi7FRNXMRBu5WHdZYu1hA6ZOblgut" crossorigin="anonymous"></script>
      <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/js/bootstrap.min.js" integrity="sha384-B0UglyR+jN6CkvvICOB2joaf5I4l3gm9GU6Hc1og6Ls7i6U/mkkaduKaBhlAXv9k" crossorigin="anonymous"></script>
      @yield('onPageJs')
   </body>
</html>