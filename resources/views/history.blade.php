@extends('common')
@section('sidebar')
    <table class="table table-striped">
        <caption>查询记录:</caption>

        <thead>
        <tr>
            <th>时间</th>
            <th>host</th>
            <th>收录记录</th>
            <th>关键词排名</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($hosts as $host)
            <tr>
                <td>{{$host->created_at}}</td>
                <td>{{$host->host}}</td>
                <td><a href="/result/{{$host->host_id}}">{{$host->amount_record}}</a></td>
                <td><a href="/rank/{{$host->host_id}}">{{$host->rank_record}}</a></td>

            </tr>
        @endforeach
        </tbody>
    </table>
    {{$hosts->links()}}

@endsection


