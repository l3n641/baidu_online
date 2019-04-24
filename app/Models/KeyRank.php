<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KeyRank extends Model
{
    /**获取ranks属性
     * @return string
     */
    public function getRanksAttribute()
    {
        $rank_list = json_decode($this->rank_list, true);
        return join(',', $rank_list);
    }
}
