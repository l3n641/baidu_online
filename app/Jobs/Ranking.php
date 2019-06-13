<?php

namespace App\Jobs;

use App\Services\HostRankService;
use App\Services\KeyRankService;
use App\Services\UrlService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use App\Services\Spider;
use App\Models\HostRank;
use App\Models\Host;
use App\Models\Url;

/**
 * Class Ranking 关键词排名
 * @package App\Jobs
 */
class Ranking implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $keyword, $hostId;

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
        $this->updateUrlRank($this->hostId);
    }


    /**获取百度搜索关键词 在首页的链接.如果数据存在在缓存里面就从缓存中取出,否则就重新查询
     * @param $keyword
     * @return mixed
     */
    protected function getUrlsByKeyword($keyword)
    {
        $urls = $this->getKeywordInCache($keyword);
        $urls_collection = collect();
        if (empty($urls)) {
            $end_page = env('BAIDU_RANK_END_PAGE', 1);
            $sleep_time = env('BAIDU_RANK_SLEEP_TIME ', 1);
            $spider = new Spider($keyword);
            for ($page = 1; $page <= $end_page; $page++) {
                $content = $spider->getContent($page);
                $urls = $content->getUrls(true);
                $urls_collection = $urls_collection->merge($urls);
                sleep($sleep_time);
            }

            $this->cacheKeyword($keyword, $urls_collection);
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
        $hostRankSrv = new HostRankService();
        $keyRankSrv = new KeyRankService();
        foreach ($urls as $url) {
            $rank++;
            if (strpos($url['link'], $host->host)) {
                $rankAmount++;
                $hostRankSrv->save($url, $rank, $keyword, $this->hostId);
                $rankList[] = $rank;
            }
        }

        if (!empty($rankList)) {
            $keyRankSrv->saveKeyRank($rankAmount, $rankList, $this->hostId, $this->keyword);
        }

    }


    /**更新指定url
     * @param $host_id
     * @return int
     */
    protected function updateUrlRank($host_id)
    {
        $urlSrv = new UrlService();
        $urlSrv->updateUrlRank($host_id);

    }

}
