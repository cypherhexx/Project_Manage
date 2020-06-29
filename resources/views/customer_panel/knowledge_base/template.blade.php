@extends('layouts.customer.main')
@section('content')
<style type="text/css">

.page-title {
    font-size: 22px;
    line-height: 1.2;
    margin-bottom: .296rem;

    font-style: normal;
    font-weight: 600;
}


ul {
   /* padding: 0;*/
  list-style-type: none;
}
li{
    margin-left: 0!important;
}

.card{
   box-shadow: 0 1px 15px 1px rgba(90,90,90,.08);
   border: 0 !important;
   border-radius: 0!important;
}
.card-body ul {

    list-style: none;
    line-height: 20px;
   

}
.card-body > ul {

    padding: 0;  

}
.card-body > ul > ul {

    padding-top: 10px;  

}
.card-body li a {
	color: #4d4d4d;
	transition-duration: .12s;

transition-timing-function: ease-out;
}

.search-bg {
    background: #d1e6f9;
    background: linear-gradient(165deg,#d1e6f9,#f3f9fd 75%,#fff);

}

</style>
@include('customer_panel.knowledge_base.search')
@yield('knowledge_base_content')
@endsection
@section('onPageJs')
@yield('innerPageJs')

<footer class="footer mt-auto py-3" style="background-color: #fff;">
  <div class="container text-center">
    <span class="text-muted">&copy {{ config('constants.company_name') }} {{ date("Y") }}</span>
  </div>
</footer>
@endsection

