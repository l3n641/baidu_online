<?php

namespace App\Http\Controllers;

use App\Models\HostRank;
use Illuminate\Http\Request;
use App\Jobs\SearchBaidu;
use QL\QueryList;
use App\Models\Host;
use App\Models\Url;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('index');
        }
        $id = session_create_id();
        $site = $request->post('site');
        $host_name = filter_var($site, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);

        if (!$host_name) {
            return view('index');

        }

        SearchBaidu::dispatch($id, $host_name);
        $ql = QueryList::get('http://www.baidu.com/s?wd=site:' . $host_name);
        $quantity = $ql->find('.op_site_domain_right b')->text();
        $host = new Host();
        $host->host = $host_name;
        $host->host_id = $id;
        $host->quantity = $quantity;
        $host->save();
        return redirect()->action('SearchController@result', ['id' => $id]);


    }

    public function result($id)
    {
        $host = Host::where('host_id', $id)->first();
        $urls = Url::where('host_id', $id)->simplePaginate(50);
        return view('result', ['host' => $host, 'urls' => $urls]);
    }

    public function history()
    {
        $hosts = Host::simplePaginate(20);
        return view('history', ['hosts' => $hosts]);
    }


    public function rank($id)
    {
        $hostRank = HostRank::where('host_id', $id)->simplePaginate(50);
        return view('rank', ['ranks' => $hostRank]);


    }


}
