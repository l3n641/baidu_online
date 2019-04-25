<?php
/**
 * Created by PhpStorm.
 * User: lengai
 * Date: 2019/4/25
 * Time: 8:07 AM
 */

namespace App\Services;

use Illuminate\Support\Facades\Redis;


/** 任务查询类
 * Class Task
 * @package App\Services
 */
class Task
{

    const SEARCH_COMPLETE = 1;
    const SEARCH_PROCESSING = 0;

    /**查询targetSite job是否已经完成
     * @param $host_id
     * @return int|mixed
     */
    public static function getExcute_status($host_id)
    {

        $status = self::getSearchBaiduDispatchStatus($host_id);
        $willPageList = self::getWillPage($host_id);
        $completePageList = self::getCompletePage($host_id);
        if ($status && empty(array_diff($willPageList, $completePageList))) {
            $status = self::SEARCH_COMPLETE;
        } else {
            $status = self::SEARCH_PROCESSING;

        }
        return $status;
    }

    /**获取指定host_id searchBaidu job任务发布状态
     * @param $host_id
     * @return mixed
     */
    public static function getSearchBaiduDispatchStatus($host_id)
    {
        return Redis::get("task_status_" .$host_id );

    }

    /**设置baidusearch job任务状态为执行中
     * @param $host_id
     */
    public static function setSearchBaiduStart($host_id)
    {
        Redis::set("task_status_" . $host_id, self::SEARCH_PROCESSING);

    }

    /**设置baidusearch job任务状态为完成
     * @param $host_id
     */
    public static function setSearchBaiduComplete($host_id)
    {
        Redis::set('task_status_' . $host_id, self::SEARCH_COMPLETE);

    }

    /**设置将要搜索页面页号
     * @param $host_id
     * @param $page_num
     */
    public static function setWillPage($host_id, $page_num)
    {
        Redis::sadd("will_page_list_" . $host_id, $page_num);

    }

    /**获取将要搜索页面页号
     * @param $host_id
     * @param $page_num array
     */
    public static function getWillPage($host_id)
    {
        return $willPageList = Redis::SMEMBERS("will_page_list_" . $host_id);

    }

    /**设置已经完成搜索页面页号
     * @param $host_id
     * @param $page_num
     */
    public static function setCompletePage($host_id, $page_num)
    {
        Redis::sadd("complete_page_list_" . $host_id, $page_num);

    }

    /**获取已经完成搜索页面页号
     * @param $host_id
     * @param $page_num array
     */
    public static function getCompletePage($host_id)
    {
        return $willPageList = Redis::SMEMBERS("complete_page_list_" . $host_id);

    }


}