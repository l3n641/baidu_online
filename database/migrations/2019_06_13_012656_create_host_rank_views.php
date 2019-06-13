<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHostRankViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
      CREATE VIEW host_rank_views AS
      (
      select host_id,url,group_concat(id) as host_rank_ids,group_concat(`keyword`) as keywords,group_concat(`rank`) as ranks,count(url) as count_url  from host_ranks group by host_id,url      
      )
    ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS host_rank_views');
    }
}
