<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class projecttask extends Model
{
    use HasFactory;
    public function admindata()
	{
        return  $this->belongsto(User::class, 'aid', 'id');
        
    }
    public function assigned()
	{
        return  $this->belongsto(User::class, 'assignedto', 'id');
        
    }
    public function comments()
	{
        return  $this->hasMany(projectupdates::class, 'taskid', 'id');
        
    }
}
