<?php
/**
 * Created by PhpStorm.
 * User: lengai
 * Date: 2019/3/22
 * Time: 4:31 PM
 */

namespace App\Services;


use Mockery\Exception;
use  GuzzleHttp\Client;

/**
 * Class BaiduContent 解析百度搜索结果
 * @package App\Services
 */
class BaiduContent
{
    protected $currentPage;
    protected $content = '';


    public function __construct($content, $currentPage)
    {
        $this->content = $content;
        $this->currentPage = $currentPage;

    }


    public function getUrls($realURL = false)
    {
        return $this->content->query()->getData(function ($item) use ($realURL) {
            $realURL && $item['link'] = $this->getRealURL($item['link']);
            return $item;
        });
    }


    protected function getRealURL($url)
    {
        $client = new Client();
        $response = $client->head($url, ['allow_redirects' => false]);
        $locations = $response->getHeader('Location');
        if ($locations) {
            return $locations[0];
        }
        return $url;
    }


    protected function getTargetHost($target_url)
    {
        $info = explode('/', $target_url);
        return $info[0];
    }

    public function hasNextPage()
    {
        $nextPage = $this->content->find('#page>a:last[class="n"]')->text();
        if (empty($nextPage)) {
            return false;
        }
        return $this->currentPage + 1;

    }
}