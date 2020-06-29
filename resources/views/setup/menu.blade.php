@php
    $route_name = Route::currentRouteName();
    $group_name = app('request')->input('group') ;
    $main_url   = route('settings_main_page');
@endphp

<style type="text/css">
.dropdown-submenu {
  position: relative;
}

.dropdown-submenu .dropdown-menu {
  top: 0;
  left: 100%;
  margin-top: -1px;
}
.dropdown-submenu .dropdown-toggle::after{
    transform: rotate(270deg);
}

.dropdown-submenu  a, .dropdown-submenu a:hover, .dropdown-submenu a:focus {
    text-decoration: none;
    -webkit-transition: all 0.3s;
    transition: all 0.3s;
    color: inherit;
}
</style>

<nav class="navbar navbar-expand-lg bg-primary bg-light">
   <a class="navbar-brand" href="{{ route('settings_main_page') }}"><i class="fas fa-cog menu-icon"></i></a>
   <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
   <span class="navbar-toggler-icon"></span>
   </button>
   <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mr-auto">
         <li class="nav-item">
            <a class="nav-link" href="{{ route('settings_main_page') }}"> @lang('form.general')</a>
         </li>
         <!--        <li class="nav-item">
            <a class="nav-link" href="{{ route('settings_email_page') }}"> @lang('form.email')</a>
            </li> -->
         <li class="nav-item dropdown">
            <a href="#" id="menu" data-toggle="dropdown" class="nav-link dropdown-toggle">@lang('form.email')</a>
            <ul class="dropdown-menu">
               <li><a class="dropdown-item" href="{{ route('settings_email_page') }}">@lang('form.email_settings')</a></li>
               <li><a class="dropdown-item" href="{{ route('settings_email_template_home_page') }}">@lang('form.email_templates')</a></li>
            </ul>
         </li>
         <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            @lang('form.sales')
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">                    
               <a class="dropdown-item" href="{{ route('settings_proposal_page') }}">@lang('form.proposal')</a>
               <a class="dropdown-item" href="{{ route('settings_estimate_page') }}">@lang('form.estimate')</a>
               <a class="dropdown-item" href="{{ route('settings_invoice_page') }}">@lang('form.invoice')</a>
            </div>
         </li>
         <li class="nav-item active dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            @lang('form.team_members')
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
               <a class="dropdown-item" href="{{ route('role_list') }}">@lang('form.user_roles')</a>
               <a class="dropdown-item" href="{{ route('skills_list') }}">@lang('form.skills')</a>
               
            </div>
         </li>
         <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            @lang('form.customers')
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
               <a class="dropdown-item" href="{{ route('customer_configuration_page') }}">@lang('form.configuration')</a>
               <div class="dropdown-divider"></div>
               <a class="dropdown-item" href="{{ route('customer_groups_list') }}">@lang('form.groups')</a>
            </div>
         </li>
         <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            @lang('form.finance')
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
               <a class="dropdown-item" href="{{ route('tax_list') }}">@lang('form.tax_rates')</a>
               <a class="dropdown-item" href="{{ route('currency_list') }}">@lang('form.currencies')</a>
               <a class="dropdown-item" href="{{ route('payment_modes_list') }}">@lang('form.payment_modes')</a>
               <a class="dropdown-item" href="{{ route('payment_modes_online_page') }}">@lang('form.online_payment_modes')</a>
               <a class="dropdown-item" href="{{ route('expense_categories_list') }}">@lang('form.expense_categories')</a>
            </div>
         </li>
         <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            @lang('form.leads')
            </a>
            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
               <a class="dropdown-item" href="{{ route('leads_statuses_list') }}">@lang('form.statuses')</a>
               <a class="dropdown-item" href="{{ route('lead_sources_list') }}">@lang('form.sources')</a>
            </div>
         </li>
         <li class="nav-item">
            <a class="nav-link" href="{{ route('tags_list') }}"> @lang('form.tags')</a>
         </li>
         <li class="nav-item">
            <a class="nav-link" href="{{ route('settings_pusher_page') }}"> @lang('form.pusher.com')</a>
         </li>
         <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            @lang('form.support')
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
               <a class="dropdown-item" href="{{ route('support_configuration_page') }}">@lang('form.configuration')</a>
               <div class="dropdown-divider"></div>
               <a class="dropdown-item" href="{{ route('department_list') }}">@lang('form.departments')</a>
               <a class="dropdown-item" href="{{ route('ticket_service_list') }}">@lang('form.services')</a>
               <a class="dropdown-item" href="{{ route('ticket_pre_defined_replies_list') }}">@lang('form.predefined_replies')</a>
               <a class="dropdown-item" href="{{ route('ticket_priority_list') }}">@lang('form.ticket_priorities')</a>
               <a class="dropdown-item" href="{{ route('ticket_status_list') }}">@lang('form.ticket_statuses')</a>
              
            </div>
         </li>
      </ul>
   </div>
</nav>