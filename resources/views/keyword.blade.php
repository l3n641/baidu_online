@extends('common')
@section('sidebar')
    <table class="table table-striped">
        <thead>
        <tr>
            <th>keyword</th>
            <th>排名数量</th>
            <th>rank</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($hosts as $host)
            <tr>
                <td>{{$host->keyword}}</td>
                <td><a href="{{ URL::action('SearchController@rank',['id'=>$host->host_id,'keyword'=>$host->keyword])}}">{{$host->rank_amount}}</a></td>
                <td>{{$host->ranks}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection