
@extends('layout')
@section('content')
<h3>Active Polls</h3>
<ul>
@foreach($polls as $p)
<li><a href="#" onclick="loadPoll({{ $p->id }})">{{ $p->question }}</a></li>
@endforeach
</ul>
@endsection
