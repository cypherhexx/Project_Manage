<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $data['page_title'] }}</title>
    <style type="text/css">

       body {
    font-family: opensans;
}

.col-md-6 {
    float: left;
    width: 50%;
}

.col-md-12 {
    width: 100%;
}

.col-md-7 {
float: left;
    width: 58.33333333%;

}
.col-md-5 {
float: left;
    width: 41.66666667%;

}

/* Clear floats after the columns */

.row:after {
    content: "";
    display: table;
    clear: both;
}

.text-right {
    text-align: right !important;
}

.text-left {
    text-align: left;
}

.bold {
    font-weight: bold;
}

.table {
    width: 100%;
    max-width: 100%;
    margin-bottom: 1rem;
    background-color: transparent;
    font-size: 12px;
    border-collapse: collapse;
}

.table thead {
    background: #415164;
    color: #fff !important;
    border: 0;
}

.table {
    width: 100%;
    margin-bottom: 1rem;
    background-color: transparent
}

.table th {
    padding: .75rem;
    vertical-align: top;
}

.table thead th {
    vertical-align: bottom;
    border-bottom: 2px solid #dee2e6
}

.table tbody+tbody {
    border-top: 2px solid #dee2e6
}

.table td {
    padding-top: 5px;
    padding-bottom: 10px;
    vertical-align: top;

}

.table > tbody > tr > td, .table > tfoot > tr > td {

    padding: 10px 10px 5px 10px !important;

}
.table th, .table td {
    border-top: 1px solid #dee2e6;
}
td {
    border: 0 !important;
}

@page :first {    
    header: html_firstpageheader;
    
}


</style>
</head>
<body>
<div class="row">           
 <br>
<?php echo $data['html']; ?>         
</div>
</body>
</html>
