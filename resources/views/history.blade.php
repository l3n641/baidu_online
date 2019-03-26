@extends('common')
@section('sidebar')
    <table class="table table-striped">
        <caption>查询记录:</caption>

        <thead>
        <tr>
            <th>时间</th>
            <th>host</th>
            <th>收录数量</th>
            <th>收录记录</th>
            <th>关键词排名</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($hosts as $host)
            <tr>
                <td>{{$host->created_at}}</td>
                <td>{{$host->host}}</td>
                <td>{{$host->quantity}}</td>
                <td><a href="/result/{{$host->host_id}}">查看</a></td>
                <td><a href="/rank/{{$host->host_id}}">查看</a></td>

            </tr>
        @endforeach
        </tbody>
    </table>
@endsection


