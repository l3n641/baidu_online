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


    /**获取百度收录页面的url链接
     * @param bool $realURL 如果为true 返回真实地址
     * @param bool $snapshotDate 如果为true就返回收录时间
     * @return mixed 收录的urls 数组
     */
    public function getUrls($realURL = false, $snapshotDate = false)
    {
        $rules = [
            'link' => ['h3>a', 'href'],
            'snapshot' => ['a.m', 'href'],
            'c-tools' => ['.c-tools', 'data-tools']
        ];
        $range = '#content_left .c-container ';
        return $this->content->rules($rules)->range($range)->query()->getData(function ($item) use ($realURL, $snapshotDate) {

            if (empty($item['link'])) {
                $info = $this->parseCTools($item['c-tools']);
                $item['link'] = $info['url'] ?? '';
            }
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
        if (empty($url)) {
            return '';
        }

        try {
            $client = new Client();
            $response = $client->get($url, ['allow_redirects' => false]);
            if ($response->getStatusCode() == 302) {
                return '';
            }
            if ($response->getStatusCode() == 200) {
                $html = $response->getBody()->getContents();
                $html = mb_convert_encoding($html, 'utf-8', 'gb2312,utf-8');
                if ($html && preg_match('|\d+年\d+月\d+日|', $html, $matches)) {
                    return $matches[0];
                } else {
                    return '';
                }

            }
        } catch (ClientException $exception) {
            return '';
        }


    }


    /**解密百度链接
     * @param $url
     * @return mixed
     */
    protected function getRealURL($url)
    {
        if (empty($url)) {
            return $url;
        }

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

    /**判断当前页面是否还有下一页,如果有就返回下一页页码
     * @return bool|int
     */
    public function hasNextPage()
    {
        $nextPage = $this->content->find('#page>a:last[class="n"]')->text();
        if (empty($nextPage)) {
            return false;
        }
        return $this->currentPage + 1;

    }

    /**解析ctools 节点
     * @param $value
     * @return array
     */
    protected function parseCTools($value)
    {
        $value = str_replace(['{', '}', '"', "'"], '', $value);
        $datas = explode(',', $value);
        $info = [];
        foreach ($datas as $data) {
            list($key, $v) = explode(':', $data, 2);
            $info[$key] = $v;
        }
        return $info;
    }
}