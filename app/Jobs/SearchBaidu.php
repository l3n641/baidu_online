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


class SearchBaidu implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $site, $id;

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
            $spider = new Spider('site:' . $this->site);
            $baiduContent = $spider->getContent($nextPage);
            $nextPage = $baiduContent->hasNextPage();
            $urls = $baiduContent->getUrls(true);
            TargetSite::dispatch($urls, $this->id);
        } while ($nextPage);

    }


}
