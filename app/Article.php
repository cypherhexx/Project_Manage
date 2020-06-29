<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
       'article_group_id' ,'subject', 'details', 'is_internal', 'is_disabled', 'slug'];
       
    

    public function group()
    {
    	return $this->belongsTo(ArticleGroup::class, 'article_group_id', 'id');
    }

    static function dropdown()
    {

        $select                         		= __('form.dropdown_select_text');

        $data['article_group_id_list'] 			= ArticleGroup::orderBy('name','ASC')->pluck('name', 'id')->toArray();


        return $data;
    }

    public function related_articles()
    {
    	return $this->group->articles()->where('articles.id', '<>', $this->id);
    }
    
  

    public function scopeAccessible_by_cusotmer($query)
    {
        return $query->whereNull('is_internal')->whereNull('is_disabled');
    }
}
