@extends('layout')
@section('content')
<div class="container mt-4">
    <h3>Active Polls</h3>
    <ul class="list-group">
    @foreach($polls as $p)
        <li class="list-group-item">
            <a href="#" onclick="loadPoll({{ $p['id'] }}); return false;">{{ $p['question'] }}</a>
        </li>
    @endforeach
    </ul>
</div>
@endsection
