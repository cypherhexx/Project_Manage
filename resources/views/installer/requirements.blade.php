@extends('installer.template')
@section('title', "Server Requirements")
@section('content')

<?php

$reqList = array(
    'php'           => '7.1.3',    
    'openssl'       => true,
    'pdo'           => true,
    'mbstring'      => true,
    'tokenizer'     => true,
    'xml'           => true,
    'ctype'         => true,
    'json'          => true,    
    'gd'            => true,
    'imap'          => true
    
);




$strOk = '<i class="fas fa-check-circle"></i>';
$strFail = '<i class="fas fa-exclamation-triangle"></i>';
$strUnknown = '<i class="fas fa-question"></i>';

$requirements = [];
// PHP Version
$requirements['php'] = version_compare(PHP_VERSION, $reqList['php'], ">=");

// OpenSSL PHP Extension
$requirements['openssl'] = extension_loaded("openssl");

// PDO PHP Extension
$requirements['pdo'] = defined('PDO::ATTR_DRIVER_NAME');

// Mbstring PHP Extension
$requirements['mbstring'] = extension_loaded("mbstring");

// Tokenizer PHP Extension
$requirements['tokenizer'] = extension_loaded("tokenizer");

// XML PHP Extension
$requirements['xml'] = extension_loaded("xml");

// CTYPE PHP Extension
$requirements['ctype'] = extension_loaded("ctype");

// JSON PHP Extension
$requirements['json'] = extension_loaded("json");

// GD
$requirements['gd'] = extension_loaded("gd");

// GD
$requirements['imap'] = extension_loaded("imap");

// mod_rewrite
$requirements['mod_rewrite'] = null;

if (function_exists('apache_get_modules')) 
{
    $requirements['mod_rewrite'] = in_array('mod_rewrite', apache_get_modules());
}

function check_folder_permissions($folders)
{
    $i = 0;
    foreach ($folders as $folder=>$full_path) 
    {
        $data[$i]['isSet']  = (is_dir($full_path) && is_writable($full_path)) ? TRUE : FALSE;
       
        $data[$i]['folder'] = $folder;
        $i++;
    }
   return $data;
}



   $folder_permissions = check_folder_permissions([
        'storage/framework/'     => storage_path(). '/framework',
        'storage/logs/'          => storage_path(). '/logs',
        'bootstrap/cache/'       => base_path(). '/bootstrap/cache/'
    ]);

   $err = 0;

// Finding Errors
foreach ($reqList as $key => $value) 
{
    if(!$requirements[$key])
    {       
        $err++;
    }
}
foreach($folder_permissions as $row)
{
    if(!($row['isSet'] == 1))
    {
        $err++;
    }
}
 
if(!($data['sym_link_eanabled'] == TRUE) )
{
    $err++;
}

// End of Findining errors 
?>


<div class="mx-auto" style="background: #fff; width: 40%; padding: 20px;  margin-bottom: 10%; font-size: 13px;">

    <h3>Server Requirements.</h3>
    <hr>
    <p>PHP >= {{   $reqList['php'] }} <?php echo ($requirements['php'] ? $strOk : $strFail); ?></p>    

    <table class="table table-sm table-bordered">
         <thead>
            <tr>
               <th scope="col">PHP Extensions</th>
            
               <th scope="col"></th>
            </tr>
         </thead>
         <tbody style="font-size: 13px;">
            
        <?php if ($reqList['openssl']) : ?>
           
            <tr><td>OpenSSL PHP Extension</td> <td><?php echo $requirements['openssl'] ? $strOk : $strFail; ?></td></tr>
            <?php endif; ?>

            <?php if ($reqList['pdo']) : ?>
                <tr><td>PDO PHP Extension</td> <td> <?php echo $requirements['pdo'] ? $strOk : $strFail; ?></td></tr>
            <?php endif ?>

            <?php if ($reqList['mbstring']) : ?>
                <tr><td>Mbstring PHP Extension</td> <td> <?php echo $requirements['mbstring'] ? $strOk : $strFail; ?></td></tr>
            <?php endif ?>

            <?php if ($reqList['tokenizer']) : ?>
                <tr><td>Tokenizer PHP Extension</td> <td> <?php echo $requirements['tokenizer'] ? $strOk : $strFail; ?></td></tr>
            <?php endif ?>


            <?php if ($reqList['xml']) : ?>
                <tr><td>XML PHP Extension</td> <td> <?php echo $requirements['xml'] ? $strOk : $strFail; ?></td></tr>
            <?php endif ?>

            <?php if ($reqList['ctype']) : ?>
                <tr><td>CTYPE PHP Extension</td> <td> <?php echo $requirements['ctype'] ? $strOk : $strFail; ?></td></tr>
            <?php endif ?>

            <?php if ($reqList['json']) : ?>
                <tr><td>JSON PHP Extension</td> <td> <?php echo $requirements['json'] ? $strOk : $strFail; ?></td></tr>
            <?php endif ?>

            

            <?php if ($reqList['gd']) : ?>
                <tr><td>GD Library</td> <td> <?php echo $requirements['gd'] ? $strOk : $strFail; ?></td></tr>
            <?php endif ?>

            <?php if ($reqList['imap']) : ?>
                <tr><td>Imap</td> <td> <?php echo $requirements['imap'] ? $strOk : $strFail; ?></td></tr>
            <?php endif ?>
             


            <?php if (!empty($reqList['obs'])): ?>
                <tr><td colspan="2"><?php echo $reqList['obs'] ?></td></tr>
            <?php endif; ?>
            
         </tbody>
      </table>



    <table class="table table-sm table-bordered">
         <thead>
            <tr>
               <th scope="col">Folders</th>
            
               <th scope="col"></th>
            </tr>
         </thead>
         <tbody style="font-size: 13px;">
            @foreach($folder_permissions as $row)
            <tr>
               <td>{{ $row['folder'] }}</td>
         
               <td class="text-center"><?php echo ($row['isSet'] == 1) ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-triangle"></i>' ?></td>
            </tr>
            @endforeach
            
         </tbody>
      </table>

      <table class="table table-sm table-bordered">
         <thead>
            <tr>
               <th scope="col">SymLink</th>
               <th scope="col"></th>
            </tr>
         </thead>
         <tbody style="font-size: 13px;">
            <tr>
               <td>public/storage  <b>to</b>  storage/app/public</td>
               <td class="text-center"><?php echo ($data['sym_link_eanabled'] == TRUE) ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-triangle"></i>' ?></td>
            </tr>

            
         </tbody>
      </table>


      
      
      @if($err == 0)
        <a class="btn btn-primary float-md-right" href="{{ route('run_installation_step_2_page') }}">Next</a>          
         <div class="clearfix"></div>
      @else
            <p class="text-danger">Your server does not meet all the requirements to install the application</p>   
      @endif

</div>


@endsection