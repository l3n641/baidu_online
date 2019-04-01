<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use QL\QueryList;
use Illuminate\Support\Facades\Redis;
use App\Services\Spider;

use  App\Jobs\TargetSite;


/**
 * Class SearchBaidu 百度搜索site:
 * @package App\Jobs
 */
class SearchBaidu implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $site, $id;
    const SEARCH_COMPLETE = 0;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id, $site)
    {
        $this->site = $site;
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $nextPage = 1;

        do {
            Redis::set($this->id . "_task_status", $nextPage);
            $spider = new Spider('site:' . $this->site);
            $baiduContent = $spider->getContent($nextPage);
            $nextPage = $baiduContent->hasNextPage();
            $urls = $baiduContent->getUrls(true);
            TargetSite::dispatch($urls, $this->id);
        } while ($nextPage);
        Redis::set($this->id . "_task_status", self::SEARCH_COMPLETE);

    }


}
