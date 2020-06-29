<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectStatus extends Model {

    use SoftDeletes;

    protected $dates = ['deleted_at'];


    public function projects()
    {
        //return $this->hasMany(Project::class, 'status_id', 'id')->where('status_id', $this->id)->count();
        return $this->hasMany(Project::class, 'status_id', 'id');
    }

}