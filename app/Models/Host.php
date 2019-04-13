<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Host extends Model
{
    public function getAmountRecordAttribute()
    {
        return Url::where('host_id', $this->host_id)->count();
    }

    public function getRankRecordAttribute()
    {
        return HostRank::where('host_id', $this->host_id)->count();
    }

}
