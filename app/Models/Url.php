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
        if ($this->keyword == '无关键字') {
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


}
