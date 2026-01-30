
@extends('layout')
@section('content')
<h3>Admin Panel</h3>
<table class="table">
<tr><th>ID</th><th>Poll</th><th>Option</th><th>IP</th><th>Action</th></tr>
@foreach($votes as $v)
<tr>
<td>{{ $v->id }}</td><td>{{ $v->poll_id }}</td><td>{{ $v->option_id }}</td><td>{{ $v->ip_address }}</td>
<td><button onclick="release({{ $v->id }})">Release</button></td>
</tr>
@endforeach
</table>
<h4>History</h4>
<table class="table">
@foreach($history as $h)
<tr>
<td>{{ $h->poll_id }}</td><td>{{ $h->option_id }}</td><td>{{ $h->ip_address }}</td><td>{{ $h->action }}</td><td>{{ $h->timestamp }}</td>
</tr>
@endforeach
</table>
@endsection
