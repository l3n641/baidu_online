<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use App\Models\Host;
use App\Models\HostRank;
use App\Models\KeyRank;
use App\Models\Url;


class ClearData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clearData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清空数据库和redis数据,还原数据到最初状态';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        Redis::flushall();
        Host::query()->truncate();
        HostRank::query()->truncate();
        KeyRank::query()->truncate();
        Url::query()->truncate();
    }
}
