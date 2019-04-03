<?php
/**
 * Created by PhpStorm.
 * User: lengai
 * Date: 2019/3/18
 * Time: 8:51 AM
 */

namespace App\Services;

use QL\QueryList;
use App\Services\BaiduContent;

/**
 * Class Spider 获取百度搜索内容
 * @package App\Services
 */
class Spider
{
    protected $ql;
    protected $keyword;
    protected $pageNumber = 10;
    protected $httpOpt = [];
    protected $content = '';
    const API = 'https://www.baidu.com/s';
    const RULES = [
        #  'target_url' => ['.f13>a', 'text'],
        'link' => ['h3>a', 'href'],
    ];
    const RANGE = '.result';

    public function __construct($keyword, $pageNumber = 10)
    {
        $ql = new QueryList();
        $this->ql = $ql->rules(self::RULES)->range(self::RANGE);
        $this->pageNumber = $pageNumber;
        $this->keyword = $keyword;

    }


    public function setHttpOpt(array $httpOpt = [])
    {
        $this->httpOpt = $httpOpt;
        return $this;
    }


    public function getCount()
    {
        $count = 0;
        $text = $this->query(1)->find('.nums')->text();
        if (preg_match('/[\d,]+/', $text, $arr)) {
            $count = str_replace(',', '', $arr[0]);
        }
        return (int)$count;
    }

    protected function query($page = 1)
    {
        $this->ql->get(self::API, [
            'wd' => $this->keyword,
            'rn' => $this->pageNumber,
            'pn' => $this->pageNumber * ($page - 1)
        ], $this->httpOpt);
        return $this->ql;
    }

    public function getContent($page = 1)
    {
        $result = $this->query($page);

        $content = new BaiduContent($result, $page);
        return $content;
    }


}