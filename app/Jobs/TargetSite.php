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

/**
 * Class TargetSite 保存百度收录的页面
 * @package App\Jobs
 */
class TargetSite implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    const DEFAULT_RANK = 100; //默认排名

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
     *获取并保存url的关键字,title等信息
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

        $rule = [
            'keyword' => ['meta[name="keywords"]', 'content'],
            'description' => ['meta[name="description"]', 'content'],
            'title' => ['title', 'text'],
        ];

        $ql->curlMulti($url_list)->success(function (QueryList $ql, CurlMulti $curl, $response) use ($rule) {
            $datas = $ql->rules($rule)->query()->getData()->all();
            $empty_data = ['keyword' => '',
                'description' => '',
                'title' => ''];
            $data = empty($datas) ? $empty_data : $datas[0];

            $responseInfo = $response['info'];
            $status = $this->saveUrl($responseInfo['url'], $responseInfo['http_code'], $data);
            if ($status && $responseInfo['http_code'] == 200 && $data['keyword']) {
                $firstKeyword = $this->getFirstKeyword($data['keyword']);
                Ranking::dispatch($firstKeyword, $this->hostId);
            }

            // 释放资源
            $ql->destruct();
        })->error(function ($errorInfo, CurlMulti $curl) {
            //出现错误处理
        })->start($this->getCurlOptions());


    }


    /**如果url数据不存在就保存
     * @param $link
     * @param $httpCode
     * @param $site_data
     * @return bool
     */
    protected function saveUrl($link, $httpCode, $site_data)
    {
        $data = Url::where("host_id", $this->hostId)->where('url', $link)->first();
        if ($data) {
            return false;
        }
        $url = new Url();
        $url->host_id = $this->hostId;
        $url->url = $link;
        $url->http_code = $httpCode;
        $url->keyword = $site_data['keyword'];
        $url->title = $site_data['title'];
        $url->description = $site_data['description'];
        $url->rank = self::DEFAULT_RANK;
        $url->save();

        return true;

    }


    /**获取第一个关键词
     * @param $keyword
     * @return mixed
     */
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

    /**获取curl配置
     * @return array
     */
    protected function getCurlOptions()
    {
        $curl_options = [
            'maxThread' => 10,// 最大并发数，这个值可以运行中动态改变。
            'maxTry' => 3,   // 触发curl错误或用户错误之前最大重试次数，超过次数$error指定的回调会被调用。
            'opt' => [
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 1,
                CURLOPT_RETURNTRANSFER => true
            ],          // 全局CURLOPT_*

            // 缓存选项很容易被理解，缓存使用url来识别。如果使用缓存类库不会访问网络而是直接返回缓存。
            'cache' => ['enable' => false, 'compress' => false, 'dir' => null, 'expire' => 86400, 'verifyPost' => false]
        ];

        return $curl_options;

    }
}
