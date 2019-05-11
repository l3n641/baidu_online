<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Url extends Model
{

    protected $hidden = ['updated_at', 'created_at', 'host_id'];
    protected $appends = ['first_keyword'];

    /**如果关键字为空就显示无关键字
     * @param $value
     * @return string
     */
    public function getKeywordAttribute($value)
    {
        if (empty($value)) {
            return '无关键字';
        }
        return $value;
    }

    /**获取第一个关键字
     * @return string
     */
    public function getFirstKeywordAttribute()
    {
        if (empty($this->keyword) || $this->keyword == '无关键字') {
            return '无关键字';
        }

        $keyword = trim($this->keyword);
        $delimiter = " ";
        if (strpos($keyword, ',') !== false) {
            $delimiter = ',';
        }
        $keywords = explode($delimiter, $keyword);
        return $keywords[0];
    }

    /**如果url被百度首页收录就展示他的排名 否则就为100;
     * @return int
     */
    public function getRankAttribute()
    {
        $first_keyword = $this->first_keyword;
        $hostRank = HostRank::where('host_id', $this->host_id)->where('url', $this->url)->where('keyword',$first_keyword)->first();
        if ($hostRank) {

            $this->rank = $hostRank->rank;
            $this->save();
            return $hostRank->rank;
        }
        return 100;

    }


}
