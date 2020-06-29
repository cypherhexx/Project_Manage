<?php echo  short_code_parser_email(Config::get('constants.email_predefined_header')) ; ?>
 @yield('email_content')
<?php echo  short_code_parser_email(Config::get('constants.email_predefined_footer')) ; ?>

