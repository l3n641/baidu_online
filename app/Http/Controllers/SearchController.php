<?php

namespace App\Http\Controllers;

use App\Models\HostRank;
use Illuminate\Http\Request;
use App\Jobs\SearchBaidu;
use Illuminate\Support\Facades\Redis;
use App\Models\Host;
use App\Models\Url;
use App\Services\Spider;

class SearchController extends Controller
{
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
    public function rank($id)
    {
        $hostRank = HostRank::where('host_id', $id)->simplePaginate(50);
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
        $key = $id . "_task_status";
        $status = Redis::get($key) ?? 1;
        return ['status' => $status];

    }


}
