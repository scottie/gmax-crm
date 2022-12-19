<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class projectupdates extends Model
{
    use HasFactory;

    public function addedby()
	{
        return  $this->belongsto(User::class, 'auth', 'id');
        
    }
}
