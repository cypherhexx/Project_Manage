
<nav id="sidebar" class="sidebar">
    <div class="sidebar-sticky">
 
    <ul class="list-unstyled components" style="font-size: 14px;">

        
        {{ Eventy::action('main_menu', ['menu_order' => 1]) }}


         <li  class="{{ active_menu('dashboard') }}">
            <a href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt" aria-hidden="true"></i>@lang('form.dashboard')</a>
        </li>

        {{ Eventy::action('main_menu', ['menu_order' => 2]) }}

        @if(is_menu_enable('customers'))
        <li  class="{{ active_menu('customers') }}">
            <a href="{{ route('customers_list') }}"><i class="fas fa-user menu-icon"></i>@lang('form.customers')</a>
        </li>
        @endif

        {{ Eventy::action('main_menu', ['menu_order' => 3]) }}

        @if(is_menu_enable('vendors'))
        <li  class="{{ active_menu('vendors') }}">
            <a href="{{ route('vendors_list') }}"><i class="fas fa-industry"></i>@lang('form.vendors')</a>
        </li>
        @endif

        {{ Eventy::action('main_menu', ['menu_order' => 4]) }}


        @if(is_menu_enable(['proposals', 'estimates', 'invoices', 'payments', 'items', 'credit_notes']))
        <?php $sales = active_menu(['proposals', 'estimates', 'invoices', 'payments', 'items', 'credit-notes']); ?>
        <li  class="{{ $sales }}">
            <a href="#salesSubmenu" data-toggle="collapse" aria-expanded="{{ ($sales) ? 'true' : 'false' }}">
                <i class="fas fa-balance-scale menu-icon"></i>@lang('form.sales')</a>
            <ul class="collapse list-unstyled {{ ($sales) ? 'show' : '' }}" id="salesSubmenu">
                @if(is_menu_enable('proposals'))
                    <li  class="{{ active_menu('proposals') }}"><a href="{{ route('proposal_list') }}">@lang('form.proposals')</a></li>
                @endif
                @if(is_menu_enable('estimates'))
                    <li  class="{{ active_menu('estimates') }}"><a href="{{ route('estimate_list') }}">@lang('form.estimates')</a></li>
                @endif
                @if(is_menu_enable('invoices'))
                    <li  class="{{ active_menu('invoices') }}"><a href="{{ route('invoice_list') }}">@lang('form.invoices')</a></li>
                @endif
                @if(is_menu_enable(['payments', 'invoices']))
                    <li  class="{{ active_menu('payments') }}"><a href="{{ route('payment_list') }}">@lang('form.payments')</a></li>
                @endif
                @if(is_menu_enable('credit_notes'))                
                    <li  class="{{ active_menu('credit-notes') }}"><a href="{{ route('credit_note_list') }}">@lang('form.credit_notes')</a></li>
                @endif
                @if(is_menu_enable('items'))                
                    <li  class="{{ active_menu('items') }}"><a href="{{ route('item_list') }}">@lang('form.items')</a></li>
                @endif
            </ul>
        </li>
        @endif

        {{ Eventy::action('main_menu', ['menu_order' => 5 ]) }}

        @if(is_menu_enable('expenses')) 
        <li  class="{{ active_menu('expenses') }}">
            <a href="{{ route('expense_list') }}"> <i class="far fa-file-alt menu-icon"></i>@lang('form.expenses')</a>
        </li>
        @endif


        {{ Eventy::action('main_menu', ['menu_order' => 6 ]) }}


        @if(is_menu_enable('projects') || is_involved_in_project() ) 
        <li class="{{ active_menu('projects') }}">
            <a href="{{ route('projects_list') }}"><i class="fas fa-project-diagram menu-icon"></i>@lang('form.projects')</a>
        </li>
        @endif

        {{ Eventy::action('main_menu', ['menu_order' => 7 ]) }}


        <li class="{{ active_menu('tasks') }}">
            <a href="{{ route("task_list") }}"><i class="fas fa-tasks menu-icon"></i>@lang('form.tasks')</a>
        </li>
        

        {{ Eventy::action('main_menu', ['menu_order' => 8 ]) }}

        @if(is_menu_enable('tickets') && (!is_support_feature_disabled()))
        <li class="{{ active_menu('tickets') }}">
            <a href="{{ route('ticket_list') }}"><i class="fas fa-headset menu-icon"></i>@lang('form.support')</a>
        </li>
        @endif

        {{ Eventy::action('main_menu', ['menu_order' => 9 ]) }}

        @if(is_menu_enable('leads'))
        <li class="{{ active_menu('leads') }}">
            <a href="{{ route('leads_list') }}"><i class="fas fa-tty menu-icon"></i>@lang('form.leads')</a>
        </li>
        @endif


        {{ Eventy::action('main_menu', ['menu_order' => 10 ]) }}


        @if(auth()->user()->is_administrator)
        <?php $teams = active_menu('teams'); ?>

        <li class="{{ $teams }}">
            <a href="#teamSubmenu" data-toggle="collapse" aria-expanded="{{ ($teams) ? 'true' : 'false' }}">
                <i class="fas fa-users menu-icon"></i>@lang('form.teams_and_members')</a>

            <ul class="collapse list-unstyled {{ ($teams) ? 'show' : '' }}" id="teamSubmenu">
                <li><a href="{{ route('teams_list') }}">@lang('form.teams')</a></li>
                <li><a href="{{ route('team_members_list') }}">@lang('form.members')</a></li>

            </ul>
        </li>
        @endif


        {{ Eventy::action('main_menu', ['menu_order' => 11 ]) }}


        @if(check_perm('Knowledge_base') && !is_knowledge_base_feature_disabled() )
        <li class="{{ active_menu('manageknowledge-base') }}">
            <a href="{{ route('knowledge_base_article_list' )}}"><i class="far fa-folder-open menu-icon"></i>@lang('form.knowledge_base')</a>
        </li>
        @endif


         {{ Eventy::action('main_menu', ['menu_order' => 12 ]) }}


        @if(auth()->user()->is_administrator)
        <?php $utilities = active_menu('utilities'); ?>
        <li  class="{{ $utilities }}">
            <a href="#utilitiesSubmenu" data-toggle="collapse" aria-expanded="{{ ($utilities) ? 'true' : 'false' }}">
                <i class="fas fa-wrench menu-icon"></i>@lang('form.utilities')</a>
            <ul class="collapse list-unstyled {{ ($utilities) ? 'show' : '' }}" id="utilitiesSubmenu">
                
                    <li  class="{{ active_menu('filemanager') }}"><a href="{{ route('file_manager') }}">@lang('form.filemanager')</a></li>        
            </ul>
        </li>
        @endif


        {{ Eventy::action('main_menu', ['menu_order' => 13 ]) }}

        
        @if(check_perm('reports_view'))
        <?php $report_active =  active_menu('reports'); ?>
        <li class="{{ $report_active }}">


            <a href="#reportSubmenu" data-toggle="collapse" aria-expanded="{{ ($report_active) ? 'true' : 'false' }}">
                <i class="fas fa-chart-area menu-icon"></i>@lang('form.reports')</a>

            <ul class="collapse list-unstyled {{ ($report_active) ? 'show' : '' }}" id="reportSubmenu">
                <li><a href="{{ route('report_sales_page') }}">@lang('form.sales')</a></li>
                <li><a href="{{ route('report_expenses_page') }}">@lang('form.expenses')</a></li>
                <li><a href="{{ route('report_timesheet_page') }}">@lang('form.timesheet')</a></li>
                <li><a href="{{ route('lead_report_page') }}">@lang('form.leads')</a></li>                
                <li><a href="{{ route('report_activity_log') }}">@lang('form.activity_log')</a></li>
            </ul>
        </li>
        @endif
        
        {{ Eventy::action('main_menu', ['menu_order' => 14 ]) }}

        @if(auth()->user()->is_administrator)
        <li class="{{ active_menu('settings') }}">
            <a href="{{ route('settings_main_page') }}"><i class="fas fa-cog menu-icon"></i>@lang('form.settings')</a>
        </li>
        @endif

        {{ Eventy::action('main_menu', ['menu_order' => 15 ]) }}

    </ul>
    <ul class="list-unstyled CTAs">
        <li><a href="#" class="article"></a></li>
    </ul>
    </div>
</nav>