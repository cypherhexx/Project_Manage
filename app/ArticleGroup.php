<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArticleGroup extends Model
{
    protected $fillable = ['name' ,'name', 'slug' ,'description', 'color_code', 'sequence_number', 'is_disabled'];      
       
    
    public function articles()
    {
    	return $this->hasMany(Article::class)->whereNUll('is_disabled');
    }


    public function children()
	{
	    return $this->hasMany(ArticleGroup::class, 'parent_id', 'id')->whereNUll('is_disabled');
	}

	public function parent()
	{
	    return $this->belongsTo(ArticleGroup::class, 'parent_id')->whereNUll('is_disabled');
	}

	public function scopeAccessible_by_cusotmer($query)
    {
        return $query->orderBy('sequence_number')->whereNull('is_disabled');
    }
}
