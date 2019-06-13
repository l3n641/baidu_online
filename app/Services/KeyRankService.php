<?php
/**
 * Created by PhpStorm.
 * User: lengai
 * Date: 2019/6/13
 * Time: 8:39 AM
 */

namespace App\Services;

use App\Models\KeyRank;


class KeyRankService extends CommonService
{
    /**保存数据到key_rank 表
     * @param $rankAmount int 统计上百度首页的次数
     * @param $rankList array 排名列表
     */
    public function saveKeyRank($rankAmount, $rankList, $host_id, $keyword)
    {
        $data = KeyRank::where('host_id', $host_id)->where('keyword', $keyword)->first();
        if ($data) {
            return false;
        }
        $rankList = json_encode($rankList);
        $keyRank = new KeyRank();
        $keyRank->host_id = $host_id;
        $keyRank->keyword = $keyword;
        $keyRank->rank_amount = $rankAmount;
        $keyRank->rank_list = $rankList;
        $keyRank->save();

    }
}