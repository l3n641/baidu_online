<?php
/**
 * Created by PhpStorm.
 * User: lengai
 * Date: 2019/6/13
 * Time: 8:25 AM
 */

namespace App\Services;

use App\Models\HostRank;

class HostRankService extends CommonService
{

    /**保存数据到host_rank表
     * @param $url
     * @param $rank
     * @param $keyword
     * @return bool
     */
    public function save($url, $rank, $keyword, $host_id)
    {
        $data = HostRank::where('url', $url['link'])->where('host_id', $host_id)->where('keyword', $keyword)->first();
        if ($data) {
            return false;
        }
        $hostRank = new HostRank();
        $hostRank->url = $url['link'];
        $hostRank->host_id = $host_id;
        $hostRank->rank = $rank;
        $hostRank->keyword = $keyword;
        $hostRank->save();
    }


}