<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tasktodos extends Model
{
    use HasFactory;

    public function added()
	{
        return  $this->belongsto(User::class, 'auth', 'id');
        
    }
}
