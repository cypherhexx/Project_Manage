<?php
namespace App;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ClientResetPasswordNotification;


class CustomerContact extends Authenticatable {

    use Notifiable;
    use SoftDeletes;
  

    
    protected $guard = 'customer';

    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id','first_name', 'last_name', 'email', 'phone', 'position', 'is_primary_contact', 'password' , 
        'social_links', 'smart_summary', 'photo'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    
    


    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class,'user_id','id')->where('comments.user_type', USER_TYPE_CUSTOMER);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class,'created_by','id')->where('attachments.user_type', USER_TYPE_CUSTOMER);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class,'created_by','id')->where('tasks.user_type', USER_TYPE_CUSTOMER);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class,'created_by','id')->where('tickets.user_type', USER_TYPE_CUSTOMER);
    }


    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ClientResetPasswordNotification($token));
    }

    public function delete_has_many_relations($relations)
    {
        if(is_array($relations) && count($relations) > 0)
        {
            foreach($relations as $relation) 
            {
                $relation = $this->{$relation}()->get();

                if(count($relation) > 0)
                {
                    foreach ($relation as $r) 
                    {
                       $r->forcedelete();
                    }
                } 
            
                
            }
        }
    }



}