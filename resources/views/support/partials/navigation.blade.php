 <ul class="nav project-navigation">
    <li class="nav-item">
        <a class="nav-link {{ is_active_nav('', $group_name) }}" href="{{ route('show_ticket_page', $rec->id) }}"> 
             <i class="fas fa-comments"></i> @lang('form.add_reply')</a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ is_active_nav('note', $group_name) }}" href="{{ route('show_ticket_page', $rec->id) }}?group=note"><i class="fas fa-sticky-note"></i> @lang('form.add_note')</a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ is_active_nav('other-tickets', $group_name) }}" href="{{ route('show_ticket_page', $rec->id) }}?group=other-tickets"><i class="fas fa-ticket-alt"></i> @lang('form.other_tickets')</a>
    </li>

    <li class="nav-item">
        <a class="nav-link {{ is_active_nav('tasks', $group_name) }}" href="{{ route('show_ticket_page', $rec->id) }}?group=tasks"><i class="fas fa-tasks"></i> @lang('form.tasks')</a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ is_active_nav('settings', $group_name) }}" href="{{ route('show_ticket_page', $rec->id) }}?group=settings"><i class="fas fa-cog menu-icon"></i> @lang('form.settings')</a>
    </li>
</ul>