<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    function tasks()
    {
        return $this->hasMany(Task::class ,'milestone_id','id');
//            ->where('component_id', '=', COMPONENT_TYPE_PROJECT)
//            ->where('status_id', '<>', TASK_STATUS_COMPLETE);
    }
}
