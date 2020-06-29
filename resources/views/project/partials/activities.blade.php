<style type="text/css">
    .activity-feed .feed-item {
    position: relative;
    padding-bottom: 30px;
    padding-left: 30px;
    border-left: 2px solid #84c529;
    font-size: 13px;
}

.activity-feed .feed-item:after {
    content: "";
    display: block;
    position: absolute;
    top: 0;
    left: -6px;
    width: 10px;
    height: 10px;
    border-radius: 6px;
    background: #fff;
    border: 1px solid #4b5158;
}
</style>
<div class="activity-feed">
    <?php $activities = Spatie\Activitylog\Models\Activity::where('log_name', LOG_NAME_PROJECT.$rec->id)
    ->where('causer_id', $rec->id )->orderBy('id', 'DESC')->get(); ?>
    @foreach($activities as $activity)
    
        <div class="feed-item">
            <div class="row">
                <div class="col-md-8">
                   <div class="date">
                        <span class="text-has-action" data-toggle="tooltip" data-title="{{ $activity->created_at }}" data-original-title="" title="">
                            {{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}
                        </span>
                    </div>
                   <div class="text">
                    <br>
                        <a href="{{ route('member_profile',$activity->causer->id) }}">
                            <img src="{{ get_avatar_small_thumbnail($activity->causer->photo) }}" 
                            class="staff-profile-image-small" alt="{{ $activity->causer->first_name . " " . $activity->causer->last_name}}">
                        </a>
                        <br>    
                      <p class="mtop10 no-mbot">
                        <a href="{{ route('member_profile',$activity->causer->id) }}">
                            {{ $activity->causer->first_name . " " . $activity->causer->last_name}} 
                        </a>
                        - 
                        <b><?php echo $activity->description; ?></b>
                      </p>

                      <p>
                        <?php echo $activity->getExtraProperty('item') ; ?>
                      </p>
                      
                    </div>
                </div>
                <div class="col-md-4 text-right">
                   
                </div>
                <div class="clearfix"></div>
                <div class="col-md-12">
                    <hr class="hr-10">
                </div>
            </div>
        </div>


    @endforeach
</div>        