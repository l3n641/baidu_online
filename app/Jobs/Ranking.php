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


    protected function getUrlsByKeyword($keyword)
    {
        $urls = $this->getKeywordInCache($keyword);
        if (empty($urls)) {
            $spider = new Spider($keyword);
            $content = $spider->getContent(self::FIRST_PAGE);
            $urls = $content->getUrls(true);
            $this->saveKeyword($keyword, $urls);
        }

        return $urls;

    }

    protected function saveKeyword($keyword, $urls)
    {
        $hash = md5($keyword);
        $urls = serialize($urls);
        Redis::set($hash, $urls);
        Redis::expire($hash, 3600 * 12);

    }

    protected function getKeywordInCache($keyword)
    {
        $hash = md5($keyword);
        $urls = Redis::get($hash);
        if ($urls) {
            $urls = unserialize($urls);
        }
        return $urls;

    }

    protected function rank($hostId, $urls, $keyword)
    {
        $host = Host::where('host_id', $hostId)->first();
        $rank = 0;
        foreach ($urls as $url) {
            $rank++;
            if (strpos($url['link'], $host->host)) {
                $hostRank = new HostRank();
                $hostRank->url = $url['link'];
                $hostRank->host_id = $this->hostId;
                $hostRank->rank = $rank;
                $hostRank->keyword = $keyword;
                $hostRank->save();
            }
        }

    }

}
