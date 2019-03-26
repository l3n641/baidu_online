@extends('common')
@section('sidebar')
    <table class="table table-striped">
        <thead>
        <tr>
            <th>url</th>
            <th>keyword</th>
            <th>rank</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($ranks as $rank)
            <tr>
                <td>{{$rank->url}}</td>
                <td>{{$rank->keyword}}</td>
                <td>{{$rank->rank}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{$ranks->links()}}
@endsection