<?php
/**
 * Created by PhpStorm.
 * User: lengai
 * Date: 2019/4/25
 * Time: 8:59 PM
 */

namespace App\Services;

use App\Models\Host;


class HostService
{

    public function updateHostStatus()
    {
        $hosts = Host::where('status', Host::SEARCH_PROCESSING)->get();

        foreach ($hosts as $host) {
            $status = Task::getExcuteStatus($host->host_id);
            if ($status) {
                $host->status = Host::SEARCH_COMPLETE;
                $host->save();
            }

        }
    }

    public function saveHost($host_name)
    {
        $id = session_create_id();
        $quantity = Spider::getSizeQuantity($host_name);
        $host = new Host();
        $host->host = $host_name;
        $host->host_id = $id;
        $host->quantity = $quantity;
        $host->save();
        return $id;
    }

    public function getList($page_size = 50)
    {
        $this->updateHostStatus();
        return Host::orderBy('created_at', 'desc')->simplePaginate($page_size);
    }
}