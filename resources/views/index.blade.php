@extends('common')
@section('sidebar')
    <div class="row">
        <div class="col-lg-6">
            <form action="" method="post">
                @csrf
                <div class="input-group">
                    <input type="text" class="form-control" name="site" id="site" placeholder="Search for...">
                    <span class="input-group-btn">
                  <button class="btn btn-default" type="submit" id="search">Go!</button>
                </span>
                </div>
            </form>

        </div>
    </div>
    <table class="table table-striped">
        <caption>查询记录:</caption>

        <thead>
        <tr>
            <th>查询时间</th>
            <th>网址</th>
            <th>百度收录</th>
            <th>有效收录</th>
            <th>关键词排名</th>
            <th>状态</th>
            <th>更新个数</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($hosts as $host)
            <tr>
                <td>{{$host->created_at}}</td>
                <td>{{$host->host}}</td>
                <td>{{$host->quantity}}</td>
                <td><a href="/result/{{$host->host_id}}">{{$host->amount_record}}</a></td>
                <td><a href="/keyword/{{$host->host_id}}">{{$host->rank_record}}</a></td>
                <td><a href="/keyword/{{$host->host_id}}">{{$host->rank_record}}</a></td>
                <td>{{$host->zt}}</td>
                <td>{{$host->update_amount}}</td>


            </tr>
        @endforeach
        </tbody>
    </table>
    {{$hosts->links()}}

@endsection