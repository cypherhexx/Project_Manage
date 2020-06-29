@if(isset($data['summaries']) && count($data['summaries']) > 0)
<div class="d-flex flex-row">
    @foreach($data['summaries'] as $summary)
        <div class="p-2">
            <h3>{{ $summary['value'] }}</h3>
            <div>{{ $summary['title'] }}</div>
        </div>
    @endforeach
</div>
@endif