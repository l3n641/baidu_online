<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs\SearchBaidu;
use Illuminate\Support\Facades\Redis;
use App\Models\Host;
use App\Models\Url;
use App\Models\KeyRank;
use App\Models\HostRank;

use App\Services\Spider;
use QL\QueryList;
use QL\Ext\CurlMulti;

class SearchController extends Controller
{


    const SEARCH_COMPLETE = 1;
    const SEARCH_PROCESSING = 0;

    /**查询首页
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $site = $request->post('site');
        $host_name = filter_var($site, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
        if ($site && $host_name) {
            $id = session_create_id();
            $quantity = Spider::getSizeQuantity($host_name);
            $host = new Host();
            $host->host = $host_name;
            $host->host_id = $id;
            $host->quantity = $quantity;
            $host->save();
            SearchBaidu::dispatch($id, $host_name);
            return redirect()->action('SearchController@result', ['id' => $id]);
        } else {
            $hosts = Host::orderBy('created_at', 'desc')->simplePaginate(50);
            return view('index', ['hosts' => $hosts]);
        }

    }

    /**查看收录的地址前端页面
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function result($id)
    {
        $host = Host::where('host_id', $id)->first();
        return view('url', ['host' => $host, 'host_id' => $id]);
    }


    /** 查询关键字排名
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function rank(Request $request, $id)
    {

        $keyword = $request->input('keyword');
        if ($keyword) {
            $hostRank = HostRank::where('host_id', $id)->where('keyword', $keyword)->simplePaginate(50);

        } else {
            $hostRank = HostRank::where('host_id', $id)->simplePaginate(50);

        }
        return view('rank', ['ranks' => $hostRank]);

    }

    /** 域名的收录记录
     * @param Request $request
     * @param $id
     * @return array
     */
    public function url(Request $request, $id)
    {
        $last_id = $request->input('last', 0);
        $urls = Url::where('host_id', $id)->where('id', '>', $last_id)->get();
        return $urls->tojson();
    }

    public function status($id)
    {
        $status = Redis::get($id . "_task_status");
        $willPageList = Redis::SMEMBERS($id . "will_page_list");
        $completePageList = Redis::SMEMBERS($id . "complete_page_list");
        if ($status && empty(array_diff($willPageList, $completePageList))) {
            $status = self::SEARCH_COMPLETE;
        } else {
            $status = self::SEARCH_PROCESSING;

        }

        return ['status' => $status];

    }

    /**关键词排名
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function keyword($id)
    {
        $hosts = KeyRank::where('host_id', $id)->get();
        return view('keyword', ['hosts' => $hosts]);

    }



}
