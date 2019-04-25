<?php

namespace App\Jobs;

use function GuzzleHttp\Psr7\str;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use App\Services\Spider;
use App\Models\HostRank;
use App\Models\Host;
use App\Models\KeyRank;

/**
 * Class Ranking 关键词排名
 * @package App\Jobs
 */
class Ranking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $keyword, $hostId;
    const FIRST_PAGE = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($keyword, $hostId)
    {
        $this->keyword = $keyword;
        $this->hostId = $hostId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $urls = $this->getUrlsByKeyword($this->keyword);
        $this->rank($this->hostId, $urls, $this->keyword);
    }


    /**获取百度搜索关键词 在首页的链接.如果数据存在在缓存里面就从缓存中取出,否则就重新查询
     * @param $keyword
     * @return mixed
     */
    protected function getUrlsByKeyword($keyword)
    {
        $urls = $this->getKeywordInCache($keyword);
        if (empty($urls)) {
            $spider = new Spider($keyword);
            $content = $spider->getContent(self::FIRST_PAGE);
            $urls = $content->getUrls(true);
            $this->cacheKeyword($keyword, $urls);
        }

        return $urls;

    }

    /** 缓存百度首页关键字对应的url
     * @param $keyword
     * @param $urls
     * @param $expire 缓存时间默认是12个小时
     */
    protected function cacheKeyword($keyword, $urls, $expire = 3600 * 12)
    {
        $hash = md5($keyword);
        $urls = serialize($urls);
        Redis::set('keyword_' . $hash, $urls);
        Redis::expire($hash, $expire);

    }

    /**从缓存中获取关键字对应的url
     * @param $keyword
     * @return mixed 如果存在就返回urls数组 否则为false
     */
    protected function getKeywordInCache($keyword)
    {
        $hash = md5($keyword);
        $urls = Redis::get('keyword_' . $hash);
        if ($urls) {
            $urls = unserialize($urls);
        }
        return $urls;

    }

    /**保存对应host的被百度首页收录的记录
     * @param $hostId hostid
     * @param $urls 百度搜索关键词得到的第一页url
     * @param $keyword 关键词
     */
    protected function rank($hostId, $urls, $keyword)
    {
        $host = Host::where('host_id', $hostId)->first();
        $rank = 0;
        $rankAmount = 0;
        $rankList = [];
        foreach ($urls as $url) {
            $rank++;
            if (strpos($url['link'], $host->host)) {
                $rankAmount++;
                $this->save($url, $rank, $keyword);
                $rankList[] = $rank;
            }
        }

        if (!empty($rankList)) {
            $this->saveKeyRank($rankAmount, $rankList);
        }

    }

    /**保存数据到host_rank表
     * @param $url
     * @param $rank
     * @param $keyword
     * @return bool
     */
    protected function save($url, $rank, $keyword)
    {
        $data = HostRank::where('url', $url['link'])->where('host_id', $this->hostId)->where('keyword', $keyword)->first();
        if ($data) {
            return false;
        }
        $hostRank = new HostRank();
        $hostRank->url = $url['link'];
        $hostRank->host_id = $this->hostId;
        $hostRank->rank = $rank;
        $hostRank->keyword = $keyword;
        $hostRank->save();
    }

    /**保存数据到key_rank 表
     * @param $rankAmount int 统计上百度的次数
     * @param $rankList array 排名列表
     */
    protected function saveKeyRank($rankAmount, $rankList)
    {
        $rankList = json_encode($rankList);
        $keyRank = new KeyRank();
        $keyRank->host_id = $this->hostId;
        $keyRank->keyword = $this->keyword;
        $keyRank->rank_amount = $rankAmount;
        $keyRank->rank_list = $rankList;
        $keyRank->save();

    }

}
