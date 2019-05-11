<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Host extends Model
{
    /**获取收录记录总数
     * @return mixed
     */
    const SEARCH_COMPLETE = 2;
    const SEARCH_PROCESSING = 1;

    public function getAmountRecordAttribute()
    {
        return Url::where('host_id', $this->host_id)->count();
    }


    /**获取排名记录总数
     * @return mixed
     */
    public function getRankRecordAttribute()
    {
        return HostRank::where('host_id', $this->host_id)->count();
    }

    /**获取查询状态
     * @return string 查询状态
     */
    public function getZtAttribute()
    {
        $zt = '';
        switch ($this->status) {
            case 1:
                $zt = "估计10分钟完成";
                break;
            case 2:
                $zt = "已经完成";
                break;
        }

        return $zt;
    }

    public function getUpdateAmountAttribute()
    {
        return Url::where('host_id', $this->host_id)->where('rank', '!=', 100)->count() ?? 0;
    }

}
