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

        $urls = $this->content->rules($rules)->range($range)->query()->getData(function ($item) use ($realURL, $snapshotDate) {

            $is_validate_url = $this->is_validate_url($item['link']);

            if (!$is_validate_url) {
                $info = $this->parseCTools($item['c-tools']);;
                if (is_array($info) && array_key_exists('url', $info) && $this->is_validate_url($info['url'])) {
                    $item['link'] = $info['url'];
                } else {
                    return null;
                }

            }

            $realURL && $item['link'] = $this->getRealURL($item['link']);
            $snapshotDate && $item['snapshot_date'] = $this->getSnapshotDate($item['snapshot']);
            return $item;
        });

        //过滤空的数据
        $datas = $urls->reject(function ($name) {
            return empty($name);
        });
        return $datas;
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
        $info = [];
        try {
            $value = str_replace(['{', '}', '"', "'"], '', $value);
            $datas = explode(',', $value);
            foreach ($datas as $data) {
                list($key, $v) = explode(':', $data, 2);
                $info[$key] = $v;
            }
            return $info;
        } catch (\Exception $e) {
            return $info;
        }

    }

    /**判断url 是否有效 ,有效返回url 否则为false
     * @param $url
     * @return mixed
     */
    protected function is_validate_url($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED);

    }
}