<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{

    protected $hidden = ['updated_at', 'created_at', 'host_id'];
}
