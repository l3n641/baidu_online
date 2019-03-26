@extends('common')
@section('sidebar')
    <table class="table table-striped">
        <caption>记录总数概览:{{$host->quantity}} <a href="/rank/{{$host->host_id}}">关键词排名</a></caption>
        <thead>
        <tr>
            <th>url</th>
            <th>keyword</th>
            <th>http_status</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($urls as $url)
            <tr>
                <td>{{$url->url}}</td>
                <td>{{$url->keyword}}</td>
                <td>{{$url->http_code}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{$urls->links()}}
@endsection