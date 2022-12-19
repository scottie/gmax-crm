<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class invoice extends Model
{
    use HasFactory;
    public function clientdata()
	{
        return  $this->belongsto(client::class, 'userid', 'id');
        
    }
    public function projectdata()
	{
        return  $this->belongsto(project::class, 'projectid', 'id');
        
    }
}
