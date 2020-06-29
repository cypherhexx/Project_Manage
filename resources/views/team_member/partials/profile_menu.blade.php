<nav class="navbar navbar-expand-lg navbar-light bg-light">
      <div class="container">
        <a class="navbar-brand" href="{{ route('member_profile', $rec->id ) }}">{{ $rec->first_name . " " . $rec->last_name }}</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExample07" aria-controls="navbarsExample07" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExample07">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item active">
              <a class="nav-link" href="{{ route('member_profile', $rec->id )}}">@lang('form.profile') <span class="sr-only">(current)</span></a>
            </li>
            <!-- <li class="nav-item">
              <a class="nav-link" href="{{ route('member_profile', $rec->id )}}?group=tasks">@lang('form.tasks')</a>
            </li> -->
            @if(is_current_user($rec->id))
            <li class="nav-item">
              <a class="nav-link" href="{{ route('member_profile', $rec->id )}}?group=notifications">@lang('form.notifications')</a>
            </li>
        
              <li class="nav-item">
                <a class="nav-link" href="{{ route('member_profile', $rec->id )}}?group=change-password">@lang('form.change_password')</a>
              </li>
            @endif

            
          </ul>
          
        </div>
      </div>
</nav>