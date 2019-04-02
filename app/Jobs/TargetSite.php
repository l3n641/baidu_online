<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use QL\QueryList;
use QL\Ext\CurlMulti;
use App\Models\Url;


class TargetSite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $urls, $hostId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($urls, $hostId)
    {
        $this->urls = $urls;
        $this->hostId = $hostId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $url_list = [];
        foreach ($this->urls as $url) {
            $url_list[] = $url['link'];
        }

        $ql = QueryList::getInstance();
        $ql->use(CurlMulti::class);

        $ql->curlMulti($url_list)->success(function (QueryList $ql, CurlMulti $curl, $response) {
            $keywords = $ql->find('meta[name="keywords"]')->attrs('content')->all();
            $keywords = empty($keywords) ? '' : $keywords[0];
            $responseInfo = $response['info'];
            $status = $this->saveUrl($responseInfo['url'], $responseInfo['http_code'], $keywords);
            if ($status && $responseInfo['http_code'] == 200 && $keywords) {
                $firstKeyword = $this->getFirstKeyword($keywords);
                Ranking::dispatch($firstKeyword, $this->hostId);
            }
        })->error(function ($errorInfo, CurlMulti $curl) {
            //出现错误处理
        })->start([
            'maxThread' => 10,// 最大并发数，这个值可以运行中动态改变。
            'maxTry' => 3,   // 触发curl错误或用户错误之前最大重试次数，超过次数$error指定的回调会被调用。
            'opt' => [
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 1,
                CURLOPT_RETURNTRANSFER => true
            ],          // 全局CURLOPT_*

            // 缓存选项很容易被理解，缓存使用url来识别。如果使用缓存类库不会访问网络而是直接返回缓存。
            'cache' => ['enable' => false, 'compress' => false, 'dir' => null, 'expire' => 86400, 'verifyPost' => false]
        ]);


    }

    protected function saveUrl($link, $httpCode, $keyword)
    {
        $data = Url::where("host_id", $this->hostId)->where('url', $link)->first();
        if ($data) {
            return false;
        }
        $url = new Url();
        $url->host_id = $this->hostId;
        $url->url = $link;
        $url->http_code = $httpCode;
        $url->keyword = $keyword;
        $url->save();

        return true;

    }


    protected function getFirstKeyword($keyword)
    {
        $keyword = trim($keyword);
        $delimiter = " ";
        if (strpos($keyword, ',') !== false) {
            $delimiter = ',';
        }
        $keywords = explode($delimiter, $keyword);
        return $keywords[0];
    }
}
