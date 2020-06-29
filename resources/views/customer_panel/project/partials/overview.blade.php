<div class="row">

    <div class="col-md-6">
        <h6>@lang('form.overview')</h6>
        <div class="row">
            <div class="col-md-7">
                <table class="table project-overview-table">
                    <tbody>
                    <tr class="project-overview-customer">
                        <td class="bold">@lang('form.customer')</td>
                        <td><a href="{{ route('view_customer_page', $rec->customer_id) }}">{{ $rec->customer->name }}</a>
                        </td>
                    </tr>
                    <tr class="project-overview-billing">
                        <td class="bold">@lang('form.billing_type')</td>
                        <td>{{ $rec->billing_type->name }}</td>
                    </tr>
                    @if($rec->billing_type_id != BILLING_TYPE_TASK_HOURS)
                        <tr>
                            <td class="bold">{{ ($rec->billing_type_id == BILLING_TYPE_FIXED_RATE) ? __('form.total_rate') : __('form.rate_per_hour') }}</td>
                            <td>{{ format_currency($rec->billing_rate, true) }}</td>
                        </tr>
                    @endif    
                    <tr class="project-overview-status">
                        <td class="bold">@lang('form.status')</td>
                        <td>{{ $rec->status->name }}</td>
                    </tr>
                    <tr class="project-overview-date-created">
                        <td class="bold">@lang('form.date_created')</td>
                        <td>{{ sql2date($rec->created_at) }}</td>
                    </tr>
                    <tr class="project-overview-start-date">
                        <td class="bold">@lang('form.start_date')</td>
                        <td>{{ sql2date($rec->start_date) }}</td>
                    </tr>
                    <tr class="project-overview-deadline">
                        <td class="bold">@lang('form.dead_line')</td>
                        <td>{{ ($rec->dead_line) ? sql2date($rec->dead_line) : '' }}</td>
                    </tr>
                    
                    </tbody>
                </table>
            </div>

            <div class="col-md-5">
                <h6 class="text-center">@lang('form.project_progress')</h6>
                <canvas width="100%" height="100%" id="project_progress"></canvas>
            </div>
        </div>
        

        <hr>
        <div id="description">
            <h6>@lang('form.description')</h6>
            <div style="font-size: 13px;"><?php echo nl2br($rec->description); ?></div>
        </div>

        


    </div>

    <div class="col-md-6" style="border-left: 1px solid #eee;">
        <div class="row">
            <div class="{{ (isset($rec->start_date) && isset($rec->dead_line)) ? 'col-md-6' : 'col-md-12' }}">
                <h6 style="font-weight: bold">
                    {{ count($rec->open_tasks) }} / {{ count($rec->tasks) }} {{ strtoupper(__('form.open_tasks')) }}
                    <i class="fas fa-check-circle float-md-right" style="color: #eee; font-size: 24px;"></i>
                </h6>
            </div>
            <div class="col-md-6">
                @if(isset($rec->start_date) && isset($rec->dead_line))
                    <?php
                    $now        = \Carbon\Carbon::now();
                    $start      = \Carbon\Carbon::parse($rec->start_date);
                    $end        = \Carbon\Carbon::parse($rec->dead_line);                    
                    $total_days = $end->diffInDays($start);
                    $days_left  = $now->diffInDays($end, false);


                    ?>
                    <h6 style="font-weight: bold">
                        {{ $days_left }} / {{ $total_days }} {{ strtoupper(__('form.days_left')) }}
                        <i class="far fa-calendar-check float-md-right" style="color: #eee; font-size: 24px;"></i>
                    </h6>


                @endif



            </div>
        </div>
        <hr>
        @if(check_customer_project_permission($rec->settings->permissions, 'view_task_total_logged_time')) 
            
            @if($rec->billing_type_id != BILLING_TYPE_FIXED_RATE)  
                @include('project.partials.overview.timesheet_overview')
                <hr>
            @endif 
        @endif 

        @if(check_customer_project_permission($rec->settings->permissions, 'view_finance_overview')) 
            @include('project.partials.overview.expenses_overview')        
            <hr>
        @endif
        
        @if(check_customer_project_permission($rec->settings->permissions, 'view_team_members')) 
            <div id="members">
                <h6>@lang('form.members')</h6>
                <?php $members = $rec->members; ?>
                @if(count($members) > 0)
                    <ul class="list-unstyled" style="font-size: 13px;">
                    @foreach($members as $member)
                        <li class="media">
                        <img class="staff-profile-image-small mr-2" src="{{ asset('images/user-placeholder.jpg') }}" alt="Generic placeholder image">
                        <div class="media-body">
                          <div class="mt-0 mb-1">
                            <a href="{{ route('member_profile', $member->id )}}">
                                {{ $member->first_name . " ". $member->last_name }}
                            </a>
                        </div>
                          
                        </div>
                        <br>
                      </li>

                    @endforeach
                    </ul>
                @endif
            </div>
            <hr>
        @endif

        
        <div id="tags">
            <h6>@lang('form.tags')</h6>
            <?php echo $rec->get_tags_as_badges(); ?>
        </div>

    </div>
</div>

@section('innerPageJS')

<script>

    $(function () {

        var percentCompleted = "{{ $rec->progress_percentage() }}";
        var data = {
            labels: [
                "Red",
                "Blue"

            ],
            datasets: [
                {
                    data: [percentCompleted, (100 - percentCompleted)],
                    backgroundColor: [

                        "#FF6384",
                        "#eee"
                    ],
                    hoverBackgroundColor: [

                        "#FF6384",
                        "#eee"
                    ]
                }]
        };

        var promisedDeliveryChart = new Chart(document.getElementById('project_progress'), {
            type: 'doughnut',
            data: data,
            options: {
                responsive: true,

                legend: {
                    display: false
                },
                elements: {
                    arc: {
                        borderWidth: 0
                    }
                },
                tooltips: {
                    enabled: false
                }

            }
        });

        Chart.pluginService.register({
            beforeDraw: function(chart) {
                var width = chart.chart.width,
                    height = chart.chart.height,
                    ctx = chart.chart.ctx;

                ctx.restore();
                var fontSize = (height / 114).toFixed(2);
                ctx.font = fontSize + "em sans-serif";
                ctx.textBaseline = "middle";

                var text = percentCompleted +"%",
                    textX = Math.round((width - ctx.measureText(text).width) / 2),
                    textY = height / 2;

                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        });
    });
</script>

    @endsection