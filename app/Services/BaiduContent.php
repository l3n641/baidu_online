<?php
/**
 * Created by PhpStorm.
 * User: lengai
 * Date: 2019/3/22
 * Time: 4:31 PM
 */

namespace App\Services;


use  GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Mockery\Exception;
use QL\QueryList;


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


    public function getUrls($realURL = false, $snapshotDate = false)
    {
        return $this->content->query()->getData(function ($item) use ($realURL, $snapshotDate) {
            $realURL && $item['link'] = $this->getRealURL($item['link']);
            $snapshotDate && $item['snapshot_date'] = $this->getSnapshotDate($item['snapshot']);
            return $item;
        });
    }

    /**获取快照时间
     * @param $url
     * @return string
     */
    protected function getSnapshotDate($url)
    {
        try {
            $client = new Client();
            $response = $client->get($url, ['allow_redirects' => false]);
            if ($response->getStatusCode() == 200) {
                $html = $response->getBody();
                $datas = QueryList::html($html)->find('#bd_snap_txt span')->eq(0)->texts();
                $data = $datas->first();
                if ($data && preg_match('|\d+年\d+月\d+日|', $data, $matches)) {
                    return $matches[0];
                } else {
                    return '';
                }

            }
        } catch (ClientException $exception) {
            return '';
        }


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