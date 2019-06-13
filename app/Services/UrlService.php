<?php
/**
 * Created by PhpStorm.
 * User: lengai
 * Date: 2019/6/13
 * Time: 8:41 AM
 */

namespace App\Services;

use App\Models\Url;
use App\Models\HostRank;

class UrlService extends CommonService
{
    /**更新指定url
     * @param $host_id
     * @return int
     */
    public function updateUrlRank($host_id)
    {
        $urls = Url::where('host_id', $host_id)->get();
        foreach ($urls as $url) {
            $first_keyword = $url->first_keyword;
            $hostRank = HostRank::where('host_id', $url->host_id)->where('url', $url->url)->where('keyword', $first_keyword)->first();
            if ($hostRank) {

                $url->rank = $hostRank->rank;
                $url->save();
            }
        }

    }

}