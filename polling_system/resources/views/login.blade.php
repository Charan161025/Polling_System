
@extends('layout')
@section('content')
<h3>Login</h3>
<input id="email" class="form-control mb-2" placeholder="Email">
<input id="password" type="password" class="form-control mb-2" placeholder="Password">
<button onclick="login()" class="btn btn-primary">Login</button>
<p id="msg" class="text-danger"></p>
@endsection
