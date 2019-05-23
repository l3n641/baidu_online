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

    public function __construct($keyword, $pageNumber = 10)
    {
        $ql = new QueryList();
        $this->ql = $ql;
        $this->pageNumber = $pageNumber;
        $this->keyword = $keyword;

    }


    /**设置querylist的选项
     * @param array $httpOpt
     * @return $this
     */
    public function setHttpOpt(array $httpOpt = [])
    {
        $this->httpOpt = $httpOpt;
        return $this;
    }


    /**发起http请求
     * @param int $page
     * @return QueryList
     */
    protected function query($page = 1)
    {
        $this->ql->get(self::API, [
            'wd' => $this->keyword,
            'rn' => $this->pageNumber,
            'pn' => $this->pageNumber * ($page - 1)
        ], $this->httpOpt);
        return $this->ql;
    }

    /**查询百度
     * @param int $page 页号
     * @return BaiduContent
     */
    public function getContent($page = 1)
    {
        $result = $this->query($page);


        $content = new BaiduContent($result, $page);
        return $content;
    }

    /**查询指定域名百度收录数量
     * @param $host_name
     * @return int
     */
    public static function getSizeQuantity($host_name)
    {
        $ql = QueryList::get('http://www.baidu.com/s?wd=site:' . $host_name);
        $quantity = $ql->find('.op_site_domain_right b')->text();
        if (empty($quantity)) {

            $quantity = $ql->find('.c-border b')->text();
            if (preg_match('/[\d,]+/', $quantity, $matches)) {
                $quantity = $matches[0];
            } else {
                $quantity = 0;
            }
        }

        return $quantity;

    }


}