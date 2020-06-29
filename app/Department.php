<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
   protected $fillable = ['name','hide_from_client',
   		'email', 'imap_host', 'imap_port' , 'imap_username', 'imap_password', 'imap_encryption', 
   		'delete_email_after_import', 'enable_auto_ticket_import'];
	

	protected $casts = [
        'hide_from_client' 			=> 'boolean',
        'enable_auto_ticket_import' => 'boolean',
        'delete_email_after_import' => 'boolean',
    ];


   function team_members()
    {
         return $this->belongsToMany(User::class, 'user_departments', 'department_id', 'user_id');
    }
}
